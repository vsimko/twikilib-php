<?php
namespace twikilib\utils;

use \ReflectionClass;

/**
 * @runnable
 * @author Viliam Simko
 */
class TerminalTest {
	public function run() {
		Terminal::setColor(Terminal::GREEN);
		echo "Available colors:\n";
		Terminal::resetColor();
		
		$class = new ReflectionClass('twikilib\utils\Terminal');
		foreach($class->getConstants() as $constName => $constValue) {
			echo " - ".str_pad($constName, 20);
			Terminal::setColor($constValue);
			echo "########";
			Terminal::resetColor();
			echo "\n";
		}
	}
}
?>