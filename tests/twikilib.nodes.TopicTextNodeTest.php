<?php
namespace twikilib\tests;

use twikilib\core\ITopic;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

class TopicTextNodeTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var twikilib\core\ITopic
	 */
	private $topic;

	/**
	 * @var twikilib\core\ITopicFactory
	 */
	private $topicFactory;

	protected function setUp() {
		chdir(__DIR__);
		require_once 'init-twikilib-api.php';
		$twikiConfig = new Config('dummy-twikilib-config.ini');
		$this->topicFactory = new FilesystemDB($twikiConfig);

		$this->topic = $this->topicFactory->loadTopicByName('Main.TestUser');
	}

	public function testSlots() {
		$textNode = $this->topic->getTopicTextNode();

		$slot = $textNode->createSlot('slot1');
		$this->assertRegExp('/(<!--[0-9a-f]+-->){2}/', $slot);

		// append the slot at the end of the text
		$textNode->replaceText('/$/D', $slot);

		$textNode->updateSlot('slot1', 'TESTVAL');
		$this->assertRegExp('/<!--[0-9a-f]+-->TESTVAL<!--[0-9a-f]+-->/', $textNode->toWikiString());

		$textNode->removeSlot('slot1');
		$this->assertRegExp('/TESTVAL/', $textNode->toWikiString());
		$this->assertNotRegExp('/<!--[0-9a-f]+-->TESTVAL<!--[0-9a-f]+-->/', $textNode->toWikiString());
	}

	public function testTableIterator() {
		$topic = $this->topicFactory->loadTopicByName('Main.UserForm');
		$this->assertTrue($topic instanceof ITopic);

		$allTables = $topic->getTopicTextNode()->getTablesFromText();
		$this->assertArrayHasKey(0, $allTables);

		$firstTable = $allTables[0];

		$allRows = array();
		foreach($firstTable as $rowIdx => $row) {
			$allRows[$rowIdx] = $row;
		}

		$this->assertEquals(16, count($allRows) ); // actual number of rows in the table
	}
}