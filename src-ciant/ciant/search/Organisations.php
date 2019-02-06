<?php
namespace ciant\search;

use twikilib\runtime\Logger;

use twikilib\runtime\Container;
use twikilib\core\MetaSearch;
use twikilib\core\Config;
use twikilib\core\ITopicFactory;
use ciant\wrap\CiantOrg;

/**
 * Creates a list of organisations.
 * The filter is used in the derived classes.
 *
 * @author Viliam Simko
 */
class Organisations
{
	private $cachedOrgs = array();
	private $mapCountryCityOrg = array();
	private $mapCityOrg = array();

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @param Config $twikiConfig
	 * @param ITopicFactory $topicFactory
	 * @param string $filterPattern
	 */
	final public function __construct(Config $twikiConfig, ITopicFactory $topicFactory) {
		$this->topicFactory = $topicFactory;
		$this->twikiConfig = $twikiConfig;

		$list = $this->search($twikiConfig);
		$this->rebuildOrgMap($list);
	}

	/**
	 * This function can be overridden in child classes.
	 * @param Config $twikiConfig
	 * @return array An array of strings
	 */
	protected function search(Config $twikiConfig) {
		$search = new MetaSearch($twikiConfig);
		$search->setFormNameFilter('OrganisationForm');
		$search->executeQuery();
		return $search->getResults();
	}

	/**
	 * @return array of CiantOrg
	 */
	final public function getAllOrganisations() {
		return $this->cachedOrgs;
	}

	/**
	 * @return array An array of strings
	 */
	final public function getCountries() {
		return array_keys($this->mapCountryCityOrg);
	}

	/**
	 * @return array An array of strings
	 */
	final public function getCities() {
		return array_keys($this->mapCityOrg);
	}

	/**
	 * @return array of string
	 */
	final public function getCitiesByCountry($countryName) {
		return array_keys($this->mapCountryCityOrg[$countryName]);
	}

	/**
	 * @return array of CiantOrg
	 */
	final public function getOrgsByCity($cityName) {
		return $this->mapCityOrg[$cityName];
	}

	// ==========================================================
	// Private methods
	// ==========================================================

	/**
	 * Rebuilds the tree of organisations
	 */
	private function rebuildOrgMap(array $listOfTopics) {
		$this->mapCountryCityOrg = array();
		$this->cachedOrgs = array();

		// DISBLE STRICT MODE
		$oldStrictMode = $this->twikiConfig->useStrictPublishedMode;
		$this->twikiConfig->useStrictPublishedMode = false;

		Container::measureTime('Loading organisations');
		foreach( $listOfTopics as $topicName)
		{
			try {
				$topic = $this->topicFactory->loadTopicByName($topicName);
				$org = new CiantOrg($topic);

				$this->cachedOrgs[] = $org;

				$cityName = $org->getCity()->getFieldValue();
				$this->mapCityOrg[$cityName][] = $org;

				$countryName = $org->getCountry()->getFieldValue();
				$this->mapCountryCityOrg[$countryName][$cityName][] = $org;

			} catch(Exception $e) {
				Logger::logWarning( "Topic: $topicName, Error:".$e->getMessage() );
			}
		}
		Container::measureTime();

		// RESTORE STRICT MODE
		$this->twikiConfig->useStrictPublishedMode = $oldStrictMode;
	}
}