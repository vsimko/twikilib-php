<?php
namespace ciant\apps;

use twikilib\wrap\UnknowTopicTypeException;

use twikilib\runtime\Logger;
use twikilib\utils\Encoder;
use twikilib\core\TopicNotFoundException;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use ciant\wrap\CiantOrg;
use ciant\wrap\CiantUser;
use ciant\wrap\CiantWrapFactory;
use ciant\search\Organisations;

/**
 * @runnable
 * @author Viliam Simko
 */
class TableOfPartnerOrgs {

	final public function run() {
		Logger::disableLogger();
		header('Content-type: text/plain');

		$config = new Config('config.ini');
		$db = new FilesystemDB($config);

		$config->pushStrictMode(false);

		echo "|*Organisation Name*|*Address*|*Options*|*Emails*|\n";

		$lister = new Organisations($config, $db);
		$found = array();

		foreach($lister->getAllOrganisations() as $orgTopic) {
			assert($orgTopic instanceof CiantOrg);

			$orgTopicName = $orgTopic->getWrappedTopic()->getTopicName();

			$fieldOptions = $orgTopic->getWrappedTopic()->getTopicFormNode()->getFormField('Options')->getFieldValue();

			if( preg_match('/Partner/', $fieldOptions) ) {

				// prepare attributes that can be taken directly from the organisation topic
				$orgName = $orgTopic->getName();
				$orgName = str_replace('|', ' ', $orgName );
				$orgName = Encoder::filterStringLength( $orgName, 40);
				$orgStreet = $orgTopic->getWrappedTopic()->getTopicFormNode()->getFormField('Address')->getFieldValue();
				$orgCity = $orgTopic->getCity();
				$orgZIP = $orgTopic->getWrappedTopic()->getTopicFormNode()->getFormField('Post Code')->getFieldValue();
				$orgCountry = $orgTopic->getCountry();

				// now collect all email addresses
				$collectedEmails = array();

				// first email is taked directly from the organisation topic "Email" field
				$collectedEmails[] = $orgTopic->getWrappedTopic()->getTopicFormNode()->getFormField('Email')->getFieldValue();

				// now collect email addresses from related users
				$fieldStatRep = $orgTopic->getWrappedTopic()->getTopicFormNode()->getFormField('Statutory Representative');
				$fieldContactPerson = $orgTopic->getWrappedTopic()->getTopicFormNode()->getFormField('ContactPerson');

				// trying to extract topic names from plain text
				$statRep = Encoder::createSingleLineText( $fieldStatRep->getFieldValue() );
				$contactPerson = Encoder::createSingleLineText( $fieldContactPerson->getFieldValue() );

				$found = array_merge(
					Encoder::extractWikiNamesFromString($statRep),
					Encoder::extractWikiNamesFromString($contactPerson)
				);

				foreach($found as $topicName) {
					try {
						$topic = $db->loadTopicByName($topicName);
						$wrap = CiantWrapFactory::getWrappedTopic($topic);

						if( ! $wrap instanceof CiantUser) {
							throw new UnknowTopicTypeException("$topicName is not a user");
						}

						$collectedEmails = array_merge($collectedEmails, $wrap->getAllEmailsAsArray(null, true));

					} catch(TopicNotFoundException $e) {
						// ignore
					} catch(UnknowTopicTypeException $e) {
						// ignore
					}
				}
				$collectedEmails = array_filter($collectedEmails);
				$collectedEmails = array_unique($collectedEmails);

				$orgEmails = implode('; ', $collectedEmails);

				echo "| [[$orgTopicName][$orgName]] | Street: $orgStreet<br> City: $orgCity<br>ZIP: $orgZIP<br>$orgCountry | $fieldOptions | $orgEmails |\n";
			}
		}
	}
}