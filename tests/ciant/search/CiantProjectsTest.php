<?php
namespace tests\ciant\search;

use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use ciant\search\CiantProjects;

/**
 * @author Viliam Simko
 */
class CiantProjectsTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var twikilib\core\ITopicFactory
	 */
	private $topicFactory;
	
	protected function setUp() {
		$this->twikiConfig = new Config('dummy-twikilib-config.ini' );
		$this->topicFactory = new FilesystemDB($this->twikiConfig);
	}
	
	function testGetProjectsPublishedOnWeb() {
		$search = new CiantProjects($this->twikiConfig, $this->topicFactory);
		$projects = $search->getProjectsPublishedOnWeb();
	}
}

?>