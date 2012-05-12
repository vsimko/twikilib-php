<?php
namespace twikilib\wrap;

use twikilib\utils\FeaturedImage;

use twikilib\fields\IAttachment;
use twikilib\core\ResultCache;
use twikilib\core\ITopic;
use twikilib\wrap\ITopicWrapper;
use twikilib\form\IFormField;
use twikilib\utils\ImageUtils;

/**
 * Extracts information about users.
 * Data are stored within the UserForm attached to the topic.
 * The image is stored as attachment, where the lexicographically first filename is selected.
 * @author Viliam Simko
 */
class UserTopic implements ITopicWrapper {

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
	 * @param ITopic $topic
	 */
	final public function __construct(ITopic $topic) {
		$this->wrappedTopic = $topic;
	}

	/**
	 * @return string
	 */
	final public function getName() {
		$formNode = $this->wrappedTopic->getTopicFormNode();
		return	(string) $formNode->getFormField('FirstName').
				' '.
				$formNode->getFormField('LastName');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getProfession() {
		$formNode = $this->wrappedTopic->getTopicFormNode();
		return $formNode->getFormField('Profession');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getPublicEmail() {
		$formNode = $this->wrappedTopic->getTopicFormNode();
		return $formNode->getFormField('PublicEmail');
	}

	/**
	 * The public email is converted to an image.
	 * The image is stored in the cache similar to the photo and logo thumbnails.
	 * @return string URL to the image
	 */
	final public function getPublicEmailAsImageUrl() {
		$cache = new ResultCache(
			$this->wrappedTopic->getConfig(),
			$this->wrappedTopic->getTopicFactory() );

		return $cache->getCachedUrl(
			function($email) {
				return ImageUtils::emailToImage($email);
			}, $this->getPublicEmail()->getFieldValue() );
	}

	/**
	 * Collects email addresses of a user defined in the UserForm and also in the .htpasswd file.
	 * @param int $maxEmails
	 * @param boolean $includePublicEmail
	 * @return string This method returns a user-readable string of email addresses delimited by comma
	 */
	final public function getAllEmails($maxEmails=null, $includePublicEmail = true) {
		return implode(', ', $this->getAllEmailsAsArray($maxEmails, $includePublicEmail) );
	}

	/**
	 * Collects email addresses of a user defined in the UserForm and also in the .htpasswd file.
	 * @param int $maxEmails
	 * @param boolean $includePublicEmail
	 * @return array This method returns an array of unique email addresses
	 */
	final public function getAllEmailsAsArray($maxEmails=null, $includePublicEmail = true) {
		$allEmails = array();

		if($includePublicEmail) {
			try {
				$email = strtolower($this->getPublicEmail());
				if( preg_match('/[a-z0-9._]+@[a-z0-9._]+/', $email) ) {
					$allEmails[] = $email;
				}
			} catch(Exception $e) {}
		}

		$twikiConfig = $this->wrappedTopic->getConfig();

		$parsedTopicName =
			$twikiConfig->parseTopicName( $this->wrappedTopic->getTopicName() );

		if(preg_match('/'.$parsedTopicName->topic.':[^:]*:(.*)/', $twikiConfig->getHtpasswd(), $match)) {
			$allEmails[] = strtolower( $match[1] );
		}

		// remove empty emails and duplicates
		$allEmails = array_unique( array_filter($allEmails) );

		if($maxEmails > 0) {
			$allEmails = array_slice($allEmails, 0, $maxEmails);
		}

		return $allEmails;
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getHomepage() {
		$formNode = $this->wrappedTopic->getTopicFormNode();
		return $formNode->getFormField('Homepage');
	}

	// =========================================================================
	// Methods mapped to FeaturedImage class
	// =========================================================================

	/**
	 * @see twikilib\utils.FeaturedImage::getImageUrl()
	 * @return string
	 */
	final public function getPhotoUrl() {
		$featuredImage = new FeaturedImage($this->wrappedTopic, 'photo');
		return $featuredImage->getImageUrl();
	}

	/**
	 * @see twikilib\utils.FeaturedImage::getThumbnailUrl()
	 * @return string
	 */
	final public function getThumbnailUrl($cropToFitWidth, $cropToFitHeight=0) {
		$featuredImage = new FeaturedImage($this->wrappedTopic, 'photo');
		return $featuredImage->getThumbnailUrl($cropToFitWidth, $cropToFitHeight);
	}

	/**
	 * @see twikilib\utils.FeaturedImage::getAllAttachments()
	 * @return array of IAttachment
	 */
	final public function getAllPhotoAttachments() {
		$featuredImage = new FeaturedImage($this->wrappedTopic, 'photo');
		return $featuredImage->getAllAttachments();
	}
}