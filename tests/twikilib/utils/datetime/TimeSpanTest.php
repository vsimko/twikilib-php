<?php
namespace tests\twikilib\utils\timespan;

use twikilib\utils\timespan\TimeSpan;

/**
 * @author Viliam Simko
 */
class TimeSpanTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider valuesForParseDateTime
	 */
	function testParseDateTime($in, $out) {
		try {
			$this->assertEquals($out, TimeSpan::parseDateTime($in)->getTimestamp() );
			$this->assertNotEquals(false, $out);
		} catch(\Exception $e) {
			$this->assertFalse($out);
		}
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
				array("invalid", false),
				array("29. Jan 2004", 1075330800),
			);
	}

	/**
	 * @dataProvider valuesForEndingWithin
	 */
	function testIsEndingWithin($a,$b, $c,$d, $expectedResult) {
		$dateint = new TimeSpan($a, $b);
		$other = new TimeSpan($c, $d);

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
		$dateint = new TimeSpan($a, $b);
		$other = new TimeSpan($c, $d);

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
		$dateint = new TimeSpan($a, $b);
		$other = new TimeSpan($c, $d);

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
		$dateint = new TimeSpan($a, $b);
		$other = new TimeSpan($c, $d);

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
		$x = new TimeSpan("1.1.2000", "1.2.2000");
		$y = new TimeSpan("1.1.1999", "1.2.1999");

		$this->assertType(	'twikilib\utils\timespan\EmptyTimeSpan',
							$x->getIntersection($y) );

		$y = new TimeSpan("5.1.2000", "1.7.2000");
		$z = $x->getIntersection($y);
		$this->assertEquals($y->getBeginUTIME(), $z->getBeginUTIME());
		$this->assertEquals($x->getEndUTIME(), $z->getEndUTIME());
	}

	function testBothInvalidDates() {
		try {
			$span =  new TimeSpan('invalid', 'invalid');
		} catch(\Exception $e) {
			return;
		}
		$this->fail("If both dates are unparseable, an exception should be thrown");
	}

	function testSecondInvalidDateIsFine() {
		$span =  new TimeSpan('1.1.2012', 'invalid');
		$this->assertEquals(1, $span->getTotalDays());
	}

	/**
	 * @dataProvider valuesForDateTimeDiff
	 */
	function testDateTimeDiff($begin, $end, $y, $m, $w, $d, $h, $i, $s) {
		$span = new TimeSpan($begin, $end);
		$this->assertEquals($y, $span->getTotalYears());
		$this->assertEquals($m, $span->getTotalMonths());
 		$this->assertEquals($w, $span->getTotalWeeks());
 		$this->assertEquals($d, $span->getTotalDays());
 		$this->assertEquals($h, $span->getTotalHours());
 		$this->assertEquals($i, $span->getTotalMinutes());
 		$this->assertEquals($s, $span->getTotalSeconds());
	}

	function valuesForDateTimeDiff() {
		return array(
			// 		$begin		$end		$y		$m		$w		$d		$h		$i			$s
			array(	'1.1.1990',	'2.1.1990',	0,		0,		0,		1,		24,		1440,		86400),
			array(	'1.1.1990',	'1.1.1991',	1,		12,		52,		365,	8760,	525600,		31536000),
			array(	'1.1.1990',	'2.2.1991',	1,		13,		56,		397,	9528,	571680,		34300800),
			array(	'1.1.1990',	'1.1.2012',	22,		264,	1147,	8035,	192840,	11570400,	694224000),
			array(	'2011-01-01 00:00:00',	'2011-12-31 23:59:59',
											0,		11,		52,		364,	8759,	525599,		31535999),
			array(	'1.1.2012', '1.1.2013',	1,		12,		52,		366,	8784,	527040,		31622400),
		);
	}
}