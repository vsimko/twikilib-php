<?php
namespace tests\twikilib\runtime;

use twikilib\runtime\Logger;

/**
 * @author Viliam Simko
 */
class LoggerTest extends \PHPUnit_Framework_TestCase {

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

		// no messages should have been generated
		$this->assertEquals('', $msg_out);

		// trying to reenable logging
		Logger::initLogger();
		ob_start();
		$visiblemsg = 'some message that should be visible';
		Logger::log($visiblemsg);
		$msg_out = ob_get_clean();

		// messages should be visible again
		$this->assertEquals( $visiblemsg."\n", $msg_out );
    }
}