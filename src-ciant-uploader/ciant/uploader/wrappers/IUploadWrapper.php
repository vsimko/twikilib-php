<?php
namespace ciant\uploader\wrappers;

/**
 * This interface represents the client side uploader code.
 * This way you can create multiple front-ends for uploading files.
 * Usually, you would also need to implement the servser-side upload/download
 * handler by implementing the interface IUploadHandler.
 *
 * @see ciant\uploader\handlers.IUploadHandler
 *
 * @author Viliam Simko
 */
interface IUploadWrapper {

	/**
	 * You can prepare any necessary javascript here
	 * e.g. <script type="text/javascript" src="path/to/your.js"></script>
	 * @return void
	 */
	function onRenderHtmlHead();

	/**
	 * You should render your uploader here.
	 */
	function onRenderWidget();

	/**
	 * This is an optional method for uploader that needs to
	 * implement a chunked or streamed upload.
	 * @param string $targetDir
	 * @return void
	 */
	function onHandleUpload($targetDir);
}
?>