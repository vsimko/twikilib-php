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
class StaffCalendarForm {

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
		if( @$this->params['restupdate']) {
			$this->handleUpdate();
		} else {
			echo Container::getTemplate('ciant/apps/calendar/tpl/StaffCalendarForm.tpl.php',
					$this->params,
					'twikiConfig', $this->config );
		}

	}

	private function handleUpdate() {
		//print_r($this->params);
 		$from = $this->params['event_from_date'];
 		$to = @$this->params['event_to_date'];

 		if( !empty($to) )
 			$to = " - $to";

		$descr = $this->params['event_description'];
		$days = $this->params['event_credit_days'];
		$hours = $this->params['event_credit_hours'];
		$icon = $this->params['event_icon'];
		$sign = preg_match('/off|sick/', $icon) ? '-' : '';
		$topicName = $this->params['topic'];
		$webName = $this->params['web'];

 		$topic = $this->topicFactory->loadTopicByName("$webName.$topicName");
 		assert($topic instanceof ITopic);

 		$newEntry = "   * {$from}{$to} - {$topicName} - {$descr} ({$sign}{$days}d{$hours}h) - {$icon}";

 		$topic->getTopicTextNode()->replaceText('/(%PHPSCRIPT[^\n]+StaffCalendarForm)/', "$newEntry\n$1");
 		$this->topicFactory->saveTopic($topic);

 		echo "Successfully added :$newEntry <br/>".
 			"Please wait until this page reloads automatically.";
	}
}