Table of contents:


# API initialization #

Example `config.ini` file:
```
[Config]
  twikiRootDir = /var/www/twiki42
  twikiWebUrl = http://yourhostname.com/twiki
  userName = SomeTestingUser
  defaultWeb = Main
```


## The easiest way ##
Download the latest version of the library packaged as a PHAR archive ([twikilib-php.phar](http://twikilib-php.googlecode.com/svn/trunk/dist/twikilib-php.phar)). The API initialization is performed when the PHAR is included.
```
require_once 'path/to/twikilib-php.phar';

use twikilib\core\Config;
use twikilib\core\FilesystemDB;

$config = new Config('path/to/config.ini');
$db = new FilesystemDB($config);
```


## Using PHAR on include path ##
If you put the `twikilib-php.phar` library to your `include_path`, you could cleverly decouple your scripts from the physical location of the library. There are more ways how to do that:
  1. using .htaccess file: `php_value include_path phar://full/path/to/twikilib-php.phar`
  1. when running from cli: `php -d include_path=phar://full/path/to/twikilib-php.phar`
  1. inside a PHP script: `set_include_path( 'phar://full/path/to/twikilib-php.phar'.PATH_SEPARATOR.get_include_path() )`

(Note: PHAR can be created from SVN source using the `build-phar.php` script which generates `dist/twikilib-php.phar`)

Now initialize the API using:
```
require_once 'init-twikilib-api.php';
```


## Initialize directly from twikilib-php source directory ##

If you don't want to use the bundled PHAR version of the library, you can use the source folder directly. To initialize the API:

  1. either add the source folder to your include path and use `require_once 'init-twikilib-api.php'`,
  1. or use `require_once 'path/to/init-twikilib-api.php'`

# Support for Lightweight Applications #
  * see LightweightApps

# Accessing form fields #
If a topic has a form attached, you can access its fields.
By default, all fields are protected from reading and any access would throw the `FormFieldNotPublishedException`.
Only fields marked with "published" flag are accessible.

There are two possibilities.
  * Either the default **strict mode** is used which causes exceptions to be thrown when a non-published or non-existing field is accessed,
  * or the silent mode can be used, which returns the field value regardless of the "published" flag

## Handling exceptions in strict mode ##

```
use twikilib\nodes\FormFieldNotPublishedException;
...
$topic = $db->loadTopicByName('SomeTopic');
try {
  $secretBankAccount = $topic->getTopicFormNode()->getFormField('BankAccount');
} catch (FormFieldNotPublishedException $e) {
  echo "Sorry, form field not published";
  exit;
}
```

## Using silent mode ##
```
$config->pushStrictMode(false);
  $topic = $db->loadTopicByName('SomeTopic');
  $secretBankAccount = $topic->getTopicFormNode()->getFormField('BankAccount');
  echo $secretBankAccount; // returns empty string
$config->popStrictMode();
```


# Searching #

Try to keep the amount of queries at minimum. Complex filters should be reimplemented with some sort of caching mechanism if possible. Anyway, the query processing is independent of the TWiki Perl implementation and thus parallel execution will be handled by multiple cores.

## General use of query filters ##
```
use twikilib\search\MetaSearch;
...
$search = new MetaSearch($config);
$search->setParentFilter('SomeParentTopic');
$search->setFormNameFilter('UserForm');
$search->setFormFieldFilter('Name', 'Peter.*');
$search->invertLastFilter();
$search->executeQuery();
foreach($search->getResults() as $idx => $topicName) {
  $topic = $db->loadTopicByName($topicName);
  ...
}
```

# Working with groups #
## Getting all users of a group (transitively) ##
```
use twikilib\wrap\Group;
...
$topic = $db->loadTopicByName('SomeGroup');
$group = new Group($topic);
$groupUsers = $group->getGroupUsers();
```

# Working with attachments #
## Searching for topics using attachment comments ##
```
use twikilib\search\MetaSearch;
...
$search = new MetaSearch($config);
$search->setAttachCommentFilter('logo_[0-9]+_'); // searches for matching substring or regular expression
$search->executeQuery();
$results = $search->getResults(); // list of topic names
```

## Filtering attachments by comment or other attributes ##
This is useful, when we already have an instance of type `ITopic` and we are interested only in attachments matching a given comment, e.g. logos.
```
$topic = $db->loadTopicByName('SomeProjectTopic');
$matchingAttachments = $topic->getTopicAttachmentsNode()->getAttachmentsByComment('logo');
// It is also possible to use other attributes for filtering.
$matchingAttachments = $topic->getTopicAttachmentsNode()->getAttachmentsByUser('SomeUser');
$matchingAttachments = $topic->getTopicAttachmentsNode()->getAttachmentsByName('jpg');
```


# Cached Images #

## User's e-mail address as PNG image ##
```
use twikilib\wrap\UserTopic;
...
$topic = $db->loadTopicByName('SomeUser');
$wrapped = new UserTopic($topic);
$url = $wrapped->getPublicEmailAsImageUrl();
```


## Cached image thumbnails ##
Sometimes it is necessary to get a thumbnail of an image.
The API provides mechanism for resizing and caching images.
Such images are then publicly accessible through a URL.

```
use twikilib\core\ResultCache;
use twikilib\utils\ImageUtils;
...
$cache = new ResultCache($config, $db);
$callback = function($imgSrcFile, $width, $height) {
  return ImageUtils::createImageThumbnail($imgSrcFile, $width, $height);
};

$url = $cache->getCachedUrl( $callback, $pathToImg, 100, 200); // crop to 100x200

$url = $cache->getCachedUrl( $callback, $pathToImg, 100, 0, false ); // height is resized proportionally to width=100px
$url = $cache->getCachedUrl( $callback, $pathToImg, 0, 80, false );  // width is resized proportionally to height=80px
$url = $cache->getCachedUrl( $callback, $pathToImg, 100, 80, true ); // crop-to-fit 100x80 + grayscale image
```