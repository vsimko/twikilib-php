<?php
namespace ciant\uploader;

/**
 * Implement this interface if you want to create a new uploader.
 * @author Viliam Simko
 */
interface IUploaderConfig {

	/**
	 * @return string
	 */
	function getAfterUploadEmailAddress();

	/**
	 * @return string
	 */
	function getTopicNameForUploads();

	/**
	 * @return string
	 */
	function getApplicationTitle();

	/**
	 * @return string
	 */
	function getApplicationEmail();

	/**
	 * @return string
	 */
	function getEmailSubject();

	/**
	 * @return array of ciant\uploader\fields\IFormField
	 */
	function getFormFields();

	/**
	 * @return twikilib\core\Config
	 */
	function getTwikiConfig();

	/**
	 * @param string $filename
	 * @return sting
	 */
	function getPublicUrlForDownload($filename);

	/**
	 * @return string
	 * @param string $qualifiedName
	 */
	function createUrl($qualifiedName);
}