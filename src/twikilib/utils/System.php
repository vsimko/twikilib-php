<?php
namespace twikilib\utils;

/**
 * - Logging
 * - Performance measurement
 * - CLI prameters
 * 
 * @author Viliam Simko
 */
abstract class System {
	/**
	 * Measures execution time between two points in a script.
	 * Uses a stack for nested measures.
	 *
	 * @param string $measureId non-empty value pushes time to stack, empty value pops the time from stack
	 */
	static final public function measureTime($measureId = null) {
		static $timeStart = array();
		
		$timestamp = time() + microtime();
		
		if ($measureId === null) {
			list($start, $measureId) = array_pop($timeStart);
			
			$taken = round($timestamp - $start, 4);
			self::log( round($timestamp, 0) . " TIME TAKEN [$measureId] : $taken second(s), memused:".memory_get_usage() );
		} else {
			$timeStart[] = array($timestamp, $measureId);
			$taken = null;
		}
		
		return $taken;
	}
	
	/**
	 * Log message are written directly to the web page by default
	 * You can change this by using Engine::initLogger() method.
	 * @see Engine::initLogger
	 */
	const DEFAULT_LOG = 'php://output';
	
	/**
	 * File handle for logging.
	 * @var resource
	 */
	static private $logFileHandle;
	
	/**
	 * Redirects logged messages to a file (or PHP stream such as php://output).
	 * it is advised to use it only in special cases. A good example could be an entry script
	 * Although this medhod can be used multiple times in any place of your application,
	 * of the application such as index.php
	 *
	 * @see Engine::log
	 *
	 * @param string $filename
	 */
	static final public function initLogger($filename = self::DEFAULT_LOG) {
		if ($fh = fopen($filename, 'a')) {
			// try to close the previous logFileHandle
			if( is_resource(self::$logFileHandle) )
				@fclose(self::$logFileHandle);
				
			// use the new handle
			self::$logFileHandle = $fh;
		}
	}
	
	/**
	 * After calling this method, all log messages will be ignored.
	 */
	static final public function disableLogger() {
		self::initLogger('/dev/null');
	}
	
	/**
	 * Use this function to write messages into the application log.
	 *
	 * Note: There is only one log level.
	 * @param string $message
	 */
	static final public function log($message) {
		if (!is_scalar($message)) {
			$message = print_r($message, true);
		}
		
		fwrite(self::$logFileHandle, $message . "\n");
		fflush(self::$logFileHandle);
	}
	
	/**
	 * Render warning message.
	 * @param string $message
	 */
	static final public function logWarning($message) {
		assert(is_string($message));
		self::log('WARNING: ' . $message);
	}
	
	/**
	 * Copies CLI parameters (args) to the $_REQUEST variable
	 * as if they were received from HTTP request.
	 * 
	 * @return void
	 */
	static final public function argvToRequest() {
		global $argv;
		
		if(empty($argv))
			return;
			
		chdir( dirname($argv[0]) );
		for($i=1; $i<count($argv); $i += 2) {
			$paramName = @$argv[$i];
			$paramValue = @$argv[$i+1];
			$_REQUEST[$paramName] = $paramValue;
		}
	}
}
?>