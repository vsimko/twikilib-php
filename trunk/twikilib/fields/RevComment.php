<?php
namespace twikilib\fields;

use twikilib\utils\Encoder;
use twikilib\core\IRenderable;

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
?>