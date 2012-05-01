<?php
namespace twikilib\fields;

use twikilib\core\IRenderable;

/**
 * Provides API for accessing TWiki tables.
 * Since the class implements the Iterator interface, it can be also used in foreach statements.
 * <code>foreach($table as $rowid => $row) {...}</code>
 * @author Viliam Simko
 */
class Table implements IRenderable, \Iterator {

	private $header = array();
	private $columnNameToIdx = array();
	private $data = array();

	/**
	 * TODO: use string instead of an array
	 * @param string $tableData array of strings
	 */
	final public function __construct(array $tableData) {

//		if(is_string($tableData))
//			$tableData = explode("", $string)

		if(preg_match('/\s*\|(\s*\*[^\*]+\*\s*\|)+/', $tableData[0])) {
			$header = array_shift($tableData);
			$header = str_replace('*', '', $header);
			$this->header = explode('|', substr($header, 1, -2));
			self::trimArray($this->header);

		}

		// use the header to create mapping from columnName to columnIdx
		$this->columnNameToIdx = array_flip($this->header);

		foreach($tableData as $row) {
			$row = explode('|', substr($row, 1, -1));
			self::trimArray($row);
			$this->data[] = $row;
		}
	}

	final public function toWikiString() {
		$result = array();

		// prepare the table header
		$row = '|';
		foreach($this->getTableHeader() as $cell) {
			$row .= " *$cell* |";
		}

		$result[] = $row;

		return implode("\n", $result);
	}

	/**
	 * TODO: try to make this function private instead of assert
	 */
	final public function __toString() {
		assert('/* conversion to string not supported */');
	}

	/**
	 * The table header is an array of column names.
	 * @return array of string
	 */
	final public function getTableHeader() {
		return $this->header;
	}

	/**
	 * @param array $arr Array passed by reference
	 */
	private static function trimArray(&$arr) {
		foreach($arr as $idx => $value) {
			$arr[$idx] = trim($value);
		}
	}

	// ======================================================
	// implementing Iterator methods
	// ======================================================

	/**
	 * @var integer
	 */
	private $currentRow;

	final public function rewind() {
		$this->currentRow = 0;
	}

	final public function current() {
		return $this->data[ $this->currentRow ];
	}

	final public function next() {
		$this->currentRow++;
	}

	final public function key() {
		return $this->currentRow;
	}

	final public function valid() {
		return $this->currentRow < count($this->data);
	}
}