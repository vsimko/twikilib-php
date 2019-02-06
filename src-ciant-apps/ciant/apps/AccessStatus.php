<?php
namespace ciant\apps;

use twikilib\utils\Encoder;

use twikilib\fields\Preference;
use twikilib\runtime\Logger;

use twikilib\core\PrefsFactory;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use twikilib\core\TopicNotFoundException;

use \Exception;

/**
 * @runnable
 * Shows the content of the ALLOWTOPICVIEW preference field when applicable.
 * Otherwise nothing will be rendered.
 * @author Viliam Simko
 */
class AccessStatus {

	/**
	 * @var string
	 */
	private $topicName;

	public function __construct($params) {
		$this->topicName = @$params['topic'];
		if(empty($this->topicName))
			throw new Exception("Undefined parameter: topic");
	}

	final public function run() {
		Logger::disableLogger();

		$twikiConfig = new Config('config.ini');
		$topicFactory = new FilesystemDB($twikiConfig);

		try {
			$topic = $topicFactory->loadTopicByName($this->topicName);

			//print_r($topic->getTopicPrefsNode()->getAllPreferences());
			$pref = $topic->getTopicPrefsNode()->getFirstPreferenceByName( PrefsFactory::PREF_ALLOWTOPICVIEW );

			if($pref instanceof Preference) {
				echo '<div style="border: solid 4px red; padding: 4px">';
				echo "<b>Shared with:</b> ";

				$allowedList = Encoder::extractWikiNamesFromString( $pref->getValue() );
				$allowedList = array_diff($allowedList, array('Main.CiantGroup', 'CiantGroup'));
				echo implode(', ', $allowedList);

				echo '</div>';
			}
		} catch (TopicNotFoundException $e) {
			// do nothing
		}
	}
}