<?php
namespace ciant\apps\sysinfo;

/**
 * @runnable
 * @author Viliam Simko
 */
class ThermalMonitor {
	public function run() {
		echo '<pre>';
		passthru('sensors');
		echo '</pre>';
	}
}