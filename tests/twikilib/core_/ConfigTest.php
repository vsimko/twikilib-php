<?php
namespace tests\twikilib\core;

use twikilib\utils\TWikiSiteConfig;
use twikilib\core\Config;

/**
 * @author Viliam Simko
 */
class ConfigTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Config
	 */
	private $twikiConfig;

	protected function setUp() {
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