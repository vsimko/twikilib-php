<?php
namespace ciant\apps\sysinfo;

/**
 * @runnable
 * Shows a list of redirections defined
 * on the main website http://www.ciant.cz
 * Used by the topic Main.CiantDomains
 *
 * @author Viliam Simko
 */
class CiantWebsiteRedirections {
	final public function run() {
		$HTACCESSFILE = '/var/www/SITES/SITE_CIANTWEB/.htaccess';

		echo "<div>\n";
		echo "The following list of redirections has been taken from file: *$HTACCESSFILE*\n\n";
		echo "%TWISTY%\n";
		echo "<verbatim>\n";
		echo file_get_contents($HTACCESSFILE);
		echo "</verbatim>\n";
		echo "%ENDTWISTY%\n";
		echo "</div>\n";
	}
}