<?php
namespace ciant\wrap;

use ciant\factory\ProjectFactory;
use twikilib\core\TopicNotFoundException;
use twikilib\utils\Encoder;
use twikilib\core\ITopic;

/**
 * Container for events extracted either from event-topics
 * or from inlined calendar entries.
 *
 * @author Viliam Simko
 */
class ExtractedEvent {

	public $visibleInCalendar = true;

	public $beginDate;
	public $endDate;

	public $title;
	public $description;
	public $projectAcronym;
	public $relatedTopic;
	public $venueName;
	public $venueCity;
	public $venueCountry;
	public $info;
	public $confirmed = true;

	/**
	 * Just for debugging purposes.
	 * @return string
	 */
	final public function __toString() {
		return	"Begin:{$this->beginDate}, ".
				"End:{$this->endDate}, ".
				"Project:{$this->projectAcronym}, ".
				"Description:".Encoder::filterStringLength($this->title, 30);
	}

	/**
	 * @return string ISO format YYYY-MM-DD
	 */
	public function getBeginDateISO() {
		return date('Y-m-d', strtotime($this->beginDate));
	}

	/**
	 * Check whether the event begins within a given date interval.
	 * @param string $lowerBoundDate Unbound if empty
	 * @param string $upperBoundDate Unbound if empty
	 * @return boolean
	 */
	public function isBeginsWithinDateInterval($lowerBoundDate, $upperBoundDate) {

		$minday = date('Y-m-d', strtotime($lowerBoundDate));
		$maxday = date('Y-m-d', strtotime($upperBoundDate));
		$thisday = date('Y-m-d', strtotime($this->beginDate));

		return ( empty($lowerBoundDate) || $thisday >= $minday)
			&& ( empty($upperBoundDate) || $thisday <= $maxday);
	}
}