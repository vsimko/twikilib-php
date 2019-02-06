<?php
namespace tests\ciant\wrap;

use twikilib\utils\timespan\TimeSpan;

use twikilib\core\FilesystemDB;
use ciant\wrap\CiantWrapFactory;
use twikilib\core\Config;

/**
 * @author Viliam Simko
 */
class CiantProjectTest extends \PHPUnit_Framework_TestCase {
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

	final public function testGetIncludedEvents() {
		// TODO:
	}

	function testOverlapsWith() {
		$topic = $this->topicFactory->loadTopicByName('SampleProject');
		$projectTopic = CiantWrapFactory::getWrappedTopic($topic);
		$this->assertInstanceOf('ciant\wrap\CiantProject', $projectTopic);

		$this->assertTrue( $projectTopic->getTimeSpan()->isIntersectingWith( new TimeSpan('2007-01-01', '2007-12-01') ) );
		$this->assertTrue( $projectTopic->getTimeSpan()->isIntersectingWith( new TimeSpan('2007-01-01', '2007-12-01') ) );

		$topic = $this->topicFactory->loadTopicByName('SampleProjectWithBadDate');
		$projectTopic = CiantWrapFactory::getWrappedTopic($topic);
		$this->assertInstanceOf('ciant\wrap\CiantProject', $projectTopic);

		$this->assertFalse( $projectTopic->getTimeSpan()->isIntersectingWith( new TimeSpan('2007-01-01', '2007-12-01') ) );
	}
}