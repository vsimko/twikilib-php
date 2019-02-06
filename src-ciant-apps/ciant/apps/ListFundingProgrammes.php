<?php
namespace ciant\apps;

use twikilib\runtime\Logger;
use twikilib\core\ITopic;
use twikilib\core\ResultCache;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use ciant\search\FundingProgrammes;

/**
 * @runnable
 * @author Viliam Simko
 */
class ListFundingProgrammes {

	public function run() {
		Logger::disableLogger();

		$config = new Config('config.ini');
		//$config->cacheLifetimeSeconds = 0;
		$db = new FilesystemDB($config);

		$cache = new ResultCache($config, $db);
		echo $cache->getCachedData(function() use ($config, $db) {
			$lister = new FundingProgrammes($config, $db);

			$config->pushStrictMode(false);
			$list = array_map(
				function (ITopic $topic) {
					$category = $topic->getTopicFormNode()->getFormField('Category')->getFieldValue();
					$category = preg_replace('/[^a-zA-Z0-9]/', '', $category);

					$fundProgrammeName = $topic->getTopicFormNode()->getFormField('Name')->getFieldValue();
					$fundProgrammeName = preg_replace('/[^a-zA-Z0-9]/', '', $fundProgrammeName);

					$topicName = $topic->getTopicName();
					return "$category : $fundProgrammeName=$category : $topicName";
				}, $lister->getAll() );
			$config->popStrictMode();

			return implode(', ', $list);
		});
	}
}