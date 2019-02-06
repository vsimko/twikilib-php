<?php
namespace ciant\apps;

use twikilib\runtime\Logger;
use ciant\wrap\CiantEvent;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use twikilib\core\ResultCache;
use ciant\wrap\CiantProject;
use ciant\wrap\CiantWrapFactory;
use \Exception;

/**
 * @runnable
 *
 * Generates a list of calendar entries representing all child event of a givent project.
 * This script should be used within a project-topic in a dedicated section e.g. "Events".
 * User-specified calendar entires can be attached after (or before) the generated list.
 * This way, generated and user-specified events can be mixed in a calendar that uses
 * the project-topic as a source of calendar entries.
 *
 * INPUT: CiantProject (a topic with ProjectForm attached)
 * OUTPUT: List of calendar entries parseable by the CalendarPlugin
 *
 * Notes:
 * - An event in this regard is a topic with EventForm attached.
 * - Child-events are transitively reachable events from a given project Project->Event->Event...
 * - Design of the generated output can be changed in a separate template file (see the source code)
 *
 * @author Viliam Simko
 */
class ProjectCalEvents {

	private $topicName;

	public function __construct($params) {
		$this->topicName = @$params['topic'];
		if(empty($this->topicName))
			throw new Exception("Undefined parameter: topic");
	}

	final public function run() {
		Logger::disableLogger();

		$config = new Config('config.ini');
		$db = new FilesystemDB($config);
		$cache = new ResultCache($config, $db);

		$config->pushStrictMode(false);

		$CALENDAR_ENTRIES = $cache->getCachedData(
			function($projectTopicName) use ($config, $db) {

				$topic = $db->loadTopicByName($projectTopicName);
				$wrappedTopic = CiantWrapFactory::getWrappedTopic($topic);

				if( ! $wrappedTopic instanceof CiantProject)
					throw new Exception("Topic $projectTopicName is not a project");

				// remove events that should not appear in the calendar
				$result = array_filter(
					$wrappedTopic->getAllChildEvents(),
					function(CiantEvent $event) {
						return $event->isVisibleInCalendar(); // the filter keeps visible events
					});

				// sort events by begin date
				usort($result, function(CiantEvent $e1, CiantEvent $e2) {
					return strtotime($e1->getBeginDate()) < strtotime($e2->getBeginDate());
				});

				// create the WIKI list
				$result = array_map(
					function(CiantEvent $event) {
						$topicName = $event->getWrappedTopic()->getTopicName();
						$parsedTopicName = $event->getWrappedTopic()->getConfig()->parseTopicName($topicName);
						return "   * {$event->getBeginDate()} - {$event->getEndDate()} - [{$parsedTopicName->topic}] EVENT: [[{$topicName}][{$event->getTitle()}]]";
					}, $result);

				return implode("\n", $result);
		}, $this->topicName);

		$config->popStrictMode();

		require 'ciant/tpl/ProjectCalEvents.tpl.php';
	}
}