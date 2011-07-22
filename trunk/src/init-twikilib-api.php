<?php

/*
 * Inilialization of twikilib API includes:
 * - configuration of error handling
 * - autoloading of classes which are organized in namespaces
 * - logging
 * - if called from CLI, the commandline arguments are copied to request variables
 * 
 * This method should be called only once.
 */

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
	// echo "Autoloading class:$class\n"; // DEBUG
	// convert namespace to full file path
	@include_once(str_replace('\\', '/', $class) . '.php');
});

use twikilib\utils\System;
System::initLogger();
System::argvToRequest();
?>