<?php
namespace ciant\wrap;

use twikilib\wrap\UnknowTopicTypeException;
use twikilib\core\ITopic;

/**
 * @see twikilib\wrap\DefaultWrapFactory
 * @author Viliam Simko
 */
class CiantWrapFactory {

	/**
	 * Converts and instance of a topic to the instance of a wrapped topic.
	 * @param ITopic $topic
	 * @return ITopicWrapper
	 * @throws UnknowTopicTypeException
	 */
	final static public function getWrappedTopic(ITopic $topic) {

		assert($topic instanceof ITopic);

		$topicName = $topic->getTopicName();
		$formName = $topic->getTopicFormNode()->getFormName();

		if($formName == 'EventForm') {
			return new CiantEvent($topic);
		}

		elseif($formName == 'OrganisationForm') {
			return new CiantOrg($topic);
		}

		elseif($formName == 'ProjectForm') {
			return new CiantProject($topic);
		}

		elseif($formName == 'UserForm') {
			return new CiantUser($topic);
		}

		elseif( preg_match('/[a-z]+Group$/', $topicName)) {
			return new Group($topic);
		}

		// could not wrap the topic
		throw new UnknowTopicTypeException($topicName);
	}
}