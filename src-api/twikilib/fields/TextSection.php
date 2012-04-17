<?php
namespace twikilib\fields;

use twikilib\nodes\TopicTextNode;

/**
 * Represents the content of a section within the topic text.
 * @author Viliam Simko
 */
class TextSection extends TopicTextNode {

	/**
	 * @var string
	 */
	private $sectionName;

	public function __construct($sectionName, $sectionText) {
		$this->sectionName = $sectionName;
		$this->setText($sectionText);
	}

	/**
	 * @return string
	 */
	final public function getSectionName() {
		return $this->sectionName;
	}
}