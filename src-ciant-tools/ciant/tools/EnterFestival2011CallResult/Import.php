<?php
namespace ciant\tools\EnterFestival2011CallResult;

use twikilib\utils\Encoder;
use twikilib\core\ITopicFactory;

use \Exception;
class ImportExteption extends Exception {}

/**
 * 0	NUM
 * 1	ID
 * 2	TITLE
 * 3	GENRE
 * 4	TEXT1
 * 5	TEXT2
 * 6	LINK
 * 7	AUTHOR
 * 8	AUTHOR-TEXT1
 * 9	AUTHOR-TEXT2
 * 10	???
 * 11	EMAIL
 * 12	PHONE
 * 13	???
 * 14	ATTACHMENT
 * @author Viliam Simko
 *
 */
class Entry {
	public $id;
	public $title;
	public $genre;
	public $text;
	public $link;
	public $author;
	public $authorText;
	public $email;
	public $phone;
	public $attachment;

	//static private $sequence = 0;

	function __construct(array $row) {

		//self::$sequence++;

		array_walk($row, function (&$txt){
			$txt = str_replace('”', '"', $txt);
			$txt = str_replace('[', '(', $txt);
			$txt = str_replace(']', ')', $txt);
			$txt = str_replace('|', '_', trim($txt));
			$txt = preg_replace('/[^[:print:]]/', '', $txt);
		});

		$this->id			= preg_replace('/[#-]/', '', @$row[1]);//.'_'.self::$sequence;
		$this->title		= empty($row[2]) ? '-No Title-' : $row[2];
		$this->genre		= @$row[3];
		$this->text			= @$row[4]."\n".@$row[5];
		$this->link			= @$row[6];
		$this->author		= @$row[7];
		$this->authorText	= @$row[8]."\n".@$row[9];
		$this->email		= @$row[11];
		$this->phone		= @$row[12];
		$this->attachment	= @$row[14];


		if(empty($this->author))
			$this->author = $this->email;

		if(preg_match('/^www\./', $this->link))
			$this->link = 'http://'.$this->link;

		if(preg_match_all('/(https?:\/\/[^" ]+)/', $this->link, $m)) {
			$this->link = $m[1];
		} else {
			$this->link = array($this->link);
		}

		// remove empty links
		$this->link = array_filter($this->link);
	}
}

class Import {

	/**
	 * @var array of Entry
	 */
	private $entires;

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	function __construct(ITopicFactory $topicFactory) {
		$this->topicFactory = $topicFactory;
	}

	/**
	 * Loads a spreadsheet table encoded in CSV into a PHP array structure $rows.
	 * @param string $filename
	 */
	final public function loadCSVFromFile($filename) {

		$fh = @fopen($filename, 'r');

		if( ! $fh)
			throw new ImportExteption("Could not read CSV data from file: $filename");

		//now read the remaining rows and translate index into column name
		$this->entires = array();
		while(!feof($fh)) {
			$line = trim(fgets($fh));
			if( ! empty($line) ) {
				$row = explode('|', $line);
				$this->entires[] = new Entry($row);
			}
		}

		fclose($fh);
	}

	/**
	 * @return array of Entry
	 */
	final public function getEntires() {
		return $this->entires;
	}
}