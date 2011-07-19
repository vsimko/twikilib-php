<?php
use twikilib\utils\System;

class UsageTestFromIncludePath extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		require_once 'init-twikilib-api.php';
	}
	
	public function testApiInilialization() {
		// the System class should be autoloaded
		$this->assertTrue( class_exists('System', true) );
		System::log("we should be able to print this test message");
    }
}

class UsageTestFromPhar extends PHPUnit_Framework_TestCase {
	
	protected function setUp() {
    	require_once __DIR__.'/../dist/twikilib-php.phar';
	}
	
	public function testApiInilialization() {
		// the System class should be autoloaded
		$this->assertTrue( class_exists('System', true) );
		System::log("we should be able to print this test message");
    }
	
}
?>