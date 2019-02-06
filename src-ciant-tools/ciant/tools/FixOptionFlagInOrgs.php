<?php
namespace ciant\tools;

use twikilib\runtime\Logger;
use ciant\wrap\CiantOrg;
use ciant\wrap\CiantProject;
use ciant\search\CiantProjects;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use \Exception;

/**
 * @runnable
 * REQ: The tool should accept any WikiTopic with OrganisationForm attached as a value in "Co-organisers" field (Michal Masa 2011-06-08)
 *
 * Oznacit EC partnery-organizace: pro vsechny projekty, ktere maji ve Funding Programme.Category
 * vyplneno EC, mrknout do pole Co-organisers, projit vsechny tam vyplnene organizace a nastavit
 * jim v Options hodnotu "Is EC Partner"+"Is Partner"
 *
 * TODO: Do buducnosti by to chcelo uplne vyhodit ten flag a dohladat vsetky data on-line priamo v scripte "listpersons".
 *
 * @author Viliam Simko
 */
class FixOptionFlagInOrgs {

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

		$lister = new CiantProjects($config, $db);

		$config->pushStrictMode(false);
		foreach($lister->getAllProjects() as $projectTopic) {
			assert($projectTopic instanceof CiantProject);

			$projectTopicName = $projectTopic->getWrappedTopic()->getTopicName();
			$fundingProgramme = $projectTopic->getFundingProgramme()->getFieldValue();
			$isEC = ( strtoupper(substr($fundingProgramme, 0, 2)) == 'EC' );

			Logger::log("$projectTopicName\t\t$fundingProgramme\t\t$isEC");

			if($isEC) {

				// we need to use coorganisers + coordinator
				$listOrgs = $projectTopic->getCoorganisers();
				assert( is_array($listOrgs) );
				$listOrgs[] = $projectTopic->getCoordinator();

				foreach($listOrgs as $orgTopic) {
					if( $orgTopic instanceof CiantOrg ) {

						$fieldOptions = $orgTopic->getWrappedTopic()->getTopicFormNode()->getFormField('Options');

						$optList = preg_split('/ *, */', $fieldOptions->getFieldValue() ); // create an array out of string
						$optList[] = 'Is EC Partner';
						$optList[] = 'Is Partner';
						$optList = array_unique($optList); // remove duplicates
						$optList = array_filter($optList); // remove empty elements

						$fieldStrOptions = implode(',', $optList);

						Logger::log("  - ".$orgTopic->getName()." ($fieldStrOptions)");

						if($this->performSave) {
							$fieldOptions->setFieldValue($fieldStrOptions);
							$db->saveTopic($orgTopic->getWrappedTopic());
						}

					} elseif( is_string($orgTopic) ) {
						Logger::logWarning("'$orgTopic' does not have a topic");
					}
				}
			}
		}
	}
}