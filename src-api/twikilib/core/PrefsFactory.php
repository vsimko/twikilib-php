<?php
namespace twikilib\core;

use twikilib\fields\Preference;

/**
 * @author Viliam Simko
 */
class PrefsFactory {

	const PTYPE_LOCAL = 'Local';
	const PTYPE_SET = 'Set';
	
	const PREF_VIEW_TEMPLATE = 'VIEW_TEMPLATE';
	const PREF_ALLOWTOPICVIEW = 'ALLOWTOPICVIEW';
	const PREF_ALLOWTOPICCHANGE = 'ALLOWTOPICCHANGE';

	/**
	 * Creates an instance of Preference that represents
	 * a VIEW_TEMPLATE with a given template name.
	 * 
	 * @param string $templateName
	 * @return Preference
	 */
	final static public function createViewTemplatePref($templateName) {
		return new Preference( array(
			'name'	=> self::PREF_VIEW_TEMPLATE,
			'title'	=> self::PREF_VIEW_TEMPLATE,
			'type'	=> self::PTYPE_LOCAL,
			'value'	=> $templateName
		));
	}
	
	/**
	 * Creates an instance of Preference that represents
	 * an ALLOWTOPICVIEW preference.
	 * 
	 * @param array $listOfTopicNames
	 * @return Preference
	 */
	final static public function createAllowTopicViewPref( array $listOfTopicNames ) {
		return new Preference( array(
			'name'	=> self::PREF_ALLOWTOPICVIEW,
			'title'	=> self::PREF_ALLOWTOPICVIEW,
			'type'	=> self::PTYPE_LOCAL,
			'value'	=> implode(', ', $listOfTopicNames)
		));
	}

	/**
	 * Creates an instance of Preference that represents
	 * an ALLOWTOPICCHANGE preference.
	 * 
	 * @param array $listOfTopicNames
	 * @return Preference
	 */
	final static public function createAllowTopicChangePref( array $listOfTopicNames ) {
		return new Preference( array(
			'name'	=> self::PREF_ALLOWTOPICCHANGE,
			'title'	=> self::PREF_ALLOWTOPICCHANGE,
			'type'	=> self::PTYPE_LOCAL,
			'value'	=> implode(', ', $listOfTopicNames)
		));
	}
}
?>