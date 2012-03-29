<?php
namespace twikilib\nodes;

use twikilib\fields\TextSection;
use twikilib\fields\Table;
use twikilib\core\IRenderable;

/**
 * Represents the visible part of a topic text.
 * @author Viliam Simko
 */
class TopicTextNode implements IRenderable {

	/**
	 * Cloning not allowed for this class.
	 */
	final private function __clone() {}

	/**
	 * @var string
	 */
	private $text = '';

	/**
	 * Replaces the content of the text node.
	 * @param string $newText
	 */
	final public function setText($newText) {
		assert( is_string($newText) );
		$this->text = $newText;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IRenderable::toWikiString()
	 */
	final public function toWikiString() {
		return rtrim($this->text)."\n";
	}

	/**
	 * Extracts all variables set inside the topic text.
	 * In TWiki, local variables can using: '   * Set VARNAME = value' notation.
	 * @return array
	 */
	final public function getLocalVariablesFromText() {

		$result = array();

		if( preg_match_all('/\n(   )+\* Set ([a-zA-Z0-9_]+)\s*=\s*(.*)\n/', $this->text, $match) ) {
			foreach( array_keys($match[2]) as $idx) {
				$result[] = (object) array(
					'name'	=> $match[2][$idx],
					'value'	=> $match[3][$idx] );
			}
		}

		return $result;
	}

	/**
	 * Extract all tables from the topic text.
	 * Only the simple table format is supported:
	 *
	 * | *header* | *header* | *...* |
	 * |   cell   |   cell   |  ...  |
	 * |   cell   |   cell   |  ...  |
	 * ...
	 *
	 * @return array of Table
	 */
	final public function getTablesFromText() {
		$tables = array();

		// splitting the text by continuous table definitions
		// the "\n" added at the beginning of the text is needed when the first table starts
		// from the very beginning of the text
		if( preg_match_all('/\n\h*\|[^\n]*\||\n/', "\n".$this->text, $match) ) {

			$tableData = array();
			foreach($match[0] as $row) {
				$row = trim($row);

				if($row) {
					$tableData[] = $row;
				} elseif( !empty($tableData) ) {
					$tables[] = new Table($tableData);
					$tableData = array();
				}
			}

			if( $tableData ) {
				$tables[] = new Table($tableData);
			}
		}

		return $tables;
	}

	/**
	 * Helper recursive function.
	 * Builds a tree of sections depending on their nesting level.
	 *
	 * @param integer $mylevel
	 * @param string $myname
	 * @param string $mytext
	 * @param array $list list to be reduced
	 *
	 * @return array root node of the tree
	 */
	private function buildTree($mylevel, $myname, $mytext, &$list) {
		$children = array();

		while( !empty($list) ) {
			list($level, $name, $text) = reset($list); // first element of the list

			if($mylevel < $level) {
				array_shift($list);
				$children[] = $this->buildTree($level, $name, $text, $list);
			} else
				break;
		}

		return array($mylevel, $myname, $mytext, $children);
	}

	/**
	 * Helper recursive function
	 * @param array $node root of a tree
	 * @param array $collectedSections the list built during the recursion
	 */
	private function resolveNode(&$node, &$collectedSections) {
		$level = &$node[0];
		$name = &$node[1];
		$text = &$node[2];
		$children = &$node[3];

		$section = new TextSection($name, '');
		$collectedSections[] = $section;

		for($i=0; $i<count($children); ++$i) {
			$text .= "\n".$this->resolveNode($children[$i], $collectedSections);
		}

		$section->setText($text);

		return '---'.str_repeat('+', $level)." $name\n$text\n";
	}

	/**
	 * TODO: after updating a section, the changes are not propagated to the topic text
	 * @return array of TextSection
	 */
	final public function getTextSections() {
		$possibleSections = preg_split(
			'/\n---([\+]{1,10})(.*)\n/', "\n".$this->text, -1, PREG_SPLIT_DELIM_CAPTURE);

		// flat view
		$list = array();
		for($i=1; $i<count($possibleSections); $i+=3) {
			$sectionLevel = strlen( $possibleSections[$i] );
			$sectionName = trim($possibleSections[$i+1]);
			$sectionText = $possibleSections[$i+2];

			$list[] = array($sectionLevel, $sectionName, $sectionText);
		}

		$tree = $this->buildTree(0, '', '', $list);

		$collectedSections = array();
		$this->resolveNode($tree, $collectedSections);
		return $collectedSections;

	}

	/**
	 * Indicates whether the last replaceText operation changed the topic text.
	 * You need to manually reset this value to false.
	 * @var boolean
	 */
	public $isTextChanged = false;

	/**
	 * Replaces some text within the topic topic text based on a regular expression.
	 * This can be used for appending, updating or deleting content of a topic.
	 * When the applied replacement has changed the text, a flag $isTextChanged is
	 * set to true.
	 *
	 * Note: Newlines are treated in a special way. Make sure you use the right
	 * pattern modifier.
	 *
	 * Examples:
	 * - append text at the end of the whole text: /$/D
	 * - append text at the end of each line: /$/m
	 * - prepend text before the whole text: /^/
	 * - replace the whole text: /^.*$/D
	 *
	 * @see http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
	 * @param string $regexPattern
	 * @param string $replacementTextOrCallable
	 * @return void
	 */
	final public function replaceText($regexPattern, $replacementTextOrCallable) {
		assert(is_string($regexPattern));
		assert(is_string($replacementTextOrCallable) || is_callable($replacementTextOrCallable));

		$newText = is_callable($replacementTextOrCallable)
			? preg_replace_callback($regexPattern, $replacementTextOrCallable, $this->text)
			: preg_replace($regexPattern, $replacementTextOrCallable, $this->text);

		if($newText != $this->text) {
			$this->text = $newText;
			$this->isTextChanged = true;
		}
	}

	/**
	 * @param string $sectionName
	 * @return twikilib\fields\TextSection or null if the section does not exist
	 */
	final public function getSectionByName($sectionName) {
		foreach($this->getTextSections() as $section) {
			assert($section instanceof TextSection);
			if($sectionName == $section->getSectionName())
				return $section;
		}
		return null;
	}

	/**
	 * @param string $slotId
	 * @return string
	 */
	private function hashSlotId($slotId) {
		return md5($slotId);
	}

	/**
	 * Creates a value surrounded by a token which can be easily
	 * identified within the topic text and replaced.
	 * The surrounding tokens will not be visible to the user.
	 *
	 * @param string $slotId
	 * @param string $initialValue
	 * @return string
	 */
	final public function createSlot($slotId, $initialValue='') {
		assert(is_string($slotId));
		assert(!empty($slotId));
		assert(is_string($initialValue));

		$hash = $this->hashSlotId($slotId);
		return "<!--$hash-->$initialValue<!--$hash-->";
	}

	/**
	 * Changes the value of a given slot which was previously
	 * created by the createSlot() method.
	 * The value can be replaced by a new value or it can be
	 * processed by a callback/lambda function:
	 *
	 * <code>
	 * function($matches) {
	 *   return dosomething( $matches['value'] );
	 * }
	 * </code>
	 *
	 * @param string $slotId
	 * @param mixed $valueOrCallable
	 */
	final public function updateSlot($slotId, $valueOrCallable) {
		assert(is_string($slotId));
		assert(!empty($slotId));
		assert(is_string($valueOrCallable) || is_callable($valueOrCallable));

		$hash = $this->hashSlotId($slotId);

		$this->replaceText(
			"/(?P<begin><!--$hash-->)(?P<value>.*?)(?P<end><!--$hash-->)/Ds", // (.*?) = non-greedy
			is_callable($valueOrCallable)
			? function($matches) use ($valueOrCallable) {
				return $matches['begin'].$valueOrCallable($matches).$matches['end'];
			}
			: '$1'.$valueOrCallable.'$3'
		);
	}

	/**
	 * @param string $slotId
	 * @return void
	 */
	final public function removeSlot($slotId) {
		assert(is_string($slotId));
		assert(!empty($slotId));

		$hash = $this->hashSlotId($slotId);

		$this->replaceText(
			"/<!--$hash-->(.*?)<!--$hash-->/Ds", // (.*?) = non-greedy
			'$1' // just remove the surrounding hashes
		);
	}
}