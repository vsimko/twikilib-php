<?php
namespace twikilib\utils\datetime;

/**
 * @author Viliam Simko
 */
class DateTimeInterval implements IDateTimeInterval {

	private $beginUTIME;
	private $endUTIME;

	/**
	 * @param string|integer $beginSpec
	 * @param string|integer $endSpec
	 */
	final public function __construct($beginSpec, $endSpec) {
		assert( is_string($beginSpec) || is_integer($beginSpec) );
		assert( is_string($endSpec) || is_integer($endSpec) );

		$this->beginUTIME = self::parseDateTime($beginSpec);
		$this->endUTIME = self::parseDateTime($endSpec);
	}

	/**
	 * Strings are parsed into integers, while integers are returned unchanged.
	 * @param mixed $dateTimeSpec
	 * @return integer
	 */
	static final public function parseDateTime($dateTimeSpec) {
		if(is_integer($dateTimeSpec))
			return $dateTimeSpec;

		return strtotime($dateTimeSpec);
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::getBeginUTIME()
	 */
	final public function getBeginUTIME() {
		return $this->beginUTIME;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::getEndUTIME()
	 */
	function getEndUTIME() {
		return $this->endUTIME;
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::isEndingWithin()
	 */
	final public function isEndingWithin(IDateTimeInterval $other) {
		return $this->getEndUTIME() >= $other->getBeginUTIME()
			&& $this->getEndUTIME() <= $other->getEndUTIME();
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::isStartingWithin()
	 */
	final public function isStartingWithin(IDateTimeInterval $other) {
		return $this->getBeginUTIME() <= $other->getEndUTIME()
			&& $this->getBeginUTIME() >= $other->getBeginUTIME();
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::isSubsetOf()
	 */
	final public function isSubsetOf(IDateTimeInterval $other) {
		return $this->getBeginUTIME() >= $other->getBeginUTIME()
			&& $this->getEndUTIME() <= $other->getEndUTIME();
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::isIntersectingWith()
	 */
	final public function isIntersectingWith(IDateTimeInterval $other) {
		return $this->getBeginUTIME() < $other->getEndUTIME()
			&& $this->getEndUTIME() > $other->getBeginUTIME();
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\utils\datetime.IDateTimeInterval::getIntersection()
	 */
	function getIntersection(IDateTimeInterval $other) {
		if( ! $this->isIntersectingWith($other) )
			return new EmptyInterval;

		return new DateTimeInterval(
			max( $this->getBeginUTIME(), $other->getBeginUTIME() ),
			min( $this->getEndUTIME(), $other->getEndUTIME() )
		);
	}
}