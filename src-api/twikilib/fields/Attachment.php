<?php
namespace twikilib\fields;

use twikilib\utils\Encoder;
use twikilib\core\ITopic;

/**
 * Represents a single attached file.
 * @author Viliam Simko
 */
class Attachment implements IAttachment {

	/**
	 * We use objects instead of arrays to avoid copying the values.
	 * @var object
	 */
	private $args;

	/**
	 * (non-PHPdoc)
	 * @see twikilib\fields.IAttachment::getMetaTagArgs()
	 */
	final public function getMetaTagArgs() {
		return $this->args;
	}

	/**
	 * @var ITopic
	 */
	private $topicContext;

	/**
	 * @param ITopic $topicContext
	 * @param array|object $args
	 */
	final public function __construct(ITopic $topicContext, $args ) {
		$this->args = (object) $args;
		$this->topicContext = $topicContext;
	}

	/**
	 * @return string
	 */
	final public function __toString() {
		return $this->getPublicUrl();
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IRenderable::toWikiString()
	 */
	final public function toWikiString() {
		return Encoder::createWikiTag('META:FILEATTACHMENT', $this->args)."\n";
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\fields.IAttachment::getFileName()
	 */
	final public function getFileName() {
		assert( !empty($this->args->name) );
		return $this->args->name;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\fields.IAttachment::getFileLocation()
	 */
	final public function getFileLocation() {

		$topicName = $this->topicContext->getTopicName();
		$twikiConfig = $this->topicContext->getConfig();
		$location = $twikiConfig->topicNameToAttachFilename($topicName, $this->args->name);

		assert( is_string($location) );

		return $location;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\fields.IAttachment::getPublicUrl()
	 */
	final public function getPublicUrl() {

		$topicName = $this->topicContext->getTopicName();
		$twikiConfig = $this->topicContext->getConfig();
		$url = $twikiConfig->topicNameToAttachUrl($topicName, $this->args->name);
		assert( is_string($url) );
		return $url;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\fields.IAttachment::getComment()
	 */
	final public function getComment() {
		return (string) @ $this->args->comment;
	}

	/**
	 * Instances of user topics are loaded on-demand.
	 * TODO: this should be later reimplemented using a central topic-caching mechanism inside the ITopicFactory.php
	 * @var ITopic
	 */
	private $ondemand_User;

	/**
	 * (non-PHPdoc)
	 * @see twikilib\fields.IAttachment::getUser()
	 */
	final public function getUser() {

		if( empty($this->args->user) ) {
			return null;
		}

		if(	empty($this->ondemand_User) ||
			$this->ondemand_User->getTopicName() != $this->args->user) {
				$topicFactory = $this->topicContext->getTopicFactory();
				$this->ondemand_User = $topicFactory->loadTopicByName($this->args->user);
		}

		assert($this->ondemand_User instanceof ITopic);
		return $this->ondemand_User;
	}
}