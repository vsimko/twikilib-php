<?php
namespace ciant\uploader\fields;

class EmailFormField extends InputFormField {
	
	// TODO: not used
	final public function processValue($value) {
		if(preg_match('/@/', $value)) {
			return "[[mailto:$value][$value]]";
		}
		return 'No Email';
	}
}
?>