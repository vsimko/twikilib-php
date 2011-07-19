<?php
namespace twikilib\form;


use twikilib\nodes\FormFieldNotFoundException;

use twikilib\utils\System;
use twikilib\utils\Encoder;
use twikilib\form\fields\ListField;
use twikilib\form\fields\TextField;
use twikilib\form\fields\DateField;
use twikilib\form\fields\UnknownField;
use twikilib\form\fields\DummyField;

/**
 * @author Viliam Simko
 */
class FieldFactory {
	
	/**
	 * Creates a singleton instance of an empty field
	 * @return DummyField
	 */
	static final public function getDummyField() {
		return DummyField::getSingletonInstance();
	}
	
	/**
	 * @param FieldTag $fieldTag
	 * @param FormModel $formModel
	 * @return IFormField
	 */
	static final public function createField(FieldTag $fieldTag, FormModel $formModel) {
		try {
			$fieldTypeDef = $formModel->getTypeByFieldName( $fieldTag->name );
			assert($fieldTypeDef instanceof  FieldTypeDef);
			
			// some information should be copied from the form model
			// regardless of those stored in the META:FIELD tag
			// when the topic is saved, these correct model definition will replace
			// the potentially obsolete META:FIELD tag information
			$fieldTag->attributes = $fieldTypeDef->attributes;
			$fieldTag->title = $fieldTypeDef->name;
			$fieldTag->name = preg_replace('/[^a-zA-Z0-9]/', '', $fieldTag->name);

			switch($fieldTypeDef->datatype) {
				case 'checkbox':
				case 'checkbox+buttons':
				case 'radio':
				case 'select':
				case 'select+multi':
				case 'select+values':
					return new ListField($fieldTag, $formModel);
									
				case 'date':
					return new DateField($fieldTag, $formModel);
					
				case 'label':
				case 'text':
				case 'textarea':
				default:
					return new TextField($fieldTag, $formModel);
			}
		} catch (FormFieldNotFoundException $e) {
			return new UnknownField($fieldTag, $formModel);
		}
		
		assert(/* could not reach this line */);
	}
	
	/**
	 * @param string $rawArgs
	 * @param FormModel $formModel
	 * @return IFormField
	 */
	static final public function createFieldFromRawArgs($rawArgs, FormModel $formModel) {
		$parsedArg = Encoder::parseWikiTagArgs($rawArgs);
		return self::createField( new FieldTag($parsedArg), $formModel );
	}
}
?>