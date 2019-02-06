<?php
namespace ciant\apps\sysinfo;

/**
 * @runnable
 * Renders a list of PCI devices.
 * @author Viliam Simko
 */
class ShowPciDevices {
	final public function run() {
		echo '<pre>';
		passthru('lspci');
		echo '</pre>';
	}
}