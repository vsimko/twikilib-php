<?php
namespace twikilib\runtime;

use twikilib\runtime\ContainerRuntimeException;

class ContainerRuntimeException extends \Exception {};

/**
 * A lightweight container for PHP components.
 *
 * <b>Responsibilities of this class:</b>
 * <ul>
 *  <li>a component can be either PHAR archive or a directory</li>
 *  <li>container sets include_path automatically for all components</li>
 *  <li>autoloading of classes based on namespaces</li>
 *  <li>can execute applications marked as @runnable</li>
 * </ul>
 *
 * Note: you can use "namespace\of\my\class", "namespace.of.my.class" or "namespace/of/my/class"
 *
 * @example <b>From command line:</b> path/to/container.php namespace.of.my.class [args]
 * @example <b>From web browser:</b> http://myhost.com/path/to/container.php?namespace.of.my.class&args
 * @example <b>From your app:</b> require_once "path/to/container.php";
 *
 * @author Viliam Simko
 */
class Container {

	/**
	 * @return array
	 */
	static final public function getParsedIncludePath() {
		$pharSubst = '_COLLON_';
		$incPath = str_replace('phar://', 'phar'.$pharSubst.'//', get_include_path());

		return array_unique( str_replace($pharSubst, ':', explode(':', $incPath) ) );
	}

	/**
	 * @param string $componentsDir
	 * @throws twikilib\runtime\ContainerRuntimeException
	 */
	final static public function init($componentsDir) {
		//echo "COMPDIR:$componentsDir\n";

		// use all phars in the componentsDir
		$incList = glob($componentsDir.'/*.phar');
		if(count($incList) < 1) {
			throw new ContainerRuntimeException("No phars detected in directory " + $componentsDir);
		}
		$incList = preg_replace('/^/', 'phar://', $incList);

		$incList[] = $componentsDir;
		$incList[] = get_include_path();

// 		// also use all subdirs in componentsDir
// 		foreach(glob($componentsDir.'/*', GLOB_ONLYDIR) as $dirName) {
// 			$incList[] = $dirName;
// 		}

		// we always need the path '.' at the beginning of the include path
		$newIncludePath = str_replace(
			PATH_SEPARATOR.'.'.PATH_SEPARATOR,
			PATH_SEPARATOR,
			'.'.PATH_SEPARATOR.implode(PATH_SEPARATOR, $incList) );

		set_include_path( $newIncludePath );

		ini_set('display_errors', '1');
		error_reporting(E_ALL); // this is the strict-mode

		set_error_handler( function($errno, $errstr, $errfile, $errline) {
			if(error_reporting()) {
				Logger::log("<pre>");
				Logger::log("ERROR: $errstr");
				Logger::log("-----------------------------------------");
				foreach (debug_backtrace() as $idx => $bt ) {
					$out = "#$idx -";

					if(isset($bt['class']))
						$out .= " $bt[class]::";

					$out .= " $bt[function]";

					if(isset($bt['line']))
						$out .= " line $bt[line]";

					if(isset($bt['file']))
						$out .= " in file $bt[file]";

					Logger::log($out);
				}
				Logger::log("</pre>");

// In the older version we printed errors directly to the standard output
// Now, we use the Logger class instead, so that errors can be hidden or redirected to the log
// 				echo "<pre>\n";
// 				echo "ERROR: $errstr\n";
// 				echo "-----------------------------------------\n";
// 				foreach (debug_backtrace() as $idx => $bt ) {
// 					echo "#$idx -";

// 					if(isset($bt['class']))
// 						echo " $bt[class]::";

// 					echo " $bt[function]";

// 					if(isset($bt['line']))
// 						echo " line $bt[line]";

// 					if(isset($bt['file']))
// 						echo " in file $bt[file]";

// 					echo "\n";
// 				}
// 				//debug_print_backtrace();
// 				echo "</pre>\n";
			}
		});

		// TODO: the default autoloader does not work for some reason
		// ================================
		// http://www.php.net/manual/en/function.spl-autoload-register.php#92514
		// spl_autoload_extensions(".php");
		// spl_autoload_register();
		// ================================
		// Therefore use our custom autoloader
		spl_autoload_register( function ($class) {

			// convert namespace to path and use include_path
			$fname = str_replace('\\', '/', $class) . '.php';

			// ignore non-existing classes but show errors
			// such as extending an interface instead of implementing it
			if( @fopen($fname, 'r', true /* use include path */ ) ) {
				include_once $fname;
			}
		});
		Logger::initLogger();
	}

	/**
	 * @param array $params
	 * @return object
	 * @throws twikilib\runtime\ContainerRuntimeException
	 */
	static final public function createRunnableApp($params) {
		$className = preg_replace('/[.\/]/', '\\', @$params[0] );
		if( ! self::isClassRunnable($className) )
			throw new ContainerRuntimeException( "Cannot run application : $className");

		// setup the component
		return new $className($params);
	}

	/**
	 * @param object $runnableApp
	 * @return string
	 * @throws twikilib\runtime\ContainerRuntimeException
	 */
	static final public function runApplication($runnableApp) {
		if( ! self::isClassRunnable( get_class($runnableApp) ) )
			throw new ContainerRuntimeException( "Cannot run application : $className");

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
			$class = new \ReflectionClass($className);
			if( preg_match('/\*\s*@runnable/', $class->getDocComment()) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * A class is deprecated if it contains the @deprecated annotation.
	 * @param string $className
	 * @return boolean
	 */
	static final public function isClassDeprecated($className) {
		$class = new \ReflectionClass($className);
		return preg_match('/\*\s*@deprecated/', $class->getDocComment());
	}

	/**
	 * Measures execution time between two points in a script.
	 * Uses a stack for nested measures.
	 *
	 * @param string $measureId non-empty value pushes time to stack, empty value pops the time from stack
	 * @return integer The execution time in microseconds.
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

	/**
	 * Renders a template using given parameters.
	 * Template name should be relative to include_path.
	 *
	 * Example: echo Container::getTemplate('mytpl', 'PARAM1', 'VALUE1', 'PARAM2', 'VALUE2');
	 * Example: echo Container::getTemplate('mytpl', array('PARAM1' => 'VALUE1', 'PARAM2' => 'VALUE2') );
	 *
	 * @param string $tplName
	 * @param mixed $_ either array of parameters or variable arguments
	 * @return string The output generated by substituting the template.
	 */
	static final function getTemplate($tplName, $_ = null) {
		$_ = func_get_args();
		array_shift($_); // removing $tplName from parameters

		while( count($_) ) {
			$p  = array_shift($_); // next parameter

			if(is_string($p)) { // param name
				$$p = array_shift($_); // param value
			} elseif(is_array($p)) {
				foreach ($p as $paramName => $paramValue) {
					$$paramName = $paramValue;
				}
			}
		}

		ob_start();
		require $tplName;
		return ob_get_clean();
	}
}