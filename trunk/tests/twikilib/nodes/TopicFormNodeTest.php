<?php
namespace tests\twikilib\nodes;

use twikilib\form\fields\DummyField;
use twikilib\nodes\FormFieldNotFoundException;
use twikilib\nodes\FormFieldNotPublishedException;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @author Viliam Simko
 */
class TopicFormNodeTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var twikilib\core\ITopic
	 */
	private $testUserTopic;

	protected function setUp() {
		$config = new Config( 'dummy-twikilib-config.ini' );
		$db = new FilesystemDB( $config );
		$this->testUserTopic = $db->loadTopicByName('TestUser');
	}

	public function testReadPublishedFieldInStrictMode() {
		$this->assertTrue( $this->testUserTopic->getConfig()->isStrictMode() );
		$firstName = $this->testUserTopic->getTopicFormNode()->getFormField('FirstName');
		$this->assertEquals('Test', $firstName->getFieldValue() );
	}

	public function testReadNonPublishedFieldInStrictMode() {
		$this->assertTrue( $this->testUserTopic->getConfig()->isStrictMode() );
		try {
			$backAccountInfo = $this->testUserTopic->getTopicFormNode()->getFormField('BankAccountInfo');
			$this->fail("BankAccountInfo should not be readable in strict mode");
		} catch (FormFieldNotPublishedException $e) {
			$this->assertTrue( empty($backAccountInfo) );
		}
	}

	public function testReadPublishedFieldInAdminMode() {
		$this->testUserTopic->getConfig()->pushStrictMode(false);
			$firstName = $this->testUserTopic->getTopicFormNode()->getFormField('FirstName');
			$this->assertEquals('Test', $firstName->getFieldValue() );
		$this->testUserTopic->getConfig()->popStrictMode();
	}

	public function testReadNonPublishedFieldInAdminMode() {
		$this->testUserTopic->getConfig()->pushStrictMode(false);
			$backAccountInfo = $this->testUserTopic->getTopicFormNode()->getFormField('BankAccountInfo');
			$this->assertEquals('Secret info', $backAccountInfo->getFieldValue() );
		$this->testUserTopic->getConfig()->popStrictMode();
	}
}