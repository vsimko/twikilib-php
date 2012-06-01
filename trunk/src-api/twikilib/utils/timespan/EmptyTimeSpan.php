<?php
namespace twikilib\utils\timespan;

/**
 * @author Viliam Simko
 */
class EmptyTimeSpan implements ITimeSpan {

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getBeginUTIME()
	 */
	final public function getBeginUTIME() {
		return -1;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getEndUTIME()
	 */
	function getEndUTIME() {
		return -1;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::isEndingWithin()
	 */
	final public function isEndingWithin(ITimeSpan $other) {
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::isStartingWithin()
	 */
	final public function isStartingWithin(ITimeSpan $other) {
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::isSubsetOf()
	 */
	final public function isSubsetOf(ITimeSpan $other) {
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::isIntersectingWith()
	 */
	final public function isIntersectingWith(ITimeSpan $other) {
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getIntersection()
	 */
	function getIntersection(ITimeSpan $other) {
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalSeconds()
	 */
	final public function getTotalSeconds() {
		return 0;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalMinutes()
	 */
	final public function getTotalMinutes() {
		return 0;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalHours()
	 */
	final public function getTotalHours() {
		return 0;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalDays()
	 */
	final public function getTotalDays() {
		return 0;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalWeeks()
	 */
	final public function getTotalWeeks() {
		return 0;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalMonths()
	 */
	final public function getTotalMonths() {
		return 0;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalYears()
	 */
	final public function getTotalYears() {
		return 0;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::splitIntoDatePeriod()
	 */
	final public function splitIntoDatePeriod($intervalSpec) {
		return array();
	}
}