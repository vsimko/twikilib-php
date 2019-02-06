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

	/**
	 * @return string
	 */
	final public function toWikiString() {
		return Encoder::createWikiTag('META:PREFERENCE', $this->args)."\n";
	}

	/**
	 * @return string
	 */
	final public function __toString() {
		assert(/* conversion to string not supported */);
		return '';
	}

	/**
	 * @return string
	 */
	final public function getName() {
		return $this->args->name;
	}

	/**
	 * @return string
	 */
	final public function getValue() {
		return $this->args->value;
	}

//	/**
//	 * @param array $regexDelimiter
//	 */
//	final public function getValueAsList( $regexDelimiter = '/[,;] */') {
//		return preg_split($regexDelimiter, $this->getValue());
//	}
}