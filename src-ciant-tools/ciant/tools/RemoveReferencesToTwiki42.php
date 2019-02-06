<?php
namespace ciant\tools;

use twikilib\runtime\Logger;

use twikilib\fields\IAttachment;
use twikilib\core\MetaSearch;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @deprecated
 * @runnable
 * @author Viliam Simko
 */
class RemoveReferencesToTwiki42 {

	private $webName;

	public function __construct($params) {
		$this->webName = @$params['web'];
		if(empty($this->webName)) {
			throw new \Exception("Parameter 'web' required.");
		}
	}


	public function run() {
		header('Content-type: text/plain');
		$config = new Config('config.ini');
		$config->pushStrictMode(false);
		$db = new FilesystemDB($config);

		$search = new MetaSearch($config);
 		$search->addWebNameFilter($this->webName);

		$search->setRawTextFilter('twiki42');
		$search->executeQuery();

		foreach($search->getResults() as $topicName) {
			//Logger::log($topicName);
			$topic = $db->loadTopicByName($topicName);
			$list = $topic->getTopicAttachmentsNode()->getAttachmentsByName('.*');
			foreach($list as $attachment) {
				assert($attachment instanceof IAttachment);
				$args = $attachment->getMetaTagArgs();

				if(preg_match('/twiki42/', @$args->path)) {
					echo "$topic : removed path {$args->path}\n";
					unset($args->path);
				}

				if(preg_match('/twiki42/', @$args->stream)) {
					echo "$topic : removed stream {$args->stream}\n";
					unset($args->stream);
				}
			}

			$db->saveTopic($topic);
		}
	}
}