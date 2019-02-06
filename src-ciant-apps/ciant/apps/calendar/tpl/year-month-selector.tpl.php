<?php assert( is_integer($YEAR) ) ?>
<?php assert( is_integer($MONTH) ) ?>
<?php $monthStr = array(1 =>
	'January', 'February', 'March', 'April', 'May', 'June',
	'July', 'August', 'September', 'October', 'November', 'December'
)?>

<form class="staffCalendarSelector" action='%TOPICURL%'>
	<select class='year' name='year' onchange='submit()'>
	<?php for($i=-2; $i<=2; ++$i): ?>
		<?php $y = $YEAR + $i ?>
		<option value='<?php echo $y ?>' <?php echo ($i==0 ? "selected='selected'" : '') ?>><?php echo $y ?></option>
	<?php endfor ?>
	</select>
	<select class='month' name='month' onchange='submit()'>
	<?php foreach($monthStr as $monthNum => $monthName): ?>
		<option value='<?php echo $monthNum ?>' <?php echo $monthNum == $MONTH ? "selected='selected'" : ''?>>
			<?php echo $monthName ?>
		</option>
	<?php endforeach ?>
	</select>
	<input class='submit' type='Submit' value="Change"/>
</form>
<?php
	$prevTime = strtotime("$YEAR-$MONTH-01 00:00:00") - 1; // -1 second
	$prevYear = date('Y', $prevTime );
	$prevMonth = date('m', $prevTime );

	$nextTime = strtotime("$YEAR-$MONTH-28") + 60*60*24*5; // +5 days
	$nextYear = date('Y', $nextTime );
	$nextMonth = date('m', $nextTime );
?>
[[%TOPICURL%?year=<?php echo $prevYear ?>&amp;month=<?php echo $prevMonth ?>][%ICON{arrowleft}% Previous Month]]
|
[[%TOPICURL%?year=<?php echo $nextYear ?>&amp;month=<?php echo $nextMonth ?>][Next Month %ICON{arrowright}%]]
