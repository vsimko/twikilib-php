<?php use twikilib\utils\Encoder ?>
<div class="helperTitle"><div class="helperTitleMail">E-mails extracted from text</div></div>
<div class="helperBackgroundMail" style="border:solid 1px #eeeeee;padding:20px;white-space:normal">
<?php if( empty($users) ): ?>
There are no user names present in this topic.
The mailing feature is therefore not available here.
Write some user name somewhere in the topic and the feature will be activated immediately.
<?php else: ?>
	<label for="mail_all" style="background-color:rgb(65, 170, 214); display:block">
	<input id="mail_all" onclick="javascript:checkallmails(this)" type="checkbox" checked="checked"/> all
	</label>
	<?php foreach($users as $userId => $user): ?>
		<label for="mail_<?php echo htmlspecialchars($userId)?>" style="display:block" title="<?php echo htmlspecialchars($user['email']) ?>">
		<input	id="mail_<?php echo htmlspecialchars($userId)?>" type="checkbox" name="email" onclick="javascript:preparemails()" value="<?php echo htmlspecialchars(Encoder::replaceAccents($user['name']).' <'.$user['email'].'>') ?>" checked="checked" />
		<?php echo htmlspecialchars( $user['name'] ) ?>
		</label>
	<?php endforeach ?>
	<br/>
	<a id="aggregmail_list" href="mailto: ">Send mail to selected users</a>
<?php endif ?>
</div>
<script type="text/javascript">//<![CDATA[
function preparemails()
{
	var list = document.getElementsByName('email');
	var mails = new Array();
	for ( var i=0; i<list.length; i++)
	{
		if(list[i].checked == true)
			mails.push( list[i].value );
	}
	document.getElementById('aggregmail_list').setAttribute("href", "mailto:" + mails.join(", ") );
}

function checkallmails(e)
{
	var list = document.getElementsByName('email');
	for ( var i=0; i<list.length; i++ )
		list[i].checked = e.checked;
	preparemails();
}
preparemails();
//]]></script>