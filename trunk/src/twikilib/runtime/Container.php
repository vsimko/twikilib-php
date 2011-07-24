<?php
namespace twikilib\runtime;

use \ReflectionClass;

/**
 * A lightweight container for PHP components.
 *
 * Responsibilities:
 * - a component can be either PHAR archive or a directory
 * - container sets include path automatically for all components
 * - autoloading of classes based on namespaces
 * - can execute applications marked as @runnable
 *
 * Examples:
 * 1. from command line: path/to/container.php namespace.of.my.class [args]
 * 2. from web browser: http://myhost.com/path/to/container.php?namespace.of.my.class&args
 * 3. from your app: require_once "path/to/container.php";
 *
 * Note: you can use "namespace\of\my\class", "namespace.of.my.class" or "namespace/of/my/class"
 *
 * @author Viliam Simko
 */
class Container {

	/**
	 * @throws Exception
	 */
	final static public function init($componentsDir) {
		//echo "COMPDIR:$componentsDir\n";
		
		$incList = glob($componentsDir.'/*.phar');
		$incList = preg_replace('/^/', 'phar://', $incList);
		$incList[] = $componentsDir;
		$incList[] = get_include_path();

		// we need '.' always at the beginning of the include path
		$newIncludePath = str_replace(
			PATH_SEPARATOR.'.'.PATH_SEPARATOR,
			PATH_SEPARATOR,
			'.'.PATH_SEPARATOR.implode(PATH_SEPARATOR, $incList) );
			
		set_include_path( $newIncludePath );

		ini_set('display_errors', '1');
		error_reporting(E_ALL); // this is the strict-mode

		set_error_handler( function($errno, $errstr, $errfile, $errline) {
			if(error_reporting()) {
				echo "<pre>\n";
				echo "ERROR: $errstr\n";
				echo "-----------------------------------------\n";
				foreach (debug_backtrace() as $idx => $bt ) {
					echo "#$idx -";
					
					if(isset($bt['class']))
						echo " $bt[class]::";
						
					echo " $bt[function]";
						
					if(isset($bt['line']))
						echo " line $bt[line]";
					
					if(isset($bt['file']))
						echo " in file $bt[file]";
					
					echo "\n";
				}
				//debug_print_backtrace();
				echo "</pre>\n";
			}
		});
		
		// TODO: the default autoloader does not work for some reason
		// ================================
		// http://www.php.net/manual/en/function.spl-autoload-register.php#92514
		// spl_autoload_extensions(".php");
		// spl_autoload_register();
		// ================================
		// Therefore use use custom autoloader
		spl_autoload_register( function ($class) {
			// convert namespace to path and use include_path	
			@include_once str_replace('\\', '/', $class) . '.php';
		});
		
		Logger::initLogger();
	}
	
	/**
	 * @param array $params
	 * @return object
	 * @throws \Exception
	 */
	static final public function createRunnableApp($params) {
		$className = preg_replace('/[.\/]/', '\\', @$params[0] );
		if( ! self::isClassRunnable($className) )
			throw new Exception( "Cannot run application : $className");

		// setup the component
		return new $className($params);
	}
	
	/**
	 * @param object $runnableApp
	 * @return string
	 * @throws \Exception
	 */
	static final public function runApplication($runnableApp) {
		if( ! self::isClassRunnable( get_class($runnableApp) ) )
			throw new Exception( "Cannot run component : $className");
			
		// TODO: perhaps check whether the application is really runnable
		$runnableApp->run();
	}
	
	/**
	 * Try to autoload the class and check if it is runnable.
	 * @return boolean
	 */
	static final public function isClassRunnable($className) {
		// does the class exist ?
		if( class_exists($className, true) ) {
			// is the class runnable ?
			$class = new ReflectionClass($className);
			if( preg_match('/\*\s*@runnable/', $class->getDocComment()) ) {
				return true;
			}
		}
		return false;
	}
	
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
			Logger::log( round($timestamp, 0) . " TIME TAKEN [$measureId] : $taken second(s), memused:".memory_get_usage() );
		} else {
			$timeStart[] = array($timestamp, $measureId);
			$taken = null;
		}
		
		return $taken;
	}
}
?>