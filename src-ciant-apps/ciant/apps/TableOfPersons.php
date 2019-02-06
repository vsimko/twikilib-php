<?php
namespace ciant\apps;

use twikilib\wrap\UnknowTopicTypeException;

use twikilib\runtime\Logger;
use ciant\search\Organisations;
use ciant\wrap\CiantUser;
use ciant\wrap\CiantWrapFactory;
use ciant\wrap\CiantOrg;
use twikilib\core\Config;
use twikilib\core\FilesystemDB;
use twikilib\core\TopicNotFoundException;
use twikilib\utils\Encoder;

/**
 * @runnable
 * @author Viliam Simko
 */
class TableOfPersons {
	private $filterOptionsField;

	public function __construct($params) {
		if(@$params['help'])
			throw new \Exception("Optional parameter: options");

		// this parameter "options" is optional
		// in shell: /var/www/wiki/lib/PHP/phpscript table_of_persons options "Is Partner"
		// in browser: https://wiki.ciant.cz/lib/PHP/ciant/scripts/table_of_persons.php?options=Is%20Partner
		// in wiki: %{PHPSCRIPT "table_of_persons" options="Is Partner"}%
		$this->filterOptionsField = @$params['options'];
	}

	final public function run() {

		Logger::disableLogger();
		header('Content-type: text/plain');

		$config = new Config('config.ini');
		$db = new FilesystemDB($config);

		$config->pushStrictMode(false);

		// In this phase, the script collects topic names that represent
		// statutory representatives and contact persons of all organisations
		// matching the "options" parameter.
		// The topic names will be collected to the $found variable.
		// These topics may not exist or may be of any type.
		$lister = new Organisations($config, $db);
		$found = array();

		foreach( $lister->getAllOrganisations() as $orgTopic) {
			assert($orgTopic instanceof CiantOrg);
			$topicName = $orgTopic->getWrappedTopic()->getTopicName();

			$fieldOptions = $orgTopic->getWrappedTopic()->getTopicFormNode()->getFormField('Options')->getFieldValue();
			$optList = explode(',', $fieldOptions);

			array_walk($optList, function( & $value) {
				$value = trim($value);
			});

			if(empty($this->filterOptionsField) || in_array($this->filterOptionsField, $optList )) {

				$fieldStatRep = $orgTopic->getWrappedTopic()->getTopicFormNode()->getFormField('Statutory Representative');
				$fieldContactPerson = $orgTopic->getWrappedTopic()->getTopicFormNode()->getFormField('ContactPerson');

				$statRep = Encoder::createSingleLineText( $fieldStatRep->getFieldValue() );
				$contactPerson = Encoder::createSingleLineText( $fieldContactPerson->getFieldValue() );

				$found = array_merge(
					$found,
					Encoder::extractWikiNamesFromString($statRep),
					Encoder::extractWikiNamesFromString($contactPerson)
					);
			}
		}

		// We need to remove empty strings and duplicates
		sort($found);
		$found = array_filter($found);
		$found = array_unique($found);


		// all warnings will be collected here
		$warnings = array();


		// we will show only topics that represent users
		// everything else will be stored as warning
		echo "| *First Name* | *Last Name* | *Public Email* | *System Email* | *Topic* |\n";
		foreach($found as $topicName) {
			try {
				$topic = $db->loadTopicByName($topicName);
				$wrap = CiantWrapFactory::getWrappedTopic($topic);

				if( ! $wrap instanceof CiantUser)
					throw new UnknowTopicTypeException("$topicName is not a user");

				$firstName = $wrap->getWrappedTopic()->getTopicFormNode()->getFormField('FirstName')->getFieldValue();
				$lastName = $wrap->getWrappedTopic()->getTopicFormNode()->getFormField('LastName')->getFieldValue();
				$publicEmail = $wrap->getPublicEmail();
				$allEmails = $wrap->getAllEmails(null, false);
				echo "| $firstName | $lastName | $publicEmail | $allEmails | $topicName |\n";

			} catch(TopicNotFoundException $e) {
				$warnings[] = $e->getMessage();
			} catch(UnknowTopicTypeException $e) {
				$warnings[] = $e->getMessage();
			}
		}

		echo "\n";
		foreach($warnings as $w) {
			echo "   * WARNING: $w\n";
		}
	}
}