<?php
namespace ciant\apps\sysinfo;

/**
 * @runnable
 * Subversion ACL.
 * This serevice was created due to: Main.CiantSvn
 *
 * @author Viliam Simko
 */
class SubversionStatus {
	final public function run() {
		echo '<pre>';
		echo file_get_contents('/ARRAY/SVN/dav_svn.authz');
		echo '</pre>';
	}
}