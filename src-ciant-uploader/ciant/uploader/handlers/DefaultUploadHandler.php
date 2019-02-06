<?php
namespace ciant\uploader\handlers;

use ciant\uploader\IUploaderConfig;

use ciant\uploader\Utils;

use twikilib\core\ITopic;
use twikilib\runtime\Container;
use twikilib\runtime\Logger;
use ciant\uploader\fields\TextareaFormField;
use ciant\uploader\fields\EmailFormField;
use ciant\uploader\fields\InputFormField;
use ciant\uploader\fields\IFormField;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * This is the default implementation of the upload handler.
 * It handles all AJAX requests from the client and updates the wiki page.
 *
 * NOTE: the EMAIL is sent inside the method ajaxFormFieldsReceived() by calling the sendEmail() method.
 *
 * TODO: fields should implement their custom sanitization method
 * TODO: separate ajax handling from other function.
 * TODO: it should be possible to plug-in multiple handlers in order to separate emailing and saving to wiki topic
 *
 * @author Viliam Simko
 */
class DefaultUploadHandler implements IUploadHandler {

	/**
	 * @var ciant\uploader\IUploaderConfig
	 */
	private $uconfig;

	/**
	 * The topic loaded lazily from twiki database.
	 * There was a synchronization problem when the topic was loaded within the constructor.
	 * Instead, the implementation uses lazy initialization, so that the locking
	 * can be performed before the topic text is loaded.
	 *
	 * @return ITopic
	 */
	private function getTopic() {
		static $topic;
		if(empty($topic)) {

			$topicName = $this->uconfig->getTopicNameForUploads();
			$twikiConfig = $this->uconfig->getTwikiConfig();

			$topicFactory = new FilesystemDB( $twikiConfig );
			$topic = $topicFactory->loadTopicByName($topicName);
		}

		assert($topic instanceof ITopic);
		return $topic;
	}

	final public function getUploadDir() {
		// the directory is created if needed
		return $this->getTopic()->getTopicAttachmentsNode()->getAttachDir(true);
	}

	private $uploadid;
	final public function setUploadId($uploadid) {
		$this->uploadid = $uploadid;
	}

	final public function __construct(IUploaderConfig $uconfig) {
		$this->uconfig = $uconfig;
	}

	final public function onAfterAjax() {
		$text = $this->getTopic()->getTopicTextNode()->toWikiString();
		if(empty($text))
			Logger::logWarning("Topic text is empty");
	}

	//=================================
	// Helper private methods

	private function updateSlot($slotId, $valueOrCallable) {
		$this->getTopic()->getTopicTextNode()->updateSlot(
				$this->uploadid.":$slotId",
				$valueOrCallable );
	}

	private function removeSlot($slotId) {
		$this->getTopic()->getTopicTextNode()->removeSlot($this->uploadid.":$slotId");
	}

	private function createSlot($slotId, $initialValue='') {
		return $this->getTopic()->getTopicTextNode()->createSlot(
				$this->uploadid.":$slotId",
				$initialValue );
	}

	//=================================
	// AJAX handlers

	/**
	 * (non-PHPdoc)
	 * @see ciant\uploader\handlers.IUploadHandler::ajaxFormFieldsReceived()
	 */
	final public function ajaxFormFieldsReceived(array $params) {

		// These fields will be rendered inside the form
		$formFields = $this->uconfig->getFormFields();

		// we need to update fields value according to data from the $_POST variable
		array_walk($formFields, function(IFormField $formField) {
			$fieldName = $formField->getName();
			$formField->setValue( @$_REQUEST[$fieldName] );
		});

		// create a string representation from fields
		$fieldStr = array_map(function(IFormField $field){
			return Utils::sanitizeString($field->getValue());
		}, $formFields);

		// this created a new record representing the upload
		$this->getTopic()->getTopicTextNode()->replaceText('/$/D',
				"---+++ Upload:".date('Y-m-d H:i:s',time())."\n".
				"<div>".
				$this->createSlot("upload", implode(", ", $fieldStr)).
				"</div>\n");

		$this->getTopic()->getTopicFactory()->saveTopic($this->getTopic());

		$this->sendEmail($fieldStr);
	}

	private function sendEmail($fieldStr) {

		$emailAddr = $this->uconfig->getAfterUploadEmailAddress();

		if($emailAddr != '') {
			$topicUrl = $this->getTopic()->getConfig()->topicNameToTopicUrl(
					$this->getTopic()->getTopicName() );

			mail(	$emailAddr,
					$this->uconfig->getEmailSubject(),
					implode(", ", $fieldStr)."\n\nSee $topicUrl".
					"\n\n ... and don't forget to cleanup the mess as soon".
					" as possible by moving the uploaded files elsewhere.".
					" The best option is to use our TWiki FTP server accessible".
					" from within the CIANT network. Delete the attachments from the topic".
					" and also remove the text from the wiki page.",
					'From: '.$this->uconfig->getApplicationEmail() );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ciant\uploader\handlers.IUploadHandler::ajaxFileSelected()
	 */
	final public function ajaxFileSelected(array $params) {

		$statusSlot = $this->createSlot("file:$params[fileid]", "selected");
		$downloadSlot = $this->createSlot("download:$params[fileid]", $params['filename']);

		$this->updateSlot("upload", function($m) use ($params, $statusSlot, $downloadSlot) {
			return $m['value']."\n   * ".
				"file=$downloadSlot, ".
				"size=$params[size], ".
				"status=$statusSlot";
		});

		$this->getTopic()->getTopicFactory()->saveTopic($this->getTopic());
	}

	/**
	 * (non-PHPdoc)
	 * @see ciant\uploader\handlers.IUploadHandler::ajaxFileUploadStarted()
	 */
	final public function ajaxFileUploadStarted(array $params) {
		$this->updateSlot("file:$params[fileid]", "uploading since ".date('Y-m-d H:i:s', time()));
		$this->getTopic()->getTopicFactory()->saveTopic($this->getTopic());
	}

	/**
	 * (non-PHPdoc)
	 * @see ciant\uploader\handlers.IUploadHandler::ajaxFileUploaded()
	 */
	final public function ajaxFileUploaded(array $params) {

		$fileId = $params['fileid'];
		$fileName = $params['filename'];
		$downloadUrl = $this->uconfig->getPublicUrlForDownload($fileName);// TODO

		$this->updateSlot("file:$fileId", "finished on ".date('Y-m-d H:i:s', time()) );
		$this->removeSlot("file:$fileId");

		$this->updateSlot("download:$fileId", "[[$downloadUrl][$fileName]]");
		$this->removeSlot("download:$fileId");

		$this->getTopic()->getTopicFactory()->saveTopic($this->getTopic());
	}

	/**
	 * (non-PHPdoc)
	 * @see ciant\uploader\handlers.IUploadHandler::ajaxUploadFailed()
	 */
	final public function ajaxUploadFailed(array $params) {
		$this->updateSlot("file:$params[fileid]", "FAILED after finishing the upload on ".date('Y-m-d H:i:s', time()) );
		$this->removeSlot("file:$params[fileid]");
		$this->getTopic()->getTopicFactory()->saveTopic($this->getTopic());
	}
}
?>