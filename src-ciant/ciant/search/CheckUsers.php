<?php
namespace ciant\search;

use twikilib\core\Config;
use twikilib\core\ITopic;
use twikilib\core\ITopicFactory;
use twikilib\core\PrefsFactory;

use twikilib\fields\Preference;
use twikilib\core\MetaSearch;

/**
 * @author Viliam Simko
 */
class CheckUsers {

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	public function __construct(Config $twikiConfig, ITopicFactory $topicFactory) {
		$this->twikiConfig = $twikiConfig;
		$this->topicFactory = $topicFactory;
	}

	/**
	 * @return array
	 */
	final public function getUsersWithWrongViewTemplate() {

		$search = new MetaSearch($this->twikiConfig);
		$search->setFormNameFilter('UserForm');
		$search->executeQuery();

		$wrongUsers = array();
		foreach($search->getResults() as $topicName) {
			$topic = $this->topicFactory->loadTopicByName($topicName);

			$pref = $topic->getTopicPrefsNode()->getFirstPreferenceByName('VIEW_TEMPLATE');
			assert($pref==null || $pref instanceof Preference );

			if($pref == null || $pref->getValue() != 'UserView')
				$wrongUsers[] = $topic;
		}
		return $wrongUsers;
	}

	/**
	 * @return array of ITopic
	 */
	final public function getNonUserTopicsWithViewTemplate() {

		$search = new MetaSearch($this->twikiConfig);
		$search->setPreferenceFilter('VIEW_TEMPLATE', 'UserView');
		$search->executeQuery();

		$wrongUsers = array();
		foreach($search->getResults() as $topicName) {
			$topic = $this->topicFactory->loadTopicByName($topicName);

			$formName = $topic->getTopicFormNode()->getFormName();

			if($formName != 'UserForm') {
				$wrongUsers[] = $topic;
			}
		}
		return $wrongUsers;
	}
}