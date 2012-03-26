<?php
namespace twikilib\core;

use twikilib\core\Config;

/**
 * Represents a single topic with all nodes.
 * All topics contain nodes the same set of nodes.
 *
 * @author Viliam Simko
 */
interface ITopic extends IRenderable {

	/**
	 * @return Config
	 */
	function getConfig();

	/**
	 * @return ITopicFactory
	 */
	function getTopicFactory();

	/**
	 * Retrieves the topic name from which the instance has been created.
	 * @return string
	 */
	function getTopicName();

	/**
	 * @return twikilib\nodes\TopicInfoNode
	 */
	function getTopicInfoNode();

	/**
	 * @return twikilib\nodes\TopicTextNode
	 */
	function getTopicTextNode();

	/**
	 * @return twikilib\nodes\TopicFormNode
	 */
	function getTopicFormNode();

	/**
	 * @return twikilib\nodes\TopicAttachmentsNode
	 */
	function getTopicAttachmentsNode();

	/**
	 * @return twikilib\nodes\TopicPrefsNode
	 */
	function getTopicPrefsNode();

	/**
	 * @return twikilib\nodes\RevCommentsNode
	 */
	function getRevCommentsNode();
}