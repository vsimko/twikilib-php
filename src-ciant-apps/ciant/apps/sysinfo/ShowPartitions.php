<?php
namespace ciant\apps\sysinfo;

/**
 * @runnable
 * Renders a list of disk partitions.
 * @author Viliam Simko
 */
class ShowPartitions {
	final public function run() {
		echo file_get_contents('/tmp/alldiskstatus.txt');
	}
}