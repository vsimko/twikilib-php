<?php
namespace twikilib\utils;

class Terminal {
	
	const UNDERSCORE	= "[4m";
	const BLACK			= "[0;30m";
	const REVERSE		= "[7m";
	const BOLD			= "[1m";
	const NORMAL		= "[0m";
	const LIGHT_RED		= "[1;31m";
	const LIGHT_GREEN	= "[1;32m";
	const YELLOW		= "[1;33m";
	const LIGHT_BLUE	= "[1;34m";
	const MAGENTA		= "[1;35m";
	const LIGHT_CYAN	= "[1;36m";
	const WHITE			= "[1;37m";
	const RED			= "[0;31m";
	const GREEN			= "[0;32m";
	const BROWN			= "[0;33m";
	const BLUE			= "[0;34m";
	const CYAN			= "[0;36m";
	
	static final public function setColor($colorCode) {
		echo chr(27).$colorCode;
	}
	
	static final public function resetColor() {
		echo self::setColor(self::NORMAL);
	}
}
?>