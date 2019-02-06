<?php
namespace ciant\search;

use twikilib\runtime\Logger;
use twikilib\runtime\Container;
use twikilib\core\MetaSearch;
use twikilib\core\ITopicFactory;
use twikilib\core\Config;
use ciant\wrap\CiantProject;

/**
 * Search for projects.
 * @author Viliam Simko
 */
class CiantProjects {

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	/**
	 * Loaded on-demand.
	 * @var array of strings (topic names)
	 */
	private $cachedProjectTopicNames;

	/**
	 * Loaded on-demand.
	 * @var array of CiantProject
	 */
	private $cachedProjects;

	/**
	 * @param Config $twikiConfig
	 * @param ITopicFactory $topicFactory
	 */
	final public function __construct(Config $twikiConfig, ITopicFactory $topicFactory) {
		$this->twikiConfig = $twikiConfig;
		$this->topicFactory = $topicFactory;
	}

	/**
	 * @see getAllProjects() if you need array of CiantProject instances
	 * @return array of string
	 */
	final public function getAllProjectTopicNames() {

		// we are caching the list of topic names that correspond to project-topics
		if( empty($this->cachedProjectTopicNames) ) {
			$search = new MetaSearch($this->twikiConfig);
			$search->setFormNameFilter('ProjectForm'); // this is how projects are identified
			$search->executeQuery();
			$this->cachedProjectTopicNames = $search->getResults();
		}

		return $this->cachedProjectTopicNames;
	}
	
	/**
	 * Returns only projects that have the Option flag set to "Published on Web".
	 * Note: The new Ciant website needs this function.
	 * @author Viliam Simko 2013-04-22
	 * @return array of CiantProject
	 */
	final public function getProjectsPublishedOnWeb() {
		return array_filter($this->getAllProjects(), function(CiantProject $projectTopic) {
			return $projectTopic->isPublishedOnWebOption();
		});
	}

	/**
	 * @see getAllProjectTopicNames() if you need just the topic names
	 * @return array of CiantProject
	 */
	final public function getAllProjects() {

		if( empty($this->cachedProjects) ) {
			$this->twikiConfig->pushStrictMode(false);
			Container::measureTime('Loading projects');

			$listOfTopicNames = $this->getAllProjectTopicNames();
			foreach( $listOfTopicNames as $topicName)
			{
				try {
					$topic = $this->topicFactory->loadTopicByName($topicName);
					$wrappedTopic = new CiantProject($topic);

					$this->cachedProjects[$topicName] = $wrappedTopic;
				} catch(Exception $e) {
					Logger::logWarning( "Topic: $topicName, Error:".$e->getMessage() );
				}
			}
			Container::measureTime();
			$this->twikiConfig->popStrictMode();
		}

		return $this->cachedProjects;
	}
}