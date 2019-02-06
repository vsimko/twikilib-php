<?php
namespace ciant\wrap;

use twikilib\fields\IAttachment;
use twikilib\core\ITopic;
use twikilib\wrap\ITopicWrapper;
use twikilib\form\IFormField;
use twikilib\nodes\TopicFormNode;
use twikilib\nodes\TopicAttachmentsNode;

/**
 * Provides access to the organisations stored within the TWiki database.
 * @author Viliam Simko
 */
class CiantOrg implements ITopicWrapper {

	/**
	 * @var TopicFormNode
	 */
	private $topicFormNode;

	/**
	 * @var TopicAttachmentsNode
	 */
	private $topicAttachmentsNode;

	/**
	 * @var twikilib\core\ITopic
	 */
	private $wrappedTopic;

	/**
	 * (non-PHPdoc)
	 * @see twikilib\wrap.ITopicWrapper::getWrappedTopic()
	 */
	final public function getWrappedTopic() {
		return $this->wrappedTopic;
	}

	final public function __construct(ITopic $topic) {
		$this->wrappedTopic = $topic;
		$this->topicFormNode = $topic->getTopicFormNode();
		$this->topicAttachmentsNode = $topic->getTopicAttachmentsNode();
	}

	/**
	 * Creates a qualified name composed of acronym and name delimited by minus sign.
	 * @return string
	 */
	final public function getQualifiedName() {
		$chunks = array(
			$this->getAcronym()->getFieldValue(),
			$this->getName()->getFieldValue() );

		$qname = implode(' - ', array_filter($chunks));
		return str_replace('<nop>', '', $qname);
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getAcronym() {
		return $this->topicFormNode->getFormField('Acronym');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getName() {
		return $this->topicFormNode->getFormField('Name');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getCountry() {
		return $this->topicFormNode->getFormField('Country');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getCity() {
		return $this->topicFormNode->getFormField('City');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getHomepage() {
		return $this->topicFormNode->getFormField('Homepage');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getAddress() {
		return $this->topicFormNode->getFormField('Address');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getPostCode() {
		return $this->topicFormNode->getFormField('PostCode');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getPhoneNumber() {
		return $this->topicFormNode->getFormField('PostCode');
	}

	/**
	 * @return array of string
	 */
	final public function getWholeAddress() {
		$chunks = array(
				$this->getAddress()->getFieldValue(),
				$this->getCity()->getFieldValue(),
				$this->getPostCode()->getFieldValue(),
				$this->getCountry()->getFieldValue()
				);

		return array_filter($chunks);
	}

	// =========================================================================
	// Methods mapped to FeaturedImage class
	// =========================================================================

	/**
	 * @see twikilib\utils.FeaturedImage::getImageUrl()
	 * @return string
	 */
	final public function getLogoUrl() {
		$featuredImage = new FeaturedImage($this->getWrappedTopic(), '^\s*logo\s*');
		return $featuredImage->getImageUrl();
	}

	/**
	 * @see twikilib\utils.FeaturedImage::getThumbnailUrl()
	 * @return string
	 */
	final public function getThumbnailUrl($cropToFitWidth, $cropToFitHeight=0) {
		$featuredImage = new FeaturedImage($this->getWrappedTopic(), '^\s*logo\s*$');
		return $featuredImage->getThumbnailUrl($cropToFitWidth, $cropToFitHeight);
	}
}