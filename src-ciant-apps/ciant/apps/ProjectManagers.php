<?php
namespace ciant\apps;

use twikilib\runtime\Logger;
use twikilib\core\ResultCache;
use ciant\wrap\CiantUser;
use ciant\search\CiantMembers;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @runnable
 * Generates a list of all users that are either members of CiantGroup or
 * that listed as managers of some project.
 * The generated list contains a comma-separated items in format "User name=Web.TopicName".
 *
 * This script was intended to be used in form definitions such as:
 * - Inventory.DvdForm
 * - Main.ProjectForm
 * - Main.ActivityForm
 *
 * @author Viliam Simko
 */
class ProjectManagers {
	public function run() {
		Logger::disableLogger();

		$config = new Config('config.ini');
		//$config->disableCaching();

		$db = new FilesystemDB($config);

		$cache = new ResultCache($config, $db);
		echo $cache->getCachedData( function() use ($config, $db) {
			$ciantMembers = new CiantMembers($config, $db);
			$config->pushStrictMode(false);
			$results = array_map(
				function(CiantUser $managerTopic) use ($config) {
					$topicName = $managerTopic->getWrappedTopic()->getTopicName();
					return $managerTopic->getName().'='.$topicName;
				}, $ciantMembers->getProjectManagers());
			$config->popStrictMode();
			sort($results);
			return implode(', ', $results);
		});
	}
}