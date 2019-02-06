<?php
namespace ciant\apps;

use twikilib\core\TopicNotFoundException;

use ciant\uploader\Utils;

use twikilib\fields\IAttachment;

use twikilib\core\ITopic;

use twikilib\core\MetaSearch;

use twikilib\runtime\Logger;
use ciant\wrap\CiantEvent;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use twikilib\core\ResultCache;
use ciant\wrap\CiantProject;
use ciant\wrap\CiantWrapFactory;
use \Exception;


/**
 * @runnable
 * @author Viliam Simko
 */
class RenderProjectFinalApplications {

	private $topicName;

	public function __construct($params) {
		$this->topicName = @$params['topic'];
		if(empty($this->topicName))
			throw new Exception("Undefined parameter: topic");
	}

	final public function run() {
		Logger::disableLogger();
		$twikiConfig = new Config('config.ini');
		$db = new FilesystemDB($twikiConfig);

		try {
			$topic = $db->loadTopicByName($this->topicName.'Application');
		} catch(TopicNotFoundException $e) {
			echo 'No such attachments found';
			return;
		}
		assert($topic instanceof ITopic);
		$list = array_merge(
			$topic->getTopicAttachmentsNode()->getAttachmentsByName('final'),
			$topic->getTopicAttachmentsNode()->getAttachmentsByComment('final')
		);

		foreach($list as $attach) {
			assert($attach instanceof IAttachment);
			$url = $attach->getPublicUrl();
			$fname = $attach->getFileName();

			$extension = '';
			if(preg_match('/\.([^\.]+)$/', $fname, $m)) {
				$extension = $m[1];
			}

			if( ! in_array($extension, array('pdf', 'xls', 'doc', 'html', 'ppt', 'txt', 'xml', 'zip'))) {
				$extension = 'else';
			}

			echo "%ICON{".$extension."}% [[$url][$fname]]<br/>";
		}

// %FORMATLIST{
// "%EXTRACT{topic="'.$this->topicName.'Application" pattern="^%META:FILE.*?{
// name=\"(.*?)\".*?comment=\"(.*?)\"" format="[[%ATTACHURL%Application/$1][$1 - $2]]" separator=" $n"}%"
// pattern="(.*[Ff][Ii][Nn][Aa][Ll].*)"
// split="$n"
// format="$dollar()percntICON{else}$dollar()percnt $1"
// separator="<br/>$n"
// }%

	}
}