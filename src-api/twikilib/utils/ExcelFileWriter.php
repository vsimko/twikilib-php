<?php
namespace twikilib\utils;

/**
 * Simple tool for writing Execl files (XLS).
 * @author Viliam Simko
 * 
 * Example:
 *   $xls = new ExcelFileWriter;
 *   $xls->writeLabel(1,0,"Student Register");
 *   $xls->writeLabel(2,0,"COURSENO : ");
 *   $xls->writeLabel(2,1,"$courseid");
 *   ...
 *   $xls->sendByHttp('students.xls');
 */
class ExcelFileWriter {

	private $buffer = array();
	
	function writeNumber($Row, $Col, $Value) {
		$this->buffer[] = pack("sssss", 0x203, 14, $Row, $Col, 0x0);
		$this->buffer[] = pack("d", $Value);
	}

	function writeLabel($Row, $Col, $Value ) {
		$L = strlen($Value);
		$this->buffer[] = pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
		$this->buffer[] = $Value;
	}

	public function sendByHttp($filename) {
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");;
		header("Content-Disposition: attachment;filename=".$filename);
		header("Content-Transfer-Encoding: binary ");
		
		echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0); // BOF
		echo implode('', $this->buffer); // CONTENT
		echo pack("ss", 0x0A, 0x00); // EOF
		
		exit();
	}
}
?>