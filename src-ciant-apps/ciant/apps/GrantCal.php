<?php
namespace ciant\apps;

use twikilib\core\ITopic;

use twikilib\runtime\Logger;
use twikilib\fields\TextSection;
use twikilib\core\MetaSearch;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @runnable
 *
 * Requested by Michal Masa 2011-06-06:
 * Collect "Deadline" seactions from all topic with FundingProgrammeForm.
 * Store all deadlines to GrantCalendarNew where:
 * - section heading = funding programme name
 * - section content = deadlines as input for CalendarPlugin
 *
 * See topic Main.GrantCalendarNew
 * @author Viliam Simko
 */
class GrantCal {

	public function run() {
		Logger::disableLogger();
		@header('Content-type: text/plain');

		$config = new Config('config.ini');
		$config->pushStrictMode(false);
		$db = new FilesystemDB($config);

		$search = new MetaSearch($config);
		$search->setFormNameFilter('FundingProgrammeForm');
		$search->executeQuery();
		foreach ($search->getResults() as $topicName) {

			assert( is_string($topicName) ) ;
			$topic = $db->loadTopicByName($topicName);
			assert($topic instanceof ITopic);

			$dlSection = $topic->getTopicTextNode()->getSectionByName('Deadlines');

			if( $dlSection instanceof TextSection && !$dlSection->isEmpty() ) {
				$programmeName = $topic->getTopicFormNode()->getFormField('Name')->getFieldValue();
				echo '---+++ '.$programmeName."\n";
				echo 'Extracted from : [['.$topicName."]]\n";
				echo $dlSection->toWikiString();
				echo "\n";
			}
		}
	}
}