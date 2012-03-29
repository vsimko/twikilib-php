<?php
namespace twikilib\core;

use twikilib\runtime\Container;
use \ReflectionObject;
use \ReflectionProperty;

/**
 * @author Viliam Simko
 */
class Serializer {

	/**
	 * This method should always be called from the __wakeup()
	 * of an object that needs dependency injection when unserialized.
	 * @param object $obj
	 * @return void
	 */
	static final public function wakeupHandler(IInjectedAfterUnserialization $obj) {
		self::$currentSerializer->injectDependencies($obj);
	}

	/**
	 * This static variable always contains the current Serializer
	 * instance performing the unserialization.
	 *
	 * It is set at the beginning of the $this->unserialize() method
	 * and then used in Serializer::wakeupHandler()
	 *
	 * @var Serializer
	 */
	static private $currentSerializer;

	/**
	 * Serialization directly to a file.
	 * @param string $filename
	 * @param mixed $value
	 * @return void
	 */
	final public function serializeToFile($filename, $value) {
		file_put_contents($filename, $this->serialize($value));
	}

	/**
	 * Unserialization directly from a file.
	 * @param string $filename
	 * @return mixed
	 */
	final public function unserializeFromFile($filename) {
		return $this->unserialize( file_get_contents($filename) );
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	final public function serialize($value) {
		return @serialize($value);
	}

	/**
	 * @param string $data
	 * @return mixed
	 */
	final public function unserialize($data) {
		Container::measureTime("Unserialization with dependency injection");
			self::$currentSerializer = $this;
			$unserialized = @unserialize($data);

			// use the raw data if not unserializable
			if( $unserialized === false && $data !== 'b:0;')
				$unserialized = $data;

		Container::measureTime();
		return $unserialized;
	}

	/**
	 * Instantiates an object and injects dependencies.
	 * @param string $className
	 * @param mixed $_ variable arguments
	 * @return object
	 */
	final public function createObject($className, $_ = null) {
		assert('/* TODO: not implemented yet */');
	}

//	private $processed;
//
//	/**
//	 * @deprecated
//	 * Helper recursive method.
//	 * @param mixed $obj
//	 */
//	private function injectRecursive($obj) {
//		if(is_array($obj)) {
//			foreach($obj as $item) {
//				$this->injectRecursive($item);
//			}
//		} elseif( is_object($obj) ) {
//
//			// preventing loops
//			$objhash = spl_object_hash($obj);
//			if( isset($this->processed[$objhash]) ) {
//				return;
//			}
//			$this->processed[$objhash] = true;
//
//			$ro = new ReflectionObject($obj);
//			foreach($ro->getProperties() as $rp) {
//				assert($rp instanceof ReflectionProperty);
//				$rp->setAccessible(true);
//				if( isset($this->properties[$rp->name]) ) {
//					$rp->setValue($obj, $this->properties[$rp->name]);
//				} else {
//					$this->injectRecursive( $rp->getValue($obj) );
//				}
//			}
//		}
//	}

	/**
	 * List of properties to be injected when an object calls
	 * Serializer::wakeupHandler() from it's __wakeup()
	 * method after being unserialized.
     * @var array of mixed
	 */
	private $properties = array();

	/**
	 * Setting properties used for dependency injection when objects are unserialized.
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	final public function __set($name, $value) {
		$this->properties[$name] = $value;
	}

	/**
	 * Injects dependencies to an object.
	 * @param object $obj
	 * @return void
	 */
	private function injectDependencies($obj) {
		$ro = new ReflectionObject($obj);
		array_walk( $this->properties,
			function ($propertyValue, $propertyName) use ($ro, $obj) {
				if( $ro->hasProperty($propertyName)) {
					$rp = $ro->getProperty($propertyName);
					$rp->setAccessible(true);
					$rp->setValue($obj, $propertyValue);
				}
			});
	}
}