<?php
namespace ciant\apps\sysinfo;

/**
 * @runnable
 * We currently use EPSON CX21 printer and scanner device.
 * The network printer's hostname contains "CX21" substring.
 * The IP address is extracted from the /etc/dnsmasq.conf file.
 *
 * @author Viliam Simko
 */
use twikilib\core\Config;

class PrinterAndScannerStatus {
	final public function run() {
		echo '<pre>';
		$lines = file('/etc/dnsmasq.conf');
		$found = preg_grep('/dhcp-host.*=.*CX21/', $lines);

		$configstr = reset($found); // use just the first item
		$chunks = explode(',', $configstr );

		$ipaddr = trim($chunks[1]);
		system("ping -W1 -c1 -q $ipaddr", $ret);
		echo '</pre>';

		if($ret != 0) {
			echo "%X% WARNING: %RED% Could not reach the printer $ipaddr %ENDCOLOR%\n";
			echo "   * Try to turn the printer OFF and ON again in order to obtain the IP address automatically from DHCP";
		} else {
			echo "%I% %GREEN% OK, The printer is responding properly %ENDCOLOR%\n";
		}
	}
}