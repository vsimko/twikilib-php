<?php
namespace twikilib\utils;

use twikilib\core\IRenderable;

use \Exception;
class EncoderException extends Exception {}

/**
 * Encoder/Decoder of TWiki arguments and tags.
 * @author Viliam Simko
 */
abstract class Encoder {
	
	/**
	 * List of characters that need to be encoded inside TWiki TAG arguments
	 * @var array
	 */
	static private $META_VAL_CHARS = array( '%' /* must go first */, '"', '{', '}', "\r", "\n" );
	
	/**
	 * List of corresponding encoded values from TWiki TAG arguments
	 * @var array
	 */
	static private $META_VAL_ENCODED = array( '%25' /* must go first */, '%22', '%7b', '%7d', '%0d', '%0a' );
	
	/**
	 * @param string $x the referenced value will be modified
	 */
	final static public function decodeWikiArg(&$x) {
		$x = str_replace(self::$META_VAL_ENCODED, self::$META_VAL_CHARS, $x);
	}
	
	/**
	 * @param string $x the referenced value will be modified
	 */
	final static public function encodeWikiArg(&$x) {
		$x = str_replace(self::$META_VAL_CHARS, self::$META_VAL_ENCODED, $x);
	}
	
	/**
	 * Helper method creates a single %TAG%.
	 * Pairs of paramName+paramValue as variable arguments.
	 * 
	 * @param string $tagName written in UPPERCASE
	 * @param array|object $args (use NULL if you want to avoid empty brackets "{}")
	 * @return string
	 */
	final static public function createWikiTag($tagName, $tagArgs = null ) {
		
		if($tagArgs === null) {
			return '%'.$tagName.'%';
		}
		
		assert( is_array($tagArgs) || is_object($tagArgs) );

		$result = array();
		foreach($tagArgs as $argName => $argValue) {
			assert( is_string($argName) && !empty($argName) );
			assert( is_scalar($argValue) );
			Encoder::encodeWikiArg($argValue);
			$result[] = "$argName=\"$argValue\"";
		}
		
		return '%'.$tagName.'{'.implode(" ", $result).'}%';
	}
	
	/**
	 * NOTE: the most CPU intensive method, should be optimized as much as possible
`	 * @param string $unparsedArgs
	 * @return object
	 * @throws Exception
	 */
	final static public function parseWikiTagArgs( $unparsedArgs ) {
		if(preg_match_all('/([_a-zA-Z0-9]+)="([^"]*)"/', $unparsedArgs, $match)) {
			$parsedArgs = array();
			foreach($match[1] as $idx => $argName) {
				$parsedArgs[$argName] = str_replace(self::$META_VAL_ENCODED, self::$META_VAL_CHARS, $match[2][$idx]);
			}
			return (object) $parsedArgs;
		}
		
		throw new EncoderException("Unknown format of tag arguments: ".var_export($unparsedArgs, true) );
	}
	
	/**
	 * For every element of a given array calls the toWikiString() function.
	 * @param array $arrayOfRenderableObjects
	 */
	final static public function arrayToWikiString(array & $arrayOfRenderableObjects) {
		return implode( array_map(
			function(IRenderable $renderableObject){
				return $renderableObject->toWikiString();
			}, $arrayOfRenderableObjects ));
	}
	
	/**
	 * Escapes words that would be recognized as wiki-words.
	 * @param string $text
	 * @return string
	 */
	static final public function escapeWikiWords($text) {
		return preg_replace('/([A-Z][a-z0-9]+[A-Z][^\s]*|:)/', '<nop>$1', $text);
	}
	
	static final public function createVerbatimText($text) {
		return Encoder::escapeWikiWords(htmlspecialchars($text));
	}

	/**
	 * Turn a string into CamelCase format.
	 * @param $string
	 * @return string
	 */
	static final public function strToCamelCase($string)
	{
		// remove weird sequences of characters
		$string = preg_replace('/[^a-zA-Z0-9]+/',' ', $string);
				
		// uppercase first characer in a word
		return trim(preg_replace_callback(
			array('/ ([^ ])/', '/(^.)/'),
			create_function('$match', 'return strtoupper($match[1]);'),
			$string ));
	}
	
	/**
	 * Filteres entered text to desired length.
	 * @param string $text
	 * @param int $maxLength
	 * @param string $padString (optional)
	 * @return string
	 */
	static final public function filterStringLength($text, $maxLength, $padString='..') {
		return  (strlen($text) > $maxLength)
			? substr($text, 0, $maxLength - strlen($padString)).$padString
			: $text;
	}
	
	/**
	 * @param string $textToShow
	 * @param string $valueToStore
	 * @return string
	 */
	static final public function createSelectValueItem($textToShow, $valueToStore) {
		assert(is_string($textToShow));
		assert(is_string($valueToStore));
		return preg_replace('/[,=|]/', '', $textToShow).'='.preg_replace('/[,=|]/', '', $valueToStore);
	}
	
	/**
	 * @param string $string
	 * @return string
	 */
	static final public function replaceAccents($string) {
	  return str_replace(
		array(
			'ľ', 'š', 'č', 'ť', 'ž', 'ô', 'ě', 'ř', 'ů', 'ĺ',
			'Ľ', 'Š', 'Č', 'Ť', 'Ž', 'Ô', 'Ě', 'Ř', 'Ů', 'Ĺ',
			'à','á','â','ã','ä', 'ç', 'è','é','ê','ë', 'ì','í','î','ï', 'ñ', 'ò','ó','ô','õ','ö', 'ù','ú','û','ü', 'ý','ÿ',
			'À','Á','Â','Ã','Ä', 'Ç', 'È','É','Ê','Ë', 'Ì','Í','Î','Ï', 'Ñ', 'Ò','Ó','Ô','Õ','Ö', 'Ù','Ú','Û','Ü', 'Ý'),
		array(
			'l', 's', 'c', 't', 'z', 'o', 'e', 'r', 'u' ,'l',
			'L', 'S', 'C', 'T', 'Z', 'O', 'E', 'R', 'U' ,'L',
			'a','a','a','a','a', 'c', 'e','e','e','e', 'i','i','i','i', 'n', 'o','o','o','o','o', 'u','u','u','u', 'y','y',
			'A','A','A','A','A', 'C', 'E','E','E','E', 'I','I','I','I', 'N', 'O','O','O','O','O', 'U','U','U','U', 'Y'),
		$string );
	}
	
	/**
	 * Useful for converting text that should be used inside a form field or table cell.
	 * @param string $text
	 * @return string
	 */
	static final public function createSingleLineText($text) {
		return preg_replace('/\s+/', ' ', $text);
	}
	
	/**
	 * This method extracts potential wiki topic names (camel-case) from an arbitrary text.
	 * Rest of the text will be ignored.
	 * @param string $text
	 * @return array of string
	 */
	static final public function extractWikiNamesFromString($text) {
		if(preg_match_all('/([A-Z][a-z]+\.)?([A-Z][a-z0-9_]+[A-Z][^[:punct:]\s]*)/', $text, $matches)) {
			return $matches[0];
		}
		
		return array();
	}
	
}

?>