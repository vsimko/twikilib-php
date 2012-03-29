<?php
namespace twikilib\form;
use twikilib\core\IRenderable;

/**
 * @author Viliam Simko
 */
interface IFormField extends IRenderable {

	/**
	 * @return twikilib\form\FormModel
	 */
	function getFormModel();

	/**
	 * Should return the field value, same as getFieldValue()
	 * @return string
	 */
	function __toString();

	/**
	 * @return boolean
	 */
	function isEmpty();

	/**
	 * @return string
	 */
	function getFieldName();

	/**
	 * @return string
	 */
	function getFieldValue();

	/**
	 * @param string $newValue
	 * @return void
	 */
	function setFieldValue($newValue);

	/**
	 * @param string $attrSpec
	 * @return boolean
	 */
	function hasFieldAttr($attrSpec);
}