<?php
	use ciant\search\EventTree;
	use ciant\wrap\CiantProject;
	use ciant\search\EventTreeNode;
	use twikilib\utils\Encoder;

	assert($projectTopic instanceof CiantProject);
	assert($tree instanceof EventTree);

	$projectTopicName = $projectTopic->getWrappedTopic()->getTopicName();
	$parsedTopicName = $projectTopic->getWrappedTopic()->getConfig()->parseTopicName($projectTopicName);

	$ADDTOPROJ_TITLE = Encoder::escapeWikiWords(
		"Add new Event under the project $projectTopicName - {$projectTopic->getAcronym()}:{$projectTopic->getName()}" );

	$ADDTOPROJ_HREF =
		'%SCRIPTURLPATH{"view"}%/%BASEWEB%/WebCreateNewTopic?topicparent='.$projectTopicName.'&templatetopic=NewEventTemplate';
?>
<div class="helperTitle">
	<div class="helperTitleLogo">Logo of <?php echo $projectTopicName ?></div>
</div>
<div class="helperBackgroundMail">
	<?php $thumbnailUrl = $projectTopic->getThumbnailUrl(260) ?>
	<?php if($thumbnailUrl == null): ?>
		[[%SCRIPTURLPATH{"attach"}%/<?php echo $parsedTopicName->web ?>/<?php echo $parsedTopicName->topic ?>?comment=featured][Add Featured Image]]
	<?php else: ?>
		<a href="<?php echo $projectTopic->getLogoUrl() ?>"><img src="<?php echo $thumbnailUrl ?>" alt="<?php echo Encoder::escapeWikiWords("Logo of $projectTopicName") ?>" title="<?php echo Encoder::escapeWikiWords("Logo of $projectTopicName") ?>" /></a><br/>
	<?php endif ?>
</div>
<div class="helperTitle"><div class="helperTitleLinks">Project Phases</div></div>
<div class="helperBackgroundMail" style="padding-left:20px;padding-right:20px;font-size:smaller">
<b>Preparation Phase:</b><br/>
%ICON{dot_ur}%[[<?php echo $projectTopicName ?>Preparation][Preparation]]<br/>
%ICON{dot_ur}%[[<?php echo $projectTopicName ?>Application][Application]]<br/>
%ICON{dot_ur}%[[<?php echo $projectTopicName ?>Contracts][Contracts]]<br/>
%ICON{dot_ur}%[[<?php echo $projectTopicName ?>Cofinancing][Cofinancing]]<br/>
<b>Implementation Phase:</b><br/>
<?php if(strpos($projectTopicName, 'TransISTor') !== false): ?>
%ICON{dot_ur}%[[<?php echo $projectTopicName ?>Tutors][Tutors]]<br/>
%ICON{dot_ur}%[[<?php echo $projectTopicName ?>Participants][Participants]]<br/>
<?php endif ?>
%ICON{dot_ur}%[[<?php echo $projectTopicName ?>Realization][Realization]]<br/>
%ICON{dot_ur}%[[<?php echo $projectTopicName ?>Logo][Logos + Visual ID]]<br/>
%ICON{dot_ur}%[[<?php echo $projectTopicName ?>Promotion][Promotion]]<br/>
%ICON{dot_ur}%[[<?php echo $projectTopicName ?>Communication][Official Communication]]<br/>
<b>Reporting Phase:</b><br/>
%ICON{dot_ur}%[[<?php echo $projectTopicName ?>Reports][Reports]]<br/>
<b>Events:</b> <a title='<?php echo $ADDTOPROJ_TITLE ?>' href='<?php echo $ADDTOPROJ_HREF ?>'>%ICON{addon}%</a><br/>
<?php
	$closure = function($tree, $depth, $closure) use ($db) {
		// sort events by begin date
		usort($tree, function( EventTreeNode $node1, EventTreeNode $node2) {
			return strtotime($node1->event->getBeginDate()) < strtotime($node2->event->getBeginDate());
		});

		// recursively renders tree nodes using a lambda-function (closure)
		array_walk($tree, function(EventTreeNode $node) use ($closure, $depth) {
			$eventTopic = $node->event;
			$EVENTTOPICNAME = $node->eventTopicName;

			$TITLE = Encoder::createVerbatimText(
				$eventTopic->getTitle().'\n'.
				'Category: '.$eventTopic->getCategory().'\n'.
				'Venue: '.$eventTopic->getVenueAsText().'\n'.
				'When: '.$eventTopic->getBeginDate().' - '.$eventTopic->getEndDate()
				);

			$DESCR = Encoder::createVerbatimText(
				$eventTopic->getBeginDate()->getFormattedValue('Y-m', 'Date?').', '.
				Encoder::filterStringLength( $eventTopic->getVenueAsText(), 10, '').', '.
				Encoder::filterStringLength( $eventTopic->getTitle(), 10, '')
				);

			$ADDNEWTITLE = Encoder::escapeWikiWords(
				"Add new event under $EVENTTOPICNAME - {$eventTopic->getTitle()}"
				);

			$ADDNEWLINK =
				'%SCRIPTURLPATH{"view"}%/%BASEWEB%/WebCreateNewTopic?topicparent='.$EVENTTOPICNAME.'&templatetopic=NewEventTemplate';

			echo "<span style='white-space:nowrap' title='$TITLE'>".str_repeat('%ICON{empty}%', $depth)."%ICON{dot_ur}% [[$EVENTTOPICNAME][$DESCR]]&nbsp;<a title='$ADDNEWTITLE' href='$ADDNEWLINK'>%ICON{addon}%</a></span><br/>\n";
			$closure($node->children, $depth + 1, $closure);
		});
	};
	$closure($tree->getTree(), 0, $closure);
?>
</div>