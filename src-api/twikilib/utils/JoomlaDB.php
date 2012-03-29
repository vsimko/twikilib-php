<?php
namespace twikilib\utils;
use \PDO;

/**
 * Access to Joomla database.
 * @author Viliam Simko
 */
class JoomlaDB {

	/**
	 * @var PDO
	 */
	private $db;

	function __construct($dbname, $dbuser, $dbpass) {
		$this->db = new PDO(
			"mysql:host=localhost;dbname=$dbname",
			$dbuser,
			$dbpass,
			array(
				// http://cz2.php.net/manual/en/ref.pdo-mysql.php
				PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,

				// Perform direct queries, don't use prepared statements.
				PDO::MYSQL_ATTR_DIRECT_QUERY => true,

				// Enable LOAD LOCAL INFILE.
				PDO::MYSQL_ATTR_LOCAL_INFILE => true,

				// Throw exceptions instead of PHP errors
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

				// Force mysql PDO driver to use UTF-8 for the connection.
				// Will automatically be re-executed when reconnecting.
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
			));
	}

	/**
	 * Example:
	 * @code
	 * $joomla = new JoomlaDB('myjoomladb', 'myuser', 'mypwd');
	 * $stmt = $joomla->sql('select * from jos_something where id=? or id=?', 100, 200);
	 * $result = $stmt->fetchAll(PDO:FETCH_ASSOC);
	 * unset($stmt); // destructor closes the connection
	 * @endcode
	 *
	 * @param string $query
	 * @param mixed $params Also supports variable arguments
	 * @return PDOStatement
	 */
	public function sql($query, $params = array())
	{
		//debug_time_measure(__METHOD__);
		// remove the first parameter if variable arguments detected
		if(!is_array($params))
		{
			$params = func_get_args();
			array_shift($params);
		}

		$stmt = $this->db->prepare($query);
		$stmt->execute($params);
		return $stmt;
	}

	/**
	 * Fetches first cell in the result set (first column in the first row).
	 *
	 * @param string $query
	 * @param mixed $params Also supports variable arguments
	 * @return string
	 */
	public function sqlFetchValue($query, $params = array()) {
		// remove the first parameter if variable arguments detected
		if(!is_array($params)) {
			$params = func_get_args();
			array_shift($params);
		}

		$stmt = $this->db->prepare($query);
		$stmt->execute($params);
		$row = $stmt->fetch(PDO::FETCH_NUM);

		// the connection will be closed in the destructor of PDOStatement
		// because the $stmt is a local variable
		return $row[0];
	}

	/**
	 * @param string $columnName
	 * @param string $query
	 * @param mixed $params
	 * @return array
	 */
	public function sqlFetchColumn($columnName, $query, $params=array()) {
		// remove the first two parameters if variable arguments detected
		if(!is_array($params)) {
			$params = func_get_args();
			array_shift($params);
			array_shift($params);
		}

		// do the query
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		// fetch all data from the column
		$resultData = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$resultData[] = $row[$columnName];
		}
		// the connection will be closed in the destructor of PDOStatement
		// because the $stmt is a local variable
		return $resultData;
	}
}