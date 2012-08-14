<?php
namespace tests\twikilib\utils;

use twikilib\utils\FeaturedImage;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @author Viliam Simko
 */
class FeaturedImageTest extends \PHPUnit_Framework_TestCase {

	const EXPECTED_IMGURL   = 'https://localhost/twiki/pub/Main/UserWithPhoto/photo_Test.png';
	const EXPECTED_THUMBPREFIX = 'https://localhost/twiki/pub/twikiphplibcache/';

	/**
	 * @var ITopic
	 */
	private $topic;

	/**
	 * @var ITopic
	 */
	private $topicWithMultiPhoto;

	/**
	 * @var Config
	 */
	private $twikiConfig;

	protected function setUp() {
		$this->twikiConfig = new Config( 'dummy-twikilib-config.ini' );
		$topicFactory = new FilesystemDB($this->twikiConfig);
		$this->topic = $topicFactory->loadTopicByName('Main.UserWithPhoto');
		$this->topicWithMultiPhoto = $topicFactory->loadTopicByName('Main.UserWithMultiPhoto');
	}

	final public function testGetImageUrl() {

		// the default behaviour when the comment should contain 'featured' substring
		$featuredImage = new FeaturedImage($this->topic);
		$this->assertEquals( self::EXPECTED_IMGURL, $featuredImage->getImageUrl() );

		// the modified behaviour when the comment contains a different substring such as 'photo'
		$featuredImage = new FeaturedImage($this->topic, 'photo');
		$this->assertEquals( self::EXPECTED_IMGURL, $featuredImage->getImageUrl() );

		// making sure that twikiRootDir does not affect the search
		$featuredImage = new FeaturedImage($this->topic, $this->twikiConfig->twikiRootDir);
		$this->assertNull( $featuredImage->getImageUrl() );

		// testing what happens if there is no featured image (here searching for 'dummy' comment)
		$featuredImage = new FeaturedImage($this->topic, 'nonexisting');
		$this->assertNull( $featuredImage->getImageUrl() );

		// searching by filename
		$featuredImage = new FeaturedImage($this->topic, 'png');
		$this->assertEquals( self::EXPECTED_IMGURL, $featuredImage->getImageUrl() );
	}

	final public function testGetThumbnailUrl() {

		// the default behaviour when the comment should contain 'featured' substring
		$featuredImage = new FeaturedImage($this->topic);
		$this->assertStringStartsWith( self::EXPECTED_THUMBPREFIX, $featuredImage->getThumbnailUrl(100) );

		// the modified behaviour when the comment contains a different substring such as 'photo'
		$featuredImage = new FeaturedImage($this->topic, 'photo');
		$this->assertStringStartsWith( self::EXPECTED_THUMBPREFIX, $featuredImage->getThumbnailUrl(100) );

		// making sure that twikiRootDir does not affect the search
		$featuredImage = new FeaturedImage($this->topic, $this->twikiConfig->twikiRootDir);
		$this->assertNull( $featuredImage->getThumbnailUrl(100) );

		// testing what happens if there is no featured image (here searching for 'dummy' comment)
		$featuredImage = new FeaturedImage($this->topic, 'nonexisting');
		$this->assertNull( $featuredImage->getThumbnailUrl(100) );

		// searching by filename
		$featuredImage = new FeaturedImage($this->topic, 'png');
		$this->assertStringStartsWith( self::EXPECTED_THUMBPREFIX, $featuredImage->getThumbnailUrl(100) );
	}

	final public function testGetAllAttachmentsSinglePhoto() {
		$featuredImage = new FeaturedImage($this->topic);
		$list = $featuredImage->getAllAttachments();

		// the testing topic has only one attachment
		$this->assertEquals(1, count($list));

		// first element of the array should be an attachment
		$firstAttachment = $list[0];
		$this->assertInstanceOf('twikilib\fields\IAttachment', $firstAttachment);
	}

	final public function testGetAllAttachemtnsMultiPhoto() {
		$featuredImage = new FeaturedImage($this->topicWithMultiPhoto);
		$list = $featuredImage->getAllAttachments();

		// the testing topic has only one attachment
		$this->assertEquals(2, count($list));

		// first element of the array should be an attachment
		list($a1, $a2) = $list;
		$this->assertInstanceOf('twikilib\fields\IAttachment', $a1);
		$this->assertInstanceOf('twikilib\fields\IAttachment', $a2);
		$this->assertNotSame($a1, $a2);
	}
}