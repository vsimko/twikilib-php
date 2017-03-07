# About TWikiLib for PHP

**Note:** More info in [github wiki](https://github.com/vsimko/twikilib-php/wiki)

This library provides an object-oriented API to a TWiki database.
The main goal is to help users familiar with PHP extract data from
TWiki database which would otherwise require knowledge of the PERL
programming language.
Our library is intended to be self-contained and easily pluggable
into other PHP projects, such as Joomla or Wordpress.

TWiki stores its data in a directory structure where wiki pages
(called topics) are stored as separate textual files.
Topics can also contain attached files.
Information within topics can be organized into sections, paragraphs and tables.
Some topics contain so-called *forms* which are collections of key-value pairs (fields).
This allows presenting information in a more structured way.

For example, you could attach a `ProjectForm` to topics that carry information about your projects and define fields such as "Project Name", "Deadline" ...

Using **twikilib-php** you can, for instance, search for topics by text located within form fields.

## Download Latest Version

Download the following PHAR bundles and move them to your project's directory:
 * [twikilib-php.phar](https://github.com/vsimko/twikilib-php/raw/master/dist/twikilib-php.phar) - contains the minimal runtime code.
 * [twikilib-php-api.phar](https://github.com/vsimko/twikilib-php/raw/master/dist/twikilib-php-api.phar) - this bundle contains the API code.
 * [twikilib-php-examples.phar](https://github.com/vsimko/twikilib-php/raw/master/dist/twikilib-php-examples.phar) - this optional bundle contains example applications.

Note: See [Usage Examples](../../wiki/Usage-Examples) if you have never used PHARs before.

## Requirements
 * PHP 5.3 (we use namespaces)
 * TWiki directory should be accessible from your PHP code.
   It means that you should be able to read files located in `TWIKIROOT/data` and `TWIKIROOT/pub`.

## Example
For more examples and user documentation see UsageExamples.

```{php}
<?php
   require_once 'init-twikilib-api.php';
   
   use twikilib\core\Config;
   use twikilib\core\FilesystemDB;
   
   $config = new Config('myconfig.ini');
   
   $db = new FilesystemDB($config);
   $topic = $db->loadTopicByName('Main.WebHome');
   $lastModified = $topic->getTopicInfoNode()->getTopicDate();
   
   echo $lastModified;
?>
```

# Features Overview

 * Object-oriented API that uses namespaces (PHP 5.3+ required)
 * Read/Write access to the TWiki database
 * Access to basic information about a topic - name, author, date ...
 * Access to form fields (date, text, list)
 * Access to form model (the topic containing the form definition)
   * some form fields can be marked *published*, the API throws
     an exception when accessing unpublished fields
     (this can be turned off explicitly)
 * Access to raw topic text
   * working with individual sections
   * working with wiki tables
 * Access to topic's hidden preferences (e.g. `VIEW_TEMPLATE`)
 * Access to attachments
   * API for generating cached thumbnails out of attached images
 * Access to revision comments
 * API for parsing CSV files (useful for batch imports to TWiki using our API)
 * API for accessing Joomla database (useful when we need to embed information
   from an existing Joomla database or during a batch import/export)
 * API for caching results (useful when we search for topics frequently)
 * Search API
 * The framework supports bundling source code into PHAR archives
   * Support for autoloading of classes from all PHARS/dirs in a given
     directory (very handy)
   * Support for CLI/web mini-applications (try `runapp.php --list`)
