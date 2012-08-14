#!/usr/bin/php
<?php

// This initializes the minimal runtime environment
require_once 'phar://'.__DIR__.'/twikilib-php.phar';

use twikilib\runtime\Terminal;
use twikilib\runtime\Container;
use twikilib\runtime\RunnableAppsLister;
use \Exception;

// In PHP, you can call a function before it is declared
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
	global $argv, $argc;

	if(empty($argc)) {
		ob_end_clean();
		die('Sorry, this is a CLI application');
	}

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
		Terminal::setColor(Terminal::YELLOW);
		echo "USAGE: ";
		Terminal::resetColor();

		echo "$APPNAME [classname] [args ...]\n";
		echo "or     $APPNAME --list\n";
		return;
	}

	// a special case, when user requested a list of all runnable applications
	if( @$params['list'] ) {

		Terminal::setColor(Terminal::GREEN);
		echo "Searching for runnable applications in:\n";
		Terminal::resetColor();

		foreach(Container::getParsedIncludePath() as $incItem) {
			echo " - ".htmlspecialchars($incItem)."\n";
		}

		Terminal::setColor(Terminal::GREEN);
		echo "Listing runnable applications:\n";
		Terminal::resetColor();

		foreach( RunnableAppsLister::listRunnableApps() as $className) {
			echo " - ".str_replace('\\', '.', $className);
			if(Container::isClassDeprecated($className)) {
				Terminal::setColor(Terminal::LIGHT_RED);
				echo ' (deprecated)';
				Terminal::resetColor();
			}
			echo "\n";
		}
		return;
	}

	try {
		$app = Container::createRunnableApp($params);
		Container::runApplication($app);
	} catch (Exception $e) {
		Terminal::setColor(Terminal::LIGHT_RED);
		echo 'ERROR ('.get_class($e).'): ';

		Terminal::setColor(Terminal::WHITE);
		echo $e->getMessage();
		echo "\n";
		Terminal::resetColor();
	}
}