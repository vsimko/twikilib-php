<?php
namespace tests\twikilib\wrap;

use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use twikilib\wrap\UserTopic;
use twikilib\wrap\DefaultWrapFactory;

/**
 * @author Viliam Simko
 */
class UserTopicTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var ITopic
	 */
	private $originalTopic;

	/**
	 * @var UserTopic
	 */
	private $userTopic;

	protected function setUp() {
		$this->twikiConfig = new Config( 'dummy-twikilib-config.ini' );
		$topicFactory = new FilesystemDB($this->twikiConfig);
		$this->originalTopic = $topicFactory->loadTopicByName('Main.UserWithMultiPhoto');
		$this->userTopic = DefaultWrapFactory::getWrappedTopic($this->originalTopic);
	}

	final public function testWrappedUserTopic() {
		$this->assertType('twikilib\wrap\UserTopic', $this->userTopic);
		$this->assertSame(
				$this->originalTopic,
				$this->userTopic->getWrappedTopic() );
	}

	/**
	 * @depends testWrappedUserTopic
	 */
	final public function testGetAllPhotoAttachments() {
		$list = $this->userTopic->getAllPhotoAttachments();
		$this->assertEquals(2, count($list));

		list($a1, $a2) = $list;
		$this->assertType('twikilib\fields\IAttachment', $a1);
		$this->assertType('twikilib\fields\IAttachment', $a2);
	}
}