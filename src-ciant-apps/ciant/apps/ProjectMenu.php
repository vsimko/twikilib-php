<?php
namespace ciant\apps;

use twikilib\runtime\Logger;
use ciant\search\EventTreeNode;
use ciant\search\EventTree;
use twikilib\core\ResultCache;
use twikilib\utils\Encoder;
use ciant\wrap\CiantEvent;
use ciant\wrap\CiantProject;
use twikilib\core\TopicNotFoundException;
use ciant\factory\ParentProjectNotFoundException;
use ciant\factory\ProjectFactory;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @runnable
 * @author Viliam Simko
 */
class ProjectMenu {

	/**
	 * @var string
	 */
	private $topicName;

	public function __construct($params) {
		$this->topicName = @$params['topic'];
		if(empty($this->topicName))
			throw new \Exception("Undefined parameter: topic");
	}

	final public function run() {
		Logger::disableLogger();
		// EVENT   -> topic name of the event's parent project
		// PROJECT -> topic name of self

		$config = new Config('config.ini');
		//$config->disableCaching();
		$db = new FilesystemDB($config);

		$config->pushStrictMode(false);
		try {
			$factory = new ProjectFactory($db);
			$projectTopic = $factory->getNearestProjectFromTopicName($this->topicName);
			$projectTopicName = $projectTopic->getWrappedTopic()->getTopicName();
			$projectTopicName = $config->parseTopicName($projectTopicName)->topic;

			$cache = new ResultCache($config, $db);
			$listEvents = $cache->getCachedData(
				function($projectTopicName) use ($db) {
					$topic = $db->loadTopicByName($projectTopicName);
					$project = new CiantProject($topic);

					$result = $project->getAllChildEvents();

					// sort events by begin date
					usort($result, function(CiantEvent $e1, CiantEvent $e2) {
						return strtotime($e1->getBeginDate()) < strtotime($e2->getBeginDate());
					});

					return $result;
				}, $projectTopicName);

			$tree = new EventTree($db);
			foreach($listEvents as $event) {
				$tree->addEvent($event);
			}

			require_once 'ciant/tpl/projectmenu/summary.tpl.php';

		} catch(\Exception $e) {

			assert(	$e instanceof ParentProjectNotFoundException ||
					$e instanceof TopicNotFoundException );

			require_once 'ciant/tpl/projectmenu/noproject.tpl.php';
		}

		$config->popStrictMode();
	}
}