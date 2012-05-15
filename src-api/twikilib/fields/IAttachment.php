<?php
namespace twikilib\fields;

use twikilib\core\IRenderable;
use twikilib\core\ITopic;

/**
 * Represents a single attachment of a topic.
 * @author Viliam Simko
 */
interface IAttachment extends IRenderable {

	/**
	 * @return object
	 */
	public function getMetaTagArgs();

	/**
	 * Returns just the file name (without the full path).
	 * This is useful when we want to avoid interference of the path name
	 * when searching just within the file name.
	 * @return string
	 */
	function getFileName();

	/**
	 * Filesystem path to the attachment.
	 * @return string
	 */
	function getFileLocation();

	/**
	 * Publicly accessible URL where the attachment can be downloaded.
	 * @return string
	 */
	function getPublicUrl();

	/**
	 * Useful when searching for attachments by comment.
	 * @return string
	 */
	function getComment();

	/**
	 * Useful when searching for attachments by creator.
	 * @return ITopic
	 */
	function getUser();
}