<?php
namespace ciant\apps\sysinfo;

/**
 * @runnable
 * Service created due to the Main.CiantNetwork topic
 *
 * Note: this operation may take several seconds
 * and therefore it SHOULD NOT be used within real-time queries.
 *
 * @author Viliam Simko
 */
class AvailCiantHosts {
	final public function run() {
		echo '<pre>';
		passthru('nmap -n --host-timeout 3000 -sP 10.10.10.0/24');
		echo '</pre>';
	}
}