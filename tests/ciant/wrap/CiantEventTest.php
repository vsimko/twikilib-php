<?php
namespace tests\ciant\wrap;

use twikilib\utils\timespan\TimeSpan;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use ciant\wrap\CiantWrapFactory;

/**
 * @author Viliam Simko
 */
class CiantEventTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var twikilib\core\ITopicFactory
	 */
	private $topicFactory;

	protected function setUp() {
		$this->twikiConfig = new Config('dummy-twikilib-config.ini' );
		$this->topicFactory = new FilesystemDB($this->twikiConfig);
	}

	function testGetPerex() {
		$topic = $this->topicFactory->loadTopicByName('SampleEvent');
		$event = CiantWrapFactory::getWrappedTopic($topic);
		$this->assertInstanceOf('ciant\wrap\CiantEvent',$event);

		echo "PEREX: " . $event->getPerex();
	}
}