<?php
namespace twikilib\utils\timespan;

/**
 * @author Viliam Simko
 */
interface ITimeSpan {

	/**
	 * @return integer UNIX time
	 */
	function getBeginUTIME();

	/**
	 * @return integer UNIX time
	 */
	function getEndUTIME();

	/**
	 * @return integer
	 */
	function getTotalSeconds();

	/**
	 * @return integer
	 */
	function getTotalMinutes();

	/**
	 * @return integer
	 */
	function getTotalHours();

	/**
	 * @return integer
	 */
	function getTotalDays();

	/**
	 * @return integer
	 */
	function getTotalWeeks();

	/**
	 * @return integer
	 */
	function getTotalMonths();

	/**
	 * @return integer
	 */
	function getTotalYears();


	/**
	 * @param ITimeSpan $other
	 * @return ITimeSpan
	 */
	function getIntersection(ITimeSpan $other);

	/**
	 * Whether this interval (a,b) has an intersection with $other interval (c,d).
	 * @param ITimeSpan $other
	 * @return boolean <pre>
	 * |   c======d       |
	 * |     a===b        | true
	 * | a=======b        | true
	 * |     a======b     | true
	 * |            a===b | false
	 * </pre>
	 */
	function isIntersectingWith(ITimeSpan $other);

	/**
	 * Whether this interval (a,b) ends within $other interval (c,d).
	 * @param ITimeSpan $other
	 * @return boolean <pre>
	 * |   c======d       |
	 * |     a===b        | true
	 * | a=======b        | true
	 * |     a======b     | false
	 * |            a===b | false
	 * </pre>
	 */
	function isEndingWithin(ITimeSpan $other);

	/**
	 * Whether this interval (a,b) starts within $other interval (c,d).
	 * @param ITimeSpan $other
	 * @return boolean <pre>
	 * |   c======d       |
	 * |     a===b        | true
	 * |     a======b     | true
	 * |            a===b | false
	 * | a=======b        | false
	 * </pre>
	 */
	function isStartingWithin(ITimeSpan $other);

	/**
	 * Whether this interval (a,b) is fully contained within $other interval (c,d)
	 * @param ITimeSpan $otherInterval
	 * @return boolean <pre>
	 * |   c=========d       |
	 * |     a====b          | true
	 * |     a=========b     | false
	 * |               a===b | false
	 * </pre>
	 */
	function isSubsetOf(ITimeSpan $other);

	/**
	 * @param string $intervalSpec which is same as the parameter of DateInterval
	 * @return \DatePeriod
	 *
	 * @see \DateInterval
	 * @see \DatePeriod
	 */
	function splitIntoDatePeriod($intervalSpec);
}