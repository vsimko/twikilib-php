<?php
namespace twikilib\core;

use twikilib\core\Config;
use twikilib\nodes\TopicPrefsNode;
use twikilib\nodes\TopicAttachmentsNode;
use twikilib\nodes\TopicFormNode;
use twikilib\nodes\TopicTextNode;
use twikilib\nodes\TopicInfoNode;
use twikilib\nodes\RevCommentsNode;
use twikilib\utils\Encoder;

/**
 * Encapsulates a TWiki topic.
 * A single topic contains multiple nodes.
 * 
 * @author Viliam Simko
 */
class TopicImpl implements ITopic, IInjectedAfterUnserialization {
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IInjectedAfterUnserialization::__wakeup()
	 */
	final public function __wakeup() {Serializer::wakeupHandler($this);}
	
	/**
	 * @var Config
	 */
	private $twikiConfig;
	
	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;
	
	/**
	 * Every topic constist of several nodes.
	 * These nodes are encapsulated in separate classes
	 * and implement the IParseNode interface.
	 * 
	 * @var array of IParseNode
	 */
	private $nodes;
	
	/**
	 * Every instance represents a single file stored in the TWiki database.
	 * Creating a new instance using the 'new' operator is only permitted
	 * from the ITopicFactory class.
	 * 
	 * @param string $topicName
	 * @param Config $twikiConfig
	 * @param ITopicFactory $topicFactory
	 */
	final public function __construct(Config $twikiConfig, ITopicFactory $topicFactory) {
		
		$this->twikiConfig = $twikiConfig;
		$this->topicFactory = $topicFactory;
		
		// IMPORTANT: nodes will be rendered in this order
		$this->nodes = array(
			'info'		=> new TopicInfoNode($this),
			'text'		=> new TopicTextNode($this),
			'form'		=> new TopicFormNode($this),
			'attach'	=> new TopicAttachmentsNode($this),
			'rev'		=> new RevCommentsNode($this),
			'prefs'		=> new TopicPrefsNode($this),
		);
	}
	
	/**
	 * Short textual representation of the topic for debugging purposes.
	 * @return string
	 */
	final public function __toString() {
		$formName = $this->getTopicFormNode()->getFormName();
		return 'Topic: '.$this->getTopicName()." ".
			( empty($formName) ? 'without form' : "with form $formName" );
	}
	
	/**
	 * Cloning not allowed for this class.
	 */
	final private function __clone() {}
		
	// =========================================
	// Inherited from ITopic
	// =========================================
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopic::getTopicName()
	 */
	final public function getTopicName() {
		return $this->topicFactory->objectToTopicName($this);
	}
	
	/**
	 * @param string $rawText
	 */
	final public function parseRawTopicText( $rawText ) {
		
		assert( is_string($rawText) );
		
		// all nodes except the TopicTextNode
		foreach($this->nodes as $parsedNode) {
			if($parsedNode instanceof IParseNode) {
				$pattern = $parsedNode->getPattern();
			
				// apply multiple times if the pattern matches on multiple places in the string
				while(preg_match($pattern, $rawText, $match)) {
			
					$matchlen = strlen($match[0]);
					$matchpos = strpos($rawText, $match[0]);
					
					$rawText =	substr($rawText, 0, $matchpos).				// before mathing text
								substr($rawText, $matchpos + $matchlen);	// after matching text
					
					$parsedNode->onPatternMatch($match);
				}
			}
		}
		
		// rest of the unparsed text comes to the TopicTextNode
		$textNode = $this->nodes['text'];
		assert($textNode instanceof TopicTextNode);
		$this->nodes['text']->setText($rawText);
		
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopic::getConfig()
	 */
	final public function getConfig() {
		return $this->twikiConfig;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopic::getTopicFactory()
	 */
	final public function getTopicFactory() {
		return $this->topicFactory;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopic::getTopicInfoNode()
	 */
	final public function getTopicInfoNode() {
		return $this->nodes['info'];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopic::getTopicTextNode()
	 */
	final public function getTopicTextNode() {
		return $this->nodes['text'];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopic::getTopicFormNode()
	 */
	final public function getTopicFormNode()  {
		return $this->nodes['form'];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopic::getTopicAttachmentsNode()
	 */
	final public function getTopicAttachmentsNode() {
		return $this->nodes['attach'];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopic::getTopicPrefsNode()
	 */
	final public function getTopicPrefsNode() {
		return $this->nodes['prefs'];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.ITopic::getRevCommentsNode()
	 */
	final public function getRevCommentsNode() {
		return $this->nodes['rev'];
	}
	
	// =========================================
	// Inherited from IRenderable
	// =========================================
	
	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IRenderable::toWikiString()
	 */
	final public function toWikiString() {
		return Encoder::arrayToWikiString($this->nodes);
	}
}
?>