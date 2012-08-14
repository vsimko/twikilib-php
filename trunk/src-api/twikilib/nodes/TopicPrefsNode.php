<?php
namespace twikilib\nodes;

use twikilib\fields\Preference;
use twikilib\utils\Encoder;
use twikilib\core\IParseNode;

/**
 * @author Viliam Simko
 */
class TopicPrefsNode implements IParseNode {

	/**
	 * Cloning not allowed for this class.
	 */
	final private function __clone() {}

	/**
	 * @var array
	 */
	private $preferences = array();

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IParseNode::getPattern()
	 */
	final public function getPattern() {
		return '/%META:PREFERENCE\{(.*)\}%\n/';
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IParseNode::onPatternMatch()
	 */
	final public function onPatternMatch(array $match) {
		$parsedArgs = Encoder::parseWikiTagArgs( $match[1] );
		$this->preferences[] = new Preference($parsedArgs);
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IRenderable::toWikiString()
	 */
	final public function toWikiString() {
		return Encoder::arrayToWikiString($this->preferences);
	}

	/**
	 * @return array An array of Preference objects
	 */
	final public function getAllPreferences() {
		return $this->preferences;
	}

	/**
	 * First preference matching the name or null.
	 * @param string $prefName
	 * @return Preference or null
	 */
	final public function getFirstPreferenceByName($prefName) {
		$result = array();
		foreach ($this->preferences as $pref) {
			assert($pref instanceof Preference);
			if( $pref->getName() == $prefName )
				return $pref;
		}
		return null;
	}

	/**
	 * Deletes all preferences matching the given name.
	 * @param string $prefNameToDelete
	 */
	final public function deletePreferencesByName($prefNameToDelete) {
		assert( !empty($prefNameToDelete) );
		assert( is_string($prefNameToDelete) );

		foreach($this->preferences as $index => $pref) {
			assert($pref instanceof Preference);
			if($pref->getName() == $prefNameToDelete) {
				unset( $this->preferences[$index] );
			}
		}
	}

	/**
	 * Add a preference to the list of preferences for the given topic.
	 * @param Preference $pref
	 */
	final public function addPreference(Preference $pref) {
		$this->preferences[] = $pref;
	}

//	final public function getPreferencesByNameRegex($nameRegex) {
//		$result = array();
//		foreach ($this->preferences as $pref) {
//			assert($pref instanceof Preference);
//			if( preg_match("/$nameRegex/", $pref->getName()) ) {
//				$result[] = $pref;
//			}
//		}
//		return $result;
//	}
}