<?php
namespace twikilib\core;

use \Exception;
class TopicNotFoundException extends Exception {}
class TopicNotSavedException extends Exception {}
class TopicAlreadyExistsException extends Exception {}

/**
 * Interface for creating, loading and saving TWiki topics.
 * The actual persistance layer can be implemented in many ways.
 * Although the default implementation will be the access to the
 * TWiki filesystem structure it is also possible to use some
 * relational database as well.
 * 
 * @author Viliam Simko
 */
interface ITopicFactory {
	
	/**
	 * Derives topic name from the given topic object instance.
	 * @param ITopic $topicObject
	 * @return string
	 */
	function objectToTopicName(ITopic $topicObject);
	
	/**
	 * Creates an empty topic object.
	 * @param string $topicName
	 * @return ITopic
	 * @throws TopicAlreadyExistsException
	 */
	function createEmptyTopic($topicName);
	
	/**
	 * Creates a topic object by the given topic name.
	 * The actual loading depend on the class implementation.
	 * 
	 * @param string $topicName "TOPIC" or "WEB.TOPIC"
	 * @return ITopic
	 * @throws TopicNotFoundException
	 */
	function loadTopicByName($topicName);
	
	/**
	 * @param ITopic $topic
	 * @param string $newWebName
	 * @throws TopicAlreadyExistsException
	 */
	function moveTopicToWeb(ITopic $topic, $newWebName);

	/**
	 * Stores the in-memory representation of a topic to the file.
	 * @param ITopic $topicObject
	 * @throws TopicNotSavedException
	 */
	function saveTopic(ITopic $topic);
}
?>