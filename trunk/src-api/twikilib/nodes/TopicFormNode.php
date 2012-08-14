<?php
namespace twikilib\nodes;

use twikilib\form\FieldTag;
use twikilib\core\ParseNodeException;
use twikilib\form\FieldFactory;
use twikilib\form\FormModel;
use twikilib\form\IFormField;
use twikilib\utils\Encoder;
use twikilib\core\ITopic;
use twikilib\core\IParseNode;

use \Exception;
class FormFieldNotFoundException extends Exception {}
class FormFieldNotPublishedException extends Exception {}

/**
 * @author Viliam Simko
 */
class TopicFormNode implements IParseNode {

	/**
	 * @var twikilib\form\FormModel
	 */
	private $formModel;

	/**
	 * An array containig IFormField objects indexed by hashed field name
	 * @var array of IFormField
	 */
	private $formFields = array();

	/**
	 * @var twikilib\core\ITopic
	 */
	private $topicContext;

	final public function __construct(ITopic $topicContext) {
		$this->topicContext = $topicContext;
	}

	/**
	 * Cloning not allowed for this class.
	 */
	final private function __clone() {}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IParseNode::getPattern()
	 */
	final public function getPattern() {
		return '/%META:(FORM|FIELD)\{(.*)\}%\n/'; //([^%]|%[0-9a-f][0-9a-f])*
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IParseNode::onPatternMatch()
	 */
	final public function onPatternMatch(array $match) {
		$this->{"match_$match[1]"}($match[2]);
	}

	/**
	 * Matching the META:FORM tag.
	 * @param string $rawArgs
	 * @return void
	 * @throws ParseNodeException
	 */
	private function match_FORM($rawArgs) {
		if( ! empty($this->formModel) )
			throw new ParseNodeException("Multiple META:FORM tags detected");

		$formName = Encoder::parseWikiTagArgs($rawArgs)->name;
		$this->setFormName( $formName );
	}

	/**
	 * Matching the META:FORM tag.
	 * @param string $rawArgs
	 * @return void
	 */
	private function match_FIELD($rawArgs) {
		$field = FieldFactory::createFieldFromRawArgs($rawArgs, $this->formModel);
		$hash = self::getFieldHash( $field->getFieldName() );
		$this->formFields[ $hash ] = $field;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IRenderable::toWikiString()
	 */
	final public function toWikiString() {

		if( empty($this->formModel) )
			return '';

		return $this->formModel->toWikiString().
				Encoder::arrayToWikiString($this->formFields);
	}

	/**
	 * @param string $formName
	 * @return void
	 */
	final public function setFormName($formName) {

		// use the web name of the topic to which this form is attached
		if( strpos('.', $formName) === false ) {
			list($webOfTopic) = explode('.', $this->topicContext->getTopicName());
			$formName = $webOfTopic.'.'.$formName;
		}

		if( ! empty($this->formModel) )
			throw new Exception("Replacing FormModel not supported");

		$this->formModel = new FormModel(
			$formName, $this->topicContext->getTopicFactory() );
	}

	/**
	 * @return string
	 */
	final public function getFormName() {
		return $this->formModel instanceof FormModel
			? $this->formModel->getFormName()
			: '';
	}

	/**
	 * Creates a hashed version of the fieldName,
	 * which is useful as an index.
	 *
	 * @param string $fieldName
	 * @return string
	 */
	static public function getFieldHash($fieldName) {
		return preg_replace('/[^a-z0-9_]/', '', strtolower($fieldName) );
	}

	/**
	 * Checks if the given field has already been instantiated.
	 * @param string $fieldName
	 * @return boolean
	 */
	private function isFieldCreated($fieldName) {
		return isset($this->formFields[ self::getFieldHash($fieldName) ]);
	}

	/**
	 * Null is returned only if the field has not been found and strict mode is disabled.
	 * @param string $fieldName
	 * @return twikilib\form\IFormField
	 * @throws FormFieldNotFoundException
	 */
	final public function getFormField($fieldName) {
		assert( is_string($fieldName) );

		// only a single "@" is allowed, which represents the translated version of the field
		assert( strlen(preg_replace('/[^@]/', '', $fieldName)) <= 1 );

		$twikiConfig = $this->topicContext->getConfig();

		if( ! $this->isFieldCreated($fieldName) ) {
			if( $this->formModel->isFieldDefined($fieldName) ) {
				// the field is not stored as META:FIELD tag in the topic
				// but it is defined in the FormModel and we will now create the tag
				$field = $this->formModel->createFieldFromModel($fieldName);
				$this->formFields[ self::getFieldHash($fieldName) ] = $field;
				return $field;
			} else {
				// the field is not stored in the topic META:FIELD tag and also
				// not defined by FormModel
				if( $twikiConfig->useStrictPublishedMode ) {
					throw new FormFieldNotFoundException(
						$fieldName." in topic ".$this->topicContext->getTopicName() );
				}
				return FieldFactory::getDummyField();
			}
		}

		assert( $this->isFieldCreated($fieldName) );

		// try to use a translated version of the field if available
		if( $twikiConfig->language && strpos($fieldName, '@') === false) {
			try {
				$translatedField = $this->getFormField( $fieldName.' @'.$twikiConfig->language );
				if( is_object($translatedField) && ! $translatedField->isEmpty() ) {
					return $translatedField;
				}
			} catch(Exception $e) { /* ignore any exception */ }
		}

		// translated version not found, just return the field value
		$hash = self::getFieldHash($fieldName);
		$field = $this->formFields[$hash];

		// checking restrictions
		if( $twikiConfig->useStrictPublishedMode && !$field->hasFieldAttr('P') ) {
			throw new FormFieldNotPublishedException(
				$fieldName.' in class '.$this->topicContext->getTopicName());
		}

		assert( $field instanceof IFormField );
		return $field;
	}
}