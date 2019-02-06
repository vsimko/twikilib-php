<?php
namespace ciant\search;

use twikilib\core\Config;

/**
 * Creates a list of organisations that are marked as partners.
 * @author Viliam Simko
 */
class CiantPartners extends Organisations {
	/**
	 * (non-PHPdoc)
	 * @see ciant\search.Organisations::search()
	 */
	final protected function search(Config $twikiConfig) {
		$search = new MetaSearch($twikiConfig);
		$search->setFormNameFilter('OrganisationForm');
		$search->setFormFieldFilter('Options', '.*Partner.*');
		$search->executeQuery();
		return $search->getResults();
	}
}