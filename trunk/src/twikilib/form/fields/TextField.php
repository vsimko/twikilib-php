<?php
namespace twikilib\form\fields;

use twikilib\form\FieldTypeDef;

use twikilib\form\FormModel;

use twikilib\form\FieldTag;

use twikilib\utils\Encoder;
use twikilib\core\IRenderable;
use twikilib\form\IFormField;

/**
 * Decorator for the FieldTag object that represents a textual field type.
 * @author Viliam Simko
 */
class TextField implements IFormField {

	/**
	 * @var twikilib\form\FieldTag
	 */
	private $fieldTag;
	
	/**
	 * @var twikilib\form\FormModel
	 */
	private $formModel;
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::getFormModel()
	 */
	final public function getFormModel() {
		return $this->formModel;
	}
	
	/**
	 * @param FieldTag $fieldTag
	 * @param FormModel $formModel
	 */
	public function __construct(FieldTag $fieldTag, FormModel $formModel) {
		$this->fieldTag = $fieldTag;
		$this->formModel = $formModel;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::__toString()
	 */
	final public function __toString() {
		return $this->getFieldValue();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IRenderable::toWikiString()
	 */
	final public function toWikiString() {
		return $this->fieldTag->toWikiString();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::getFieldValue()
	 */
	final public function getFieldValue() {
		return $this->fieldTag->value;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::isEmpty()
	 */
	final public function isEmpty() {
		return empty($this->fieldTag->value);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::setFieldValue()
	 */
	public function setFieldValue($newValue) {
		assert( is_string($newValue) );
		$this->fieldTag->value = $newValue;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::getFieldName()
	 */
	final public function getFieldName() {
		return $this->fieldTag->name;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form.IFormField::hasFieldAttr()
	 */
	final public function hasFieldAttr($attrSpec) {
		// always use the FormModel to check attributes
		// TODO: what if the META:FIELD tag exists but the field is not defined in the FormModel
		return $this->formModel->getTypeByFieldName( $this->getFieldName() )->hasAttribute($attrSpec);
	}
}
?>