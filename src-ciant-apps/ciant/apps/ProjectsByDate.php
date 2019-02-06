<?php
namespace ciant\apps;

use ciant\wrap\CiantProject;
use ciant\search\CiantProjects;
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
class ProjectsByDate {
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
			echo Container::getTemplate('ciant/apps/calendar/tpl/ProjectsByDateForm.tpl.php',
					$this->params,
					'twikiConfig', $this->config );
		if( @$this->params['restupdate']) {
			$this->handleUpdate();
		}
	}

	private function handleUpdate() {
		//$cache = new ResultCache($config, $db);
		//echo $cache->getCachedData( function() use ($config, $db) {

			$lister = new CiantProjects($this->config, $this->db);

			/*
			$list = array_map( function(CiantProject $projectTopic) use ($config, $db) {
				return Encoder::createSelectValueItem(
					$projectTopic->getAcronym().' - '.$projectTopic->getName(),
					$projectTopic->getWrappedTopic()->getTopicName() );
			} , $lister->getAllProjects());
			*/

			//usort($list, 'strcmp');
			
			$from = $this->params['from_date'];
			$to = $this->params['to_date'];
			
			$projects = array();

			foreach ($lister->getAllProjects() as $pr) {
				if ($pr->overlapsWith($from, $to) && ($pr->hasStatus(CiantProject::STATUS_PROJECT) || $pr->hasStatus(CiantProject::STATUS_PAST)))
				$projects[] = $pr;
			}

			echo "From: $from <br>\n";
			echo "To: $to <br>\n";
			foreach ($projects as $pr) {
				$topicName = $pr->getWrappedTopic()->getTopicName();
				echo "| $topicName | " . $pr->getAcronym() . " | " . $pr->getName() . " | " . $pr->getBeginDate() . " | " . $pr->getEndDate() . " | \n";
			}
			
		//});
	}
}