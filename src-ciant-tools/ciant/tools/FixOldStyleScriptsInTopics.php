<?php
namespace ciant\tools;

use twikilib\core\MetaSearch;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use \Exception;

/**
 * @runnable
 * @author Viliam Simko
 */
class FixOldStyleScriptsInTopics {

	private $performSave = false;

	public function __construct($params) {
		if(@$params['help'])
			throw new Exception("Parameters: save (optional, default:false)");

		if(@$params['save'] === '' || @$params['save'] == 'true' || @$params['save'] == 1)
			$this->performSave = true;
	}

	public function run() {

		header('Content-type: text/plain');

		$config = new Config('config.ini');
		$db = new FilesystemDB($config);

		$config->pushStrictMode(false);
		$search = new MetaSearch($config);

		$search->setRawTextFilter('%PHPSCRIPT');
		$search->executeQuery();

		foreach($search->getResults() as $topicName) {
			$topic = $db->loadTopicByName($topicName);
			$textNode = $topic->getTopicTextNode();
			$textNode->isTextChanged = false;
			$textNode->replaceText('/%PHPSCRIPT{"?projectcalevents"?}%/', '%PHPSCRIPT{ciant.apps.ProjectCalEvents}%');
			$textNode->replaceText('/%PHPSCRIPT{"?extractmails"?}%/', '%PHPSCRIPT{ciant.apps.ExtractMails}%');
			$textNode->replaceText('/%PHPSCRIPT{"?organisations"?}%/', '%PHPSCRIPT{ciant.apps.ListOrganisations}%');
			$textNode->replaceText('/%PHPSCRIPT{"?grantcal"?}%/', '%PHPSCRIPT{ciant.apps.GrantCal}%');
			$textNode->replaceText('/%PHPSCRIPT{"?genid"?}%/', '%PHPSCRIPT{ciant.apps.GenId}%');
			$textNode->replaceText('/%PHPSCRIPT{"?fundingprogrammes"?}%/', '%PHPSCRIPT{ciant.apps.ListFundingProgrammes}%');
			$textNode->replaceText('/%PHPSCRIPT{"?allprojects"?}%/', '%PHPSCRIPT{ciant.apps.AllProjects}%');
			$textNode->replaceText('/%PHPSCRIPT{"?allevents"?}%/', '%PHPSCRIPT{ciant.apps.AllEvents}%');

			if($textNode->isTextChanged) {
				echo "$topicName - changed ";
				if($this->performSave) {
					$db->saveTopic($topic);
					echo "(saved)";
				} else {
					echo "(not saved)";
				}
				echo "\n";
			} else {
				echo "$topicName - ignored\n";
			}
		}
	}
}