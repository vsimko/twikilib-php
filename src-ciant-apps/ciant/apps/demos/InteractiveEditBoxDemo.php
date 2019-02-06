<?php
namespace ciant\apps\demos;

/**
 * @runnable
 * @author Viliam Simko
 * TODO: not finished yet
 */
use twikilib\runtime\Container;

use twikilib\core\ResultCache;

class InteractiveEditBoxDemo {
	private $queryStr;

	final public function __construct($params) {
		$this->queryStr = @$params['q'];
	}

	final public function run() {
		if(empty($this->queryStr))
			$this->renderEditBox();
		else
			$this->evalQuery();
	}

	private function renderEditBox() {
		echo "renderEditBox";
		echo Container::getTemplate("ciant/apps/demos/tpl/jqueryautocomplete.tpl.php");
	}

	private function evalQuery() {
		echo "evalQuery";
//		$cache = new ResultCache($twikiConfig, $topicFactory);
	}
}