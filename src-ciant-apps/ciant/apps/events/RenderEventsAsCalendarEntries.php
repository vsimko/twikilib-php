<?php
namespace ciant\apps\events;

use ciant\wrap\ExtractedEvent;
use ciant\factory\ExtractedEventsFactory;

/**
 * Renders events as formatted TWiki text readable by the CalendarPlusing.
 * @author Viliam Simko
 */
class RenderEventsAsCalendarEntries implements IEventsRenderer {

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

		$extractedEvents = $this->eventsFactory->getAllExtractedEvents();

		echo "*Last cached on: ".date('Y-m-d H:i:s',time())."*\n\n";

		$lastRenderedProject = '';
		foreach($extractedEvents as $e) {
			assert($e instanceof ExtractedEvent);

			// skip events older than 1 year = 31536000 seconds
			if(strtotime($e->beginDate) < time() - 31536000)
				continue;

			if($lastRenderedProject != $e->projectAcronym) {
				$lastRenderedProject = $e->projectAcronym;
				echo "\n---+++ Extracted from <nop>{$e->projectAcronym}\n";
			}
			echo "   * ";
			echo $e->visibleInCalendar ? '' : 'INVISIBLE: ';
			echo $e->beginDate; // Begin Date
			echo " - ";
			echo $e->endDate; // End Date
			echo " - ";
			echo '['.$e->projectAcronym.']'; // Project
			echo " ";
			echo $e->title; // Event Title
			echo ", see ".$e->relatedTopic;
			echo "\n";
		}
	}
}