<?php
namespace ciant\tools;

use twikilib\runtime\Logger;
use ciant\wrap\CiantEvent;
use ciant\search\CiantEvents;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @runnable
 * @deprecated
 * @author Viliam Simko
 */
class FixEvents {

	public function run() {
		header('Content-type: text/plain');

		$config = new Config('config.ini');
		$db = new FilesystemDB($config);

		$lister = new CiantEvents($config, $db);

		array_map(function(CiantEvent $event) use ($db) {
			$topic = $event->getWrappedTopic();
			$coordinatorField = $topic->getTopicFormNode()->getFormField('Coordinator');

			Logger::log( $coordinatorField->getFieldValue() ) ;

		}, $lister->getAllEvents());
	}
}