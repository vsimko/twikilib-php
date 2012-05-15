<?php
namespace twikilib\core;

class ParseNodeException extends \Exception {}

/**
 * @author Viliam Simko
 */
interface IParseNode extends IRenderable {

	function getPattern();
	function onPatternMatch(array $match);
}