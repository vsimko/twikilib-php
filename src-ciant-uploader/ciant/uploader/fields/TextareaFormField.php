<?php
namespace ciant\uploader\fields;

class TextareaFormField extends InputFormField {

	final public function getHtml() {
		return '<textarea name="'.$this->getName().'" rows="4" cols="50">'
			.htmlspecialchars($this->getValue())
			.'</textarea>';
	}
}
?>