<?php
namespace ciant\apps;

use twikilib\fields\IAttachment;
use ciant\wrap\CiantUser;
use ciant\wrap\CiantWrapFactory;
use twikilib\runtime\Logger;
use twikilib\core\Config;
use twikilib\core\FilesystemDB;
use twikilib\core\ResultCache;
use twikilib\utils\ImageUtils;

/**
 * @runnable
 * @deprecated
 *
 * REQ1: Photo is an attachment which contains "photo" in comment (Michal Masa 2011-03-18)
 * REQ2: If no photos are attached, render upload link similar to IMAGEGALLERY plugin (Viliam Simko 2011-03-20)
 * REQ3: All photo attachments should be listed, not just the first (Michal Masa 2011-03-21)
 * REQ4: First photo should be the attachment with "main" comment (Michal Masa 2011-03-21)
 *
 * @author Viliam Simko
 */
class UserPhoto {

	private $topicName;
	private $photoThubSize = 100;

	public function __construct($params) {

		if( @$params['help'] )
			throw new \Exception('Parameters: topic (required), size (optional, default:'.$this->photoThubSize.')');

		$topicName = @$params['topic'];
		if(empty($topicName))
			throw new \Exception("Undefined parameter: topic");

		if( ! empty($params['size']) ) {
			$this->photoThubSize = $params['size'];
		}

		$config = new Config('config.ini');
		$db = new FilesystemDB($config);
		$this->cache = new ResultCache($config, $db);
		$topic = $db->loadTopicByName( $topicName );
		$this->userTopic = CiantWrapFactory::getWrappedTopic($topic);

		if( ! $this->userTopic instanceof CiantUser)
			throw new \Exception("Not a user topic");
	}

	final public function run() {
		Logger::disableLogger();

		$photoThumbnailUrl = array();

		// covers REQ1, REQ3
		foreach( $this->userTopic->getAllPhotoAttachments() as $attach) {
			assert($attach instanceof IAttachment);

			// REQ4
			if( preg_match('/main/', $attach->getComment()) ) {
				$photoThumbnailUrl = array_merge(
					array($attach->getPublicUrl() => null),
					$photoThumbnailUrl );
			}

			$photoThumbnailUrl[ $attach->getPublicUrl() ] = $this->cache->getCachedUrl(
				function($imgSrcFile, $width, $height) {
					return ImageUtils::createImageThumbnail($imgSrcFile, $width, $height);
				}, $attach->getFileLocation(), $this->photoThubSize, $this->photoThubSize);
		}

		if(empty($photoThumbnailUrl))
			$this->renderUploadLink(); // covers REQ2
		else
			$this->renderPhotos($photoThumbnailUrl);
	}

	/**
	 * @return void
	 */
	private function renderUploadLink() {
		$topicName = $this->userTopic->getWrappedTopic()->getTopicName();
		echo "[[https://wiki.ciant.cz/bin/attach/Main/$topicName?comment=photo][Add photo]]";
	}

	/**
	 * @param array $photoThumbnailUrl
	 * @return void
	 */
	private function renderPhotos($photoThumbnailUrl) {
		// covers REQ3
		foreach((array) $photoThumbnailUrl as $originalUrl => $thumbnailUrl) {
			$imgtitle = 'Photo of '.htmlspecialchars($this->userTopic->getName());
			echo "<a href='".$originalUrl."'>";
			echo "<img src='$thumbnailUrl' alt='$imgtitle' title='$imgtitle'/>";
			echo "</a>\n";
		}
	}
}