<?php
namespace twikilib\form;

use twikilib\nodes\FormFieldNotFoundException;
use twikilib\form\fields\TextField;
use twikilib\form\FieldTypeDef;
use twikilib\fields\Table;
use twikilib\nodes\TopicFormNode;
use twikilib\core\ITopic;
use twikilib\utils\Encoder;
use twikilib\core\IRenderable;
use twikilib\core\ITopicFactory;

/**
 * @author Viliam Simko
 */
class FormModel implements IRenderable {

	/**
	 * @var twikilib\core\ITopic
	 */
	private $formTopic;

	/**
	 * @var array of twikilib\form\FieldTypeDef
	 */
	private $fieldTypes;

	/**
	 * @param string $formName
	 * @param ITopicFactory $topicFactory
	 */
	public function __construct($formName, ITopicFactory $topicFactory) {

		// find the topic representing the form
		$this->formTopic = $topicFactory->loadTopicByName( $formName );
		assert($this->formTopic instanceof ITopic);

		// now extract field-definitions from the table inside the topic representing the form
		$tables = $this->formTopic->getTopicTextNode()->getTablesFromText();
		$formTable = $tables[0]; // it is always the first table in the topic
		assert($formTable instanceof Table);

		foreach($formTable as $row) {
			$fieldType = new FieldTypeDef();
			list(	$fieldType->name,
					$fieldType->datatype,
					$fieldType->size,
					$fieldType->default,
					$fieldType->tooltip,
					$fieldType->attributes ) = $row;
			$hash = TopicFormNode::getFieldHash( $fieldType->name );
			$this->fieldTypes[$hash] = $fieldType;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IRenderable::toWikiString()
	 */
	final public function toWikiString() {
		//$args = array( 'name' => $this->formTopic->getTopicName() );
		$args = array( 'name' => $this->getFormName() );
		return Encoder::createWikiTag('META:FORM', $args )."\n";
	}

	/**
	 * Returns just the form name (without web name)
	 * @return string
	 */
	final public function getFormName() {

		// contains web.topic
		$topicName = $this->formTopic->getTopicName();

		// converted to an object with (web,topic) fields
		$parsedTopicName = $this->formTopic->getConfig()->parseTopicName($topicName);

		return $parsedTopicName->topic;
	}

	/**
	 * @param string $fieldName
	 * @return boolean
	 */
	final public function isFieldDefined($fieldName) {
		$hash = TopicFormNode::getFieldHash($fieldName);
		return isset( $this->fieldTypes[$hash] );
	}

	/**
	 * @param string $fieldName
	 * @return twikilib\form\FieldTypeDef
	 * @throws twikilib\nodes\FormFieldNotFoundException
	 */
	final public function getTypeByFieldName($fieldName) {
		$hash = TopicFormNode::getFieldHash($fieldName);
		if( ! isset($this->fieldTypes[$hash]) )
			throw new FormFieldNotFoundException("Field type '$fieldName' not defined in the form model");

		return $this->fieldTypes[$hash];
	}

	/**
	 * @param string $fieldName
	 * @return twikilib\form\IFormField
	 */
	final public function createFieldFromModel($fieldName) {
		$fieldTag = new FieldTag(array(
			'name'			=> $fieldName, //TODO: should be hashed for wiki to understand
			'value'			=> '',
		));

		return FieldFactory::createField($fieldTag, $this);
	}
}