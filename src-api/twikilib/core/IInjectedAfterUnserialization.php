<?php
namespace twikilib\core;

/**
 * @author Viliam Simko
 */
interface IInjectedAfterUnserialization {
	/**
	 * Always call te wakeupHandler in order to
	 * get dependencies injected automatically after
	 * unserialization.
	 * The implementation should look like this:
	 *
	 * function __wakeup() {
	 *   Serializer::wakeupHandler($this);
	 * }
	 *
	 * @return void
	 */
	function __wakeup();
}