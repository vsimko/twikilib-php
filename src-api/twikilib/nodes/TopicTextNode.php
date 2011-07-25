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
	 * TODO: tree structure of sections instead of a list
	 * @return array of TextSection
	 */
	final public function getTextSections() {
		$possibleSections = preg_split(
			'/\n---([\+]{1,6})(.*)\n/', "\n".$this->text, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		$sections = array();
		for($i=1; $i<count($possibleSections); $i+=3) {
			$sectionLevel = strlen( $possibleSections[$i] );
			$sectionName = trim($possibleSections[$i+1]);
			$sectionText = $possibleSections[$i+2];
			$sections[] = new TextSection($sectionName, $sectionText);
		}
		return $sections;
	}
	
	/**
	 * Indicates whether the last replaceText operation changed the topic text.
	 * You need to manually reset this value to false.
	 * @var boolean
	 */
	public $isTextChanged = false;
	
	/**
	 * Appends some text at the end of the existing topic text.
	 * @param string $regexPattern
	 * @param string $replacementText
	 * @return void
	 */
	final public function replaceText($regexPattern, $replacementText) {
		$newText = preg_replace($regexPattern, $replacementText, $this->text);
		
		if($newText != $this->text) {
			$this->text = $newText;
			$this->isTextChanged = true;
		}
	}
	
	/**
	 * @param string $sectionName
	 * @return twikilib\fields\TextSection
	 */
	final public function getSectionByName($sectionName) {
		foreach($this->getTextSections() as $section) {
			assert($section instanceof TextSection);
			if($sectionName == $section->getSectionName())
				return $section;
		}
		return null;
	}
}
?>