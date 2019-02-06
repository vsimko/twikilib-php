<?php
namespace ciant\apps;

use ciant\wrap\CiantEvent;
use ciant\search\CiantEvents;
use twikilib\utils\timespan\TimeSpan;
use twikilib\core\ResultCache;
use twikilib\core\FilesystemDB;
use twikilib\runtime\Container;
use twikilib\core\Config;
use twikilib\utils\Encoder;
use twikilib\runtime\Logger;

/**
 * @runnable
 * @author Viliam Simko
 */
class EventsByDate {
	private $params;

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var ITopicFactory
	 */
	private $db;

	public function __construct($params) {
		Logger::disableLogger();
		$this->params = $params;
		$this->config = new Config('config.ini');
		$this->db = new FilesystemDB($this->config);
	}

	final public function run() {
			echo Container::getTemplate('ciant/apps/calendar/tpl/EventsByDateForm.tpl.php',
					$this->params,
					'twikiConfig', $this->config );
		if( @$this->params['restupdate']) {
			$this->handleUpdate();
		}
	}

	private function compare($a, $b) {
		if ($a->getBeginDate() == $b->getBeginDate())
			return 0;
		return ($a->getBeginDate() < $b->getBeginDate()) ? -1 : 1;
	}
	
	private function handleUpdate() {
		//$cache = new ResultCache($config, $db);
		//echo $cache->getCachedData( function() use ($config, $db) {

			$config = $this->config;
			$lister = new CiantEvents($config, $this->db);

			/*
			$list = array_map( function(CiantProject $projectTopic) use ($config, $db) {
				return Encoder::createSelectValueItem(
					$projectTopic->getAcronym().' - '.$projectTopic->getName(),
					$projectTopic->getWrappedTopic()->getTopicName() );
			} , $lister->getAllProjects());
			*/

			$from = $this->params['from_date'];
			$to = $this->params['to_date'];
			$allYear = $this->params['allYear'];
			
			if ($allYear != "Select") {
				$from = $allYear . "-01-01";
				$to = $allYear . "-12-31";
			}
			
			$events = array();

			$inputTimeSpan = new TimeSpan($from, $to);
			
			foreach ($lister->getAllEvents() as $event) {
				$eventTimeSpan = $event->getTimeSpan();
				
				if ($eventTimeSpan->isIntersectingWith($inputTimeSpan))
					$events[] = $event;
			}
			
			usort($events, "compare");
			$events = array_reverse($events);

			echo "From: $from <br>\n";
			echo "To: $to <br>\n";
			echo "<table class=listing>";
			echo "<tr class=title><td>TOPIC</td><td>BEGIN</td><td>END</td><td>TITLE (CS)</td><td>TITLE (EN)</td><td>PHOTO</td></tr>";
			foreach ($events as $event) {
				$topicName = $event->getWrappedTopic()->getTopicName();
				$config->language = "cs";
				$csTitle = $event->getTitle();
				$csAbstract = $event->getAbstract();
				$config->language = "en";
				$enTitle = $event->getTitle();
				$enAbstract = $event->getAbstract();
				$isOnline = ($event->isPublishedOnWebOption() ? "publicEvent" : "privateEvent");
				$isOnline2 = ($event->isPublishedOnWebOption() ? "ONLINE<br>" : "");
				$photo = $event->getPhoto();
				if (stripos($photo, "facebook") !== FALSE)
					$photo = "FACEBOOK";
				echo "<tr class=$isOnline><td>$isOnline2 $topicName </td><td>".$event->getBeginDate()."</td><td>".$event->getEndDate()."</td><td>$csTitle</td><td>$enTitle</td><td>$photo</td></tr>";
				echo "<tr><td>CS Abstract</td><td colspan='5'>$csAbstract</td></tr>";
				echo "<tr><td>EN Abstract</td><td colspan='5'>$enAbstract</td></tr>";
			}
			echo "</tr></table>";
			
		//});
	}
}