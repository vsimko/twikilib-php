<?php
namespace ciant\apps;

use twikilib\runtime\Logger;
use twikilib\core\Config;
use \Exception;

/**
 * @runnable
 * @author Viliam Simko
 */
class ChangeCloud {

	private $exclude;
	private $web;
	private $firstshow;

	public function __construct($params) {
		Logger::disableLogger();

		$from = $this->set_value(@$params['from'], '');
		$this->web = $this->set_value( @$params['web'], 'Main');
		$this->exclude = (array) $this->set_value( @$params['exclude'], array());

		// filter allowed strings
		$allowed_from = array('month', 'week', 'day');

		if(in_array($from, $allowed_from)) {
			$map_hours = array(
			'month'	=> 720,
			'week'	=> 168,
			'day'	=> 24,
			);
			$numhours = $map_hours[$from];
		} elseif(is_numeric($from) && $from > 0) {
			$numhours = ((int) $from);
		} else {
			throw new Exception("Unknown interval '$from'. Allowed values: from = integer | ".implode(' | ', $allowed_from) );
		}

		//$today = date( 'Y-m-d - H:i', time() );
		$this->firstshow = date( 'Y-m-d - H:i', time() - 3600 * $numhours );
		//echo "TODAY: $today<br/>\n";
		//echo "FIRST: $this->firstshow<br/>\n";
	}

	public function run() {
		$config = new Config('config.ini');

		chdir( $config->getWebDataDir('') );
		$logs = glob('log*.txt');
		$logs = array_slice($logs, -2, 2);
		//echo '<pre>';print_r($logs); echo '</pre>';

		foreach($logs as $fname)
		{
			$fh = fopen($fname, 'r');
			while(!feof($fh))
			{
				$line = fgets($fh);
				$cols = explode('|', $line);
				if(	trim(@$cols[1]) > $this->firstshow &&
					trim(@$cols[3]) == 'save' &&
					preg_match('/^\s*'.$this->web.'\.([^\s]+)\s*/', $cols[4], $match)
				) {
					if(!in_array($match[1], $this->exclude)) {
						echo $match[1].' ';
					}
				}
			}
			fclose($fh);
		}
	}

	/**
	 * @param mixed $value
	 * @param mixed $default
	 * @return mixed
	 */
	private function set_value($value, $default) {
		return empty($value) ? $default : trim($value);
	}
}