<?php
namespace twikilib\core;

use twikilib\utils\TWikiSiteConfig;
use twikilib\utils\Encoder;

use \Exception;
class WrongTopicNameExcption extends Exception {}
class UnsupportedConfigItemException extends Exception {}

/**
 * Instance of this class is shared among most of the other classes.
 * TODO: use ParsedTopicName instead of stdClass
 * 
 * @author Viliam Simko
 */
class Config {
	
	/**
	 * e.g. /var/www/twiki42/
	 * @var string
	 */
	public $twikiRootDir;
	
	/**
	 * e.g. http://research.ciant.cz/twiki42/
	 * @var string
	 */
	public $twikiWebUrl;
	
	/**
	 * Name of the user that accesses the content.
	 * The username will appear inside the topic text when the topic is saved.
	 * @var string
	 */
	public $userName = 'UnknownUser';
	
	/**
	 * This web will be used when no web has been specified for a topic.
	 * @var string
	 */
	public $defaultWeb = 'Main';
	
	/**
	 * Affects the form values.
	 * @var string
	 */
	public $language = null;
	
	/**
	 * When the cached values should expire.
	 * @var integer
	 */
	public $cacheLifetimeSeconds = 300;

	/**
	 * Helper function that disables caching mechanism.
	 * @see twikilib\core\ResultCache
	 * @return void
	 */
	final public function disableCaching() {
		$this->cacheLifetimeSeconds = 0;
	}
	
	/**
	 * Restricts the API to fields with the 'P' attribute.
	 * TODO: make this variable private and introduce isStrictMode() method. Make sure all code uses the push/pop methods
	 * @var boolean
	 */
	public $useStrictPublishedMode = true; 

	/**
	 * Stack used by the push/pop functions.
	 * @var array of boolean
	 */
	private $strictModeStack = array();
	
	/**
	 * Sets a new value for useStrictPublishedMode flag
	 * and pushes the old value to the stack.
	 * @param boolean $strictMode
	 * @return void
	 */
	final public function pushStrictMode($strictMode) {
		array_push($this->strictModeStack, $this->useStrictPublishedMode);
		$this->useStrictPublishedMode = $strictMode;
	}
	
	/**
	 * Restores the last value from stack.
	 * @return void
	 */
	final public function popStrictMode() {
		$this->useStrictPublishedMode = array_pop($this->strictModeStack);
	}
	
	/**
	 * Whether the strict mode is active or not.
	 * Active strict mode causes exceptions to be thrown when accessing non-published form fields.
	 * @return boolean TRUE if strict mode is active
	 */
	final public function isStrictMode() {
		return $this->useStrictPublishedMode;
	}
	
	/**
	 * The constructor can be called with or without the config filename.
	 * @param string $configFilename
	 * @return void
	 */
	public function __construct($configFilename = null) {
		if( $configFilename ) {
			$this->loadConfigFromFile($configFilename);
		}
	}
	
	/**
	 * Cloning not allowed for this class.
	 * @return void
	 */
	final private function __clone() {}
	
	/**
	 * Serialization not allowed for this class.
	 * @return void
	 */
	final public function __sleep() {}

	/**
	 * Creates a filesystem path to the directory containing
	 * texts of topics for a given web.
	 * @param string $webName
	 * @return string
	 */
	final public function getWebDataDir($webName) {
		return $this->twikiRootDir.'/data/'.$webName;
	}
	
	/**
	 * Creates a filesystem path to the directory containing
	 * attachments of topics for a given web.
	 * @param string $webName
	 * @return string
	 */
	final public function getWebPubDir($webName) {
		return $this->twikiRootDir.'/pub/'.$webName;
	}
	
	/**
	 * Empty web part is replaced by default web name.
	 * @param string $topicName required format is "TOPIC" or "WEB.TOPIC"
	 * @return object with 'web' and 'topic' properties
	 * @throws WrongTopicNameException
	 */
	final public function parseTopicName($topicName) {
		if(	preg_match('/(([^\.\s]+)\.)?([^\s]+)$/', $topicName, $match) ) {
			
			$webPart = $match[2];
			$topicPart = $match[3];
			
			return (object) array(
				'web'	=> (empty($webPart) ? $this->defaultWeb : $webPart),
				'topic'	=> trim($topicPart) );
		}
		
		throw new WrongTopicNameExcption($topicName);
	}
	
