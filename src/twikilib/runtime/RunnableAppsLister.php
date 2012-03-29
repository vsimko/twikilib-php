<?php
namespace twikilib\runtime;

use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;

/**
 * This is used for listing all available applications that can be run thought the API.
 * @author Viliam Simko
 */
class RunnableAppsLister {

	/**
	 * Note: time consuming operation
	 * @return array of string e.g. array('twikilib\core\Config', 'my\app1')
	 */
	final static public function listRunnableApps() {
		$incList = Container::getParsedIncludePath();

		$result = array();
		foreach($incList as $incItem) {
			if( is_readable($incItem) ) {
				$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($incItem),
				RecursiveIteratorIterator::LEAVES_ONLY );

				foreach($iterator as $item ) {
					//echo "checking: $item\n";
					$className = self::getFullClassNameFromFile($item);
					if($className != null && Container::isClassRunnable($className)) {
						$result[] = $className;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Extracts classname (namespace\classname) from file.
	 * The algorithm checks whether the file contains a class whose name matches the filename.
	 * If found, the method constructs the namespace + classname as a result
	 * @return string or null
	 */
	final static public function getFullClassNameFromFile($filename) {
		if( ! is_file($filename) || ! preg_match('/([^\/]+)\.php$/', $filename, $match) ) {
			return null;
		}

		$php_file = file_get_contents( $filename );
		$desiredClassName = $match[1];

		$ns_token = false; // indicates that we encountered the 'namespace' keyword
		$nspart_token = false; // indicates that we started reading the namespace name
		$class_token = false; // indicates that we started reading the classname
		$nsname = array(); // name will be collected here

		// a simple state machine is used for extracting the namespace + classname
		foreach( token_get_all($php_file) as $token) {
			switch ( $token[0]) {

				case T_NAMESPACE:
					$ns_token = true;
					break;

				case T_WHITESPACE:
					if($nspart_token || $class_token) {
						// whitespace after namespace name
						$ns_token = false;
						$nspart_token = false;
					}
					break;

				case T_STRING:
					if($class_token) {
						if($token[1] != $desiredClassName) {
							$class_token = false;
							break;
						}

						$nsname[] = $token[1];
						return implode('\\', $nsname);
					} elseif ($ns_token) {
						$nspart_token = true;
						$nsname[] = $token[1];
					}
					break;
				case T_CLASS:
					$class_token = true;
					break;
			}
		}

		return null;
	}
}