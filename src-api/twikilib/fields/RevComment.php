<?php
namespace twikilib\fields;

use twikilib\utils\Encoder;
use twikilib\core\IRenderable;

/**
 * Represents a single META:REVCOMMENT item inside the topic text.
 * @author Viliam Simko
 */
class RevComment implements IRenderable {

	private $args;

	final public function __construct($args) {
		$this->args = (object) $args;
	}

	final public function toWikiString() {
		return Encoder::createWikiTag('META:REVCOMMENT', $this->args)."\n";
	}

	/**
	 * TODO: try to make this function private instead of assert
	 */
	final public function __toString() {
		assert('/* conversion to string not supported */');
	}
}