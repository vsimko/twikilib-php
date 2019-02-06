<?php
namespace ciant\tools\demos;

use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use twikilib\core\ResultCache;
use ciant\wrap\CiantEvent;

/**
 * @runnable
 * @author Viliam Simko
 */
class EventPokus {

	public function run() {
		$config = new Config('config.ini');
		$db = new FilesystemDB($config);
		
		$eventname = "Main.ConnextKickoff";
		$ciantevent = $db->loadTopicByName($eventname);
		$cianteventopic = new CiantEvent($ciantevent);
		print_r($cianteventopic);
	}
}
