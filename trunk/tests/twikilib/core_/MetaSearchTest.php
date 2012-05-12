<?php
namespace tests\twikilib\core;

use twikilib\core\Config;
use twikilib\core\MetaSearch;

/**
 * @author Viliam Simko
 */
class MetaSearchTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var twikilib\core\Config
	 */
	private $twikiConfig;

	protected function setUp() {
		$this->twikiConfig = new Config('dummy-twikilib-config.ini');
	}

	public function testWebFitler() {
		$search = new MetaSearch($this->twikiConfig);

		$this->assertEquals( array('Main'), $search->getWebNameFilter());
		$search->executeQuery();
		$this->assertNotContains('Sandbox.WebHome', $search->getResults());
		$this->assertContains('Main.UserForm', $search->getResults());
	}

	public function testSetAndAddWebFilter() {
		$search = new MetaSearch($this->twikiConfig);
		$search->setWebNameFilter('Sandbox');
		$search->addWebNameFilter('Main');
		$this->assertEquals( array('Main', 'Sandbox'), $search->getWebNameFilter());

		$search->addWebNameFilter('Sandbox');
		$this->assertEquals( array('Main', 'Sandbox'), $search->getWebNameFilter());

		$search->executeQuery();
		$this->assertContains('Main.UserForm', $search->getResults());
		$this->assertContains('Sandbox.WebHome', $search->getResults());
	}

	public function testSetOtherWebFilter() {
		$search = new MetaSearch($this->twikiConfig);
		$search->setWebNameFilter('Sandbox');
		$this->assertEquals( array('Sandbox'), $search->getWebNameFilter());
		$search->executeQuery();
		$this->assertContains('Sandbox.WebHome', $search->getResults());
		$this->assertNotContains('Main.UserForm', $search->getResults());
	}
}
