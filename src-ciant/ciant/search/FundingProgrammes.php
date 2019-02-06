<?php
namespace ciant\search;

use twikilib\core\ITopic;
use twikilib\core\ITopicFactory;
use twikilib\core\Config;
use twikilib\core\MetaSearch;

class FundingProgrammes {

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	final public function __construct(Config $twikiConfig, ITopicFactory $topicFactory) {
		$this->twikiConfig = $twikiConfig;
		$this->topicFactory = $topicFactory;
	}

	/**
	 * @return array of ITopic
	 */
	final public function getAll() {
		$search = new MetaSearch($this->twikiConfig);
		$search->setFormNameFilter('FundingProgrammeForm');
		$search->executeQuery();

		$db = $this->topicFactory;

		$list = array_map(
			function($topicName) use ($db) {
				return $db->loadTopicByName($topicName);
			}, $search->getResults() );

		$this->twikiConfig->pushStrictMode(false);

		usort($list, function(ITopic $topic1, ITopic $topic2) {
			return strcmp(
			$topic1->getTopicFormNode()->getFormField('Category')->getFieldValue(),
			$topic2->getTopicFormNode()->getFormField('Category')->getFieldValue() );
		});

		$this->twikiConfig->popStrictMode();

		return $list;
	}
}