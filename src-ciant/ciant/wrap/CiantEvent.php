<?php
namespace ciant\wrap;

use twikilib\utils\Encoder;

use twikilib\utils\timespan\TimeSpan;
use twikilib\utils\timespan\EmptyTimeSpan;
use twikilib\wrap\UnknowTopicTypeException;
use twikilib\form\fields\DateField;
use twikilib\core\TopicNotFoundException;
use twikilib\core\ITopic;
use twikilib\form\IFormField;
use twikilib\wrap\ITopicWrapper;

/**
 * Extracts information about events.
 * @author Viliam Simko
 */
class CiantEvent implements ITopicWrapper {

	/**
	 * @var twikilib\nodes\TopicFormNode
	 */
	private $topicFormNode;

	/**
	 * @var twikilib\core\ITopic
	 */
	private $wrappedTopic;

	/**
	 * (non-PHPdoc)
	 * @see twikilib\wrap.ITopicWrapper::getWrappedTopic()
	 */
	public function getWrappedTopic() {
		return $this->wrappedTopic;
	}

	/**
	 * @param twikilib\core\ITopic $topic
	 */
	final public function __construct(ITopic $topic) {
		$this->wrappedTopic = $topic;
		$this->topicFormNode = $topic->getTopicFormNode();
	}

	/**
	 * Events are not clonable.
	 * Trying to clone the CiantEvent results in Fatal error.
	 */
	private function __clone() {}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getTitle() {
		return $this->topicFormNode->getFormField('Title');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getCategory() {
		return $this->topicFormNode->getFormField('Category');
	}

	/**
	 * @return twikilib\core\ITopic or null
	 */
	final public function getVenue() {
		list($venueTopic, ) = $this->getVenueMixed();
		return $venueTopic;
	}

	/**
	 * Creates a compact textual representation of the venue depending on the information filled in the form field.
	 * If the field contains a link to an organisation (a topic that uses the OrganisationForm)
	 * the result contains acronym/city/venue note
	 * Otherwise, the field text is treated as a venue note.
	 * @return string
	 */
	final public function getVenueAsText() {
		list($venueTopic, $venueNote) = $this->getVenueMixed();

		$result = array();
		if($venueTopic instanceof ITopic) {
			try {
				$wrappedTopic = CiantWrapFactory::getWrappedTopic($venueTopic);
				if($wrappedTopic instanceof CiantOrg) {
					$result[] = $wrappedTopic->getQualifiedName();
					$result[] = $wrappedTopic->getAddress();
					$result[] = $wrappedTopic->getCity();
				} else {
					$result[] = $venueTopic->getTopicName();
				}
			} catch (UnknowTopicTypeException $e) {}
		}

		if( ! empty($venueNote) ) {
			$result[] = $venueNote;
		}

		if(empty($result)) {
			$result[] = 'Venue?';
		}

		assert( is_array($result) );
		return implode('/', array_filter($result));
	}

	/**
	 * Returns just the venue note part of the value.
	 * @return string
	 */
	final public function getVenueNote() {
		list(, $venueNote) = $this->getVenueMixed();
		return $venueNote;
	}

	/**
	 * The purpose of this method is to extract from the <b>Venue</b> form-field two values.
	 * <ul>
	 *  <li>An ITopic object, which is and instance of the venue topic, or null if the topic does not exist.</li>
	 *  <li>An optional string describing the venue - the venue note.</li>
	 * </ul>
	 * @return array or null
	 */
	final public function getVenueMixed() {
		$venue = $this->topicFormNode->getFormField('Venue');
		$venueNote = $venue->getFieldValue();

		if( preg_match('/^(([A-Z][a-z]+\.)?[A-Z][a-z]+[A-Z][a-zA-Z0-9]+)(.*)$/', $venueNote, $match) ) {
			$topicName = $match[1];
			$venueNote = $match[3];

			try {
				$topic = $this->wrappedTopic->getTopicFactory()->loadTopicByName($topicName);

				assert($topic instanceof ITopic);
				assert( is_string($venueNote) );
				return array($topic, $venueNote);

			} catch(TopicNotFoundException $e) {
				// exception ignored
			}
		}

		assert( is_string($venueNote) );
		return array(null, $venueNote);
	}

	/**
	 * Useful for operations with other TimeSpans.
	 * @return TimeSpan
	 */
	final public function getTimeSpan() {
		try {
			return new TimeSpan(
				$this->getBeginDate()->getFieldValue(),
				$this->getEndDate()->getFieldValue() );
		} catch(\Exception $e) {
			return new EmptyTimeSpan();
		}
		assert(/* should not reach this */);
	}

	/**
	 * @return twikilib\form\fields\DateField
	 */
	final public function getBeginDate() {
		return $this->topicFormNode->getFormField('Begin');
	}

	/**
	 * @return twikilib\form\fields\DateField
	 */
	final public function getEndDate() {
		return $this->topicFormNode->getFormField('End');
	}

	/**
	 * @return FormField
	 */
	final public function getAbstract() {
		return $this->topicFormNode->getFormField('Abstract');
	}

	const DEFAULT_PEREX_MAXLENGTH = 200;

	/**
	 * This is the first paragraph of an abstract.
	 *
	 * @author Michal Masa
	 * @author Viliam Simko (perex is computed from abstract)
	 *
	 * @return FormField
	 */
	final public function getPerex( $maxLength = self::DEFAULT_PEREX_MAXLENGTH) {
		$abstract = $this->getAbstract();
		$cutAbstract = Encoder::filterStringLength($abstract, $maxLength);
		return preg_replace('/\n.*/', '', $cutAbstract);
	}

	/**
	 * @author Michal Masa
	 * @return FormField
	 */
	final public function getPhoto() {
		return $this->topicFormNode->getFormField('Photo');
	}

	/**
	 * @author Michal Masa
	 * @return FormField
	 */
	final public function getVideo() {
		return $this->topicFormNode->getFormField('Video');
	}

	/**
	 * Uses the 'Option' form field.
	 * @return boolean
	 */
	final public function isPublishedOnWebOption() {

		$this->wrappedTopic->getConfig()->pushStrictMode(false);
		$optionsFieldValue = (string) $this->topicFormNode->getFormField('Options');
		$this->wrappedTopic->getConfig()->popStrictMode();

		return (boolean) preg_match('/Published on Web/i', $optionsFieldValue);
	}

	/**
	 * Uses the 'Option' form field.
	 * @return boolean
	 */
	final public function isVisibleInCalendar() {

		$this->wrappedTopic->getConfig()->pushStrictMode(false);
		$optionsFieldValue = (string) $this->topicFormNode->getFormField('Options');
		$this->wrappedTopic->getConfig()->popStrictMode();

		return ! (boolean) preg_match('/Invisible in Wiki Calendar/i', $optionsFieldValue);
	}

	/**
	 * Uses the 'Option' form field.
	 * @return boolean
	 */
	final public function isConfirmed() {

		$this->wrappedTopic->getConfig()->pushStrictMode(false);
		$optionsFieldValue = (string) $this->topicFormNode->getFormField('Options');
		$this->wrappedTopic->getConfig()->popStrictMode();

		return ! (boolean) preg_match('/Unconfirmed/i', $optionsFieldValue);
	}
}