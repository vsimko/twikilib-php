<?php
namespace ciant\tools;

use twikilib\runtime\Logger;
use ciant\wrap\CiantProject;
use ciant\wrap\CiantEvent;
use ciant\search\CiantEvents;
use ciant\factory\ProjectFactory;
use twikilib\core\ResultCache;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use twikilib\utils\Encoder;

/**
 * @runnable
 * @author Viliam Simko
 */
class ListPublicEvents {

	public function __construct() {
		Logger::disableLogger();
	}

	public function run() {
		$config = new Config('config.ini');
		$db = new FilesystemDB($config);

		echo "<table border='1'>\n";
//		echo "|*Name*|*Begin*|*End*|*Venue*|*Abstract*|\n";

		$projectFactory = new ProjectFactory($db);
		$project = $projectFactory->getNearestProjectFromTopicName('Reset2011Project');
		$project->getAllChildEvents();

		//$lister = new CiantEvents($config, $db);
		//foreach( $lister->getAllEvents() as $event) {
		foreach( $project->getAllChildEvents() as $event ) {
			assert($event instanceof CiantEvent);
			echo	'<tr>'.
					'<td>'.$event->getTitle().'</td>'.
					'<td>'.$event->getBeginDate().'</td>'.
					'<td>'.$event->getEndDate().'</td>'.
					'<td>'.$event->getVenueAsText().'</td>'.
					'<td>'.Encoder::createSingleLineText($event->getAbstract()).'</td>'.
					"</tr>";
		}
		echo "</table>\n";
	}
}