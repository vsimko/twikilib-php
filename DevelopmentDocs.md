# Introduction #

For those who would like to contribute to the twikilib-php project.




# Setup Eclipse IDE #
  * Install Eclipse 3.6 Helios
  * Install Subclipse SVN plugin from Help -> Eclipse Marketplace
  * Update PDT to milestone 2.2, use update site: http://download.eclipse.org/tools/pdt/updates/2.2/milestones/


# Unit Tests #
We use [PHPUnit framework](http://www.phpunit.de) for automated unit-testing.

All tests are located inside the **tests/** directory. The directory also contains testing data inside **dummy\_twiki\_root** subdirectory.


## How to setup PHPUnit ##
On Ubuntu, just install the version from repository using `sudo apt-get install phpunit`.
On other platforms, please refer to the [PHPUnit manual](http://www.phpunit.de/manual/3.4/en/installation.html)


## How to run tests ##
To run tests against the source code:
```
  #!/bin/sh
  cd path/to/your/twikilib-php
  INC=$(find . -maxdepth 1 -name 'src*' -printf '%h/%f:')
  phpunit --include-path "$INC" "$FULLPATH/src" tests/
```

To run tests against the generated PHAR archive:
```
  #!/bin/sh
  cd path/to/your/twikilib-php
  FULLPATH=`pwd`
  phpunit --include-path "phar://$FULLPATH/dist/twikilib-php.phar" tests/
```


## How to setup Eclipse for automated unit-testing ##
Install [MakeGood](http://marketplace.eclipse.org/content/makegood) plugin using `Help->Eclipse Marketplace`.
Make sure you have phpunit in the include\_path of twikilib-php project.

`Project->Properties->PHP Include Path->Libraries`. There are more ways how to add phpunit to the include\_path. For example, you can add `External Source Folder`, on Ubuntu `/usr/share/php` (PHPUnit is located inside that directory)

# Project Layout #

This section describes the directory structure of our twikilib-php project.
A similar layout is recommended (but not required) also for projects that use our library.

The main idea is that a `twikilib-php` project should be divided into independent bundles. When added to the `include_path`, they will enable a given set of functions.

## Encapsulation Hierarchy ##
Here is an overview of the desired encapsulation hierarchy:
  * Bundles
    * Packages
      * Classes
        * Methods

### Bundles (PHARs) ###
Bundles are the highest level of encapsulation. A bundle corresponds to a PHAR archive that can be placed to the `include_path`. The main bundle in our project is `twikilib-php.phar`.
Bundles are created using the `build-phar.php` script which takes all `src-*` directories in the project and for each directory it creates one bundle whose file name is composed of the project name and the bundle name.

Example:
Suppose there are two source directories in the `twikilib-php` project.
  * `src/` : contains the main parts of the library
  * `src-examples` : contains examples of runnable applications

The `build-phar.php` script will create two PHAR files:
```
src          -> dist/twikilib-php.phar
src-examples -> dist/twikilib-php-examples.phar
```

As shown above, the generated PHAR files are copied to the `dist/` directory. The `dist/` directory may also contain additional files, such as `config.ini` or `.htaccess` that are used when running the application either from CLI or from web interface.

### Packages (PHP namespaces) ###
The directory structure within a bundle directly represents the hierarchy of packages. It should be noted that **Package = PHP namespace**. During the initialization phase of the `twikilib-php`, an autoloader is set up to automatically load classes from the directory structure based on PHP namespaces.

```
<?php
// This is file twikilib/examples/MyApp.php
namespace twikilib\examples;
class MyApp {
  ...
}
?>
```

It is also possible to have a single package located in two bundles.
When you place these two bundles to the `include_path` their packages will be actually merged into a single hierarchy.

Example of two bundles with the same package:
```
bundle1.phar/apps/MyApp1.php
bundle2.phar/apps/MyApp2.php

set_include_path("phar://bundle1.phar:phar://bundle2.phar");
```

The merged hierarchy will be:
```
apps/
  MyApp1.php
  MyApp2.php
```

### Classes (PHP Classes) ###
This level corresponds to PHP classes.

### Methods (PHP Methods) ###
This level corresponds to PHP methods.