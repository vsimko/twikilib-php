<?php
namespace ciant\apps\sysinfo;

/**
 * @runnable
 * Provides status information about RAID devices.
 * @author Viliam Simko
 */
class RaidStatus {
	final public function run() {
		echo '<pre>'.file_get_contents('/proc/mdstat').'</pre>';
	}
}