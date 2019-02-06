<?php
namespace ciant\wrap;

use twikilib\core\ITopic;
use twikilib\core\ITopicFactory;
use twikilib\wrap\ITopicWrapper;
use twikilib\fields\Table;

/**
 * @author Viliam Simko
 */
class RolesTable implements ITopicWrapper, \Iterator {

	/**
	 * @var twikilib\core\ITopic
	 */
	private $wrappedTopic;

	/**
	 * (non-PHPdoc)
	 * @see twikilib\wrap.ITopicWrapper::getWrappedTopic()
	 */
	public function getWrappedTopic() {
		return $this->wrappedTopic;
	}

	/**
	 * @param ITopic $topic
	 */
	public function __construct(ITopic $topic) {
		$this->wrappedTopic = $topic;

		$tables = $topic->getTopicTextNode()->getTablesFromText();
		foreach($tables as $table) {
			if( $this->isRolesTable($table) ) {
				$this->loadTableToRoles($table);
				break;
			}
		}
	}

	/**
	 * @var array of string
	 */
	static private $requiredColumnNames = array(
		'Role', 'Public', 'Who', 'Description' );

	/**
	 * @param Table $table
	 * @return boolean
	 */
	private function isRolesTable(Table $table) {
		$columnNames = $table->getTableHeader();
		$intersection = array_intersect($columnNames, self::$requiredColumnNames);
		return count($intersection) == count(self::$requiredColumnNames);

//		// if we want to force strict order of columns
//		list($role, $public, $who, $descr) = $table->getTableHeader();
//		return
//			strtolower($role) == 'role' &&
//			strtolower($public) == 'public' &&
//			strtolower($who) == 'who' &&
//			strtolower($descr) == 'description';
	}

	private $allRoles = array();

	/**
	 * @param Table $table
	 * @return void
	 */
	final public function loadTableToRoles(Table $table) {
		assert( $this->isRolesTable($table) );

		foreach( $table as $row ) {
			try {
				$role = new RoleEntry(
						$row['Role'], $row['Public'], $row['Who'], $row['Description'] );

				$this->allRoles[] = $role;

			} catch (RoleEntryException $e) {
				// the role will be skipped without any feedback
			}
		}
	}

	function __toString() {
		return 'ROLES LOADED FROM:'.
			$this->getWrappedTopic()->getTopicName()."\n".
			print_r($this->rolesByRoleType, true);
	}

	// ======================================================
	// implementing Iterator methods
	// ======================================================

	/**
	 * @var integer
	 */
	private $currentRow;

	final public function rewind() {
		$this->currentRow = 0;
	}

	final public function current() {
		return $this->allRoles[ $this->currentRow ];
	}

	final public function next() {
		$this->currentRow++;
	}

	final public function key() {
		return $this->currentRow;
	}

	final public function valid() {
		return $this->currentRow < count($this->allRoles);
	}

}