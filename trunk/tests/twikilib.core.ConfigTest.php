<?php
use twikilib\core\Config;

class ConfigTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var twikilib\core\Config
	 */
	private $twikiConfig;

	protected function setUp() {
		chdir(__DIR__);
		
		// we presume that the API is on include path
		require_once 'init-twikilib-api.php';
		
		$this->twikiConfig = new Config( 'dummy-twikilib-config.ini' );
	}
	
	public function testDummyTWikiRoot() {
		$this->assertFileExists( $this->twikiConfig->twikiRootDir );
	}
	
	public function testDummyMainWeb() {
		$this->assertFileExists( $this->twikiConfig->getWebPubDir('Main') );
	}
	
	public function testReadingHtpasswd() {
		$htpasswdContent = $this->twikiConfig->getHtpasswd();
		$this->assertFalse( empty($htpasswdContent) );
	}
}
?>