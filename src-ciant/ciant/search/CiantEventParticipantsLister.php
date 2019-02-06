<?php
namespace ciant\search;

use twikilib\core\ITopicFactory;
use twikilib\core\Config;
use ciant\wrap\CiantUser;
use ciant\wrap\CiantEvent;

/**
 * TODO: not finished yet
 * @author Viliam Simko
 */
class CiantEventParticipantsLister {

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	/**
	 * @param ITopicFactory $topicFactory
	 */
	final public function __construct(Config $config, ITopicFactory $topicFactory) {
		$this->topicFactory = $topicFactory;
		$this->config = $config;
	}

	/**
	 * Prepare a list of topic names that are representing a unique set of participants.
	 * @return array of CiantUser
	 */
	final public function getParticipants() {

		// all events
		$search = new MetaSearch($config);
		$search->setWebNameFilter('Main');
		$search->setFormNameFilter('EventForm');
		$search->executeQuery();

		$this->config->pushStrictMode(false);
		foreach($search->getResults() as $eventTopicName) {
			$topic = $this->topicFactory->loadTopicByName($eventTopicName);
			$event = new CiantEvent($topic);

			$collaborators = $event->getCollaborators();
		}
		$this->config->popStrictMode();

//		$list = array();
//		foreach($search->getResults() as $topicName) {
//			$topic = $db->loadTopicByName($topicName);
//			$event = new CiantEvent($topic);
//			$collab = $event->getCollaborators();
//			foreach($collab as $user) {
//				if($user instanceof CiantUser) {
//					$list[spl_object_hash($user)] = $user;
//				}
//			}
//		}
//		foreach($list as $idx => $user) {
//			assert($user instanceof CiantUser);
//			echo $user->getName().", ";
//		}
	}
}