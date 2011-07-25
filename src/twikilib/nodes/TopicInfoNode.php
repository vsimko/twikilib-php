<?php
namespace twikilib\nodes;

use twikilib\runtime\Logger;
use twikilib\utils\EncoderException;
use twikilib\core\ParseNodeException;
use twikilib\core\ITopic;
use twikilib\utils\Encoder;
use twikilib\core\IParseNode;

/**
 * @author Viliam Simko
 */
class TopicInfoNode implements IParseNode {

	/**
	 * Arguments extracted from the META:TOPICINFO tag.
	 * - date, author, version ...
	 * @var object
	 */
	private $topicInfoArgs;
	
	/**
	 * Arguments extracted from the META:TOPICPARENT tag.
	 * - name
	 * @var object
	 */
	private $parentTopicArgs;
	
	/**
	 * Used internally to ensure there is only one META:TOPICINFO tag inside the raw text.
	 * @var boolean
	 */
	private $occured_TOPICINFO;
	
	/**
	 * @var ITopic
	 */
	private $topicContext;
		
	/**
	 * @param ITopic $topicContext
	 */
	final public function __construct(ITopic $topicContext) {
		$this->topicContext = $topicContext;
		
		$this->topicInfoArgs = (object) array(
			'author'	=> $this->topicContext->getConfig()->userName,
			'date'		=> time(),
			'format'	=> '1.1',
			'version'	=> '1.1' );
		
		$this->parentTopicArgs = (object) array();
		$this->occured_TOPICINFO = false;
	}
	
	/**
	 * Cloning not allowed for this class.
	 */
	final private function __clone() {}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IRenderable::toWikiString()
	 */
	final public function toWikiString() {
		$result = Encoder::createWikiTag('META:TOPICINFO', $this->topicInfoArgs)."\n";
		$result .= isset($this->parentTopicArgs->name)
			? Encoder::createWikiTag('META:TOPICPARENT', $this->parentTopicArgs)."\n"
			: '';
			
		return $result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IParseNode::getPattern()
	 */
	final public function getPattern() {
		return '/%META:(TOPICINFO|TOPICPARENT)\{(.*)\}%\n/';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IParseNode::onPatternMatch()
	 */
	final public function onPatternMatch(array $match) {
		$this->{"match_$match[1]"}($match[2]);
	}
	
	/**
	 * Handler for the META:TOPICINFO tag.
	 * @param string $rawArgs Arguments to be parsed
	 * @throws ParseNodeException
	 */
	private function match_TOPICINFO($rawArgs) {
		if( $this->occured_TOPICINFO )
			throw new ParseNodeException("Multiple META:TOPICINFO tags detected");
		
		$this->topicInfoArgs = Encoder::parseWikiTagArgs( $rawArgs );
		$this->occured_TOPICINFO = true;
	}
	
	/**
	 * Handler for the META:TOPICPARENT tag.
	 * @param string $rawArgs Arguments to be parsed 
	 * @throws ParseNodeException
	 */
	private function match_TOPICPARENT($rawArgs) {
		if( isset($this->parentTopicArgs->name) ) {
			throw new ParseNodeException(
				"Multiple META:TOPICPARENT tags detected in ".
				$this->topicContext->getTopicName() );
		}
		
		try {
			$this->parentTopicArgs = Encoder::parseWikiTagArgs($rawArgs);
		} catch(EncoderException $e) {
			Logger::logWarning($e->getMessage().' in '.$this->topicContext->getTopicName());
		}
	}
	
	/**
	 * This date represents the 'date' argument from the META:TOPICINFO tag.
	 * @return integer UNIX timestamp
	 */
	final public function getTopicDate() {
		return $this->topicInfoArgs->date;
	}
	
	/**
	 * @param integer $topicDate UNIX timestamp
	 * @return void
	 */
	final public function setTopicDate($topicDate) {
		assert( is_integer($topicDate) );
		$this->topicInfoArgs->date = $topicDate;
	}
	
	/**
	 * Sets the topic date to the current date.
	 * This can be used when updating a topic.
	 * @return string
	 */
	final public function updateTopicDate() {
		$this->topicInfoArgs->date = time();
		return $this->topicInfoArgs->date;
	}
	
	/**
	 * The "author" attribute from the META:TOPICINFO tag.
	 * @return string
	 */
	final public function getTopicAuthor() {
		return $this->topicInfoArgs->author;
	}
	
	/**
	 * @param string $topicAuthor
	 */
	final public function setTopicAuthor($topicAuthor) {
		$this->topicInfoArgs->author = $topicAuthor;
	}

	/**
	 * @return string
	 */
	final public function getParentName() {
		return @ $this->parentTopicArgs->name;
	}
	
	/**
	 * @param string $parentName
	 */
	final public function setParentName($parentName) {
		$this->parentTopicArgs->name = $parentName;
	}
}

?>