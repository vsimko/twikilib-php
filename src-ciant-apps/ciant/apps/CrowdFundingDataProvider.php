<?php
namespace ciant\apps;

use twikilib\runtime\Logger;
use ciant\wrap\RolesTable;
use ciant\wrap\RoleEntry;
use ciant\wrap\CiantEvent;
use ciant\wrap\CiantProject;
use ciant\wrap\CiantWrapFactory;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use \Exception;

/**
 * @runnable
 * @deprecated
 * Former extract4cf.php script used by komunitnifinancovani.cz (Pino Foris)
 *
 * Note: This code can be used as a demonstation of how XML files can be generated
 * from wiki and how multilingual support works for form fields.
 *
 * Requirements:
 * REQ: The service should provide information about a projects and events
 * REQ: The output should be XML because it can be easily parsed by the data consumer (
 * REQ: TODO All persons involved in the project/event should be listed
 *
 * @author Viliam Simko
 */
class CrowdFundingDataProvider {

	/**
	 * @var twikilib\core\ITopicWrapper
	 */
	private $wrap;

	/**
	 * @var twikilib\core\Config
	 */
	private $twikiConfig;

	public function __construct($params) {
		$topicName = @$params['topic'];
		if(empty($topicName))
			throw new Exception("Parameters: topic (required), lang (optional)");

		$this->twikiConfig = new Config('config.ini');
		$this->twikiConfig->language = @$params['lang'];
		$db = new FilesystemDB($this->twikiConfig);

		$topic = $db->loadTopicByName($topicName);
		$this->wrap = CiantWrapFactory::getWrappedTopic($topic);
	}

	public function run() {
		Logger::disableLogger();

		$this->twikiConfig->pushStrictMode(false);

		if( $this->wrap instanceof CiantProject ) {
			echo "<info>\n";
			echo '<title>'.htmlspecialchars($this->wrap->getAcronym().' - '.$this->wrap->getName())."</title>\n";
			echo '<abstract>'.htmlspecialchars($this->wrap->getAbstract())."</abstract>\n";
			echo "<people>\n";
				echo "<person>\n";
				$user = $this->wrap->getManager();
				echo "<role>Artist - Project Manager</role>\n";
				echo "<name>".htmlspecialchars($user->getName())."</name>\n";
				echo "<photo>".htmlspecialchars($user->getThumbnailUrl(100))."</photo>\n";
				echo "<bio>".htmlspecialchars($user->getBiography())."</bio>\n";
				echo "</person>\n";
			echo "</people>\n";
			echo "</info>";

		} elseif( $this->wrap instanceof CiantEvent ) {
			echo "<info>\n";
			echo '<title>'.htmlspecialchars($this->wrap->getTitle())."</title>\n";
			echo '<abstract>'.htmlspecialchars($this->wrap->getAbstract())."</abstract>\n";
			echo "<venue>".htmlspecialchars($this->wrap->getVenueAsText())."</venue>\n";
			echo "<people>\n";
				try {
					$userName = $this->wrap->getWrappedTopic()->getTopicFormNode()->getFormField('Author')->getFieldValue();
					if( ! empty($userName)) {
						$topic = $this->wrap->getWrappedTopic()->getTopicFactory()->loadTopicByName($userName);
						$user = CiantWrapFactory::getWrappedTopic($topic);

						echo "<person>\n";
							echo "<role>Artist - Author</role>\n";
							echo "<name>".htmlspecialchars($user->getName())."</name>\n";
							echo "<photo>".htmlspecialchars($user->getThumbnailUrl(100))."</photo>\n";
							echo "<bio>".htmlspecialchars($user->getBiography())."</bio>\n";
						echo "</person>\n";
					}
				} catch(Exception $e) {}

				// -------------------------------------------------------
				$roles = new RolesTable( $this->wrap->getWrappedTopic() );

				foreach($roles as $role) {
					assert($role instanceof RoleEntry);
					if($role->isPublic()) {
						echo "  <person>\n";
							echo "    <role>".htmlspecialchars($role->getRoleType()." - ".$role->getDescription())."</role>\n";
							echo "    <name>".htmlspecialchars($role->getWho())."</name>\n";
						echo "  </person>\n";
					}
				}
			echo "</people>\n";
			echo "</info>";
		} else {
			$this->twikiConfig->popStrictMode();
			throw new Exception("Not allowed");
		}

		$this->twikiConfig->popStrictMode();
	}
}