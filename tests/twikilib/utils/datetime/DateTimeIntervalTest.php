<?php
namespace tests\twikilib\utils\datetime;

use twikilib\utils\datetime\DateTimeInterval;

/**
 * @author Viliam Simko
 */
class DateTimeIntervalTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider valuesForParseDateTime
	 */
	function testParseDateTime($in, $out) {
		$this->assertEquals($out, DateTimeInterval::parseDateTime($in));
	}

	function valuesForParseDateTime() {
		return array(
				array(0,0),
				array(10,10),
				array("1.2.2000", 949359600),
				array("10.3.1980", 321490800),
				array("1.1.1960", -315622800),
				array("1. February 2000", 949359600),
				array("February 1. 2000", 949359600),
				array("2000-2-1", 949359600),
				array("2000-02-01", 949359600),
				array("00-02-01", 949359600),
				array("99-12-23", 945903600),
				array("12-23", false),
				array("29. Jan 2004", 1075330800),
			);
	}

	/**
	 * @dataProvider valuesForConstruction
	 */
	function testConstruction($beginSpec, $endSpec, $expectedBeginUTIME, $expectedEndUTIME) {
		$d = new DateTimeInterval($beginSpec, $endSpec);

		$this->assertEquals($expectedBeginUTIME, $d->getBeginUTIME() );
		$this->assertEquals($expectedEndUTIME, $d->getEndUTIME() );
	}

	function valuesForConstruction() {
		return array(
				array("1.1.2000", "1.2.2000", 946681200, 949359600),
			);
	}

	/**
	 * @dataProvider valuesForEndingWithin
	 */
	function testIsEndingWithin($a,$b, $c,$d, $expectedResult) {
		$dateint = new DateTimeInterval($a, $b);
		$other = new DateTimeInterval($c, $d);

		$this->assertEquals($expectedResult, $dateint->isEndingWithin($other));
	}

	function valuesForEndingWithin() {
		return array(
				//    |a==================b|  |c=================d|
				array("1.1.2000", "1.2.2000", "1.5.2000","1.6.2000", false),
				array("1.1.2000", "1.7.2000", "1.5.2000","1.6.2000", false),
				array("9.5.2000", "1.7.2000", "1.5.2000","1.6.2000", false),
				array("3.5.2000", "9.5.2000", "1.5.2000","1.6.2000", true),
				array("1.1.2000", "9.5.2000", "1.5.2000","1.6.2000", true),
		);
	}

	/**
	 * @dataProvider valuesForStartingWithin
	 */
	function testIsStartingWithin($a,$b, $c,$d, $expectedResult) {
		$dateint = new DateTimeInterval($a, $b);
		$other = new DateTimeInterval($c, $d);

		$this->assertEquals($expectedResult, $dateint->isStartingWithin($other));
	}

	function valuesForStartingWithin() {
		return array(
				//    |a==================b|  |c=================d|
				array("1.1.2000", "1.2.2000", "1.5.2000","1.6.2000", false),
				array("1.1.2000", "1.7.2000", "1.5.2000","1.6.2000", false),
				array("9.5.2000", "1.7.2000", "1.5.2000","1.6.2000", true),
				array("9.5.2000", "9.6.2000", "1.5.2000","1.6.2000", true),
				array("1.1.2000", "9.5.2000", "1.5.2000","1.6.2000", false),
		);
	}

	/**
	 * @dataProvider valuesForIntersectingWith
	 */
	function testIsIntersectingWith($a,$b, $c,$d, $expectedResult) {
		$dateint = new DateTimeInterval($a, $b);
		$other = new DateTimeInterval($c, $d);

		$this->assertEquals($expectedResult, $dateint->isIntersectingWith($other));
	}

	function valuesForIntersectingWith() {
		return array(
				//    |a==================b|  |c=================d|
				array("1.1.2000", "1.2.2000", "1.5.2000","1.6.2000", false),
				array("1.1.2000", "1.7.2000", "1.5.2000","1.6.2000", true),
				array("9.5.2000", "1.7.2000", "1.5.2000","1.6.2000", true),
				array("3.5.2000", "9.5.2000", "1.5.2000","1.6.2000", true),
				array("1.1.2000", "9.5.2000", "1.5.2000","1.6.2000", true),
				array("9.6.2000", "1.7.2000", "1.5.2000","1.6.2000", false),
		);
	}

	/**
	 * @dataProvider valuesForSubsetOf
	 */
	function testIsSubsetOf($a,$b, $c,$d, $expectedResult) {
		$dateint = new DateTimeInterval($a, $b);
		$other = new DateTimeInterval($c, $d);

		$this->assertEquals($expectedResult, $dateint->isSubsetOf($other), "$a $b $c $d");
	}

	function valuesForSubsetOf() {
		return array(
				//    |a==================b|  |c=================d|
				array("1.1.2000", "1.2.2000", "1.5.2000","1.6.2000", false),
				array("1.1.2000", "1.7.2000", "1.5.2000","1.6.2000", false),
				array("9.5.2000", "1.7.2000", "1.5.2000","1.6.2000", false),
				array("3.5.2000", "9.5.2000", "1.5.2000","1.6.2000", true),
				array("1.1.2000", "9.5.2000", "1.5.2000","1.6.2000", false),
				array("9.6.2000", "1.7.2000", "1.5.2000","1.6.2000", false),
		);
	}

	function testGetIntersection() {
		$x = new DateTimeInterval("1.1.2000", "1.2.2000");
		$y = new DateTimeInterval("1.1.1999", "1.2.1999");

		$this->assertType(	'twikilib\utils\datetime\EmptyInterval',
							$x->getIntersection($y) );

		$y = new DateTimeInterval("5.1.2000", "1.7.2000");
		$z = $x->getIntersection($y);
		$this->assertEquals($y->getBeginUTIME(), $z->getBeginUTIME());
		$this->assertEquals($x->getEndUTIME(), $z->getEndUTIME());
	}

}