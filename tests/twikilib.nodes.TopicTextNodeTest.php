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

	protected function setUp() {
		chdir(__DIR__);
		require_once 'init-twikilib-api.php';
		$twikiConfig = new Config('dummy-twikilib-config.ini');
		$topicFactory = new FilesystemDB($twikiConfig);

		$this->topic = $topicFactory->loadTopicByName('Main.TestUser');
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
}
?>