# Introduction #

This page describes lightweight applications that can be created using our framework. The goal is to help users to encapsulate some well defined functionality, e.g. an export script, into an application that can be launched either from CLI or from a web browser.
These applications are usually state-less and perform a single task that processes some TWiki data.


# Details #

As a part of the twikilib-php, we provide few examples bundled as a single PHAR archive. You can download it here [twikilib-php-examples.phar](http://twikilib-php.googlecode.com/svn/trunk/dist/twikilib-php-examples.phar). After downloading, copy the PHAR file to the same directory where the main [twikilib-php.phar](http://twikilib-php.googlecode.com/svn/trunk/dist/twikilib-php.phar) is located.

Runnable applications are classes located somewhere on your `include_path` that provide the `run()` method. Furthermore, a runnable class must also contain the `@runnable` annotation as a doc-comment.
The class may provide an optional constructor with a single parameter `$params`. The framework will pass a pre-processed list of CLI/web parameters to the constructor and then it will call the `run()` method. Your application may throw an exception whose type and message will be displayed to the user.

Example `HelloWorld` application:
```
<?php
namespace twikilib\examples;
/**
 * @runnable
 */
class HelloWorld {
  public function __construct($params) {
    // this method is optional; you can handle parameters here
  }
  public function run() {
    echo "Hello World!\n";
  }
}
?>
```

## How to launch from CLI ##
Twikilib-php provides a script `runapp.php`. Make sure it is executable by the user who has access to the twiki files e.g. `chmod +x runapp.php`.

Example usage:
```
$ cd path/to/my/project;
$ ./runapp.php

USAGE: runapp.php <classname> [args ...]
or     runapp.php --list

$ ./runapp.php --list

Searching for runnable applications in:
 - phar:///full/path/to/my/project/myproj-apps.phar
 - phar:///full/path/to/my/project/twikilib-php.phar
 - ...
Listing runnable applications:
 - myproj.MyApp
 - twikilib.examples.HelloWorld
 - ...

$ ./runapp.php twikilib.examples.HelloWorld

Hello World!
```

## How to launch from web ##
The `twikilib-php.phar` contains a default web stub which automatically calls the `init-twikilib-api.php` script.
Example:
```
In browser: http://myhostname.com/path/to/my/project/twikilib-php.phar/

USAGE: ...?classname[&name=value&name=value...]
or     ...?list

In browser: http://myhostname.com/path/to/my/project/twikilib-php.phar/index-web.php?list

Searching for runnable applications in:
 - phar:///full/path/to/my/project/myproj-apps.phar
 - phar:///full/path/to/my/project/twikilib-php.phar
 - ...
Listing runnable applications:
 - myproj.MyApp
 - twikilib.examples.HelloWorld
 - ...

In browser: http://myhostname.com/path/to/my/project/twikilib-php.phar/index-web.php?twikilib.examples.HelloWorld

Hello World!
```