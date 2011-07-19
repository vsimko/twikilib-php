<?php
namespace twikilib\nodes;

use twikilib\fields\RevComment;
use twikilib\utils\Encoder;
use twikilib\core\IParseNode;

/**
 * @author Viliam Simko
 */
class RevCommentsNode implements IParseNode {
		
	/**
	 * @var array
	 */
	private $revComments = array();
	
	/**
	 * Cloning not allowed for this class.
	 */
	final private function __clone() {}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IParseNode::getPattern()
	 */
	public function getPattern() {
		return '/%META:REVCOMMENT\{(.*)\}%\n/';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IParseNode::onPatternMatch()
	 */
	public function onPatternMatch(array $match) {
		$parsedArgs = Encoder::parseWikiTagArgs($match[1]);
		$this->revComments[] = new RevComment($parsedArgs);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IRenderable::toWikiString()
	 */
	public function toWikiString() {
		return Encoder::arrayToWikiString($this->revComments);
	}
}

?>