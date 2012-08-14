<?php
namespace twikilib\core;

use twikilib\runtime\Container;
use \ReflectionObject;
use \ReflectionProperty;

/**
 * This class implements serialization / unserialization of objects that need
 * to be injected after unserialization.
 * The serializer can be configured to inject specific properties (private/public) within objects
 * that contain a definition of such a property.
 *
 * @author Viliam Simko

 * @example <pre>
 * class X {
 *   final public function __wakeup() {Serializer::wakeupHandler($this);}
 * }
 * class Y extends X {
 *   public $attrToInject;
 * }
 *
 * $s = new Serializer;
 * $s->attrToInject = 'myvalue';
 * $data = $s->serialize( array(new X, new Y) );
 * $list = $s->unserialize($data);
 *
 * assert( is_array($list) );
 * assert( $list[0] instanceof X );
 * assert( $list[1] instanceof Y );
 * assert( empty($list[0]->attrToInject) );
 * assert( $list[1]->attrToInject == 'myvalue' );
 * </pre>
 *
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
		assert(!empty($filename));
		if( !empty($filename) ) {
			file_put_contents($filename, $this->serialize($value));
		}
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