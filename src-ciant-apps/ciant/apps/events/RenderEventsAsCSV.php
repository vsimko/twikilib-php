<?php
namespace ciant\apps\events;

use ciant\factory\ExtractedEventsFactory;
use ciant\wrap\ExtractedEvent;
use twikilib\utils\Encoder;

/**
 * Renders events as a CSV file.
 * @author Viliam Simko
 */
class RenderEventsAsCSV implements IEventsRenderer {

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

		// headers can be generated here, because we use output buffering
		@header('Content-Encoding: UTF-8');
		@header('Content-type: text/csv; charset=UTF-8');

		//header("Content-type: application/octet-stream");
		@header("Content-Disposition: attachment; filename=\"events-".date('Y-m-d', time()).".csv\"");

		// now render the CSV file
		echo '"Begin Date","End Date","Project","Info","Venue Name","Venue City","Venue Country","Confirmed","Title","Description"';
		echo "\n";

		foreach($extractedEvents as $e) {
			assert($e instanceof ExtractedEvent);
			echo '"'.date('Y-m-d', strtotime($e->beginDate)).'",'; // Begin Date
			echo '"'.( empty($e->endDate) ? '' : date('Y-m-d', strtotime($e->endDate)) ).'",'; // End Date
			echo self::encodeCSVItem($e->projectAcronym);
			echo ',';
			echo self::encodeCSVItem($e->info);
			echo ',';
			echo self::encodeCSVItem($e->venueName, 40);
			echo ',';
			echo self::encodeCSVItem($e->venueCity);
			echo ',';
			echo self::encodeCSVItem($e->venueCountry);
			echo ',';
			echo $e->confirmed ? "yes" : "no";
			echo ',';
			echo self::encodeCSVItem($e->title, 100);
			echo ',';
			echo self::encodeCSVItem($e->description, 100);
			echo "\n";
		}
	}

	/**
	 * Helper method
	 * @param string $text
	 * @param integer $maxLength
	 */
	final static public function encodeCSVItem($text, $maxLength = 0) {
		$text = str_replace('"', '\'', $text);
		$text = Encoder::createSingleLineText($text);
		$text = Encoder::filterStringLength($text, $maxLength);
		return '"'.$text.'"';
	}
}