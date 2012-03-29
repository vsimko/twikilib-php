<?php
namespace twikilib\utils;

use \Exception;
class TWikiSiteConfigException extends Exception {};

class TWikiSiteConfig {

	private $params = array();

	public function __construct($configFilename) {
		if( preg_match_all('/\n\$TWiki::cfg\{([^=]+)\}\s+=\s+(.*);\n/', file_get_contents($configFilename), $matches) ) {
			$this->params = array_combine(
				str_replace('}{', '-', $matches[1]),
				preg_replace('/^[^\'"](.*)[^\'"]$|^[\'"](.*)[\'"]$/', '$1$2', $matches[2])
			);
		} else {
			throw new TWikiSiteConfigException("Could not parse the TWiki site config file: $configFilename");
		}
	}

	public function getParamByName($paramName) {
		return $this->params[$paramName];
	}
}