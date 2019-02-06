<?php
namespace ciant\uploader\fields;

interface IFormField {

	const OPTIONAL = 0;
	const MANDATORY = 1;

	/**
	 * @return string
	 */
	function getName();

	/**
	 * @return string
	 */
	function getTitle();

	/**
	 * @return string
	 */
	function getHtml();

	/**
	 * @return boolean
	 */
	function isMandatory();

	/**
	 * @return mixed
	 */
	function getValue();

	/**
	 * @param mixed $value
	 * @return void
	 */
	function setValue($value);
}
?>