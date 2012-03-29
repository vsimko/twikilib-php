<?php
namespace twikilib\core;

/**
 * @deprecated
 * @author Viliam Simko
 */
class Session {

	private $sessionData;

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @param Config $twikiConfig
	 * @param array $cookies
	 */
	public function __construct(Config $twikiConfig, array $cookies) {

		if( empty($cookies['TWIKISID']) )
			return;

		$sessionId = $cookies['TWIKISID'];
		$this->sessionData = $this->loadSessionDataFromFile(
			$twikiConfig->twikiRootDir."/working/tmp/cgisess_$sessionId" );
	}

	/**
	 * @param string $filename
	 * @return array
	 */
	private function loadSessionDataFromFile($filename) {
		assert( is_file($filename) );

		$content = file_get_contents($filename);
		preg_match_all('/\'([^\']+)\'\s*=>\s*([0-9]+|\'([^\']*)\')/', $content, $match);

		$result = array();
		foreach($match[1] as $idx => $key) {
			$result[$key] = $match[2][$idx][0]=="'" ? $match[3][$idx] : $match[2][$idx];
		}

		return $result;
	}

	/**
	 * @return boolean
	 */
	public function isAuthenticated() {
		return (boolean) ! empty( $this->sessionData['AUTHUSER'] );
	}
}