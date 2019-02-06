<?php
namespace ciant\apps;

use twikilib\runtime\Logger;
use ciant\wrap\CiantUser;
use twikilib\core\MetaSearch;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use \Exception;

/**
 * @runnable
 * @author Viliam Simko
 */
class ExtractMails {

	private $topicName;

	public function __construct($params) {
		$this->topicName = @$params['topic'];
		if( empty($this->topicName) )
			throw new Exception("Undefined parameter: topic");
	}

	public function run() {
		Logger::disableLogger();
		$config = new Config('config.ini');
		$db = new FilesystemDB($config);

		$topic = $db->loadTopicByName($this->topicName);
		$topicContent = $topic->toWikiString();

		// get all users from the system
		$search = new MetaSearch($config);
		$search->setFormNameFilter('UserForm');
		$search->executeQuery();

		$listUserNames = array_map(
			function($topicName) use ($config) {
				$parsedTopicName = $config->parseTopicName($topicName);
				return $parsedTopicName->topic;
			} , $search->getResults() );

		sort($listUserNames);

		// remove names that should be skipped
		$skipNames = @$params['skip'].':'.@$params['user'];
		$skipNames = (array) preg_split('/[,: ]\s*/', $skipNames);
		$skipNames = array_filter($skipNames); // remove empty elements
		$listUserNames = array_diff($listUserNames, $skipNames);

		$regex = '/'.implode('|',$listUserNames).'/';
		$match = array();
		preg_match_all($regex, $topicContent, $match);
		$listUserNames = $match[0];
		sort($listUserNames);
		$listUserNames = array_unique($listUserNames);

		$users = array();
		foreach($listUserNames as $userName)
		{
			$topic = $db->loadTopicByName($userName);
			$user = new CiantUser($topic);

			$config->pushStrictMode(false);
			try {
				$users[$userName] = array(
					'name'	=> $user->getName(),
					'email'	=> $user->getAllEmails(1),
				);
			} catch(Exception $e) {
				echo $userName.':'.$e->getMessage()."<br/>\n";
			}
			$config->popStrictMode();
		}
		include 'ciant/tpl/ExtractMails.tpl.php';
	}
}