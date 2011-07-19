<?php
namespace twikilib\fields;

use twikilib\utils\Encoder;
use twikilib\core\IRenderable;

/**
 * @author Viliam Simko
 */
class Preference implements IRenderable {
	
	/**
	 * @var object
	 */
	private $args;
	
	final public function __construct($args) {
		$this->args = (object) $args;
	}
	
	final public function toWikiString() {
		return Encoder::createWikiTag('META:PREFERENCE', $this->args)."\n";
	}
	
	final public function __toString() {
		assert(/* conversion to string not supported */);
	}
	
	final public function getName() {
		return $this->args->name;
	}
	
	final public function getValue() {
		return $this->args->value;
	}
	
}
?>