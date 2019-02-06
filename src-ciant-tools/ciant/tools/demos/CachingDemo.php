<?php
namespace ciant\tools\demos;

use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use twikilib\core\ResultCache;

/**
 * @runnable
 * @author Viliam Simko
 */
class CachingDemo {

	static final public function odd($v) {
		return ($v % 2);
	}

	/**
	 * @var ResultCache
	 */
	private $cache;

	public function run() {
		header('Content-type: text/plain');

		$config = new Config('config.ini');
		//$config->disableCaching();

		$db = new FilesystemDB($config);
		$this->cache = new ResultCache($config, $db);

		echo "Demonstrates caches in series (2x cache read):\n";
		echo "----------------------------------------------\n";
		$this->demoCachesInSeries();
		echo "\n";

		echo "Demonstrates nested caches (1x cache read):\n";
		echo "-------------------------------------------\n";
		$this->demoNestedCaches();
		echo "\n";
	}

	final public function demoCachesInSeries() {

		// caches values 1..9
		$data1 = $this->cache->getCachedData(function() {
			return array(1,2,3,4,5,6,7,8,9);
		});

		// takes only odd values from the previous list
		$data2 = $this->cache->getCachedData(function() use ($data1) {
			return array_filter($data1, 'ciant\tools\demos\CachingDemo::odd');
		});

		print_r($data2);
	}

	final public function demoNestedCaches() {

		$cache = $this->cache;

		$data2 = $cache->getCachedData(function() use ($cache) {

			// caches values 1..9
			$data1 = $cache->getCachedData(function() {
				return array(1,2,3,4,5,6,7,8,9);
			});

			// takes only odd values from the list
			return array_filter($data1, 'ciant\tools\demos\CachingDemo::odd');
		});

		print_r($data2);
	}
}
