<?php // @pharstub

// this file should be used as a PHAR stub
// alternatively, it can be used as an initialization script
// when the application is distributed in a directory

// We need to explicitly include the Container.
// There is no autoloading mechanism available at this point.
// We can only presume that the include_path contains the Container
// which further initializes the autoloader.
require_once 'twikilib/runtime/Container.php';

use twikilib\runtime\Container;

if( preg_match('/^phar:\/\/(.*)$/', __DIR__, $match) ) {
	// if this script is located inside a PHAR we use the enclosing directory
	Container::init( dirname($match[1]) );
} else {
	// if the script is located in a directory we can use that directory
	Container::init( __DIR__ );
}
?>