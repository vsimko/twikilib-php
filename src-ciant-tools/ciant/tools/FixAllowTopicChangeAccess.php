<?php
namespace ciant\tools;

use twikilib\core\PrefsFactory;
use twikilib\core\MetaSearch;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @runnable
 * @author Viliam Simko
 */
class FixAllowTopicChangeAccess {
	final public function run() {

		$twikiConfig = new Config('config.ini');
		$topicFactory = new FilesystemDB($twikiConfig);

		$search = new MetaSearch($twikiConfig);
		$search->setPreferenceFilter( PrefsFactory::PREF_ALLOWTOPICVIEW, '.*');
		//$search->setWebNameFilter('Main');
		$search->executeQuery();

		foreach($search->getResults() as $topicName) {
			$topic = $topicFactory->loadTopicByName($topicName);
			$pref = $topic->getTopicPrefsNode()->getFirstPreferenceByName( PrefsFactory::PREF_ALLOWTOPICVIEW );
			echo $topicName.' : '.$pref->getValue()."\n";
		}

		//TODO: apply the fix
	}
}