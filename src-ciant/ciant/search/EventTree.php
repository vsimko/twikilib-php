<?php
namespace ciant\search;

use twikilib\wrap\UnknowTopicTypeException;
use twikilib\core\TopicNotFoundException;

use ciant\wrap\CiantWrapFactory;
use twikilib\core\ITopicFactory;
use ciant\wrap\CiantEvent;
use twikilib\core\ITopic;
use ciant\search\EventTreeNode;

class EventTreeNode {
	/**
	 * @var string
	 */
	public $eventTopicName;

	/**
	 * @var CiantEvent
	 */
	public $event;

	/**
	 * @var array of EventTreeNode
	 */
	public $children;
}

class EventTree {

	/**
	 * @var array of array
	 */
	private $children = array();

	private $topicFactory;
	function __construct(ITopicFactory $topicFactory) {
		$this->topicFactory = $topicFactory;
	}

	/**
	 * @param string $rootTopicName Optionally specify the root of a subtree to be returned
	 * @return EventTreeNode
	 */
	final public function getTree($rootTopicName = '') {
		if($rootTopicName)
			$rootTopicName = $this->topicFactory->loadTopicByName($rootTopicName)->getTopicName();

		return $this->getChildNodes($rootTopicName);
	}

	/**
	 * @param string $nodeId
	 * @return array of EventTreeNode
	 */
	private function getChildNodes($rootTopicName) {
		$result = array();
		foreach((array) @$this->children[$rootTopicName] as $topicName) {
			$node = new EventTreeNode();
			$node->eventTopicName = $topicName;

			$topic = $this->topicFactory->loadTopicByName($topicName);
			$node->event = new CiantEvent($topic);
			$node->children = $this->getChildNodes($topicName);

			$result[$topicName] = $node;
		}
		return $result;
	}

	/**
	 * @param CiantEvent $event
	 * @return void
	 */
	final public function addEvent(CiantEvent $event) {
		$topic = $event->getWrappedTopic();
		$topicName = $topic->getTopicName();

		if( isset($this->children[$topicName]) )
			return;

		$this->children[$topicName] = array();

		$parentEvent = $this->getParentEvent($topic);
		if($parentEvent == null) {
			$parentTopicName = '';
		} else {
			$this->addEvent($parentEvent);
			$parentTopicName = $parentEvent->getWrappedTopic()->getTopicName();
		}

		$this->children[$parentTopicName][] = $topicName;
	}

	/**
	 * @param ITopic $topic
	 * @param array $cycleDetector (optional) it is used internally diring the recursion
	 * @return CiantEvent
	 */
	private function getParentEvent(ITopic $topic, $cycleDetector = array()) {

		// detection of cycles in the (parent topic) hierarchy
		// ====================================================
		$topicName = $topic->getTopicName();
		if(isset($cycleDetector[$topicName]))
			return null;

		$cycleDetector[$topicName] = true;
		// ====================================================

		$parentTopicName = $topic->getTopicInfoNode()->getParentName();
		if(empty($parentTopicName))
			return null;


		try {
			$parentTopic = $this->topicFactory->loadTopicByName($parentTopicName);
			try {
				$parentEvent = CiantWrapFactory::getWrappedTopic($parentTopic);
				if($parentEvent instanceof CiantEvent)
					return $parentEvent;
			} catch(UnknowTopicTypeException $e) {}

			return $this->getParentEvent($parentTopic, $cycleDetector);

		} catch(TopicNotFoundException $e) {}

		return null;
	}
}