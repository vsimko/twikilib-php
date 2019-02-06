<?php
namespace ciant\apps;

use twikilib\runtime\Logger;

use ciant\search\EventTreeNode;
use ciant\wrap\CiantEvent;
use ciant\search\EventTree;
use ciant\factory\ProjectFactory;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use twikilib\utils\Encoder;
use twikilib\fields\TextSection;
use \Exception;

/**
 * @runnable
 * This script should be used within twiki topics representing events.
 * Currently it can be used as %PHPSCRIPT{ciant.apps.SubEventsTable}%
 *
 * It generates a Wiki table containing data from child events.
 * | Title | Author | Abstract | content of the "Technical" section |
 *
 * In other words, it will first get the list of all events of
 * the enclosing project, however, only child events will be rendered.
 *
 * @author Viliam Simko
 */
class SubEventsTable {

	private $topicName;

	public function __construct($params) {
		$this->topicName = @$params['topic'];
		if( empty($this->topicName) )
			throw new Exception("Undefined parameter: topic");
	}

	final public function run() {
		Logger::disableLogger();

		$config = new Config('config.ini');
		$config->pushStrictMode(false);

		$db = new FilesystemDB($config);

		$pfactory = new ProjectFactory($db);
		$project = $pfactory->getNearestProjectFromTopicName($this->topicName);

		// here are all events of the project
		$listEvents = $project->getAllChildEvents();

		// however, we just need subevents
		$tree = new EventTree($db);
		array_walk($listEvents, function(CiantEvent $event) use ($tree) {
			$tree->addEvent($event);
		});

		echo "|*Title*|*Author*|*Abstract*|*Technical*|\n";
		$closure = function( array $children, $closure) {
			foreach($children as $node) {
				assert( $node instanceof  EventTreeNode);
				assert( $node->event instanceof  CiantEvent);

				echo '|';
				echo '[['.$node->eventTopicName.']['.Encoder::createSingleLineText($node->event->getTitle()).']]';
				echo '|';
				echo $node->event->getWrappedTopic()->getTopicFormNode()->getFormField('Author');
				echo '|';
				echo Encoder::createSingleLineText($node->event->getAbstract());
				echo '|';
				$sectionTechnical = $node->event->getWrappedTopic()->getTopicTextNode()->getSectionByName('Technical');
				if($sectionTechnical instanceof TextSection) {
					echo Encoder::createSingleLineText($sectionTechnical->toWikiString());
				}
				echo '|';
				echo "\n";

				if($node->children) {
					$closure($node->children, $closure);
				}
			}
		};

		// and because we need just subevents, we select the $topicNode branch
		$closure($tree->getTree($this->topicName), $closure);
	}
}