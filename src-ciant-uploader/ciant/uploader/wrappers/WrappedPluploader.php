<?php
namespace ciant\uploader\wrappers;

use ciant\uploader\IUploaderConfig;
use ciant\uploader\wrappers\IUploadWrapper;

class WrappedPluploader implements IUploadWrapper {

	/**
	 * @var UploadWorkflow
	 */
	private $uconfig;

	/**
	 * Manual dependency injection.
	 * @param IUploaderConfig $uconfig
	 */
	function __construct(IUploaderConfig $uconfig) {
		$this->uconfig = $uconfig;
	}

	final public function onRenderHtmlHead(){
		echo '
		<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
		<script type="text/javascript" src="'.$this->uconfig->createUrl("ciant/uploader/tpl/jquery-1.7.min.js").'"></script>
		<script type="text/javascript" src="'.$this->uconfig->createUrl("plupload/js/plupload.full.js").'"></script>
		';
	}

	final public function onRenderWidget(){
		echo '
		<script type="text/javascript">//<![CDATA[

		$(function() {

			// tweaked browser detection
			$.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase());
			$.browser.safari &= !$.browser.chrome;
			$.browser.safari &= !$.browser.mozilla;

 			if($.browser.safari) {
				$("#uploaderForm").html("Safari browser does not handle chunked uploads well. Please use Chrome, Firefox or IE");
 				return;
 			}

			var uploader = new plupload.Uploader({
				runtimes : "html5,silverlight,flash",
				browse_button : "pickfiles",
				container : "container",
				max_file_size : "50000mb",
				chunk_size : "1mb",
				url : ".?upload",
				flash_swf_url : "plupload/js/plupload.flash.swf",
				silverlight_xap_url : "plupload/js/plupload.silverlight.xap",
				filters : [
					{title : "Image files", extensions : "jpg,gif,png"},
					{title : "Archives", extensions : "zip,rar,7z,tgz,gz"},
					{title : "Other types", extensions : "iso,mov,avi,mp3,mkv,wmv,wma,ogg,ogm,flv,pdf"},
					{title : "All files", extensions : "*"}
				]
			});

			uploader.bind("Init", function(up, params) {
				$("#uploaderRuntime").html("using " + params.runtime);
				$("#uploaderWidget").css("visibility","hidden"); // due to Silverlight bug
			});

			uploader.init();

			uploader.bind("FilesAdded", function(up, files) {
				$.each(files, function(i, file) {
					$("#filelist").append(
						"<li id=\'" + file.id + "\'>" +
						file.name + " (" + plupload.formatSize(file.size) + ") <b>queued...</b>" +
						"</li>"
					);
					$.ajax({
						type: "POST",
						data: {
							action: "FileSelected",
							uploadid: uploader.id,
							fileid: file.id,
							filename: file.name,
							size: file.size
						}
					}).done(function(result){
						//console.log(result);
					});
				});

				up.start();
				up.refresh(); // Reposition Flash/Silverlight
			});

			uploader.bind("UploadFile", function(up, file) {
				$.ajax({
					type: "POST",
					data: {
						action: "FileUploadStarted",
						uploadid: uploader.id,
						fileid: file.id,
						filename: file.name
					}
				}).done(function(result){
					//console.log(result);
				});
			});

			uploader.bind("UploadProgress", function(up, file) {
				$("#" + file.id + " b").html(file.percent + "%");
			});

			uploader.bind("Error", function(up, err) {
				$("#filelist").append("<div>Error: " + err.code +
					", Message: " + err.message +
					(err.file ? ", File: " + err.file.name : "") +
					"</div>"
				);

				up.refresh(); // Reposition Flash/Silverlight
			});

			uploader.bind("FileUploaded", function(up, file, jsonResponse) {
				//console.log(jsonResponse);
				// server sends the real filename that may differ from the requested filename
				var response = JSON.parse(jsonResponse.response);

				file.name = response.result.filename;
				var upsize = response.result.upsize;


				if(upsize == file.size) {
					$("#" + file.id + " b").html("100% (uploading finished)");
					$.ajax({
						type: "POST",
						data: {
							action: "FileUploaded",
							uploadid: uploader.id,
							fileid: file.id,
							filename: file.name
						}
					}).done(function(result){
						//console.log(result);
					});
				} else {
					$("#" + file.id + " b").html(
						"Failed, try again. " +
						"(Reason: File Size=" + file.size + ", Uploaded Size=" + upsize + ")"
					);
					$.ajax({
						type: "POST",
						data: {
							action: "UploadFailed",
							uploadid: uploader.id,
							fileid: file.id,
							filename: file.name,
						}
					}).done(function(result){
						//console.log(result);
					});
				}
			});

			// The form will be submitted asynchronously
			// =========================================
			$("#uploaderForm").submit(function(){
				$.ajax({
					type: "POST",
					data: $.merge([
						{name:"action", value:"FormFieldsReceived"},
						{name:"uploadid", value:uploader.id}
					], $("#uploaderForm").serializeArray()),
				});

				$("#uploaderForm").hide(300);
				$("#uploaderWidget").css("visibility","visible"); // due to Silverlight bug

				return false;
			});
		});

		//]]></script>
		';
	}

	/**
	 * This method was taken directly from plupload/upload.php
	 * @see ciant\uploader\wrappers.IUploadWrapper::onHandleUpload()
	 */
	final public function onHandleUpload($targetDir) {

		// HTTP headers for no cache etc
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		//$cleanupTargetDir = false; // Remove old files
		//$maxFileAge = 60 * 60; // Temp file age in seconds

		//10 minutes execution time
		@set_time_limit(10 * 60);

		// Uncomment this one to fake upload time
		// usleep(5000);

		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
		$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '', $fileName);

		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);

			$count = 1;
			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
				$count++;

			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}

		// Create target dir
		if (!file_exists($targetDir))
			@mkdir($targetDir);

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];

		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				// Open temp file
				$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['file']['tmp_name'], "rb");

					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					fclose($in);
					fclose($out);
					@unlink($_FILES['file']['tmp_name']);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

				fclose($in);
				fclose($out);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		// Return JSON-RPC response
		$fileSize = (integer) filesize($targetDir . DIRECTORY_SEPARATOR . $fileName);
		die('{"jsonrpc" : "2.0", "result" : {"filename":"'.$fileName.'", "upsize":"'.$fileSize.'"} }');
	}
}
?>