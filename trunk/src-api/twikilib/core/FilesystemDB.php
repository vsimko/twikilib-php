<?php
namespace twikilib\core;

/**
 * Loading and saving of topics from the TWiki database stored in the filesystem.
 * @author Viliam Simko
 */
class FilesystemDB implements ITopicFactory {

	/**
	 * @var twikilib\core\Config
	 */
	private $twikiConfig;

	/**
	 * Loaded topics will be cached in this list indexed by WEBNAME.TOPICNAME
	 * @var array of ITopic
	 */
	private $cachedTopics = array();

	/**
	 * @param Config $twikiConfig
	 */
	final public function __construct(Config $twikiConfig) {
		assert( is_dir($twikiConfig->twikiRootDir) );
		$this->twikiConfig = $twikiConfig;
	}

	/**
	 * Cloning not allowed for this class.
	 */
	final private function __clone() {}

	/**
	 * Serialization not allowed for this class.
	 */
	final public function __sleep() {}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopicFactory::objectToTopicName()
	 */
	final public function objectToTopicName(ITopic $topicObject) {
		assert( !empty($topicObject->_cached_topicName) );
		return $topicObject->_cached_topicName;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopicFactory::createEmptyTopic()
	 */
	final public function createEmptyTopic($topicName) {
		assert(is_string($topicName));
		assert(!empty($topicName));

		// empty instance created here
		$topic = new TopicImpl($this->twikiConfig, $this);
		assert($topic instanceof ITopic);

		// make sure the name has the format WEBNAME.TOPICNAME
		$topicName = $this->twikiConfig->normalizeTopicName($topicName);

		// now cache the topic name within the instance
		$topic->_cached_topicName = $topicName;

		return $topic;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopicFactory::loadTopicByName()
	 */
	final public function loadTopicByName($topicName) {

		// if have already loaded the topic previously return the cached instance
		$normalizedTopicName = $this->twikiConfig->normalizeTopicName($topicName);
		if( !empty($this->cachedTopics[$normalizedTopicName]) ) {
			return $this->cachedTopics[$normalizedTopicName];
		}

		// this topic has not been loaded yet
		// checking whether the file exists in TWiki database
		$filename = $this->twikiConfig->topicNameToFilename($topicName);

		if( ! is_file($filename) )
			throw new TopicNotFoundException("Topic not found '$topicName'");

		// the new topic will be cached
		$topic = $this->createEmptyTopic($topicName);
		$this->cachedTopics[$normalizedTopicName] = $topic;

		// load and parse the raw topic text
		$rawText = file_get_contents($filename);
		$topic->parseRawTopicText( $rawText );

		// store modification time
		$mtime = filemtime($filename);
		$topic->getTopicInfoNode()->setTopicDate($mtime);

		return $topic;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopicFactory::saveTopic()
	 */
	final public function saveTopic(ITopic $topic) {

		// we should update the modification time first
		$modificationTime = $topic->getTopicInfoNode()->updateTopicDate();

		$filename = $this->topicObjectToFilename($topic);

		// TODO: potential race-condition
		if($this->isTopicExternallyModified($topic))
			throw new TopicNotSavedException("File has beed modified by someone else:".$filename);

		file_put_contents("$filename.tmpsave", $topic->toWikiString());
		rename("$filename.tmpsave", $filename);

		// the topic 'date' metadata field and file modification may slightly differ
		// this should explicitly set the modification using the value from the topic text
		touch($filename, $modificationTime);
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopicFactory::moveTopicToWeb()
	 */
	final public function moveTopicToWeb(ITopic $topic, $newWebName) {

		$topicName = $topic->getTopicName();
		$parsedTopicName = $this->twikiConfig->parseTopicName($topicName);

		$topic->_cached_topicName = $newWebName.'.'.$parsedTopicName->topic;
	}

	// =========================================================================================
	// Helper methods below this line
	// =========================================================================================

	/**
	 * Helper method.
	 * Using modification time of the corresponding file.
	 * @param ITopic $topic
	 * @return boolean
	 */
	private function isTopicExternallyModified(ITopic $topic) {
		$filename = $this->topicObjectToFilename($topic);

		if( ! is_file($filename) )
			return false; // non-existing topics could not have been modified by definition

		clearstatcache();
		$currentmtime = filemtime($filename);

		return $currentmtime > $topic->getTopicInfoNode()->getTopicDate();
	}

	/**
	 * Helper method.
	 * @param ITopic $topicObject
	 */
	private function topicObjectToFilename(ITopic $topicObject) {
		$topicName = $topicObject->getTopicName();
		return $this->twikiConfig->topicNameToFilename( $topicName );
	}
}
