<?php

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

if( ! function_exists('__autoload')) {
	function __autoload($class) {
		// convert namespace to full file path
		$class = str_replace('\\', '/', $class) . '.php';
		require_once($class);
	}
}

init_twiki_api();

use twikilib\utils\System;
System::initLogger();
System::argvToRequest();

/**
 * Encapsulating into the function prevents multiple calls of this init script.
 * It also prevents the variables to become global.
 */
function init_twiki_api() {
	ini_set('display_errors', '1');
	error_reporting(E_ALL); // this is the strict-mode
	
	$curIniPath = ini_get('include_path');
	$twikiLibPath = dirname(__FILE__);
	
	// We are using the strpos() function for fast searching.
	// We must prepend the PATH_SEPARATOR to cover the case where there
	// is only one item in the include_path.
	if (!strpos(PATH_SEPARATOR.$curIniPath, PATH_SEPARATOR.$twikiLibPath)) {
		ini_set('include_path', $curIniPath .PATH_SEPARATOR.$twikiLibPath);
	}
}

?>