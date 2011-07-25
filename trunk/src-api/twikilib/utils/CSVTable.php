<?php
namespace twikilib\utils;

use \Exception;
class CSVTableException extends Exception {};

/**
 * Loading data from CSV files where records are delimited by semi-collon.
 * @author Viliam Simko
 */
class CSVTable {

	/**
	 * Mapping from columnName to columnIdx
	 * @var array
	 */
	private $header = array();
	private $data = array();
	
	final public function __construct() {}
	
	/**
	 * @param string $filename
	 * @return void
	 * @throws CSVTableException
	 */
	final public function loadFromFile($filename) {
		
		$fh = @fopen($filename, 'r');

		if( ! $fh)
			throw new CSVTableException("Could not read CSV data from file: $filename");
		
		// first row contains the table header with column names
		$this->header = array_flip( fgetcsv($fh,0,';') );
		
		// now read the remaining rows and translate index into column name
		while( !feof($fh) ) {
			$row = fgetcsv($fh,0,';');
			if( !empty($row) && is_array($row)) {
				$this->data[] = $row;
			}
		}
	}

	/**
	 * @return array of string
	 */
	final public function getColumnNames() {
		return array_keys($this->header);
	}
	
	/**
	 * @return integer
	 */
	final public function getRowCount() {
		return count($this->data);
	}
	
	/**
	 * Rows are numbered from 0 to size-1.
	 * @param integer $number
	 * @return array
	 */
	final public function getRowByNumber($rowNumber) {
		$result = array();
		$row = & $this->data[$rowNumber];
		
		foreach($this->header as $columnName => $columnIdx) {
			$result[$columnName] = $row[$columnIdx];
		}
		
		return $result;
	}
	
	/**
	 * @param string $columnName
	 * @return array of string Indexed by row number 0..size-1
	 */
	final public function getColumnByName($columnName) {
		$columnNumber = $this->header[$columnName];
		return array_map(function($row) use ($columnNumber) {
			return $row[$columnNumber];
		}, $this->data);
	}
}
?>