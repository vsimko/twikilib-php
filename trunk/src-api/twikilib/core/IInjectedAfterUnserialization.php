<?php
namespace twikilib\core;

/**
 * This interface is used together with the Serializer class.
 * It defines how the callback to Serializer should be implmeneted if we
 * want to get the dependecies automatically injected.
 *
 * @see Serializer
 *
 * @author Viliam Simko
 */
interface IInjectedAfterUnserialization {
	/**
	 * Always call te wakeupHandler in order to
	 * get dependencies injected automatically after
	 * unserialization.
	 * The implementation should look like this:
	 *
	 * <pre>
	 * function __wakeup() {
	 *   Serializer::wakeupHandler($this);
	 * }
	 * </pre>
	 *
	 * @return void
	 */
	function __wakeup();
}