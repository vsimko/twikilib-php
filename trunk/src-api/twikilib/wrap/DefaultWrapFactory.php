<?php

namespace twikilib\wrap;

use \Exception;
class UnknowTopicType extends Exception {}

class DefaultWrapFactory {
	/**
	 * Converts and instance of a topic to the instance of a wrapped topic.
	 * @param ITopic $topic
	 * @return ITopicWrapper
	 * @throws UnknowTopicType
	 */
	final static public function getWrappedTopic(ITopic $topic) {

		assert($topic instanceof ITopic);
		
		$topicName = $topic->getTopicName();
		$formName = $topic->getTopicFormNode()->getFormName();

		
		if($formName == 'UserForm') {
			return new UserTopic($topic);
		}

		elseif( preg_match('/[a-z]+Group$/', $topicName)) {
			return new Group($topic);
		}
		
		// could not wrap the topic
		throw new UnknowTopicType($topicName);
	}
}
?>