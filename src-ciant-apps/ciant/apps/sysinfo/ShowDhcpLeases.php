<?php
namespace ciant\apps\sysinfo;

/**
 * @runnable
 * DHCP leases from dnsmasq.
 * Service created due to Main.CiantNetwork
 *
 * @author Viliam Simko
 */
class ShowDhcpLeases {
	final public function run() {
		echo '<pre>';
		echo file_get_contents('/var/lib/misc/dnsmasq.leases');
		echo '</pre>';
	}
}