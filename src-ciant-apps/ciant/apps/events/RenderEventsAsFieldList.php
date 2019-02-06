<?php
namespace ciant\apps\events;

use ciant\wrap\ExtractedEvent;
use twikilib\utils\Encoder;
use ciant\factory\ExtractedEventsFactory;

/**
 * Renders events as a comma separated list of values that can be used
 * inside form field definition.
 *
 * @author Viliam Simko
 */
class RenderEventsAsFieldList implements IEventsRenderer {

	/**
	 * @var ciant\factory\ExtractedEventsFactory
	 */
	private $eventsFactory;

	final public function __construct(ExtractedEventsFactory $eventsFactory) {
		$this->eventsFactory = $eventsFactory;
	}

	/**
	 * (non-PHPdoc)
	 * @see ciant\apps\events.IEventsRenderer::render()
	 */
	final public function render() {
		$extractedEvents = $this->eventsFactory->getEventsFromProjects();

		$list = array();
		foreach($extractedEvents as $event) {
			assert($event instanceof ExtractedEvent);

			$list[] = Encoder::createSelectValueItem(
				Encoder::filterStringLength($event->projectAcronym.' - '.$event->title, 100),
				$event->relatedTopic );
		}

		sort($list);

		echo implode(', ', $list);
	}
}