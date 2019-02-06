<?php
namespace ciant\wrap;

/**
 * TODO:
 * Container for entries that appear in Staff Calendar
 * @author Viliam Simko
 */
class StaffCalendarEntry {

	public $beginDate; // string
	public $beginTime; // unix timestamp
	public $endDate; // string
	public $endTime; // unix timestamp

	public $who;
	public $what;
	public $icon;
	public $credit;

	public function loadFromString($str) {
//		if(preg_match('/^   \* (?P<begin>[^-]+)( -\s*(?P<end>[0-9][^-]+))? - (?P<who>[^-]+) - (?P<what>.+) - (?P<icon>.*)/', $str, $match))
	}

	/**
	 * @param integer $intervalBegin
	 * @param integer $intervalEnd
	 * @return boolean
	 */
	public function isEndingInInterval($intervalBeginTime, $intervalEndTime) {
		assert(is_integer($intervalBeginTime) && $intervalBeginTime > 0);
		assert(is_integer($intervalEndTime) && $intervalEndTime > 0);
		return $this->endTime >= $intervalBeginTime && $this->endTime <= $intervalEndTime;
	}

	/**
	 * @param integer $intervalBegin
	 * @param integer $intervalEnd
	 * @return boolean
	 */
	public function isWithinInterval($intervalBeginTime, $intervalEndTime) {
		assert(is_integer($intervalBeginTime) && $intervalBeginTime > 0);
		assert(is_integer($intervalEndTime) && $intervalEndTime > 0);
		return $this->beginTime >= $intervalBeginTime && $this->endTime <= $intervalEndTime;
	}

	/**
	 * @param integer $intervalBegin
	 * @param integer $intervalEnd
	 * @return boolean
	 */
	public function isOverlappingInterval($intervalBeginTime, $intervalEndTime) {
		assert(is_integer($intervalBeginTime) && $intervalBeginTime > 0);
		assert(is_integer($intervalEndTime) && $intervalEndTime > 0);
		return $this->beginTime < $intervalEndTime && $this->endTime > $intervalBeginTime;
	}

}