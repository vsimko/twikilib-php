<?php
namespace twikilib\form\fields;

use twikilib\form\IFormField;

use \Exception;
class DummyFieldException extends Exception {}

/**
 * Represents a field that contain empty value and cannot be changed.
 * It can be used as a placeholder for a field inaccessible for a user.
 * Singleton - only one instnace for everyone. However, multiple instances
 * may exists after an object has been unserialized.
 * 
 * @author Viliam Simko
 */
class DummyField implements IFormField {
	
	/**
	 * Private constructor due to the Singleton design pattern.
	 */
	private function __construct() {}
	
	/**
	 * Cloning not allowed for this class.
	 */
	final private function __clone() {}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::__toString()
	 */
	final public function __toString() {
		return '';
	}
	
	
	final public function toWikiString() {
		return '';
	}
	
	/**
	 * Creates a singleton instance of an empty field.
	 * @return DummyField
	 */
	static final public function getSingletonInstance() {
		static $instance;
		if( empty($instance) ) {
			$instance = new DummyField();
		}
		return $instance;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::isEmpty()
	 */
	final public function isEmpty() {
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::getFieldName()
	 */
	final public function getFieldName() {
		return '';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::getFieldValue()
	 */
	final public function getFieldValue() {
		return null;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::setFieldValue()
	 */
	final public function setFieldValue($newValue) {
		throw new DummyFieldException(__METHOD__);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::hasFieldAttr()
	 */
	final public function hasFieldAttr($attrSpec) {
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::getFormModel()
	 */
	final public function getFormModel() {
		return null;
	}
}
?>