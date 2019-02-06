<?php
namespace ciant\search;

use twikilib\runtime\Logger;

use twikilib\core\TopicNotFoundException;

use twikilib\core\ITopicFactory;
use twikilib\core\Config;
use twikilib\fields\Table;
use ciant\wrap\CiantProject;
use ciant\wrap\CiantUser;
use ciant\wrap\CiantWrapFactory;

/**
 * @author Viliam Simko
 */
class CiantMembers {

	/**
	 * @var twikilib\core\Config
	 */
	private $twikiConfig;

	/**
	 * @var twikilib\core\ITopicFactory
	 */
	private $topicFactory;

	/**
	 * @param ITopicFactory $topicFactory
	 */
	public function __construct(Config $twikiConfig, ITopicFactory $topicFactory) {
		$this->twikiConfig = $twikiConfig;
		$this->topicFactory = $topicFactory;
	}

	/**
	 * List of all project managers together with all current ciant members.
	 * TODO: should be moved to a different class
	 * @return array of CiantUser
	 */
	final public function getProjectManagers() {

		$results = $this->getCurrentCiantMembers();
		assert( is_array($results) );

		// list of managers already assigned to projects (might be foreign ciant members)
		$lister = new CiantProjects($this->twikiConfig, $this->topicFactory);
		foreach( $lister->getAllProjects() as $project ) {
			assert($project instanceof  CiantProject);
			try {
				$managerTopic = $project->getManager();
				assert($managerTopic instanceof CiantUser);

				$userName = $managerTopic->getWrappedTopic()->getTopicName();
				if( ! isset($results[$userName])) {
					$results[$userName] = $managerTopic;
				}
			} catch(TopicNotFoundException $e) {}
		}

		return $results;
	}

	/**
	 * @return array of CiantUser
	 */
	final public function getCurrentCiantMembers() {
		$ciantMembersTopic = $this->topicFactory->loadTopicByName('CiantMembers');
		$section = $ciantMembersTopic->getTopicTextNode()->getSectionByName('Current Members');

		$tables = $section->getTablesFromText();
		return $this->tableToUsers( $tables[0] );
	}

	/**
	 * @return array of CiantUser
	 */
	final public function getPastCiantMembers() {
		$ciantMembersTopic = $this->topicFactory->loadTopicByName('CiantMembers');
		$section = $ciantMembersTopic->getTopicTextNode()->getSectionByName('Former Members');
		$tables = $section->getTablesFromText();
		return $this->tableToUsers( $tables[0] );
	}

	/**
	 * Helper method.
	 * @param array $tableRow
	 * @return CiantUser
	 * @throws \Exception When the row cannot be converted to an instance of CiantUser class.
	 */
	final public function tableRowToCiantUser($tableRow) {
		$userName = $tableRow['User'];

		$userTopic = $this->topicFactory->loadTopicByName($userName);
		$wrappedUserTopic = CiantWrapFactory::getWrappedTopic($userTopic);

		if( ! $wrappedUserTopic instanceof CiantUser ) {
			throw new \Exception("The table row '$userName' cannot be treated as CiantUser");
		}

		assert($wrappedUserTopic instanceof CiantUser);
		return $wrappedUserTopic;
	}

	/**
	 * Helper method.
	 * @param Table $table
	 * @return array of CiantUser
	 */
	private function tableToUsers(Table $table) {
		$result = array();

		foreach( $table as $row ) {
			try {
				$ciantUserTopic = $this->tableRowToCiantUser($row);
				$topicName = $ciantUserTopic->getWrappedTopic()->getTopicName();
				$result[$topicName] = $ciantUserTopic;
			} catch(\Exception $e) {
				Logger::logWarning($e->getMessage());
			}
		}

		return $result;
	}
}