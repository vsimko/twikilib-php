<?php
namespace ciant\apps;

use \Exception;

/**
 * @runnable
 * Just a very simple application that generates a short random password.
 * REQ1: The return value should not end with newline because that would cause rendering problems when included to TWiki text
 * @author Viliam Simko
 */
class GenId {

	/**
	 * Length of the generated password.
	 * @var int
	 */
	private $length = 8;

	const HASHTYPE_NONE = 'none';
	const HASHTYPE_MD5 = 'md5';

	private $hashType = self::HASHTYPE_NONE;

	final public function __construct($params) {

		// allowed hash types
		$hashTypes = array(self::HASHTYPE_NONE, self::HASHTYPE_MD5);

		if( @$params['help'] ) {
			throw new Exception(
				"Optional parameters: ".
				"length (default:".$this->length."), ".
				"hashtype='".implode('|', $hashTypes)."' (default:".$this->hashType.")"
			);
		}

		if(@$params['length'] > 1)
			$this->length = $params['length'];

		$this->hashType = empty($params['hashtype'])
			? self::HASHTYPE_NONE
			: $params['hashtype'];


		// check allowed hash types
		if(! in_array($this->hashType, $hashTypes))
			throw new Exception("This hashtype is not allowed");
	}

	public function run() {
		// start with a blank password
		$password = "";

		// define possible characters
		$possible = "0123456789aAbBcdfFgGhHjJkmMnNpPqQrRstTvwxyYz";

		// set up a counter
		$i = 0;

		// add random characters to $password until $length is reached
		while ($i < $this->length) {

			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

			$password .= $char;
			$i++;
		}

		switch($this->hashType) {
			case self::HASHTYPE_NONE : echo $password; break;
			case self::HASHTYPE_MD5  : echo "MD5( $password ) = ".md5($password); break;
		}
	}
}