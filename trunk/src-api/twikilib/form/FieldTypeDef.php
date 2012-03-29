<?php
namespace twikilib\form;

class FieldTypeDef {
	public $name;
	public $datatype;
	public $size;
	public $default;
	public $tooltip;
	public $attributes;

	/**
	 * @param string $attrSpec
	 * @return boolean
	 */
	final public function hasAttribute($attrSpec) {
		assert( strlen($attrSpec) == 1);
		return strpos($this->attributes, $attrSpec) !== false;
	}
}