	/**
	 * - string containing either "TOPIC" or "WEB.TOPIC" 
	 * - an array with 'web' and 'topic' fields
	 * - an object (stdClass) with 'web' and 'topic' fields
	 * @param mixed $topicName string, array or object
	 * @return string
	 */
	final public function normalizeTopicName($topicName) {
		assert( !empty($topicName) );
		
		if( is_string($topicName) ) {
			$parsedTopicName = $this->parseTopicName($topicName);
		} elseif( is_array($topicName) ) {
			assert( isset($topicName['web']) );
			assert( isset($topicName['topic']) );
			$parsedTopicName = (object) $topic;
		} elseif( is_object($topicName) ) {
			assert( isset($topicName->web) );
			assert( isset($topicName->topic) );
			$parsedTopicName = $topicName;
		} else
			assert(/* should not reach this statement */);
		
		return trim($parsedTopicName->web).'.'.trim($parsedTopicName->topic);
	}
	
	/**
	 * Helper method.
	 * @param string $topicName required format is "TOPIC" or "WEB.TOPIC"
	 * @return string Filesystem location of the topic.
	 */
	final public function topicNameToFilename($topicName) {
		assert( !empty($this->twikiRootDir) );
		$parsedTopicName = $this->parseTopicName($topicName);
		return "{$this->twikiRootDir}/data/{$parsedTopicName->web}/{$parsedTopicName->topic}.txt";
	}
	
	/**
	 * Helper method.
	 * @param string $topicName required format is "TOPIC" or "WEB.TOPIC"
	 * @param string $attachmentName optional name of the attachment
	 * @return string Filesystem location
	 */
	final public function topicNameToAttachFilename($topicName, $attachmentName = '') {
		assert( !empty($this->twikiRootDir) );
		$parsedTopicName = $this->parseTopicName($topicName);
		return "{$this->twikiRootDir}/pub/{$parsedTopicName->web}/{$parsedTopicName->topic}/{$attachmentName}";
	}
	
	/**
	 * Helper method.
	 * @param string $topicName required format is "TOPIC" or "WEB.TOPIC"
	 * @return string URL acessible through a web browser
	 */
	final public function topicNameToTopicUrl($topicName) {
		assert( !empty($this->twikiWebUrl) );
		$parsedTopicName = $this->parseTopicName($topicName);
		return "{$this->twikiWebUrl}/bin/view/{$parsedTopicName->web}/{$parsedTopicName->topic}";
	}
	
	/**
	 * Helper method.
	 * @param string $topicName required format is "TOPIC" or "WEB.TOPIC"
	 * @param string $attachmentName optional name of the attachment appended to the URL
	 * @return string URL acessible through a web browser
	 */
	final public function topicNameToAttachUrl($topicName, $attachmentName = '') {
		assert( !empty($this->twikiWebUrl) );
		$parsedTopicName = $this->parseTopicName($topicName);
		// TODO: add some config switch that causes coversion of 'https' to 'http'
		return "{$this->twikiWebUrl}/pub/{$parsedTopicName->web}/{$parsedTopicName->topic}/{$attachmentName}";
	}

	/**
	 * Loads config values from INI file.
	 * @param string $filename
	 * @return void
	 */
	final public function loadConfigFromFile($filename) {
		$inistr = file_get_contents($filename, true);
		$parsedConfig = parse_ini_string($inistr);
		foreach($parsedConfig as $name => $value) {
			if( property_exists($this, $name) ) {
				$this->$name = $value;
			} else {
				throw new UnsupportedConfigItemException($name);
			}
		}
	}
	
	/**
	 * Loads config values from a configuration of a running TWiki installation.
	 * @param string $twikiRootDir
	 * @return void
	 */
	final public function loadConfigFromTwikiSiteConfig($twikiRootDir) {
		$this->twikiRootDir = $twikiRootDir;
		$siteConfig = new TWikiSiteConfig($twikiRootDir.'lib/LoacalSite.cfg');
		$this->twikiWebUrl = $siteConfig->getParamByName('DefaultUrlHost');
		$this->defaultWeb = $siteConfig->getParamByName('UsersWebName');
		$this->userName = $siteConfig->getParamByName('DefaultUserWikiName');
	}
	
	/**
	 * Content of the .htpasswd file will be cached here.
	 * Note: The value is cached throughout the whole request.
	 * @var string
	 */
	private $htpasswdData;
	
	/**
	 * Returns content of the TWIKIROOT/data/.htpasswd file.
	 * Note: The value is cached throughout the whole request.
	 * @return string
	 */
	final public function getHtpasswd() {
		if( empty($this->htpasswdData) ) {
			$this->htpasswdData = file_get_contents($this->twikiRootDir.'/data/.htpasswd');
		}
		return $this->htpasswdData;
	}
}
?>