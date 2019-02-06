<?php $DEFDAYS = 1 ?>
<?php $DEFHOURS = 0 ?>

Days:
<select name="event_credit_days">
	<?php for($i = 0; $i <= 30; ++$i):?>
		<option value="<?php echo $i ?>"<?php echo $i==$DEFDAYS ? ' selected="selected"' : '' ?>><?php echo $i ?></option>
	<?php endfor ?>
</select>

Hours:
<select name="event_credit_hours">
	<?php for($i = 0; $i <= 23; ++$i):?>
		<option value="<?php echo $i ?>"<?php echo $i==$DEFHOURS ? ' selected="selected"' : '' ?>><?php echo $i ?></option>
	<?php endfor ?>
</select>