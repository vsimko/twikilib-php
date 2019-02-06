<?php assert($this instanceof ciant\uploader\UploadWorkflow) ?>
<?php echo '<?xml version="1.0" encoding="UTF-8" ?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Uploader</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php $this->uwrapper->onRenderHtmlHead() ?>
	<link rel="stylesheet" type="text/css" href="<?php echo $this->uconfig->createUrl("ciant/uploader/tpl/style.css") ?>"/>
</head>
<body>
	<div>Uploader runtime: <span id="uploaderRuntime">none</span></div>

	<form id="uploaderForm" action=".">
		<h1>Step 1/2: Fill the form to describe your submission</h1>
		<table>
		<?php $allMandatoryFieldsOk = true ?>
		<?php foreach($this->uconfig->getFormFields() as $field): ?>
			<?php assert($field instanceof ciant\uploader\fields\IFormField) ?>
			<?php if($field->isMandatory() && $field->getValue() == '' ): ?>
				<?php $allMandatoryFieldsOk = false ?>
			<?php endif ?>
			<tr>
				<th> <?php echo $field->getTitle()?>:</th>
				<td> <?php echo $field->getHtml() ?></td>
			</tr>
		<?php endforeach ?>
		</table>

		<div style="text-align:center">
			<input type="submit" value="Continue" /><br/>
			<br/>
			<h2>Supported Browsers</h2>
			<img src="<?php echo $this->uconfig->createUrl("ciant/uploader/tpl/supported-browsers.png") ?>"/>
		</div>
	</form>

	<div id="uploaderWidget">
		<h1>Step 2/2: Upload files</h1>

		<?php $this->uwrapper->onRenderWidget() ?>
		<div id="container">
			Files will be uploaded asynchronously, you can also select multiple files.
			<span style="color:magenta; font-weight:bold">
				Do not close this window until all files have been fully uploaded.</span>
			<div style="text-align:center;padding:30px">
				<button id="pickfiles">Select files</button></div>
		</div>

		<div id="uploadProgress">
			<ul id="filelist">
				<li style="font-weight:bold">Progress:</li>
			</ul>
		</div>
	</div>

	<?php // must be last because the jquery stuff has to be fully initialized fist ?>
	<?php if($allMandatoryFieldsOk): ?>
		<script type="text/javascript">$(function(){ $("#uploaderForm").submit() })</script>
	<?php endif ?>
</body>
</html>