<?php
namespace ciant\tools\listusers;

use twikilib\core\MetaSearch;
use twikilib\runtime\Logger;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @runnable
 * @author Viliam Simko
 */
class ListUsers {
	final public function run() {
		header('Content-type: text/plain');
		$config = new Config('config.ini');
		$config->pushStrictMode(false);
		$db = new FilesystemDB($config);

		$search = new MetaSearch($config);
		$search->setFormNameFilter('UserForm');
		$search->executeQuery();

		Logger::log("BankAccount");
		array_map(function($topicName) use ($db) {
			$topic = $db->loadTopicByName($topicName);
			$bankAccountField = $topic->getTopicFormNode()->getFormField('BankAccount');
			if(! $bankAccountField->isEmpty()) {
				Logger::log(' - '.$topicName);
			}
		}, $search->getResults());

		Logger::log("PassportInfo");
		array_map(function($topicName) use ($db) {
			$topic = $db->loadTopicByName($topicName);
			$bankAccountField = $topic->getTopicFormNode()->getFormField('PassportInfo');
			if(! $bankAccountField->isEmpty()) {
				Logger::log(' - '.$topicName);
			}
		}, $search->getResults());
	}
}