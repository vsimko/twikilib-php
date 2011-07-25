<?php
namespace twikilib\fields;

use twikilib\nodes\TopicTextNode;

class TextSection extends TopicTextNode {
	
	private $sectionName;
	
	public function __construct($sectionName, $sectionText) {
		$this->sectionName = $sectionName;
		$this->setText($sectionText);
	}
	
	final public function getSectionName() {
		return $this->sectionName;
	}
}
?>