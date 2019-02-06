<?php
namespace ciant\apps;

use ciant\wrap\CiantProject;
use ciant\search\CiantProjects;
use twikilib\core\ResultCache;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use twikilib\utils\Encoder;
use twikilib\runtime\Logger;

/**
 * @runnable
 * @author Viliam Simko
 */
class AllProjects {
	public function __construct() {
		Logger::disableLogger();
	}

	public function run() {
		$config = new Config('config.ini');
		$db = new FilesystemDB($config);

		$cache = new ResultCache($config, $db);
		echo $cache->getCachedData( function() use ($config, $db) {

			$lister = new CiantProjects($config, $db);

			$list = array_map( function(CiantProject $projectTopic) use ($config, $db) {
				return Encoder::createSelectValueItem(
					$projectTopic->getAcronym().' - '.$projectTopic->getName(),
					$projectTopic->getWrappedTopic()->getTopicName() );
			} , $lister->getAllProjects());

			usort($list, 'strcmp');

			return implode(', ', $list);
		});
	}
}