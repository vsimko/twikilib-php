<?php
namespace twikilib\core;

/**
 * @author Viliam Simko
 */
interface IRenderable {
	/**
	 * @return string
	 */
	function toWikiString();
}
?>