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
	 * @return string
	 */
	final public function getISOFormat() {
		return $this->getFormattedValue('Y-m-d', '0000-00-00');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\form\fields.TextField::setFieldValue()
	 */
	public function setFieldValue($newValue) {
		$dateValue = date('j M Y', strtotime($newValue));
		parent::setFieldValue( $dateValue );
	}
	
	/**
	 * Check whether the given date is within a date interval.
	 * @param string $lowerBoundDate Unbound if empty
	 * @param string $upperBoundDate Unbound if empty
	 */
	public function isWithinInterval($lowerBoundDate, $upperBoundDate) {
		
		$minday = $minday = date('Y-m-d', strtotime($lowerBoundDate));
		$maxday = date('Y-m-d', strtotime($upperBoundDate));
		
		$thisday = $this->getISOFormat();
		
		return ( empty($lowerBoundDate) || $thisday >= $minday)
			&& ( empty($upperBoundDate) || $thisday <= $maxday);
	}
}
?>