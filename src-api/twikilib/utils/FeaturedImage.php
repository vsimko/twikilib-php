<?php
namespace twikilib\utils;

use twikilib\nodes\TopicAttachmentsNode;

use twikilib\fields\IAttachment;
use twikilib\form\IFormField;
use twikilib\core\ResultCache;
use twikilib\core\ITopic;

/**
 * This utility provides access to featured image of a given topic.
 *
 * @author Viliam Simko
 *
 * @tutorial
 * A topic may contain several images uploaded as attachments:
 * <ul>
 *   <li>featured images of users are their photos,</li>
 *   <li>featured images of organisations are their logos,</li>
 *   <li>etc.</li>
 * </ul>
 *
 * By default, attachments representing featured images are identified
 * using the 'featured' substing stored within their comment. This behaviour
 * can be easily modified when needed. If there are multiple images matching
 * the criteria, only the (lexicographically) first image will be considered
 * as a featured image.
 *
 * @since 2012-05-12
 *   Historically, the TWiki API contained similar methods spread across several
 *   classes that are now either removed or mapped to method of the FeaturedImage class.
 *
 * @example
 * URL of a thumbnail cropped to 100x200 pixels (width x height)
 * representing the featured image of a given topic:
 * <pre>
 *   assert($topic instanceof ITopic);
 *   $featuredImage = new FeaturedImage($topic);
 *   $featuredImage->getThumbnailUrl(100,200);
 * </pre>
 *
 * @example
 * URL of the featured image identified by the 'logo' comment:
 * <pre>
 *   assert($topic instanceof ITopic);
 *   $featuredImage = new FeaturedImage($topic, 'logo');
 *   $featuredImage->getImageUrl();
 * </pre>
 */
class FeaturedImage {

	const DEFAULT_COMMENT_SUBSTRING = 'featured';

	/**
	 * @var ITopic
	 */
	private $topic;

	/**
	 * @var string
	 */
	private $commentSubstring;

	/**
	 * @param ITopic $topic
	 * @param string $commentSubstring
	 */
	final public function __construct(
			ITopic $topic,
			$commentSubstring=self::DEFAULT_COMMENT_SUBSTRING ) {

		assert( ! empty($commentSubstring) );
		assert( is_string($commentSubstring) );

		$this->topic = $topic;
		$this->commentSubstring = $commentSubstring;
	}

	/**
	 * URL of the original image will be returned.
	 * If you want to generate smaller thumbnail image, use the getThumbnailUrl() method.
	 * <b>Note:</b> Only the first image matching the criteria will be used.
	 *
	 * @return string|null NULL is returned if there are no featured images.
	 */
	final public function getImageUrl() {
		$firstAttach = $this->getFirstFeaturedAttachment();

		if($firstAttach === null)
			return null;

		assert($firstAttach instanceof IAttachment);
		return $firstAttach->getPublicUrl();
	}

	/**
	 * @return array of IAttachment
	 */
	final public function getAllAttachments() {
		$attachNode = $this->topic->getTopicAttachmentsNode();

		$merged = array_merge(
			$attachNode->getAttachmentsByComment( $this->commentSubstring ),
			$attachNode->getAttachmentsByName( $this->commentSubstring )
		);

		return array_unique($merged, SORT_REGULAR);
	}

	/**
	 * A thumbnail will be generated using the crop-to-fit algorithm.
	 * The thumbnail image will be cached and this function returns an URL
	 * which points to the generated file stored in cache.
	 *
	 * <b>Note:</b> Only the first image matching the criteria will be used.
	 *
	 * @param int $width max. width
	 * @param int $height max. height
	 * @return string
	 */
	final public function getThumbnailUrl($cropToFitWidth, $cropToFitHeight=0) {
		$firstAttach = $this->getFirstFeaturedAttachment();

		if( $firstAttach === null )
			return null;

		assert($firstAttach instanceof IAttachment);

		$cache = new ResultCache(
				$this->topic->getConfig(),
				$this->topic->getTopicFactory() );

		return $cache->getCachedUrl( function($imgSrcFile, $width, $height) {
			return ImageUtils::createImageThumbnail($imgSrcFile, $width, $height);
		}, $firstAttach->getFileLocation(), $cropToFitWidth, $cropToFitHeight);
	}

	/**
	 * Syntactic sugar for checking whether the given topic contains a featured image.
	 * @return boolean
	 */
	final public function isTopicWithFeaturedImage() {
		return ( $this->getImageUrl() != null );
	}

	/**
	 * @return IAttachment | null
	 *   <p>NULL is returned if there are no featured attachments</p>
	 */
	private function getFirstFeaturedAttachment() {
		$list = $this->getAllAttachments();
		return @$list[0];
	}
}