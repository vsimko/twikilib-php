<?php
namespace ciant\uploader\fields;

class InputFormField implements IFormField {

	private $fieldName;
	private $fieldTitle;
	private $mandatory = false;

	/**
	 * @param string $fieldName
	 * @param string $fieldTitle
	 */
	final public function __construct($fieldTitle, $flags = IFormField::OPTIONAL) {
		$this->fieldTitle = $fieldTitle;
		$this->fieldName = md5($fieldTitle);

		$this->mandatory = (boolean) ($flags & IFormField::MANDATORY);
	}

	final public function getTitle() {
		return $this->fieldTitle;
	}

	final public function getName() {
		return $this->fieldName;
	}

	public function getHtml() {
		return '<input type="text" name="'.$this->getName().'" value="'.htmlspecialchars($this->getValue()).'"/>';
	}

	public function isMandatory() {
		return $this->mandatory;
	}

	public function __toString() {
		return __CLASS__.': '.$this->getName().'='.$this->getValue();
	}

	private $value;
	public function setValue($value) {
		$this->value = $value;
	}

	public function getValue() {
		return $this->value;
	}
}
?>