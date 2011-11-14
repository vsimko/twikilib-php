#!/usr/bin/php -d phar.readonly=false
<?php

// This script should be either executable: chmod +x build-phar.php
// or you should run it using: php -d phar.readonly=false build-phar.php
// The cretaed PHAR archive is intended to be put on include path and used with PHP 5.3+
//
// How to set include path:
// 1. in .htaccess file: php_value include_path phar://path/to/mylib.phar
// 2. inside a PHP script: set_include_path( 'phar://path/to/mylib.phar'.PATH_SEPARATOR.get_include_path() );
// 3. when running from cli: php -d include_path=phar://path/to/mylib.phar
//
// Make sure that suhosin.executor.include.whitelist=phar is set in the suhosin.ini file
// (The config file is located in /etc/php5/conf.d/suhosin.ini on Ubuntu)

// accepting a single optional parameter
if( ! empty($argv[1])) {
	chdir( $argv[1] );
}

$PKGBASE = basename( getcwd() );
$SRCPREFIX = 'src'; // Directories with this prefix are regarded as containing source code of an application
$DISTDIR = 'dist';  // The generated PHAR files will be saved into this directory (one PHAR per source folder)

foreach(glob($SRCPREFIX.'*') as $SRCNAME) {

	$parts = preg_split('/[^a-zA-Z]+/', $SRCNAME );
	$parts = array_filter($parts); // remove empty parts
	array_shift($parts);
	array_unshift($parts, $PKGBASE);
	
	$PKGNAME = implode('-', $parts);
	$DISTNAME = $DISTDIR.DIRECTORY_SEPARATOR.$PKGNAME.'.phar';
	
	@unlink($DISTNAME);
	$phar = new Phar( $DISTNAME );
	$phar->setAlias($PKGNAME);
	$phar->buildFromDirectory( $SRCNAME, '/^(.(?!\.svn))*$/' ); // exclude .svn subdirectories

	// find stubs
	foreach(glob("$SRCNAME/*.php") as $entry) {
		$filecontent = file_get_contents($entry);
		if( preg_match('/^[^\n]*@pharstub[^\n]*\n/', $filecontent) ) {
			$pharstub = basename($entry);
		}
		
		if( preg_match('/^[^\n]*@pharwebstub[^\n]*\n/', $filecontent) ) {
			$pharwebstub = basename($entry);
		}
	}
	
	if( empty($pharstub) ) {
		$pharstub = 'index.php';
	}
	
	if( empty($pharwebstub) ) {
		$pharwebstub = 'index.php';
	}
	
	$phar->setStub( $phar->createDefaultStub($pharstub, $pharwebstub) );
	
	// Uncomment this if you want to explore the content of a PHAR using ZIP tools
	//$phar->convertToExecutable(Phar::ZIP);
	
	echo "PHAR written to '".$phar->getPath()."' using alias '".$phar->getAlias()."'\n";
	echo " - stub file is : $pharstub\n";
	echo " - web stub file is : $pharwebstub\n";	
}
?>