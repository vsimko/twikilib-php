<?php
namespace ciant\apps\demos;

use twikilib\runtime\Logger;

use ciant\wrap\CiantProject;

use twikilib\core\FilesystemDB;

use twikilib\core\Config;

use ciant\search\CiantProjects;

/**
 * @runnable
 * @author Viliam Simko
 */
use twikilib\runtime\Container;

use twikilib\core\ResultCache;

class ShowPublishedProjects {
	final public function run() {
		Logger::disableLogger();
		$twikiConfig = new Config('config.ini');
		$topicFactory = new FilesystemDB($twikiConfig);
		$search = new CiantProjects($twikiConfig, $topicFactory);
		$projects = $search->getProjectsPublishedOnWeb();
		
		echo "Found published projects:<br/>\n";
		foreach ($projects as $projectTopic) {
			assert($projectTopic instanceof CiantProject);
			echo $projectTopic->getAcronym()."<br/>\n";
		}
	}
}