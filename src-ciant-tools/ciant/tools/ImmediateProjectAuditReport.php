<?php
namespace ciant\tools;

use twikilib\runtime\Logger;
use twikilib\utils\Encoder;
use twikilib\form\fields\DateField;
use ciant\factory\ProjectFactory;
use ciant\wrap\CiantOrg;
use ciant\wrap\CiantProject;
use ciant\wrap\CiantEvent;
use ciant\search\CiantEvents;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @deprecated
 * @runnable
 * Requested by Karolina Broskova 2011-04-12
 * List of EC funded projects for the last five years
 * (Project Name, Number, Begin-End, Partners, Funding Programme)
 *
 * Note: this script was executed only once and its output was saved to
 * topic Main.ImmediateAuditEventsSince2006
 *
 * @author Viliam Simko
 */
class ImmediateProjectAuditReport {
	public function run() {
		header('Content-type: text/plain');
		Logger::disableLogger();

		$config = new Config('config.ini');
		$config->pushStrictMode(false);
		$db = new FilesystemDB($config);

		$lister = new CiantEvents($config, $db);
		$projectFactory = new ProjectFactory($db);

		echo "| *Event* | *Project* | *Project Nr.* | *Proj. Begin* | *Proj End.* | *Funding Prog.* | *Partners* |\n";
		foreach($lister->getAllEvents() as $event) {
			assert($event instanceof CiantEvent);

			$eventTopic = $event->getWrappedTopic();

			$project = $projectFactory->getNearestProjectFromTopic($eventTopic);
			assert($project instanceof CiantProject);

			$eventTitle = $event->getTitle()->getFieldValue();
			$eventTopicName = $eventTopic->getTopicName();
			$projectName = $project->getName()->getFieldValue();
			$projectTopicName = $project->getWrappedTopic()->getTopicName();
			$projectNumber = $project->getProjectNumber()->getFieldValue();
			$beginDate = $project->getBeginDate()->getISOFormat();
			$endDate = $project->getEndDate()->getISOFormat();
			$fundingProgramme = $project->getFundingProgramme()->getFieldValue();
			$partners = Encoder::createSingleLineText($project->getWrappedTopic()->getTopicFormNode()->getFormField('Co-organisers')->getFieldValue());

			if(
				preg_match('/^\s*EC/', $fundingProgramme) &&
				$endDate > '2006-01-01'
			) {
				echo "| [[$eventTopicName][$eventTitle]] | [[$projectTopicName][$projectName]] | $projectNumber | $beginDate | $endDate | $fundingProgramme | $partners |\n";
			}
		}
	}
}