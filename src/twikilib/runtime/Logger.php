<?php
namespace twikilib\runtime;

/**
 * @author Viliam Simko
 */
class Logger {
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
}
?>