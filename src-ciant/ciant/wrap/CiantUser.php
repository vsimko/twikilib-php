<?php
namespace ciant\wrap;

use twikilib\wrap\UserTopic;

/**
 * Extracts information about users.
 * Data are stored within the UserForm attached to the topic.
 * @author Viliam Simko
 * @deprecated
 * TODO: this class should be completely replaced by UserTopic class in future
 */
class CiantUser extends UserTopic {
	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getBiography() {
		$formNode = $this->getWrappedTopic()->getTopicFormNode();
		return $formNode->getFormField('PublicBio');
	}
}