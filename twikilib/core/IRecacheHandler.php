<?php
namespace twikilib\core;

/**
 * @author Viliam Simko
 */
interface IRecacheHandler {
	
	/**
	 * @param array $params
	 * @return mixed
	 */
	function onRecache(array $params);
}
?>