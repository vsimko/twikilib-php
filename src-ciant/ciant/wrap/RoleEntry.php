<?php
namespace ciant\wrap;

class RoleEntryException extends \Exception {}

/**
 * TODO: support multiple languages
 * @author Viliam Simko
 */
class RoleEntry {

	/**
	 * @param string $roleType
	 * @param string $public
	 * @param string $who
	 * @param string $description
	 * @throws RoleEntryException
	 */
	final public function __construct($roleType, $public, $who, $description) {
		$this->roleType = trim($roleType);
		$this->public = trim($public);
		$this->who = trim($who);
		$this->description = trim($description);

		if(empty($this->roleType))
			throw new RoleEntryException("Empty role type (the 'Role' column)");

		if(empty($this->who))
			throw new RoleEntryException("No person specified (the 'Who' column)");

		if($this->public != 'yes' && $htis->public != 'no' )
			throw new RoleEntryException("The 'Public' flag should be set either to 'yes' or 'no'");
	}

	/**
	 * @var string
	 */
	private $roleType;
	final public function getRoleType() {
		return $this->roleType;
	}

	/**
	 * @var string
	 */
	private $public;
	final public function isPublic() {
		return $this->public;
	}

	/**
	 * @var string
	 */
	private $who;
	final public function getWho() {
		return $this->who;
	}

	/**
	 * @var string
	 */
	private $description = array();
	final public function getDescription( $lang='' ) {
		return $this->description;
	}
}