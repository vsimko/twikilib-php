<?php
namespace tests\twikilib;

/**
 * @author Viliam Simko
 */
class UsageTest extends \PHPUnit_Framework_TestCase {

    public function testPharExists() {
    	$this->assertFileExists('../dist/twikilib-php.phar');
    	$this->assertFileExists('../dist/twikilib-php-api.phar');
    	$this->assertFileExists('../dist/twikilib-php-examples.phar');
    	$this->assertFileExists('../dist/runapp.php');
    	$this->assertFileExists('../build-phar.php');
    }

	public function testApiInilialization() {
		// the following classes should be autoloaded
		$this->assertTrue( class_exists('twikilib\runtime\Container', true) );
		$this->assertTrue( class_exists('twikilib\runtime\Logger', true) );
		$this->assertTrue( class_exists('twikilib\core\Config', true) );
		$this->assertTrue( class_exists('twikilib\core\FilesystemDB', true) );
    }
}