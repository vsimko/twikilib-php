<?php
namespace ciant\factory;

use twikilib\wrap\UnknowTopicTypeException;

use twikilib\core\ITopic;
use twikilib\core\ITopicFactory;
use ciant\wrap\CiantProject;
use ciant\wrap\CiantWrapFactory;

class ParentProjectNotFoundException extends \Exception {}

/**
 * Creates instances of CiantProject from various sources.
 * @author Viliam Simko
 */
class ProjectFactory {

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	function __construct(ITopicFactory $topicFactory) {
		$this->topicFactory = $topicFactory;
	}

	/**
	 * Shortcut for the getParentProjectFromTopic() method.
	 * @param string $topicName
	 * @return CiantProject
	 */
	final public function getParentProjectFromTopicName($topicName) {
		$topic = $this->topicFactory->loadTopicByName($topicName);
		return $this->getParentProjectFromTopic($topic);
	}

	/**
	 * Transitively searches for parent project.
	 * Parent project is the first project upwards through the parent hierarchy.
	 * e.g. ThisTopic -> (some non-project topics)* -> ParentProject
	 * @param ITopic $topic
	 * @return CiantProject
	 */
	final public function getParentProjectFromTopic(ITopic $topic) {
		while($topic instanceof ITopic) {

			// next iteration will use the parent topic
			$parentTopicName = $topic->getTopicInfoNode()->getParentName();
			if(empty($parentTopicName))
			break;

			$topic = $this->topicFactory->loadTopicByName($parentTopicName);

			try {
				// the wrapped topic should be a project
				$wrapProjectTopic = CiantWrapFactory::getWrappedTopic($topic);
				if($wrapProjectTopic instanceof CiantProject) {
					return $wrapProjectTopic;
				}
			} catch(UnknowTopicTypeException $e) {}
		}
		throw new ParentProjectNotFoundException(
				"Parent topic of '{$topic->getTopicName()}' not found" );
	}

	/**
	 * Similar to the getParentProjectFromTopicName() method.
	 * This method, however, returns the same topic if it already were a project.
	 * @param string $topicName
	 * @return CiantProject
	 */
	final public function getNearestProjectFromTopicName($topicName) {
		$topic = $this->topicFactory->loadTopicByName($topicName);
		return $this->getNearestProjectFromTopic($topic);
	}

	/**
	 * Similar to the getParentProjectFromTopic() method.
	 * This method, however, returns the same topic if it already were a project.
	 * @param ITopic $topic
	 * @return CiantProject
	 */
	final public function getNearestProjectFromTopic(ITopic $topic) {
		try {
			$wrapped = CiantWrapFactory::getWrappedTopic($topic);

			if($wrapped instanceof CiantProject) {
				return $wrapped;
			}
		} catch(UnknowTopicTypeException $e) {}

		return $this->getParentProjectFromTopic($topic);
	}
}