<?php
namespace ciant\search;

use twikilib\core\Config;

/**
 * Creates a list of organisations that are marked as funders.
 * @author Viliam Simko
 */
class CiantFunders extends Organisations {
	/**
	 * (non-PHPdoc)
	 * @see ciant\search.Organisations::search()
	 */
	final protected function search(Config $twikiConfig) {
		$search = new MetaSearch($twikiConfig);
		$search->setFormNameFilter('OrganisationForm');
		$search->setFormFieldFilter('Options', '.*Funder.*');
		$search->executeQuery();
		return $search->getResults();
	}
}