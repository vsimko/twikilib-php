<?php

use ciant\tools\eventsToWiki\Importer;
use twikilib\utils\CSVTable;
use twikilib\core\TopicNotFoundException;
use twikilib\core\ITopic;
use twikilib\core\Config;
use twikilib\core\FilesystemDB;

header('Content-type: text/plain');
require_once 'init-twikilib-api.php';

$config = new Config('config.ini');
$config->userName = 'Importer';
$config->pushStrictMode(false);

$db = new FilesystemDB($config);
$importer = new Importer($config, $db);

$importer->setImportedFormName('EventForm');
$importer->setImportedViewTemplate('EventView');
$importer->loadCSVFromFile("events_to_wiki.csv");
$importedTopics = $importer->getImportedTopics();

array_walk($importedTopics, function(ITopic $topic) use ($db) {
	//$db->moveTopicToWeb($topic, 'TmpImport');
	system::log("Saving topic: ".$topic->getTopicName());
	$db->saveTopic($topic);
});

exit;









$imp = new Importer($twikiConfig, $twikidb);

$FORMNAME = @$_REQUEST['formname'];
$VIEWTPL = @$_REQUEST['viewtpl'];
$FILE = @$_FILES['csvfile'];
$TMPIMPORTWEB = 'TmpImport';

if( @$_REQUEST['cleanup'] == 'true' ) {
	echo "Cleaning temporarily imported files...<br/>\n";
	$imp->cleanupTmpImport($TMPIMPORTWEB);
	exit;
}

?>
<?php $ERRORS = array() ?>

<?php if( empty($FORMNAME) ): ?>
	<?php $ERRORS[] = 'Form name must be specified!' ?>
<?php endif ?>

<?php if( empty($FILE) || !is_array($FILE) ): ?>
	<?php $ERRORS[] = 'Upload failed or nothing uploaded' ?>
<?php endif ?>

<?php if( ! empty($ERRORS) ): ?>

	<?php foreach($ERRORS as $error): ?>
		<b><?php echo htmlspecialchars($error) ?></b><br/>
	<?php endforeach ?>

	Upload a CSV (comma-separated value) file that contains form fields:
	<form action="import_csvform.php" method="post" enctype="multipart/form-data">
		<span title="e.g. 'ProjectForm'">Form name:</span><input type="text" name="formname"/><br/>
		<span title="Optional field e.g. 'ProjectView'">View Template:</span><input type="text" name="viewtpl"><br/>
		<input type="file" name="csvfile"/><br/>
		<input type="submit" value="Send File"/>
	</form>
	
	<form action="import_csvform.php" method="post" enctype="multipart/form-data">
		<input type="hidden" name="cleanup" value="true"/>
		<input type="submit" value="Cleanup Temporarily Imported Topics"/>
	</form>
<?php else: ?>
<pre>
<?php
	
	$imp->setImportedFormName($FORMNAME);
	$imp->setImportedViewTemplate($VIEWTPL);

	$uploadedTmpFile =  $FILE['tmp_name'];
	$imp->loadCSVFromFile( $uploadedTmpFile );

	foreach($imp->getImportedTopics() as $newTopic) {
		assert($newTopic instanceof ITopic);
		
		$topicName = $newTopic->getTopicName();
		echo "TOPIC: $topicName - ";
		
		try {
			$existingTopic = $twikidb->loadTopicByName($topicName);
			echo "FOUND";
			$imp->mergeTopics($existingTopic, $newTopic);
			
			// it's safer to store into a temporary web
			$twikidb->moveTopicToWeb($existingTopic, $TMPIMPORTWEB);
			// now saving to filesystem
			$twikidb->saveTopic($existingTopic);
			echo " -> ".$existingTopic->getTopicName();			
			
		} catch(TopicNotFoundException $e) {
			// it's safer to store into a temporary web
			$twikidb->moveTopicToWeb($newTopic, $TMPIMPORTWEB);
			// saving the new topic to the filesystem
			$twikidb->saveTopic($newTopic);
			echo " -> NEW:".$newTopic->getTopicName();
		}
		echo "\n";
	}
?>
</pre>
<?php endif ?>