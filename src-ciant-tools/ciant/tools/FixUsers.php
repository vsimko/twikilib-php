<?php
namespace ciant\tools;

use ciant\search\CheckUsers;
use twikilib\runtime\Container;
use twikilib\runtime\Logger;
use twikilib\core\ITopic;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use \Exception;

/**
 * @runnable
 * @author Viliam Simko
 */
class FixUsers {

	private $performSave = false;

	public function __construct($params) {
		if(@$params['help'])
			throw new Exception("Parameters: save (optional, default:false)");

		if(@$params['save'] === '' || @$params['save'] == 'true' || @$params['save'] == 1)
			$this->performSave = true;
	}

	public function run() {

		header('Content-type: text/plain');

		$twikiConfig = new Config('config.ini');
		$twikidb = new FilesystemDB($twikiConfig);
		$checker = new CheckUsers($twikiConfig, $twikidb);
		// ==============================================================
		Logger::log("Fixing users with wrong VIEW_TEMPLATE:");
		$listTopics = $checker->getUsersWithWrongViewTemplate();

		Container::measureTime('Fixing');
		foreach($listTopics as $topic) {
			assert($topic instanceof ITopic);
			$topicName = $topic->getTopicName();
			Logger::log( "  - $topicName" );

			if($this->performSave) {
				// fix user view template
				$pref = PrefsFactory::createViewTemplatePref('UserView');
				$topic->getTopicPrefsNode()->deletePreferencesByName('VIEW_TEMPLATE');
				$topic->getTopicPrefsNode()->addPreference($pref);
				$twikidb->saveTopic($topic);
			}
		}
		Container::measureTime();

		// ==============================================================
		Logger::log("Fixing non-user topics with VIEW_TEMPLATE=UserView:");
		$listTopics = $checker->getNonUserTopicsWithViewTemplate();
		Container::measureTime('Fixing');
		foreach ($listTopics as $topic) {
			assert($topic instanceof ITopic);
			$topicName = $topic->getTopicInfoNode()->getTopicName();
			Logger::log( "  - $topicName" );

			if($this->performSave) {
				$topic->getTopicPrefsNode()->deletePreferencesByName('VIEW_TEMPLATE');
				//$twikidb->moveTopicToWeb($topic, '_empty');
				$twikidb->saveTopic($topic);
			}
		}
		Container::measureTime();
		// ==============================================================
	}
}