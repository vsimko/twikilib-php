<?php
namespace ciant\apps\minutes;

use ciant\wrap\ExtractedEvent;

use twikilib\runtime\Logger;

use ciant\factory\ExtractedEventsFactory;
use ciant\factory\ExtractedEventsFilter;
use ciant\wrap\CiantProject;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use ciant\search\CiantProjects;

/**
 * @runnable
 *
 * Renders most important information about projects which is presented at regular CIANT monday meetings:
 *
 * - Current projects
 * - Current proposals before deadline
 * - CIANT activities
 * - Upcoming events for projects and proposals
 * - Open actions (from action tracker)
 *
 * TODO: split the API and do some cleanup of the code
 * TODO: use templates for rendering the results
 * TODO: use caching mechanism for rendering the results
 *
 * @author Viliam Simko
 */
class ShowMinutes {

	private $eventsByTopic = array();

	/**
	 * @return void
	 */
	public function run() {

		Logger::disableLogger();

		$twikiConfig = new Config('config.ini');
		$twikiConfig->pushStrictMode(false);
		$topicFactory = new FilesystemDB($twikiConfig);

		// Prepare extracted events
		$eventsFilter = new ExtractedEventsFilter();
		$eventsFilter->limitPastDays = 0;
		$eventsFilter->limitFutureDays = -1;
		$eventsFactory = new ExtractedEventsFactory($twikiConfig, $topicFactory, $eventsFilter);

		foreach($eventsFactory->getAllExtractedEvents() as $extractedEvent ) {
			assert($extractedEvent instanceof  ExtractedEvent);
			$this->eventsByTopic[ $extractedEvent->projectAcronym ][] = $extractedEvent;
		}

		// Prepare projects
		$search = new CiantProjects($twikiConfig, $topicFactory);
		$list = $search->getAllProjects();

		// Sort projects by "Importance" field
		usort($list, function(CiantProject $p1, CiantProject $p2) {
			$cmp = strcmp($p2->getImportance(), $p1->getImportance()); // reverse
			return $cmp == 0 ? strcmp($p1->getAcronym(), $p2->getAcronym()) : $cmp;
		});

		echo '
<style>
.twikiFormTable {
  width:100%;
}
table.twikiFormTable {
  border:solid 0px red;
  border-collapse: collapse;
  border-spacing: 0px;
}
table.twikiFormTable td {
  padding-top: 2px;
  padding-bottom: 2px;
  padding-right: 2px;
  padding-left: 10px;
  border-top:dashed 1px silver;
  border-bottom:dashed 1px silver;
}
.tdedit{width:40px;white-space:pre}
.mytab {
  border: 0;
  border-collapse: collapse;
  border-spacing: 0px;
  width:100%;
}
.mytabtd {padding-bottom: 10px}
.minutes {font-size:small}
/* ------------------------------------ */
.mytab .status_Project, .mytab .status_Proposal, .mytab .status_Reported_Project,  .status_Upcoming_Project, .status_CiantProposal {
  border: solid 1px black;
  color: white;
  font-weight: bold;
  padding: 4px;
  text-align: left;
}
.mytab .status_Project {
	background-color:#3b9e3b;
}
.mytab .status_Proposal {
	background-color:#3b9e3b;
}
.mytab .status_CiantProposal {
	background-color:#00b090;
}
.mytab .status_Reported_Project {
	background-color:#ff7e00;
}
.mytab .status_Upcoming_Project {
	background-color:#0072ff;
}
.mytab .status_CiantProposal a, .mytab .status_CiantProposal a:link, .mytab .status_CiantProposal a:visited, .mytab .status_CiantProposal a:active,
.mytab .status_Project a, .mytab .status_Project a:link, .mytab .status_Project a:visited, .mytab .status_Project a:active,
.mytab .status_Proposal a, .mytab .status_Proposal a:link, .mytab .status_Proposal a:visited, .mytab .status_Proposal a:active,
.mytab .status_Reported_Project a, .mytab .status_Reported_Project a:link, .mytab .status_Reported_Project a:visited, .mytab .status_Reported_Project a:active,
.mytab .status_Upcoming_Project a, .mytab .status_Upcoming_Project a:link, .mytab .status_Upcoming_Project a:visited, .mytab .status_Upcoming_Project a:active {
  color: white;
  font-weight: bold;
  text-decoration: none;
}
</style>

<!-- %ACTION_INCLUDE_CSS_AND_JS{}% -->
		';

		echo "\n---++ Upcoming Projects\n";
		$this->renderList( array_filter($list, function(CiantProject $project) {
			return $project->hasStatus('Project') && $project->isBeforeBegin() && ($project->getImportance() != "standby");
		}), "Upcoming_Project");
		
		echo "\n---++ Active Projects\n";
		$this->renderList( array_filter($list, function(CiantProject $project) {
			return $project->hasStatus('Project') && ! $project->isAfterReportDeadline() && !$project->isBeforeBegin() && ($project->getImportance() != "standby") || $project->isAuditOption();
		}), "Project");

		echo "\n---++ Reported Projects\n";
		$this->renderList( array_filter($list, function(CiantProject $project) {
			return $project->hasStatus('Project') && $project->isAfterReportDeadline() && ($project->getImportance() != "standby");
		}), "Reported_Project");

		echo "\n---++ Active Proposals\n";
		$this->renderList( array_filter($list, function(CiantProject $project) {
			return $project->hasStatus('Proposal') && ! $project->isAfterDeadline() && ($project->getImportance() == "\xe2\x88\x97\xe2\x88\x97\xe2\x88\x97\xe2\x88\x97\xe2\x88\x97");
		}), "CiantProposal");
		$this->renderList( array_filter($list, function(CiantProject $project) {
			return $project->hasStatus('Proposal') && ! $project->isAfterDeadline() && ($project->getImportance() != "standby") && ($project->getImportance() != "\xe2\x88\x97\xe2\x88\x97\xe2\x88\x97\xe2\x88\x97\xe2\x88\x97");
		}), "Proposal");

		$twikiConfig->popStrictMode();
	}

