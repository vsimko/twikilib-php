<?php
use twikilib\runtime\Container;
use twikilib\utils\Encoder;
use twikilib\core\Config;

assert($twikiConfig instanceof Config);
?>
%JQSCRIPT{"jquery-ui.js"}%
%JQTHEME{"smoothness"}%
%TWISTY{
	hidelink=""
	showlink="Add Deadline&nbsp;"
	showimgright="%ICONURLPATH{toggleopen-small}%"
	class="twikiHelp"
}%
<script>
	$(function() {
		$.datepicker.setDefaults({
			changeMonth: false,
			changeYear: false,
			firstDay: 1, // Monday
			dateFormat: 'd M yy'
		});

		$("[name $= '_date']").datepicker();

		$("#grantcalform").submit(function(){
			$('#grantcalformsubmit').fadeOut("fast");
			$.ajax({
				url: '<?php echo $twikiConfig->twikiWebUrl."/lib/PHP/twikilib-php.phar/index-web.php?ciant.apps.calendar.GrantCalendarForm"?>',
				type: "POST",
				data: $("#grantcalform").serializeArray(),
				success: function(result){
					$("#grantcalformresult").append(result);
					// REQUIREMENT by Michal Masa 2012-04-20
					// we reload the page instead of letting the user to add more entires using the same form
					//$('#grantcalformsubmit').delay(500).fadeIn("fast"); // this would allow users to add more entries
					location.reload(); // this reload the page
				}
			});
			return false;
		});
	});
</script>

<form id="grantcalform">
%TABLE{databg="transparent" tableborder="1" cellborder="1"}%
|*Date*|<input type="text" class="twikiInputField" name="event_from_date" readonly="readonly" size="20" value="<?php echo date('d M Y', time()) ?>"/> |
|*Description*|<input class="twikiInputField" name="event_description" type="text" size="60"/>|
|*Importance*|<select name="event_importance" id="event_importance"><option value='1'><?php echo str_repeat("\xe2\x88\x97",1);?></option><option value='2'><?php echo str_repeat("\xe2\x88\x97",2);?></option><option value='3'><?php echo str_repeat("\xe2\x88\x97",3);?></option><option value='4'><?php echo str_repeat("\xe2\x88\x97",4);?></option><option value='5'><?php echo str_repeat("\xe2\x88\x97",5);?></option></select>|
|<input id="grantcalformsubmit" type="submit" value="Add entry" class="twikiButton" />|<span id="grantcalformresult"></span>|
<input type="hidden" name="restupdate" value="1"/>
<input type="hidden" name="topic" value="<?php echo $topic ?>"/>
<input type="hidden" name="web" value="<?php echo $web ?>"/>
<input type="hidden" name="user" value="<?php echo $user ?>"/>
</form>
%ENDTWISTY%
