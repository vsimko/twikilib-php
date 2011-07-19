<?php
namespace twikilib\form\fields;

use twikilib\form\FormModel;
use twikilib\form\FieldTag;

/**
 * @author Viliam Simko
 */
class DateField extends TextField {
	
	/**
	 * @param FieldTag $fieldTag
	 * @param FormModel $formModel
	 */
	final public function __construct(FieldTag $fieldTag, FormModel $formModel) {
		if( !empty($fieldTag->value) ) {
			$fieldTag->value =  date('j M Y', strtotime($fieldTag->value));
		}
		
		parent::__construct($fieldTag, $formModel);
	}

	/**
	 * @param string $format same as the format of date() function
	 * @return string
	 */
	final public function getFormattedValue($format, $defaultValueIfEmpty = '') {
		if($this->isEmpty())
			return $defaultValueIfEmpty;
			
		return date($format, strtotime($this->getFieldValue()));
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form\fields.TextField::setFieldValue()
	 */
	public function setFieldValue($newValue) {
		$dateValue = date('j M Y', strtotime($newValue));
		parent::setFieldValue( $dateValue );
	}
}
?>