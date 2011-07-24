<?php
use twikilib\runtime\Logger;
class UsageTestFromIncludePath extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		require_once 'init-twikilib-api.php';
	}
	
	public function testApiInilialization() {
		// should be autoloaded
		$this->assertTrue( class_exists('twikilib\runtime\Container', true) );
		$this->assertTrue( class_exists('twikilib\runtime\Logger', true) );
		$this->assertTrue( class_exists('twikilib\core\Config', true) );
		$this->assertTrue( class_exists('twikilib\core\FilesystemDB', true) );
    }
    
    public function testEnabledLogging() {
    	$msg_in = "we should be able to print this test message";
		ob_start();
		Logger::log($msg_in);
		$msg_out = ob_get_clean();
		
		$this->assertEquals($msg_out, $msg_in."\n");
    }
    
    /**
     * @depends testEnabledLogging
     */
    public function testDisabledLogging() {
		Logger::disableLogger();
		ob_start();
		Logger::log("should be invisible");
		Logger::logWarning("should be invisible");
		$msg_out = ob_get_contents();
		
		// messages should be invisible
		$this->assertEquals('', $msg_out);
		
		// trying to reenable logging
		Logger::initLogger();
		ob_start();
		Logger::log($msg_in);
		$msg_out = ob_get_clean();
		
		// messages should be visible again
		$this->assertFalse( empty($msg_out) );
    }
    
    public function testPharExists() {
    	$this->assertFileExists(__DIR__.'/../dist/twikilib-php.phar');
    }
}
?>