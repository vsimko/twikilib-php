<?php
namespace twikilib\examples;

use twikilib\runtime\Terminal;
use \ReflectionClass;

/**
 * @runnable
 * @author Viliam Simko
 */
class TerminalExample {
	public function run() {
		Terminal::setColor(Terminal::GREEN);
		echo "Available colors:\n";
		Terminal::resetColor();

		$class = new ReflectionClass('twikilib\runtime\Terminal');
		foreach($class->getConstants() as $constName => $constValue) {
			echo " - ".str_pad($constName, 20);
			Terminal::setColor($constValue);
			echo "########";
			Terminal::resetColor();
			echo "\n";
		}
	}
}