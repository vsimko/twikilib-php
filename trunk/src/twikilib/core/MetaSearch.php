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
	 * @var string
	 */
	private $webNameFilter;
	
	/**
	 * Contains list of all filters that will be passed to the grep utility.
	 * @var array of string
	 */
	private $grepFilters = array();
	private $invertFilters = array();
	
	/**
	 * Results will be stored here after executing the query.
	 * @var array of string
	 */
	private $results;
	
	/**
	 * @param Config $twikiConfig
	 */
	final public function __construct(Config $twikiConfig) {
		$this->twikiConfig = $twikiConfig;
		$this->webNameFilter = $twikiConfig->defaultWeb;
	}
	
	/**
	 * The directory of the 'Main' web will be used if not specified explicitly.
	 * @param string $webName
	 */
	final public function setWebNameFilter($webName) {
		assert(is_string($webName));
		$this->webNameFilter = $webName;
	}
	
	/**
	 * Adds another filter for the grep utility.
	 * @param string $filter
	 */
	private function addFilter($filter) {
		assert(is_string($filter));
		$this->grepFilters[] = $filter;
	}
	
	/**
	 * Limits the results to the children of the specified topic.
	 * @param string $parentTopicName
	 */
	final public function setParentFilter($parentTopicName) {
		assert(is_string($parentTopicName));
		$parsedName = $this->twikiConfig->parseTopicName($parentTopicName);
		$this->addFilter('^%META:TOPICPARENT\{.*name="'.$parsedName->topic.'"\}%');
	}
	
	/**
	 * TODO: we need to change the way how inverting works because this filter is not a grep filter
	 * @param string $topicNamePattern
	 */
	final public function setTopicNameFilter($topicNamePattern) {
		assert( is_string($topicNamePattern) );
		assert('/* not implemented yet */');
	}
	
	/**
	 * Limits the result to topics containing a particular form.
	 * @param string $formName
	 */
	final public function setFormNameFilter($formName) {
		assert(is_string($formName));
		$this->addFilter('^%META:FORM\{.*name="'.$formName.'"\}%');
	}
	
	/**
	 * Limits the results to topics containing a particular form-field with a given REGEXP value.
	 * @param string $fieldName
	 * @param string $fieldValue REGEXP
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
	 */
	final public function setPreferenceFilter($prefName, $prefValue = '[^"]*') {
		assert(is_string($prefName));
		assert(is_string($prefValue));
		$this->addFilter('^%META:PREFERENCE\{ *name="'.$prefName.'" .* value="'.$prefValue.'".*\}%');
	}
	
	/**
	 * Limits the results to topics that contain an attachment with a given comment.
	 * @param string $comment REGEXP value
	 */
	final public function setAttachCommentFilter($comment) {
		assert( is_string($comment) );
		$this->addFilter('^%META:FILEATTACHMENT\{.* comment="[^"]*'.$comment.'".*\}%');
	}
	
	/**
	 * The last filter will be used in an inverted form inside the query.
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
		$searchDir = $this->twikiConfig->getWebDataDir( $this->webNameFilter );
		
		// take only topics without template topics
		$shellCommand = 'find '.escapeshellarg( $searchDir ).
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
		if($returnCode == 0) {
			foreach($output as $item) {
				$this->results[] = preg_replace('/^.*\/([^\/]+)\/([^\/]+)\.txt$/','\1.\2', $item);
			}
		}
	}
	
	/**
	 * Retrievs the results found by the executeQuery method
	 * @return array of string
	 */
	final public function getResults() {
		assert( is_array($this->results) );
		return $this->results;
	}
}
?>