<?php
namespace ciant\uploader;

use twikilib\runtime\Container;

use twikilib\runtime\Logger;
use ciant\uploader\fields\IFormField;
use ciant\uploader\wrappers\IUploadWrapper;
use ciant\uploader\handlers\IUploadHandler;

/**
 * This class encapsulates the upload/download workflow.
 * It needs and instance of IUploadWrapper and IUploadHandler which are then
 * used for creating the client AJAX code and the server-side handler used by
 * the client.
 *
 * @see IUploadWrapper
 * @see IUploadHandler
 * @see IUploaderConfig
 *
 * @author Viliam Simko
 */
class UploadWorkflow {

	/**
	 * @var ciant\uploader\IUploaderConfig
	 */
	private $uconfig;

	/**
	 * @var ciant\uploader\handlers\IUploadHandler
	 */
	private $uhandler;
	/**
	 * @var ciant\uploader\wrappers\IUploadWrapper
	 */
	private $uwrapper;

	/**
	 * @var string
	 */
	private $lockFileName;

	/**
	 * Using manual dependency injection.
	 *
	 * @param IUploaderConfig $uconfig
	 * @param IUploadHandler  $uhandler
	 * @param IUploadWrapper  $uwrapper
	 */
	final public function __construct(
		IUploaderConfig $uconfig,
		IUploadHandler  $uhandler,
		IUploadWrapper  $uwrapper,
		                $lockFileName ) {

		$this->uconfig		= $uconfig;
		$this->uhandler		= $uhandler;
		$this->uwrapper		= $uwrapper;
		$this->lockFileName	= $lockFileName;
	}

	private $lockfh;

	private function lock() {
		assert(empty($this->lockfh));

		$this->lockfh = fopen($this->lockFileName, 'r');

		//Container::measureTime("Lock: action=".@$_REQUEST['action']);
		if(flock($this->lockfh, LOCK_EX)) {
			//Container::measureTime();
			return;
		}

		throw new \Exception("Could not acquire the lock: ".$this->lockFileName);
	}

	private function unlock() {
		assert(!empty($this->lockfh));
		if(flock($this->lockfh, LOCK_UN)) {
			fclose($this->lockfh);
			return;
		}

		throw new \Exception("Could not release the lock".$this->lockFileName);
	}

	/**
	 * Decides which action to perform depending on the HTTP request parameters.
	 * Examples:
	 * <ul>
	 *   <li>http://mydomain/myapp?upload&... maps to handleUpload()</li>
	 *   <li>http://mydomain/myapp?download&... maps to handleDownload()</li>
	 *   <li>http://mydomain/myapp maps to start()</li>
	 * </ul>
	 * @return void
	 */
	final public function dispatchHttpRequest() {

		// download handles the locking in a different way
		if(isset($_REQUEST['download']))
			$this->handleDownload();

		$this->lock();

			if(isset($_REQUEST['upload']))
				$this->handleUpload();
			else
				$this->start();

		$this->unlock();
		// WARNING: this implementation relies on the fact that
		// unlocking and closing of the file descriptor happens
		// automatically after each request
	}

	/**
	 * This method either handles the AJAX calls or renders the webpage.
	 * @return void
	 */
	final public function start() {

		// action could be the name of the first parameter
		$action = empty($_REQUEST['action'])
			? key($_REQUEST)
			: $_REQUEST['action'];

		// no action selected
		if( empty($action) || !empty($_REQUEST[$action])) {
			include 'ciant/uploader/tpl/main.tpl.php';
		} else {

			echo "ACTION: $action<br/>\n";
			echo '<pre>'.print_r($_REQUEST, true).'</pre>';

			// use uploadid or create a new one
			$this->uhandler->setUploadId($_REQUEST['uploadid']);

			$methodName = 'ajax'.$action;
			if(method_exists($this->uhandler, $methodName)) {
	 			$this->uhandler->$methodName($_REQUEST);
	 			$this->uhandler->onAfterAjax();
			}
		}
	}

	/**
	 * @return void
	 */
	final public function handleUpload() {
		$targetDir = $this->uhandler->getUploadDir();
		$this->uwrapper->onHandleUpload($targetDir);
	}

	/**
	 * @return void
	 */
	final public function handleDownload() {

		set_time_limit(0); // disable execution timeout during the download

		$filename = basename(@$_GET['filename']);

		if(empty($filename))
			return;

		header("Pragma: public");
 		header("Expires: 0");
 		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
 		header("Content-Type: application/force-download");
 		header("Content-Type: application/octet-stream");
 		header("Content-Type: application/download");;
 		header("Content-Disposition: attachment;filename=".$filename);
 		header("Content-Transfer-Encoding: binary");

 		$this->lock();
		$fileLocation = $this->uhandler->getUploadDir().'/'.$filename;
		$this->unlock();

		header("Content-Length: ".filesize($fileLocation).";");

		$fh = @fopen($fileLocation, 'rb');
		if(!$fh)
			return;

		while( ! feof($fh) ) {
			echo fread($fh, 1048576); // 1MiB chunks
		}
		fclose($fh);
	}
}
?>