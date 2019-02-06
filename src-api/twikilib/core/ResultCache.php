<?php
namespace twikilib\core;

use twikilib\runtime\Logger;
use twikilib\runtime\Container;
use twikilib\core\Serializer;
use \ReflectionFunction;
use \ReflectionObject;

/**
 * This class encapsulates caching API.
 * Cached data are stored in the CACHE_SUBDIR directory of the TWiki installation.
 *
 * @author Viliam Simko
 */
class ResultCache {

	/**
	 * Location relative to the twiki root.
	 * @var string
	 */
	const CACHE_SUBDIR = 'pub/twikiphplibcache';

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	/**
	 * @param Config $twikiConfig
	 * @param ITopicFactory $topicFactory
	 */
	public function __construct(Config $twikiConfig, ITopicFactory $topicFactory) {
		$this->twikiConfig = $twikiConfig;
		$this->topicFactory = $topicFactory;
	}

	/**
	 * Depending on the arguments either the cached value is returned or
	 * the callback is executed to generate the cached value.
	 * @param callback $callback
	 * @param mixed $_ variable arguments
   * @return mixed
	 */
	final public function getCachedData($callback, $_ = null) {
		$params = func_get_args();
		$cachedId = $this->recacheIfNeeded($params);
		$cachedFileName = $this->idToFileName($cachedId);

		$serializer = new Serializer;
		$serializer->twikiConfig = $this->twikiConfig;
		$serializer->topicFactory = $this->topicFactory;
		return $serializer->unserializeFromFile($cachedFileName);
	}

	/**
	 * Depending on the arguments either a publicly accessible URL
	 * of the cached value is returned or the callback is executed
	 * to generate the cached value.
	 * @param mixed $_ variable arguments
	 * @return string URL
	 */
	final public function getCachedUrl($callback, $_ = null) {
		$params = func_get_args();
		$cachedId = $this->recacheIfNeeded($params);
		return $this->idToFileUrl($cachedId);
	}

	// --------------------------------------------------
	// Helper methods below this line
	// --------------------------------------------------

	/**
	 * This is the most important method of the caching mechanism.
	 * It is used to generate a unique cache ID depending on a set of parameters.
	 * There might be callbacks (lambda-functions) among the parameters as well.
	 *
	 * <b>IMPORTANT NOTE:</b> Also the line numbers are used when computing the ID.
	 * This means that if you add few lines to your code, the ID will be different
	 * for all calls of lambda functions that are located below the affected part
	 * of the text.
	 *
	 * @param array $params
	 * @return string
	 */
	private function createId(array & $params) {
		$signature = array_map(
			function($p){
				if(is_callable($p))
					return (string) new ReflectionFunction($p);
				elseif(is_object($p))
					return (string) new ReflectionObject($p);

				assert( empty($p) || is_scalar($p) );
				return $p;

			}, $params);

		//Logger::log("CACHE-SIGNATURE:");
		//Logger::log($signature);

		return md5( implode(':', $signature) );
	}

	/**
	 * Helper method that creates a full path to a cached file.
	 * @param string $id
	 * @return string
	 */
	protected function idToFileName($id) {
		return $this->twikiConfig->twikiRootDir.
			DIRECTORY_SEPARATOR.self::CACHE_SUBDIR.
			DIRECTORY_SEPARATOR.$id;
	}

	/**
	 * Helper method that creates a public URL of a cached file.
	 * This is useful e.g. when caching images that have to be downloaded from a browser.
	 * @param string $id
	 * @return string
	 */
	protected function idToFileUrl($id) {
		return $this->twikiConfig->twikiWebUrl.
			DIRECTORY_SEPARATOR.self::CACHE_SUBDIR.
			DIRECTORY_SEPARATOR.$id;
	}

	/**
	 * @param array $params
	 * @return string ID
	 */
	private function recacheIfNeeded(array & $params) {
		$cachedId = $this->createId($params);
		$cachedFileName = $this->idToFileName($cachedId);

		if(	!file_exists($cachedFileName) ||
			filemtime($cachedFileName) + $this->twikiConfig->cacheLifetimeSeconds < time() )
		{
			Container::measureTime("Generating new cache item: $cachedFileName");

			$callback = array_shift($params);
			$data = call_user_func_array($callback, $params);

			// create the cache directory if needed
			$dir = dirname($cachedFileName);
			if( ! is_dir($dir))
				mkdir( $dir, 0755, true );

			if(is_array($data) || is_object($data)) {
				$serializer = new Serializer;
				// here we don't need to set the dependencies to be injected
				// because they are not needed during serialization
				$serializer->serializeToFile($cachedFileName, $data);
			} else {
				file_put_contents($cachedFileName, $data);
			}
			@chmod($cachedFileName, 0664);
			Container::measureTime();
		} else
			Logger::log("Loaded from cache $cachedFileName");

		return $cachedId;
	}
}