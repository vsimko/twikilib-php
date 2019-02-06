<?php
namespace ciant\tools\eventsToWiki;

use twikilib\runtime\Logger;

use twikilib\utils\CSVTable;

use twikilib\core\Config;
use twikilib\core\ITopic;
use twikilib\core\PrefsFactory;
use twikilib\core\ITopicFactory;

use twikilib\fields\Preference;

use twikilib\nodes\TopicAttachmentsNode;
use twikilib\nodes\TopicFormNode;
use twikilib\nodes\TopicPrefsNode;
use twikilib\nodes\TopicInfoNode;
use twikilib\nodes\RevCommentsNode;

use \Exception;
class ImporterException extends Exception {}

/**
 * @author Viliam Simko
 */
class Importer {

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	/**
	 * @var array
	 */
	private $importedTopics;

	/**
	 * @var string
	 */
	private $importedFormName;

	/**
	 * @var string
	 */
	private $importedViewTemplate;

	/**
	 * @param Config $twikiConfig
	 * @param ITopicFactory $topicFactory
	 */
	final public function __construct(Config $twikiConfig, ITopicFactory $topicFactory) {
		$this->twikiConfig = $twikiConfig;
		$this->topicFactory  = $topicFactory;
	}

	/**
	 * Loads a topic that contains form model.
	 * @param string $formName
	 */
	final public function setImportedFormName($formName) {
		$this->importedFormName = $formName;
	}

	/**
	 * @param string $viewTemplateName
	 */
	final public function setImportedViewTemplate($viewTemplateName) {
		$this->importedViewTemplate = $viewTemplateName;
	}

	/**
	 * Loads a spreadsheet table encoded in CSV into a PHP array structure $rows.
	 * @param string $filename
	 */
	final public function loadCSVFromFile($filename) {

		$table = new CSVTable();
		$table->loadFromFile($filename);

		$this->importedTopics = array();
		for ($i=0; $i < $table->getRowCount(); ++$i) {
			$row = $table->getRowByNumber($i);
			$topicName = $row['TopicName'];

			if( isset($this->importedTopics[$topicName]) )
				throw new ImporterException("Duplicate definition of topic: $topicName");

			$this->importedTopics[$topicName] = $this->createTopic($row);
		}
	}

	/**
	 * @return ITopic
	 */
	private function createTopic(array $data) {

		$topicName = preg_replace('/[^a-zA-Z0-9]/', '', $data['TopicName']);
		unset($data['TopicName']);

		$parentTopicName = preg_replace('/[^a-zA-Z0-9]/', '', $data['ParentTopic']);
		unset($data['ParentTopic']);

		$topic = $this->topicFactory->createEmptyTopic($topicName);
		assert($topic instanceof ITopic);

		$topic->getTopicTextNode()->setText(
		"---++ Very important information about the imported data\n".
		"   * This topic has been imported from Karolina's CSV file.\n".
		"   * You can't edit this topic at the moment because the imported data needs to be reviewed.\n".
		"	* These imported topics still need some processing before they can be edited manually\n".
		"   * Should you find any error, please send it to viliam.simko@ciant.cz\n");

		// info
		$infoNode = $topic->getTopicInfoNode();
		assert($infoNode instanceof TopicInfoNode);
		$infoNode->setParentName($parentTopicName);

		// form
		$formNode = $topic->getTopicFormNode();
		assert($formNode instanceof TopicFormNode);
		$formNode->setFormName($this->importedFormName);

		// form fields
		foreach($data as $fieldName => $value) {
			$value = trim($value);
			if( !empty($value) ) {
				$formNode->getFormField($fieldName)->setFieldValue($value);
			}
		}

		// some preferences
		$prefsNode = $topic->getTopicPrefsNode();
		assert($prefsNode instanceof TopicPrefsNode);
		if(!empty($this->importedViewTemplate)) {
			$pref = PrefsFactory::createViewTemplatePref($this->importedViewTemplate);
			$prefsNode->addPreference($pref);
		}

		// TODO: nobody should change the topic content
		$prefsNode->addPreference(PrefsFactory::createAllowTopicChangePref(array('AdminUser')));

		// some attachments
		$attachNode = $topic->getTopicAttachmentsNode();
		assert($attachNode instanceof TopicAttachmentsNode);

		// some revcomments
		$revNode = $topic->getRevCommentsNode();
		assert($revNode instanceof RevCommentsNode);

		return $topic;
	}

	/**
	 * Data from the $otherTopic will be copied to the $topic.
	 * @param ITopic $topic
	 * @param ITopic $otherTopic
	 * @return void
	 */
	final public function mergeTopics(ITopic $topic, ITopic $otherTopic) {

		// update topic author
		$otherAuthor = $otherTopic->getTopicInfoNode()->getTopicAuthor();
		$topic->getTopicInfoNode()->setTopicAuthor($otherAuthor);

		// update topic parent
 		// =======================================================================
		$otherParentName = $otherTopic->getTopicInfoNode()->getParentName();
		$topic->getTopicInfoNode()->setParentName( $otherParentName );

		// update form fields
 		// =======================================================================
		$topicFormNode = $topic->getTopicFormNode();
		assert($topicFormNode instanceof TopicFormNode);
 		foreach($otherTopic->getTopicFormNode()->getAllFields() as $field) {
 			$topicFormNode->setFormField($field);
 		}

 		// update preferences
 		// =======================================================================
 		$otherPrefs = $otherTopic->getTopicPrefsNode()->getAllPreferences();

 		// first, delete all preferences that appeared in the 'otherTopic'
 		foreach($otherPrefs as $pref) {
 			assert($pref instanceof Preference);
 			$topic->getTopicPrefsNode()->deletePreferencesByName( $pref->getName() );
 		}

 		// now copy the preferences from 'otherTopic' to 'topic'
 		foreach($otherPrefs as $pref) {
 			assert($pref instanceof Preference);
 			$topic->getTopicPrefsNode()->addPreference($pref);
 		}
	}

	/**
	 * @return array
	 */
	final public function getImportedTopics() {
		return $this->importedTopics;
	}

	/**
	 * @param string $webName
	 */
	final public function cleanupTmpImport($webName) {
		$path = $this->twikiConfig->getWebDataDir($webName);
		Logger::log("Scanning dir: $path");
		foreach(glob("$path/*.txt") as $filename) {
			if( ! is_link($filename)) {
				Logger::log("DELETING FILE: $filename");
				unlink($filename);
			}
		}
	}
}