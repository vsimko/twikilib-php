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