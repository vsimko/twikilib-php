<?php
use twikilib\utils\TWikiSiteConfig;
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
	
	public function testLoadingSiteConfig() {
		$siteConfig = new TWikiSiteConfig('dummy_twiki_root/lib/LocalSite.cfg');
		
		$this->assertTrue( $siteConfig->getParamByName('DefaultUrlHost') == 'http://localhost' );
		$this->assertTrue( $siteConfig->getParamByName('DefaultUserWikiName') == 'TWikiGuest');
		$this->assertTrue( $siteConfig->getParamByName('Htpasswd-FileName') == '/var/www/twiki42/data/.htpasswd');
	}
}
?>