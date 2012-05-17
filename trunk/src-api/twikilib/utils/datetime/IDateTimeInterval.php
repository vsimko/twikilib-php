<?php
namespace twikilib\utils\datetime;

/**
 * @author Viliam Simko
 */
interface IDateTimeInterval {

	/**
	 * @return integer UNIX time
	 */
	function getBeginUTIME();

	/**
	 * @return integer UNIX time
	 */
	function getEndUTIME();

	/**
	 * @param IDateTimeInterval $otherInterval
	 * @return IDateTimeInterval
	 */
	function getIntersection(IDateTimeInterval $other);

	/**
	 * Whether this interval (a,b) has an intersection with $other interval (c,d).
	 * @param IDateTimeInterval $otherInterval
	 * @return boolean <pre>
	 * |   c======d       |
	 * |     a===b        | true
	 * | a=======b        | true
	 * |     a======b     | true
	 * |            a===b | false
	 * </pre>
	 */
	function isIntersectingWith(IDateTimeInterval $other);

	/**
	 * Whether this interval (a,b) ends within $other interval (c,d).
	 * @param IDateTimeInterval $otherInterval
	 * @return boolean <pre>
	 * |   c======d       |
	 * |     a===b        | true
	 * | a=======b        | true
	 * |     a======b     | false
	 * |            a===b | false
	 * </pre>
	 */
	function isEndingWithin(IDateTimeInterval $other);

	/**
	 * Whether this interval (a,b) starts within $other interval (c,d).
	 * @param IDateTimeInterval $otherInterval
	 * @return boolean <pre>
	 * |   c======d       |
	 * |     a===b        | true
	 * |     a======b     | true
	 * |            a===b | false
	 * | a=======b        | false
	 * </pre>
	 */
	function isStartingWithin(IDateTimeInterval $other);

	/**
	 * Whether this interval (a,b) is fully contained within $other interval (c,d)
	 * @param IDateTimeInterval $otherInterval
	 * @return boolean <pre>
	 * |   c=========d       |
	 * |     a====b          | true
	 * |     a=========b     | false
	 * |               a===b | false
	 * </pre>
	 */
	function isSubsetOf(IDateTimeInterval $other);
}