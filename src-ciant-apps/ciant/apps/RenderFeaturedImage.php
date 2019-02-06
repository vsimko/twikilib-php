<?php
namespace ciant\apps;

use twikilib\core\ITopic;
use twikilib\utils\FeaturedImage;
use twikilib\core\Config;
use twikilib\core\FilesystemDB;
use twikilib\runtime\Logger;

/**
 * @runnable
 *
 * Note: this replaces the old ciant.apps.UserPhoto script
 *
 * REQ1: Featured image of a topic is an attachment which contains the string "featured" in the comment (Michal Masa 2011-03-18)
 * REQ2: If no photos are attached, render upload link similar to IMAGEGALLERY plugin (Viliam Simko 2011-03-20)
 * REQ3: DEPRECATED All photo attachments should be listed, not just the first (Michal Masa 2011-03-21)
 * REQ4: DEPRECATED First photo should be the attachment with "main" comment (Michal Masa 2011-03-21)
 *
 * @author Viliam Simko
 */
class RenderFeaturedImage {

	private $imageThumbSize = 100;
	private $imageCommentSubstring = FeaturedImage::DEFAULT_COMMENT_SUBSTRING;
	private $showAddLink = 'ifempty';
	private $showAddLinkAllowed = array('ifempty', 'never', 'always');
	/**
	 * @var ITopic
	 */
	private $topic;

	public function __construct($params) {

		if( @$params['help'] ) {
			throw new \Exception("Parameters:\n".
					" - topic (required),\n".
					" - size (optional, default:{$this->imageThumbSize}),\n".
					" - comment (optional, default:".FeaturedImage::DEFAULT_COMMENT_SUBSTRING."),\n".
					" - showaddlink (optional, default:{$this->showAddLink}, enum:".implode('/', $this->showAddLinkAllowed).")"
			);
		}

		$topicName = @$params['topic'];
		if(empty($topicName)) {
			throw new \Exception("Undefined parameter: topic");
		}

		if( ! empty($params['size']) ) {
			$this->imageThumbSize = $params['size'];
		}

		if( !empty($params['comment']) ) {
			$this->imageCommentSubstring = $params['comment'];
		}

		if( !empty($params['showaddlink']) ) {
			$this->showAddLink = $params['showaddlink'];
			if( ! in_array($this->showAddLink, $this->showAddLinkAllowed) ) {
				throw new \Exception(
						"The given value of 'showaddlink' parameter is not allowed, try one of: ".
						implode('/',$this->showAddLinkAllowed)
				);
			}
		}

		$config = new Config('config.ini');
		$db = new FilesystemDB($config);
		$this->topic = $db->loadTopicByName( $topicName );
	}

	final public function run() {
		Logger::disableLogger();

		assert($this->topic instanceof ITopic);
		$image = new FeaturedImage($this->topic, $this->imageCommentSubstring);

		// 		$photoThumbnailUrl = array();

// 		// covers REQ1, REQ3
// 		foreach( $this->userTopic->getAllPhotoAttachments() as $attach) {
// 			assert($attach instanceof IAttachment);

// 			// REQ4
// 			if( preg_match('/main/', $attach->getComment()) ) {
// 				$photoThumbnailUrl = array_merge(
// 					array($attach->getPublicUrl() => null),
// 					$photoThumbnailUrl );
// 			}

// 			$photoThumbnailUrl[ $attach->getPublicUrl() ] = $this->cache->getCachedUrl(
// 				function($imgSrcFile, $width, $height) {
// 					return ImageUtils::createImageThumbnail($imgSrcFile, $width, $height);
// 				}, $attach->getFileLocation(), $this->photoThubSize, $this->photoThubSize);
// 		}

		if( $image->isTopicWithFeaturedImage() ) {
			$this->renderImage($image);
		}

		if(	 $this->showAddLink == 'always' ||
			($this->showAddLink == 'ifempty' && ! $image->isTopicWithFeaturedImage())
		) {
			$this->renderUploadLink(); // covers REQ2
		}
	}

	/**
	 * @return void
	 */
	private function renderUploadLink() {
		$topicName = $this->topic->getTopicName();
		$parsedTopicName = $this->topic->getConfig()->parseTopicName($topicName);
		echo "<br/>[[https://wiki.ciant.cz/bin/attach/".$parsedTopicName->web."/".$parsedTopicName->topic."?comment=featured][Add Featured Image]]";
	}

	/**
	 * @param FeaturedImage $image
	 * @return void
	 */
	private function renderImage(FeaturedImage $image) {
		// covers REQ3
		$imgtitle = 'Featured image';
		echo "<a href='".$image->getImageUrl()."'>";
		echo "<img src='".$image->getThumbnailUrl($this->imageThumbSize)."' alt='$imgtitle' title='$imgtitle'/>";
		echo "</a>\n";
	}
}