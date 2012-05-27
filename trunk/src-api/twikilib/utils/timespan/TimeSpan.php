<?php
namespace twikilib\utils\timespan;

/**
 * @author Viliam Simko
 */
class TimeSpan implements ITimeSpan {

	const SECONDS_IN_WEEK = 604800;
	const SECONDS_IN_DAY = 86400;
	const SECONDS_IN_HOUR = 3600;
	const SECONDS_IN_MINUTE = 60;

	/**
	 * @var \DateTime
	 */
	private $beginDateTime;

	/**
	 * @var \DateTime
	 */
	private $endDateTime;

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getBeginUTIME()
	 */
	final public function getBeginUTIME() {
		return $this->beginDateTime->getTimestamp();
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getEndUTIME()
	 */
	final public function getEndUTIME() {
		return $this->endDateTime->getTimestamp();
	}

	/**
	 * @param string|integer $beginSpec
	 * @param string|integer $endSpec
	 * @param string $defaultRelativeInterval <p>
	 * Used when the end date is invalid.
	 * By default, the length of a time span will be 1 day.
	 * </p>
	 */
	final public function __construct($beginSpec, $endSpec, $defaultRelativeInterval = '+1 day') {

		$this->beginDateTime = self::parseDateTime($beginSpec);
		assert($this->beginDateTime instanceof \DateTime); // begin date should be valid

		try {
			$this->endDateTime = self::parseDateTime($endSpec);
		} catch(\Exception $e) {
			// end date may be invalid hence it is computed from the begin date
			$this->endDateTime = clone $this->beginDateTime;
			$this->endDateTime->modify( $defaultRelativeInterval );
		}
	}

	/**
	 * @param mixed $dateTimeSpec strings are parsed, integers are considered as UNIX timestamps
	 * @return \DateTime
	 */
	static final public function parseDateTime($dateTimeSpec) {
		if( $dateTimeSpec === null || $dateTimeSpec === '')
			throw new \Exception("Using empty values in date intervals is not supported");

		if( $dateTimeSpec instanceof \DateTime )
			return $dateTimeSpec;

		// integers are considered to be UNIX timestamps
		return new \DateTime(
				is_integer($dateTimeSpec) ? "@$dateTimeSpec" : $dateTimeSpec);
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::isEndingWithin()
	 */
	final public function isEndingWithin(ITimeSpan $other) {
		return $this->getEndUTIME() >= $other->getBeginUTIME()
			&& $this->getEndUTIME() <= $other->getEndUTIME();
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::isStartingWithin()
	 */
	final public function isStartingWithin(ITimeSpan $other) {
		return $this->getBeginUTIME() <= $other->getEndUTIME()
			&& $this->getBeginUTIME() >= $other->getBeginUTIME();
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::isSubsetOf()
	 */
	final public function isSubsetOf(ITimeSpan $other) {
		return $this->getBeginUTIME() >= $other->getBeginUTIME()
			&& $this->getEndUTIME() <= $other->getEndUTIME();
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::isIntersectingWith()
	 */
	final public function isIntersectingWith(ITimeSpan $other) {
		return $this->getBeginUTIME() < $other->getEndUTIME()
			&& $this->getEndUTIME() > $other->getBeginUTIME();
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getIntersection()
	 */
	final public function getIntersection(ITimeSpan $other) {
		if( ! $this->isIntersectingWith($other) )
			return new EmptyTimeSpan();

		return new TimeSpan(
			max( $this->getBeginUTIME(), $other->getBeginUTIME() ),
			min( $this->getEndUTIME(), $other->getEndUTIME() )
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalSeconds()
	 */
	final public function getTotalSeconds() {
		return $this->getEndUTIME() - $this->getBeginUTIME();
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalMinutes()
	 */
	final public function getTotalMinutes() {
		return (integer) (($this->getEndUTIME() - $this->getBeginUTIME()) / self::SECONDS_IN_MINUTE);
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalHours()
	 */
	final public function getTotalHours() {
		return (integer) (($this->getEndUTIME() - $this->getBeginUTIME()) / self::SECONDS_IN_HOUR);
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalDays()
	 */
	final public function getTotalDays() {
		return (integer) (($this->getEndUTIME() - $this->getBeginUTIME()) / self::SECONDS_IN_DAY);
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalWeeks()
	 */
	final public function getTotalWeeks() {
		return (integer) (($this->getEndUTIME() - $this->getBeginUTIME()) / self::SECONDS_IN_WEEK);
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalMonths()
	 */
	final public function getTotalMonths() {
		$diff = $this->beginDateTime->diff($this->endDateTime);
		return $diff->y * 12 + $diff->m;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\timespan.ITimeSpan::getTotalYears()
	 */
	final public function getTotalYears() {
		return $this->beginDateTime->diff($this->endDateTime)->y;
	}
}