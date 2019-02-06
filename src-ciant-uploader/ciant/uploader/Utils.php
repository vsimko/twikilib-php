<?php
namespace ciant\uploader;

// TODO: Input values should be properly sanitized
use twikilib\utils\Encoder;

class Utils {

	/**
	 * @param string $string
	 * @return string
	 */
	static public function sanitizeString($string) {
		return Encoder::escapeWikiWords( preg_replace('/[\\n\\r\\|<>"\']+/s',' ', $string) );
	}

	static public function sanitizeUrls($string) {
		$string = preg_replace('/[\[\]]/', '', $string);
		$list = preg_split('/\s+/', $string);
		$result = array();
		foreach($list as $url) {
			if( !empty($url) ) {
				$result[] = "[[$url][$url]]";
			}
		}

		return implode(", ", $result);
	}
}
?>