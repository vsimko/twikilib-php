<?php
use twikilib\runtime\Container;
use twikilib\utils\Encoder;
use twikilib\core\Config;

assert($twikiConfig instanceof Config);
?>
%JQSCRIPT{"jquery-ui.js"}%
%JQTHEME{"smoothness"}%
<script>
	$(function() {
		$.datepicker.setDefaults({
			changeMonth: true,
			changeYear: true,
			firstDay: 1, // Monday
			dateFormat: 'yy-mm-dd'
		});

		$("[name $= '_date']").datepicker();
	});
</script>

<form id="pbdform" method="POST" action="%TOPICURL%">
%TABLE{databg="transparent" tableborder="1" cellborder="1"}%
|*From Date*|<input type="text" class="twikiInputField" name="from_date" readonly="readonly" size="20" value="<?php
echo isset($from_date) ? $from_date : date('Y-m-d', time());
?>"/> |
|*To Date*|<input type="text" class="twikiInputField" name="to_date" readonly="readonly" size="20" value="<?php
echo isset($to_date) ? $to_date : date('Y-m-d', time());
?>"/> |
|*All Year*|<select name="allYear"><option>Select</option><script>for (i=2013; i>=1998;i--) document.write('<option>' + i + '</option>');</script></select>||
|<input id="pbdformsubmit" type="submit" value="OK" class="twikiButton" />||
<input type="hidden" name="restupdate" value="1"/>
<input type="hidden" name="topic" value="<?php echo $topic ?>"/>
<input type="hidden" name="web" value="<?php echo $web ?>"/>
<input type="hidden" name="user" value="<?php echo $user ?>"/>
</form>

