<?php
namespace twikilib\wrap;

use twikilib\utils\System;
use twikilib\core\ITopic;
use twikilib\core\ITopicFactory;
use twikilib\wrap\ITopicWrapper;

/**
 * @author Viliam Simko
 */
class Group implements ITopicWrapper {
	
	private $users;
	private $subgroups;
	
	/**
	 * @var twikilib\core\ITopic
	 */
	private $wrappedTopic;
		
	/**
	 * (non-PHPdoc)
	 * @see ciant\wrap.ITopicWrapper::getWrappedTopic()
	 */
	public function getWrappedTopic() {
		return $this->wrappedTopic;
	}
	
	final public function __construct(ITopic $wrappedTopic) {
		
		$this->wrappedTopic = $wrappedTopic;
		
		$topicFactory = $wrappedTopic->getTopicFactory();
		assert($topicFactory instanceof ITopicFactory);
		
		$this->users = array();
		$this->subgroups = array();
		
		$topicVars = $wrappedTopic->getTopicTextNode()->getLocalVariablesFromText();
		foreach($topicVars as $var) {
			if($var->name == 'GROUP') {
				$topicNames = explode(',', $var->value);
				foreach($topicNames as $topicName) {
					$topicName = trim($topicName);
					//System::log($topicName);
					$topic = $topicFactory->loadTopicByName($topicName);
					$formName = $topic->getTopicFormNode()->getFormName();
					
					$normalizedTopicName = $topic->getTopicName();
					
					if($formName == 'UserForm') {
						$this->users[ $normalizedTopicName ] = $topic;
					} else {
						$this->subgroups[ $normalizedTopicName ] = new Group($topic);
						$this->users = array_merge($this->users, $this->getGroupUsers());
					}
					
				}
			}
		}
	}
	
	/**
	 * List of all users extracted using a transitive closure.
	 * @return array
	 */
	final public function getGroupUsers() {
		return $this->users;
	}
}
?>