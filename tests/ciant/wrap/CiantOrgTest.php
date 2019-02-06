<?php
namespace tests\ciant\wrap;

use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use ciant\wrap\CiantWrapFactory;

/**
 * @author Viliam Simko
 */
class CiantOrgTest extends \PHPUnit_Framework_TestCase {
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

	function testGetWholeAddress() {
		$topic = $this->topicFactory->loadTopicByName('CiantOrg');
		$org = CiantWrapFactory::getWrappedTopic($topic);

		$this->assertInstanceOf('ciant\wrap\CiantOrg', $org);

		$addr = $org->getWholeAddress();
		$this->assertTrue( is_array($addr) );
		$this->assertEquals( 4, count($addr) );
	}

	function testGetWholeAddressFewerFields() {
		// organisation with less than 4 address fields
		$topic = $this->topicFactory->loadTopicByName('HowestEdu');
		$org = CiantWrapFactory::getWrappedTopic($topic);

		$this->assertInstanceOf('ciant\wrap\CiantOrg', $org);

		$addr = $org->getWholeAddress();
		$this->assertTrue( is_array($addr) );
		$this->assertEquals( 1, count($addr) );
	}

}