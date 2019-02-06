<?php
namespace ciant\apps\calendar;

use twikilib\utils\Encoder;

use twikilib\fields\TextSection;

use twikilib\core\ITopic;
use twikilib\core\ITopicFactory;

use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use twikilib\runtime\Container;

use \Exception;

/**
 * @runnable
 * Renders "Days: [0-30], Hours: [0-23]
 * @author Viliam Simko
 */
class GrantCalendarForm {

	private $params;

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	final public function __construct($params) {
		$this->params = $params;
		$this->config = new Config('config.ini');
		$this->topicFactory = new FilesystemDB($this->config);
	}

	final public function run() {
		$tmp = $this->params["topic"] == "GrantCalendar";
		if ($tmp)
			return;
		if( @$this->params['restupdate']) {
			$this->handleUpdate();
		} else {
			echo Container::getTemplate('ciant/apps/calendar/tpl/GrantCalendarForm.tpl.php',
					$this->params,
					'twikiConfig', $this->config );
		}

	}

	private function handleUpdate() {
		//print_r($this->params);
 		$from = $this->params['event_from_date'];
		$descr = $this->params['event_description'];
		$importance = $this->params['event_importance'];
		$topicName = $this->params['topic'];
		$webName = $this->params['web'];

 		$topic = $this->topicFactory->loadTopicByName("$webName.$topicName");
 		assert($topic instanceof ITopic);
		
		$importanceStr = str_repeat("\xe2\x88\x97", $importance);

 		$newEntry = "   * {$from} - [$topicName] ($importanceStr) {$descr}";

 		$topic->getTopicTextNode()->replaceText('/(%PHPSCRIPT[^\n]+GrantCalendarForm)/', "$newEntry\n$1");
 		$this->topicFactory->saveTopic($topic);

 		echo "Successfully added :$newEntry <br/>";
	}
}