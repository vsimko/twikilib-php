<?php
namespace ciant\wrap;

use twikilib\core\ResultCache;
use twikilib\core\ITopic;
use ciant\wrap\ITopicWrapper;
use twikilib\fields\Attachment;
use twikilib\form\IFormField;
use twikilib\utils\ImageUtils;

/**
 * Extracts information about users.
 * Data are stored within the UserForm attached to the topic.
 * The image is stored as attachment, where the lexicographically first filename is selected.
 */
class UserTopic implements ITopicWrapper {

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
	
	/**
	 * @param ITopic $topic
	 */
	public function __construct(ITopic $topic) {
		$this->wrappedTopic = $topic;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		$formNode = $this->wrappedTopic->getTopicFormNode();
		return	(string) $formNode->getFormField('FirstName').
				' '.
				$formNode->getFormField('LastName');
	}
	
	/**
	 * @return twikilib\form\IFormField
	 */
	public function getProfession() {
		$formNode = $this->wrappedTopic->getTopicFormNode();
		return $formNode->getFormField('Profession');
	}
	
	/**
	 * @return twikilib\form\IFormField
	 */
	public function getPublicEmail() {
		$formNode = $this->wrappedTopic->getTopicFormNode();
		return $formNode->getFormField('PublicEmail');
	}

	/**
	 * The public email is converted to an image.
	 * The image is stored in the cache similar to the photo and logo thumbnails.
	 * @return string URL to the image
	 */
	public function getPublicEmailAsImageUrl() {
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
	public function getAllEmails($maxEmails=null, $includePublicEmail = true) {
		return implode(', ', $this->getAllEmailsAsArray($maxEmails, $includePublicEmail) );
	}
	
	/**
	 * Collects email addresses of a user defined in the UserForm and also in the .htpasswd file.
	 * @param int $maxEmails
	 * @param boolean $includePublicEmail
	 * @return array This method returns an array of unique email addresses
	 */
	public function getAllEmailsAsArray($maxEmails=null, $includePublicEmail = true) {
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
	public function getHomepage() {
		$formNode = $this->wrappedTopic->getTopicFormNode();
		return $formNode->getFormField('Homepage');
	}
	
	/**
	 * URL of the original photo image will be returned.
	 * If you want to generate smaller thumbnail image, use the getThumbnailUrl() method.
	 * Node: Only the first image will be used.
	 * @return string
	 */
	public function getPhotoUrl() {
		$firstAttach = $this->getFirstPhotoAttachment();
		return empty($firstAttach) ? null : $firstAttach->getPublicUrl();
	}
	
	/**
	 * A thumbnail will be generated using the crop-to-fit algorithm.
	 * Note: Only the first image will be used.
	 * @param int $width max. width
	 * @param int $height max. height
	 * @return string
	 */
	public function getThumbnailUrl($cropToFitWidth, $cropToFitHeight=0) {
		$firstAttach = $this->getFirstPhotoAttachment();

		if(empty($firstAttach))
			return null;

		$cache = new ResultCache(
			$this->getWrappedTopic()->getConfig(),
			$this->getWrappedTopic()->getTopicFactory() );
			
		return $cache->getCachedUrl(function($imgSrcFile, $width, $height) {
			return ImageUtils::createImageThumbnail($imgSrcFile, $width, $height);
		}, $firstAttach->getFileLocation(), $cropToFitWidth, $cropToFitHeight);
	}
	
	/**
	 * @return array of Attachment
	 */
	public function getAllPhotoAttachments() {
		$attachNode = $this->wrappedTopic->getTopicAttachmentsNode();
		
		return array_merge(
			$attachNode->getAttachmentsByComment('photo'),
			$attachNode->getAttachmentsByName('photo_') );
	}
	
	/**
	 * @return Attachment
	 */
	private function getFirstPhotoAttachment() {
		$attachNode = $this->wrappedTopic->getTopicAttachmentsNode();
		
		$list = array_merge(
			$attachNode->getAttachmentsByComment('photo'),
			$attachNode->getAttachmentsByName('photo_') );
		
		if(empty($list))
			return null;
		
		$firstAttach = $list[0];
		assert($firstAttach instanceof Attachment);
		return $firstAttach;
	}
}
?>
