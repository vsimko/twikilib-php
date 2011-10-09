<?php // @pharwebstub

require_once 'init-twikilib-api.php';

use twikilib\runtime\Container;
use twikilib\runtime\RunnableAppsLister;
use \Exception;

run_app_from_web();

function run_app_from_web() {
	if(count($_REQUEST) > 0) {
		$keys = array_keys($_REQUEST);
		$firstKey = $keys[0];
		if( $_REQUEST[$firstKey] == '' ) {
			$_REQUEST[] = preg_replace('/[\/\._]/', '\\', $firstKey);
		}
	} else {
		echo "<pre>";
		echo "USAGE: ...?classname[&name=value&name=value...]\n";
		echo "or     <a href='".$_SERVER['PHP_SELF']."?list'>...?list</a>\n";
		echo "</pre>";
		return;
	}
	
	// a special case, when user requested a list of all runnable applications
	if( isset($_REQUEST['list']) ) {
		echo "<style type='text/css' media='all'>li {font-family:monospace}</style>\n";
		echo "<h3>Searching for runnable applications in:</h3>\n";
		echo "<ul>\n";
		foreach(Container::getParsedIncludePath() as $incItem) {
			echo "\t<li>".htmlspecialchars($incItem)."</li>\n";
		}
		echo "</ul>\n";
		
		echo "<h3>Listing runnable applications:</h3>\n";
		echo "<ul>\n";
		foreach( RunnableAppsLister::listRunnableApps() as $className) {
			$appName = str_replace('\\', '.', $className);
			echo "\t<li>";
			echo "<a href='".$_SERVER['PHP_SELF']."?$appName'>$appName</a>";
			if(Container::isClassDeprecated($className)) {
				echo ' (deprecated)';
			}
			echo "</li>\n";
		}
		echo "</ul>\n";
		return;
	}
	
	try {
		$app = Container::createRunnableApp($_REQUEST);
		Container::runApplication($app);
	} catch (Exception $e) {
		echo '<pre>ERROR ('.get_class($e).'): '.$e->getMessage()."</pre>\n";
	}
}
?>