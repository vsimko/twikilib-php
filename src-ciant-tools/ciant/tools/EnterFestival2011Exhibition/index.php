<?php
use twikilib\runtime\Logger;
use twikilib\core\PrefsFactory;
use twikilib\fields\IAttachment;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
header('Content-type: text/plain');
require_once 'init-twikilib-api.php';

$config = new Config('config.ini');
$config->userName = 'Importer';
$config->pushStrictMode(false);

$db = new FilesystemDB($config);


$topic = $db->loadTopicByName('EnterFestival2011Exhibition');

array_map(function(IAttachment $attach) use ($db) {
	Logger::log($attach->getFileLocation());

	$fh = fopen($attach->getFileLocation(), 'r');
	$firstRow = fgetcsv($fh);

	while(!feof($fh)) {
		$row = fgetcsv($fh);

		list($topicName, $beginDate, $endDate, $parentName, $author, $category, $abstract, $title) = $row;
		if(empty($topicName))
			continue;

		$topic = $db->createEmptyTopic($topicName);
		$topic->getTopicInfoNode()->setParentName($parentName);
		$topic->getTopicPrefsNode()->addPreference(PrefsFactory::createViewTemplatePref('EventView'));
		$topic->getTopicTextNode()->setText('---++ People
   * Credits
%EDITTABLE{format="|select,1,Artist,Speaker,Technical|select,1,yes,no|text,30|text,30|text,30|"}%
| *Role* | *Public* | *Who* | *Description* | *Description @cs* |
| Artist | yes | | Author | Autor |
| Artist | yes | | Collaborator | Spolupracovník |

---++ Production

%EDITTABLE{format="|text,30|text,30|select,1,yes,no|"}%
| *Role* | *Who* | *Accepted* | *When* | *Comments* |
| [[%TOPIC%#PR][PR]] | | no | | |
| [[%TOPIC%#Technical][Technical]] | | no | | |
| [[%TOPIC%#Documentation][Photo]] | | no | | |
| [[%TOPIC%#Documentation][Video/Camera]] | | no | | |

---++ PR

---++ Technical

   * table with technical requirements and equipment

---++ Documentation
   * tabulka s odkazy na foto / video - to be done
');

		$topic->getTopicFormNode()->setFormName('EventForm');
		$topic->getTopicFormNode()->getFormField('Begin')->setFieldValue( $beginDate );
		$topic->getTopicFormNode()->getFormField('End')->setFieldValue( $endDate );
		$topic->getTopicFormNode()->getFormField('Author')->setFieldValue( $author );
		$topic->getTopicFormNode()->getFormField('Category')->setFieldValue( $category );
		$topic->getTopicFormNode()->getFormField('Abstract')->setFieldValue( $abstract );
		$topic->getTopicFormNode()->getFormField('Title')->setFieldValue( $title );
		$topic->getTopicFormNode()->getFormField('Options')->setFieldValue( 'Invisible in Wiki Calendar' );
		$topic->getTopicFormNode()->getFormField('EventType')->setFieldValue( 'For Public' );

		Logger::log("Creating: $topicName");
		Logger::log($topic->toWikiString());
		$db->saveTopic($topic);
	}

	fclose($fh);

}, $topic->getTopicAttachmentsNode()->getAttachmentsByName('imported'));

?>