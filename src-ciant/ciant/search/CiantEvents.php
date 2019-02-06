<?php
namespace ciant\search;

use twikilib\core\Config;
use twikilib\core\ITopicFactory;
use twikilib\core\MetaSearch;
use ciant\wrap\CiantEvent;

/**
 * List of Events
 * @author Viliam Simko
 */
class CiantEvents {

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	final public function __construct(Config $twikiConfig, ITopicFactory $topicFactory) {
		$this->twikiConfig = $twikiConfig;
		$this->topicFactory = $topicFactory;
	}

	/**
	 * @return array of CiantEvent
	 */
	final public function getPublishedEvents() {
		return $this->searchEvents(self::FLAG_PUBLISHED);
	}

	/**
	 * @return array of CiantEvent
	 */
	final public function getEventsVisibleInCalendar() {
		return $this->searchEvents(self::FLAG_VISIBLEINCAL);
	}

	/**
	 * @return array of CiantEvent
	 */
	final public function getAllEvents() {
		return $this->searchEvents( self::FLAG_NONE );
	}

	const FLAG_NONE = 0;
	const FLAG_PUBLISHED = 1;
	const FLAG_UNPUBLISHED = 2;
	const FLAG_VISIBLEINCAL = 4;
	const FLAG_INVISIBLEINCAL = 8;

	/**
	 * @param int $flags
	 * @return array of CiantEvent
	 */
	private function searchEvents($requiredFlags) {
		assert( is_int($requiredFlags) );

		$search = new MetaSearch($this->twikiConfig);
		$search->setFormNameFilter('EventForm');
		$search->executeQuery();

		$db = $this->topicFactory;

		$result = array();
		foreach($search->getResults() as $topicName) {
			$topic = $db->loadTopicByName($topicName);
			$event = new CiantEvent($topic);

			$eventFlags = 0;
			$eventFlags |= $event->isPublishedOnWebOption() ? self::FLAG_PUBLISHED : self::FLAG_UNPUBLISHED;
			$eventFlags |= $event->isVisibleInCalendar() ? self::FLAG_VISIBLEINCAL : self::FLAG_INVISIBLEINCAL;

			if(	($eventFlags & $requiredFlags) == $requiredFlags ) {
				$result[] = $event;
			}
		}

		// sort events by begin date
		usort($result, function(CiantEvent $e1, CiantEvent $e2) {
			return strtotime($e1->getBeginDate()) < strtotime($e2->getBeginDate());
		});

		return $result;
	}
}