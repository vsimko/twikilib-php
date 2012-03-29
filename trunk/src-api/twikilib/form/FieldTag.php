<?php
namespace twikilib\form;

use twikilib\utils\Encoder;
use twikilib\core\IRenderable;

/**
 * Container for the arguments extracted from a FORM:FIELD tag.
 * @author Viliam Simko
 */
class FieldTag implements IRenderable {

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $attributes;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $value;

	//... + dynamically created properties

	/**
	 * Fills properties using the extracted arguments.
	 * @param array $args
	 */
	final public function __construct($args) {
		foreach($args as $argName => $argValue) {
			$this->$argName = $argValue;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IRenderable::toWikiString()
	 */
	final public function toWikiString() {
		return Encoder::createWikiTag('META:FIELD', $this )."\n";
	}
}