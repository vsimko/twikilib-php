<?php
namespace twikilib\core;

use twikilib\runtime\Container;
use twikilib\core\Config;

/**
 * Search for topics in a directory containig raw topic files.
 * There are several types of filters supported. Multiple filters can be used
 * at once. Each filter adds an additional layer of processing using the grep
 * UNIX utility. Try to keep the number of filters low.
 *
 * @author Viliam Simko
 */
class MetaSearch {

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * Array of web names to search for topics.
	 * @var array of strings
	 */
	private $webNameFilter = array();

	/**
	 * Results will be stored here after executing the query.
	 * @var array of string
	 */
	private $results = array();

	/**
	 * Retrievs the results found by the executeQuery method
	 * @return array of string
	 */
	final public function getResults() {
		assert( is_array($this->results) );
		return $this->results;
	}

	/**
	 * Contains list of all filters that will be passed to the grep utility.
	 * @var array of string
	 */
	private $grepFilters = array();
	private $invertFilters = array();

	/**
	 * Adds another filter for the grep utility.
	 * @param string $filter
	 * @return void
	 */
	private function addFilter($filter) {
		assert(is_string($filter));
		$this->grepFilters[] = $filter;
	}

	/**
	 * @param Config $twikiConfig
	 */
	final public function __construct(Config $twikiConfig) {
		$this->twikiConfig = $twikiConfig;
		$this->setWebNameFilter($twikiConfig->defaultWeb);
	}

	/**
	 * You can specify a single web name or an array of web names where topics will be searched for.
	 * @param string|array $webNames
	 * @return void
	 */
	final public function setWebNameFilter($webNames) {
		assert(is_string($webNames) || is_array($webNames));
		$this->webNameFilter = (array) $webNames;
	}

	/**
	 * You can add another web name to the array of webs where topics will be searched for.
	 * @param string $webName
	 * @return void
	 */
	final public function addWebNameFilter($webName) {
		assert( is_string($webName) );
		$this->webNameFilter[] = $webName;
	}

	/**
	 * The array is alphabetically sorted and duplicates are removed.
	 * @return array of string
	 */
	final public function getWebNameFilter() {
		sort($this->webNameFilter);
		return array_unique($this->webNameFilter);
	}

	/**
	 * Limits the results to the children of the specified topic.
	 * @param string $parentTopicName
	 * @return void
	 */
	final public function setParentFilter($parentTopicName) {
		assert(is_string($parentTopicName));
		$parsedName = $this->twikiConfig->parseTopicName($parentTopicName);
		$this->addFilter('^%META:TOPICPARENT\{.*name="'.$parsedName->topic.'"\}%');
	}

	/**
	 * TODO: we need to change the way how inverting works because this filter is not a grep filter
	 * @param string $topicNamePattern
	 * @return void
	 */
	final public function setTopicNameFilter($topicNamePattern) {
		assert( is_string($topicNamePattern) );
		assert('/* not implemented yet */');
	}

	/**
	 * Limits the result to topics containing a particular form.
	 * @param string $formName
	 * @return void
	 */
	final public function setFormNameFilter($formName) {
		assert(is_string($formName));
		$this->addFilter('^%META:FORM\{.*name="'.$formName.'"\}%');
	}

	/**
	 * Limits the results to topics containing a particular form-field with a given REGEXP value.
	 * @param string $fieldName
	 * @param string $fieldValue REGEXP
	 * @return void
	 */
	final public function setFormFieldFilter($fieldName, $fieldValue) {
		assert(is_string($fieldName));
		assert(is_string($fieldValue));
		$this->addFilter('^%META:FIELD\{name="'.$fieldName.'".*value="'.$fieldValue.'"');
	}

	/**
	 * Limits the results to topics containing a particular preference-field with a given REGEXP value.
	 * @param string $prefName
	 * @param string $prefValue REGEXP
	 * @return void
	 */
	final public function setPreferenceFilter($prefName, $prefValue = '[^"]*') {
		assert(is_string($prefName));
		assert(is_string($prefValue));
		$this->addFilter('^%META:PREFERENCE\{ *name="'.$prefName.'" .* value="'.$prefValue.'".*\}%');
	}

	/**
	 * Limits the results to topics that contain an attachment with a given comment.
	 * @param string $comment REGEXP value
	 * @return void
	 */
	final public function setAttachCommentFilter($comment) {
		assert( is_string($comment) );
		$this->addFilter('^%META:FILEATTACHMENT\{.* comment="[^"]*'.$comment.'".*\}%');
	}

	/**
	 * Limits the results to topics that match the given regex pattern within the raw topic text.
	 * @param string $grepPattern
	 * @return void
	 */
	final public function setRawTextFilter($grepPattern) {
		assert( is_string($grepPattern) );
		$this->addFilter($grepPattern);
	}

	/**
	 * The last filter will be used in an inverted form inside the query.
	 * @return void
	 */
	final public function invertLastFilter() {
		$filterId = count($this->grepFilters) - 1;
		assert( $filterId >= 0 );
		assert( !isset($this->invertFilters[$filterId]) );
		$this->invertFilters[$filterId] = true;
	}

	/**
	 * Searches the filesystem using grep command.
	 * This method uses the previously specified filters.
	 * @return void
	 */
	final public function executeQuery() {

		// the grep should run inside this directory
		$config = $this->twikiConfig;
		$searchDirEscaped = array_map(function($webName) use ($config) {
			return escapeshellarg( $config->getWebDataDir($webName) );
		}, $this->getWebNameFilter() );

		// take only topics without template topics
		$shellCommand = 'find -- '.implode(' ', $searchDirEscaped).
			' -name \'*.txt\' -and -not -name \'*Template.txt\'';

		// now add the grep filters
		foreach($this->grepFilters as $filterIdx => $filter) {
			$invertMatch = isset($this->invertFilters[$filterIdx]) ? '--invert-match' : '';
			$shellCommand .= '|xargs grep -m1 -l -E -e '.
				$invertMatch.' '.escapeshellarg($filter);
		}

		// now execute the prepared shell command
		$output = array();
		$returnCode = 0;

		Container::measureTime("Searching: ".$shellCommand);
		exec( $shellCommand, $output, $returnCode );
		Container::measureTime();

		$this->results = array();

		// output contains array of matching file names
		if($returnCode == 0 || $returnCode == 123) {
			foreach($output as $item) {
				$this->results[] = preg_replace('/^.*\/([^\/]+)\/([^\/]+)\.txt$/','\1.\2', $item);
			}
		}
	}
}
?>