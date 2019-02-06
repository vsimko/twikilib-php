<?php
namespace ciant\tools;

use twikilib\runtime\Logger;

use twikilib\core\TopicNotFoundException;
use ciant\wrap\CiantProject;
use ciant\search\CiantProjects;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @runnable
 * @author Viliam Simko
 */
class FixManagerFieldInProjects {
	public function run() {

		header('Content-type: text/plain');

		$config = new Config('config.ini');
		$db = new FilesystemDB($config);

		$config->pushStrictMode(false);

		$lister = new CiantProjects($config, $db);
		foreach($lister->getAllProjects() as $project) {
			assert($project instanceof CiantProject);
			$topic =  $project->getWrappedTopic();

			$managerField = $topic->getTopicFormNode()->getFormField('Manager');
			$oldValue = $managerField->getFieldValue();
			try {
				$newValue =  $db->loadTopicByName($oldValue)->getTopicName();

				if($oldValue != $newValue) {
					Logger::log("Changing: $oldValue to $newValue in ".$topic->getTopicName());
					$managerField->setFieldValue($newValue);
					$db->saveTopic($topic);
				}
			} catch(TopicNotFoundException $e) {
				Logger::logWarning( $e->getMessage()." in ". $topic->getTopicName() );
			}
		}
	}
}