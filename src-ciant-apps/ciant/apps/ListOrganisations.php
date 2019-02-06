<?php
namespace ciant\apps;

use twikilib\runtime\Logger;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use twikilib\core\ResultCache;
use twikilib\utils\Encoder;
use ciant\wrap\CiantOrg;
use ciant\search\Organisations;

/**
 * @runnable
 * @author Viliam Simko
 */
class ListOrganisations {
	public function run() {
		Logger::disableLogger();

		$config = new Config('config.ini');
		$db = new FilesystemDB($config);

		$cache = new ResultCache($config, $db);

		echo $cache->getCachedData( function() use ($config, $db) {
			$lister = new Organisations($config, $db);

			$config->pushStrictMode(false);
			$result = array_map(
				function(CiantOrg $org) use ($config) {
					$topicName = $org->getWrappedTopic()->getTopicName();
					$parsedTopicName = $config->parseTopicName($topicName);

					return Encoder::createSelectValueItem(
						$org->getQualifiedName(), $parsedTopicName->topic );

				}, $lister->getAllOrganisations() );
			$config->popStrictMode();

			sort($result);

			return implode(", ", $result);
		}, $config->language);
	}
}