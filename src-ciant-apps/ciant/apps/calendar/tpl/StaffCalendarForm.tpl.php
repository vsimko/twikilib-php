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
	showlink="Add Staff Calendar Entry&nbsp;"
	showimgright="%ICONURLPATH{toggleopen-small}%"
	class="twikiHelp"
}%
<script>
	$(function() {
		$.datepicker.setDefaults({
			changeMonth: false,
			changeYear: false,
			firstDay: 1, // Monday
			dateFormat: 'd M yy',

			onSelect: function(dateText, inst) {
				var dfrom = $("[name='event_from_date']").val();
				var dto = $("[name='event_to_date']").val();
				var diff = Math.floor( (Date.parse(dto) - Date.parse(dfrom)) / 86400000 ) + 1;
				$("[name='event_credit_days'] option[value='" + diff + "']").attr("selected", "selected")
			}
		});

		$("[name $= '_date']").datepicker();

		$("#staffcalform").submit(function(){
			$('#staffcalformsubmit').fadeOut("fast");
			$.ajax({
				url: '<?php echo $twikiConfig->twikiWebUrl."/lib/PHP/twikilib-php.phar/index-web.php?ciant.apps.calendar.StaffCalendarForm"?>',
				type: "POST",
				data: $("#staffcalform").serializeArray(),
				success: function(result){
					$("#staffcalformresult").append(result);
					// REQUIREMENT by Michal Masa 2012-04-20
					// we reload the page instead of letting the user to add more entires using the same form
					//$('#staffcalformsubmit').delay(500).fadeIn("fast"); // this would allow users to add more entries
					location.reload(); // this reload the page
				}
			});
			return false;
		});
	});
</script>

<form id="staffcalform">
%TABLE{databg="transparent" tableborder="1" cellborder="1"}%
|*Date*|<input type="text" class="twikiInputField" name="event_from_date" readonly="readonly" size="20" value="<?php echo date('d M Y', time()) ?>"/> %I% You can define either single day event or date interval.|
|*To&nbsp;Date&nbsp;(optional)*|<input type="text" class="twikiInputField" name="event_to_date" readonly="readonly" size="20"/>|
|*Description*|<input class="twikiInputField" name="event_description" type="text" size="60"/>|
|*Icon*|<select name="event_icon" id="event_icon">%EXTRACT{
  topic="TWiki.SmiliesPlugin"
  pattern="\|\s*\<nop\>(.*?)\s*\|\s*(.*?)\s*\|\s*\"(.*?)\"\s*\|"
  expand="off"
  format="<option value='$1'> <nop>$3&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;<nop>$1 </option> "
}%</select>|
|*Credit*|<?php
	echo Encoder::createSingleLineText(Container::getTemplate('ciant/apps/calendar/tpl/credit.tpl.php'))
?>|
|<input id="staffcalformsubmit" type="submit" value="Add entry" class="twikiButton" />|<span id="staffcalformresult"></span>|
<input type="hidden" name="restupdate" value="1"/>
<input type="hidden" name="topic" value="<?php echo $topic ?>"/>
<input type="hidden" name="web" value="<?php echo $web ?>"/>
<input type="hidden" name="user" value="<?php echo $user ?>"/>
</form>
%ENDTWISTY%
