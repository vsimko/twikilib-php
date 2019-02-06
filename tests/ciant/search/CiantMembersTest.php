<?php
namespace tests\ciant\search;

use ciant\search\CiantMembers;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @author Viliam Simko
 */
class CiantMembersTest extends \PHPUnit_Framework_TestCase {

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

	function testCiantMembersTopicStructure() {
		$topic = $this->topicFactory->loadTopicByName('CiantMembers');
		$section = $topic->getTopicTextNode()->getSectionByName('Current Members');
		$this->assertNotNull($section);

		$tables = $section->getTablesFromText();
		$this->assertEquals(1, count($tables));
	}

	function testTableRowToCiantUser() {
		$search = new CiantMembers($this->twikiConfig, $this->topicFactory);

		// existing user
		$topic = $search->tableRowToCiantUser( array('User' => 'TestUser') );
		$this->assertInstanceOf('ciant\wrap\CiantUser', $topic);

		// non-existing user
		try {
			$topic = null;
			$topic = $search->tableRowToCiantUser( array('User' => 'NonexistingUser') );
			$this->fail("An exception should be thrown when the table rows refers to a non-existing user");
		} catch(\Exception $e) {
			$this->assertNull($topic);
		}
	}

	function testGetCurrentCiantMembers() {
		$search = new CiantMembers($this->twikiConfig, $this->topicFactory);

 		$list = $search->getCurrentCiantMembers();
	 	$this->assertEquals(1, count($list));
	}
}