<?php
namespace twikilib\utils\datetime;

/**
 * @author Viliam Simko
 */
class EmptyInterval implements IDateTimeInterval {

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::getBeginUTIME()
	 */
	final public function getBeginUTIME() {
		return -1;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::getEndUTIME()
	 */
	function getEndUTIME() {
		return -1;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::isEndingWithin()
	 */
	final public function isEndingWithin(IDateTimeInterval $other) {
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::isStartingWithin()
	 */
	final public function isStartingWithin(IDateTimeInterval $other) {
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::isSubsetOf()
	 */
	final public function isSubsetOf(IDateTimeInterval $other) {
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::isIntersectingWith()
	 */
	final public function isIntersectingWith(IDateTimeInterval $other) {
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::getIntersection()
	 */
	function getIntersection(IDateTimeInterval $other) {
		return $this;
	}

}