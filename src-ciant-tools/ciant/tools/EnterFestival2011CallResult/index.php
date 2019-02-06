<?php
use twikilib\runtime\Logger;
use twikilib\core\PrefsFactory;
use twikilib\utils\Encoder;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;
use ciant\tools\EnterFestival2011CallResult\Import;
use ciant\tools\EnterFestival2011CallResult\Entry;

require_once 'init-twikilib-api.php';

$config = new Config('config.ini');
$db = new FilesystemDB($config);


$topic = $db->loadTopicByName('EnterFestival2011CallResult');
$pref = PrefsFactory::createAllowTopicChangePref( array('CiantAdminGroup'));
$topic->getTopicPrefsNode()->addPreference($pref);
$impFile = $topic->getTopicAttachmentsNode()->getAttachDir().'/call.csv';

$imp = new Import($db);
$imp->loadCSVFromFile( $impFile );

ob_start();
?>

%X% *Do not edit this topic manually!* This topic has automatically been generated from file *<?php echo htmlspecialchars(realpath($impFile)) ?>* on <?php echo date('Y-m-d H:i:s', time()) ?>.
You should rather update the source CSV file and then reimport the topic content using the [[<?php echo $_SERVER['SCRIPT_NAME'] ?>][IMPORTER]].

%I% Voting results are stored in <?php echo $topic->getTopicName() ?>Votes

<!-- Do not remove these lines
   * Set VOTEPLUGIN_DEFAULTS = saveto="<?php echo $topic->getTopicName() ?>Votes" secret="off" format1="$small Score:$score" width1="10" global="off" open="on" bayesian="off"
   %VOTE{}%
-->

---++ Results
|*Title*|*Genre*|*URL*|*Author*|*Attachment*|*Votes*|
<?php foreach($imp->getEntires() as $entry): ?>
<?php assert($entry instanceof Entry);
	if( empty($entry->id) )
		continue;
		
	echo '|'." [[#{$entry->id}][{$entry->title}]] ";
	echo '|'.$entry->genre;
	
	echo '|';
	echo implode('<br/>', array_map(function($link){
		$filtered = Encoder::filterStringLength($link, 20);
		return "[[{$link}][$filtered]]";
	}, $entry->link));
	
	echo '|'."[[mailto:{$entry->email}][{$entry->author}]]<br/>{$entry->phone}";
	echo '|';
	if( !empty($entry->attachment))
		echo "[[%ATTACHURL%/{$entry->attachment}][download]]";

	echo "|%VOTE{id=\"vote_{$entry->id}\" stars1=\"stars_{$entry->id}\"}%";
	echo "|\n";
?>
<?php endforeach ?>

---++ Details
<?php foreach($imp->getEntires() as $entry): ?>
<?php assert($entry instanceof Entry);
		
	echo "<a name=\"{$entry->id}\"></a><h6>{$entry->title}</h6>\n";
	echo "<noautolink>\n";
	echo "[[mailto:{$entry->email}][{$entry->author}]]<br/>\n";

	echo "<div>".htmlspecialchars($entry->authorText)."</div>\n";
	echo "\n";

	echo "<div>".htmlspecialchars($entry->text)."</div>\n";
	echo "</noautolink>\n";
	echo "\n";
?>
<?php endforeach ?>
<?php $topic->getTopicTextNode()->setText( ob_get_clean() ) ?>
<?php if( @$_REQUEST['confirm'] == 'yes' ): ?>
	<?php $db->saveTopic($topic) ?>
	<?php Logger::log("Data imported to the topic:". $topic->getTopicName()) ?>
	<?php else: ?>
	<?php Logger::log("Importing data from file : ".$impFile) ?>

	<form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
		<div>
		You are about to generated new content for the topic <b><?php echo $topic->getTopicName() ?></b>.<br/>
		This action will replace the old content. Please create a backup before proceeding.
		</div>
		
		<input type="submit" value="Confirm"/>
		<input type="hidden" name="confirm" value="yes"/>
	</form>
	<h1>The new content will look like this</h1>
	<hr/>
	<pre><?php echo htmlspecialchars($topic->toWikiString()) ?></pre>
<?php endif ?>