	private function renderList($list, $style) {
		echo "<table class='mytab'>\n";
		foreach ($list as $projectTopic) {
			assert($projectTopic instanceof CiantProject);

			$projectStatus = $projectTopic->getStatus();
			$projectStatus = str_replace(" ", "_", $projectStatus);
			echo "<tr><th class='status_$style'>\n";

				// Topic name
				$topicName = $projectTopic->getWrappedTopic()->getTopicName();
				$projectAcronym = (string) $projectTopic->getAcronym();
				$projectName = $projectAcronym.' - '.$projectTopic->getName();
				echo "[[$topicName][$projectName]]";

				// Manager
				$projectManager = $projectTopic->getManager()->getName();
				echo "&nbsp;($projectManager)";

				// Importance
				echo " ".$projectTopic->getImportance();

				if( $projectTopic->hasStatus('Project') || $projectTopic->getProposalDeadline()->isEmpty()) {
					// dates
					$beginDate = $projectTopic->getBeginDate()->getFieldValue();
					$endDate = $projectTopic->getEndDate()->getFieldValue();
					echo " / ".$beginDate.'&nbsp;-&nbsp;'.$endDate;
				} else {
					echo " / ".$projectTopic->getProposalDeadline();
				}

			echo "</th></tr>\n";
			echo "<tr><td class='mytabtd'>\n";

				// Minutes section
				echo "<div class='minutes'>\n";
					echo $projectTopic->getWrappedTopic()->getTopicTextNode()->getSectionByName('Minutes')->toWikiString();
				echo "</div>\n";

				// Extracted events
				if( isset($this->eventsByTopic[$projectAcronym]) ) {

					// Sort events by begin date (from past to future)
					usort($this->eventsByTopic[$projectAcronym],
						function(ExtractedEvent $e1, ExtractedEvent $e2) {
							return strcmp( $e1->getBeginDateISO(), $e2->getBeginDateISO() );
						});

					echo "<b>Upcoming events:</b><br/>\n";
					foreach ($this->eventsByTopic[$projectAcronym] as $e) {
						assert($e instanceof  ExtractedEvent);
						echo "   * ";
						echo $e->visibleInCalendar ? '' : 'INVISIBLE: ';
						echo $e->beginDate; // Begin Date

						if ($e->endDate) {
							echo " - ";
							echo $e->endDate; // End Date
						}

						echo " - ";
						echo $e->title; // Event Title
						echo ", see ".$e->relatedTopic;
						echo "\n";
					}
				}

				// Actions
				$justTopicName = $projectTopic->getWrappedTopic()->getConfig()->parseTopicName($topicName)->topic;
	    		echo "%ACTION_LIST{web=\"Main\" topic=\"ActionTrackerActions\" section=\"$justTopicName\"}%";
	    		echo "%ACTION_ADD{section=\"$justTopicName\"}%";

	    	echo "</th></tr>\n";
		}
		echo "</table>\n";
	}
}