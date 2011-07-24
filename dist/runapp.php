#!/usr/bin/php
<?php

require_once 'phar://'.__DIR__.'/twikilib-php.phar';

use twikilib\runtime\Container;
use twikilib\runtime\RunnableAppsLister;
use \Exception;

run_app_from_cli();

/**
 * arg=value	=>	[arg]=value
 * -arg=value	=>	[arg]=value
 * --arg=value	=>	[arg]=value
 * -help		=>	[help]=true
 * --list		=>	[list]=true
 * some.class	=>	[]=some.class
 */
function run_app_from_cli() {
	global $argv;
	
	$params = array();
	
	// prepare parameters from argv
	for( $i=1; $i<count($argv); ++$i) {
		if( preg_match('/^(-?-?)([a-zA-Z0-9_\-\.]+)(=(.*))?$/', $argv[$i], $match) ) {
			if( empty($match[1]) && empty($match[3]) )
				$params[] = $match[2];
			else
				$params[ $match[2] ] = empty($match[3]) ? true : $match[4];
		}
	}
	
	if( empty($params) || (count($params) == 1 && @$params['help']) ) {
		$APPNAME = basename(__FILE__);
		echo "USAGE: $APPNAME <classname> [args ...]\n";
		echo "or     $APPNAME --list\n";
		return;
	}

	if( @$params['list'] ) {
		echo "Listing runnable components:\n";
		foreach( RunnableAppsLister::listRunnableApps() as $className) {
			echo " - ".str_replace('\\', '.', $className)."\n";
		}
		return;
	}
	
	try {
		$app = Container::createRunnableApp($params);
		Container::runApplication($app);
	} catch (Exception $e) {
		echo 'ERROR: '.$e->getMessage()."\n";
	}

}
?>