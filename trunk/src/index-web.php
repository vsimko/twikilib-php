<?php // @pharwebstub

use twikilib\runtime\Container;
use \Exception;

run_app_from_web();

function run_app_from_web() {
	// prepare parameters from $_REQUEST variable
	foreach( $_REQUEST as $key => $value ) {
		if($value == '') {
			$_REQUEST[] = preg_replace('/[\/\._]/', '\\', $key);
			unset( $_REQUEST[$key] );
		}
	}
	
	try {
		Container::init(__DIR__);
		$app = Container::createRunnableApp($_REQUEST);
		Container::runApplication($app);
	} catch (Exception $e) {
		echo 'ERROR: '.$e->getMessage()."\n";
	}
}
?>