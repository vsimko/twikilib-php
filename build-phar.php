#!/usr/bin/php -d phar.readonly=false
<?php

// This script should be either executable: chmod +x build-phar.php
// or you should run it using: php -d phar.readonly=false build-phar.php
// The cretaed PHAR archive is intended to be put on include path and used with PHP 5.3+
//
// How to set include path:
// 1. inside a PHP script: ini_set('include_path', 'phar://mylib.phar'.PATH_SEPARATOR.get_include_path());
// 2. in .htaccess file: php_value include_path phar://mylib.phar
// 3. in php.ini file
//
// Make sure that suhosin.executor.include.whitelist=phar is set in the php.ini or suhosin.ini file.

$PKGNAME	= basename(__DIR__);
$SRCNAME	= 'src';
$DISTNAME	= "dist/{$PKGNAME}.phar";

$phar = new Phar( $DISTNAME );
$phar->buildFromDirectory( $SRCNAME, '/^(.(?!\.svn))*$/' ); // exclude .svn subdirectories

// Uncomment this if you want to explore the content of a PHAR using ZIP tools
//$phar->convertToExecutable(Phar::ZIP);

echo "PHAR written to: ".$phar->getAlias()."\n";

?>