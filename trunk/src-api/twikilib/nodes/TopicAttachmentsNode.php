<?php
namespace twikilib\nodes;

use twikilib\fields\Attachment;
use twikilib\utils\Encoder;
use twikilib\core\ITopic;
use twikilib\core\IParseNode;

use \Exception;
class AttachmentException extends Exception {}

/**
 * @author Viliam Simko
 */
class TopicAttachmentsNode implements IParseNode {

	/**
	 * @var ITopic
	 */
	private $topicContext;

	/**
	 * @var array
	 */
	private $attachments = array();

	final public function __construct(ITopic $topicContext) {
		$this->topicContext = $topicContext;
	}

	/**
	 * Cloning not allowed for this class.
	 */
	final private function __clone() {}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IParseNode::getPattern()
	 */
	final public function getPattern() {
		return '/%META:FILEATTACHMENT\{(.*)\}%\n/';
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IParseNode::onPatternMatch()
	 */
	final public function onPatternMatch(array $match) {
		$parsedArgs = Encoder::parseWikiTagArgs($match[1]);
		$this->attachments[] = new Attachment($this->topicContext, $parsedArgs);
	}

	/**
	 * (non-PHPdoc)
	 * @see twikilib\core.IRenderable::toWikiString()
	 */
	final public function toWikiString() {
		return Encoder::arrayToWikiString($this->attachments);
	}

	/**
	 * Adds a record about an attached file.
	 * The file must already exist in the file system.
	 *
	 * @param string $srcFileName
	 * @param string $targetName
	 * @param string $targetComment
	 * @param string $attr attributes such as "M" = Mandatory or "P" = Published (or combination of both)
	 * @throws AttachmentException
	 */
	final public function addAttachment($srcFileName, $targetName, $targetComment = '', $attr = '')
	{
		//TODO: what about absolute/relative path in the $srcFileName parameter ?

		// file must exist, this should filter out obsolete records
		if( !file_exists($srcFileName)) {
			throw new AttachmentException('Attachment file does not exist: '.$srcFileName);
		}

		$this->attachments[] = new Attachment(
			$this->topicContext,
			array(
				'name'			=> $targetName,
				'attachment'	=> $targetName,
				'attr'			=> $attr,
				'comment'		=> $targetComment,
				'date'			=> time(),
				'path'			=> $srcFileName,
				'size'			=> filesize($srcFileName),
				'user'			=> $this->topicContext->getConfig()->userName,
				'version'		=> '1' ));
	}

	/**
	 * @param string $commentPattern regex pattern
	 * @param string $methodName name of the method from Attachment class used for the matching function.
	 * @return array of Attachment
	 */
	private function getAttachmentsByPattern($regexPattern, $methodName) {
		assert( is_string($regexPattern) );
		assert( is_string($methodName) );
		assert( method_exists('twikilib\fields\Attachment', $methodName) );

		if($regexPattern[0] != '/')
			$regexPattern = "/$regexPattern/i";

		$result = array();
		foreach($this->attachments as $attach) {
			assert($attach instanceof Attachment);
			if( preg_match($regexPattern, $attach->$methodName()) ) {
				$result[] = $attach;
			}
		}
		return $result;
	}

	/**
	 * @param string $regexPattern REGEXP
	 * @return array of Attachment
	 */
	final public function getAttachmentsByComment($regexPattern) {
		return $this->getAttachmentsByPattern($regexPattern, 'getComment');
	}

	/**
	 * @param string $regexPattern REGEXP
	 * @return array of Attachment
	 */
	final public function getAttachmentsByName($regexPattern) {
		return $this->getAttachmentsByPattern($regexPattern, 'getFileLocation');
	}

	/**
	 * @param string $regexPattern REGEXP
	 * @return array of Attachment
	 */
	final public function getAttachmentsByUser($regexPattern) {
		return $this->getAttachmentsByPattern($regexPattern, 'getUser');
	}

	/**
	 * Helper method that creates a filesystem path to the directory
	 * containing attachments for a given topic.
	 *
	 * @param boolean $createIfNeeded If TRUE, the directory will be create if it does not exist
	 * @return string
	 */
	final public function getAttachDir($createIfNeeded = false) {
		$dirName = $this->topicContext->getConfig()->topicNameToAttachFilename($this->topicContext->getTopicName());
		if($createIfNeeded && !is_dir($dirName)) {
			mkdir($dirName, 0755);
		}
		return $dirName;
	}
}
?>