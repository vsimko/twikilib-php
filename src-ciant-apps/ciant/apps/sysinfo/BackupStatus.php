<?php
namespace ciant\apps\sysinfo;

use twikilib\core\Config;

/**
 * @runnable
 * Provides information about backup files and accessibility of the backup host.
 * @author Viliam Simko
 */
class BackupStatus {

	final public function run() {
		$config = new Config('config.ini');

		$BACKUPHOST = 'backup.ciant.cz';

		echo "\n---+++ List of directories excluded from backup\n";
		echo '<pre>';
		passthru( 'find '.escapeshellarg($config->twikiRootDir).' -name .nobackup');
		echo '</pre>';

		// there was some problem pinging the backup host
		echo "\n---+++ Status of the host $BACKUPHOST\n";
		echo '<pre>';
		system("ping -W1 -c1 -q $BACKUPHOST", $ret);
		echo '</pre>';

		if($ret != 0) {
			echo "%X% WARNING: %RED% Could not reach the host *$BACKUPHOST* %ENDCOLOR%\n";
		} else {
			echo "%I% %GREEN% OK, The host *$BACKUPHOST* is responding properly %ENDCOLOR%\n";
		}
	}
}