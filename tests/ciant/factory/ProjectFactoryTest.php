<?php
namespace tests\ciant\factory;

use ciant\factory\ParentProjectNotFoundException;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use twikilib\core\ITopicFactory;
use ciant\factory\ProjectFactory;

/**
 * @author Viliam Simko
 */
class ProjectFactoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	/**
	 * @var ProjectFactory
	 */
	private $projectFactory;

	protected function setUp() {
		$this->twikiConfig = new Config('dummy-twikilib-config.ini' );
		$this->topicFactory = new FilesystemDB($this->twikiConfig);
		$this->projectFactory = new ProjectFactory($this->topicFactory);
	}

	final public function testGetParentProjectFromTopic() {
		// a topic which has a project as its parent
		$topic = $this->topicFactory->loadTopicByName('Main.SampleProjectSubtopic');
		$projectTopic = $this->projectFactory->getParentProjectFromTopic($topic);
 		$this->assertEquals('SAMPLE', $projectTopic->getAcronym());

 		// a project itself SHOULD NOT have a parent project
 		$topic = $this->topicFactory->loadTopicByName('Main.SampleProject');
 		$projectTopic = null;
 		try {
 			$projectTopic = $this->projectFactory->getParentProjectFromTopic($topic);
 		} catch(ParentProjectNotFoundException $e) {}
 		$this->assertNull($projectTopic);
	}

	final public function testGetParentProjectFromTopicName() {
		$projectTopic = $this->projectFactory->getParentProjectFromTopicName('Main.SampleProjectSubtopic');
 		$this->assertEquals('SAMPLE', $projectTopic->getAcronym());
	}

	final public function testGetNearestProjectFromTopicName() {
		$projectTopic = $this->projectFactory->getNearestProjectFromTopicName('Main.SampleProject');
 		$this->assertEquals('SAMPLE', $projectTopic->getAcronym());
	}
}