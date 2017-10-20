<?php
// vim: set ai ts=4 sw=4 ft=php:

/**
 * DB_Helper From BMO
 */
if (!class_exists('DB_Helper')) {
	class DB_Helper {

		private static $db;
		private static $dbname = "kvstore";
		private static $getPrep;

		private static $checked = false;

		private static $dbGet;
		private static $dbGetAll;
		private static $dbDel;
		private static $dbAdd;
		private static $dbDelId;

		public function __construct() {
			self::checkDatabase();
		}

		/** This is our pseudo-__construct, called whenever our public functions are called. */
		private static function checkDatabase() {
			// Have we already run?
			if (self::$checked != false)
				return;

			if (!isset(self::$db))
				self::$db = new Database();

			// Definitions
			$create = "CREATE TABLE IF NOT EXISTS ".self::$dbname." ( `module` CHAR(64) NOT NULL, `key` CHAR(255) NOT NULL, `val` LONGBLOB, `type` CHAR(16) DEFAULT NULL, `id` CHAR(255) DEFAULT NULL)";
			$index['index2'] = "ALTER TABLE ".self::$dbname." ADD INDEX index2 (`key`)";
			$index['index4'] = "ALTER TABLE ".self::$dbname." ADD UNIQUE INDEX index4 (`module`, `key`, `id`)";
			$index['index6'] = "ALTER TABLE ".self::$dbname." ADD INDEX index6 (`module`, `id`)";

			// Check to make sure our Key/Value table exists.
			try {
				$res = self::$db->query("SELECT * FROM `".self::$dbname."` LIMIT 1");
			} catch (Exception $e) {
				if ($e->getCode() == "42S02") { // Table does not exist
					self::$db->query($create);
				} else {
					print "I have ".$e->getCode()." as an error<br>\nI don't know what that means.<br/>";
					exit;
				}
			}

			// Check for indexes.
			// TODO: This only works on MySQL
			$res = self::$db->query("SHOW INDEX FROM `".self::$dbname."`");
			$out = $res->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP, 2);
			foreach ($out as $i => $null) {
				// Do we not know about this index? (Are we upgrading?)
				if (!isset($index[$i])) {
					self::$db->query("ALTER TABLE ".self::$dbname." DROP INDEX $i");
				}
			}

			// Now lets make sure all our indexes exist.
			foreach ($index as $i => $sql) {
				if (!isset($out[$i])) {
					self::$db->query($sql);
				}
			}

			// Add our stored procedures
			self::$dbGet = self::$db->prepare("SELECT `val`, `type` FROM `".self::$dbname."` WHERE `module` = :mod AND `key` = :key AND `id` = :id");
			self::$dbGetAll = self::$db->prepare("SELECT `key` FROM `".self::$dbname."` WHERE `module` = :mod AND `id` = :id");
			self::$dbDel = self::$db->prepare("DELETE FROM `".self::$dbname."` WHERE `module` = :mod AND `key` = :key  AND `id` = :id");
			self::$dbAdd = self::$db->prepare("INSERT INTO `".self::$dbname."` ( `module`, `key`, `val`, `type`, `id` ) VALUES ( :mod, :key, :val, :type, :id )");
			self::$dbDelId = self::$db->prepare("DELETE FROM `".self::$dbname."` WHERE `module` = :mod AND `id` = :id");

			// Now this has run, everything IS JUST FINE.
			self::$checked = true;
		}

		/**
		 * Requests a var previously stored
		 *
		 * getConfig requests the variable stored with the key $var, and returns it.
		 * Note that it will return an array or a StdObject if it was handed an array
		 * or object, respectively.
		 *
		 * The optional second parameter allows you to specify a sub-grouping - if
		 * you setConfig('foo', 'bar'), then getConfig('foo') == 'bar'. However,
		 * if you getConfig('foo', 1), that will return (bool) false.
		 *
		 * @param string $var Key to request (not null)
		 * @param string $id Optional sub-group ID. 
		 * @return bool|string|array|StdObject Returns what was handed to setConfig, or bool false if it doesn't exist
		 */
		public function getConfig($var = null, $id = "noid") {
			if ($var === null)
				throw new Exception("Can't getConfig for null");

			// Call our pretend __construct
			self::checkDatabase();

			// Who's asking?
			$mod = get_class($this);
			$query[':mod'] = $mod;
			$query[':id'] = $id;
			$query[':key'] = $var;

			self::$dbGet->execute($query);
			$res = self::$dbGet->fetchAll();
			if (isset($res[0])) {
				// Found!
				if ($res[0]['type'] == "json-obj") {
					return json_decode($res[0]['val']);
				} elseif ($res[0]['type'] == "json-arr") {
					return json_decode($res[0]['val'], true);
				} else {
					return $res[0]['val'];
				}
			}

			// We don't have a result. Maybe there's a default?
			if (property_exists($mod, "dbDefaults")) {
				$def = $mod::$dbDefaults;
				if (isset($def[$var]))
					return $def[$var];
			}

			return false;
		}

		/**
		 * Store a variable, array or object.
		 *
		 * setConfig stores $val against $key, in a format that will return
		 * it almost identically when returned by getConfig.
		 *
		 * The optional third parameter allows you to specify a sub-grouping - if
		 * you setConfig('foo', 'bar'), then getConfig('foo') == 'bar'. However,
		 * getConfig('foo', 1) === (bool) false.
		 *
		 * @param string $key Key to set $var to (not null)
		 * @param string $var Value to set $key to. Can be (bool) false, which will delete the key.
		 * @param string $id Optional sub-group ID. 
		 * @return true
		 */
		public function setConfig($key = null, $val = false, $id = "noid") {

			if ($key === null)
				throw new Exception("Can't setConfig null");

			// Our pretend __construct();
			self::checkDatabase();

			// Start building the query
			$query[':key'] = $key;
			$query[':id'] = $id;

			// Which module is calling this?
			$query[':mod'] = get_class($this);

			// Delete any that previously match
			$res = self::$dbDel->execute($query);

			if ($val === false) // Just wanted to delete
				return true;

			if (is_array($val)) {
				$query[':val'] = json_encode($val);
				$query[':type'] = "json-arr";
			} elseif (is_object($val)) {
				$query[':val'] = json_encode($val);
				$query[':type'] = "json-obj";
			} else {
				$query[':val'] = $val;
				$query[':type'] = null;
			}

			self::$dbAdd->execute($query);
			return true;
		}

		/**
		 * Returns an associative array of all key=>value pairs referenced by $id
		 *
		 * If no $id was provided, return all pairs that weren't set with an $id.
		 * Don't trust this to return the array in any order. If you wish to use
		 * an ordered set, use IDs and sort based on them.
		 *
		 * @param string $id Optional sub-group ID. 
		 * @return array
		 */
		public function getAll($id = "noid") {

			// Our pretend __construct();
			self::checkDatabase();

			// Basic fetchAll.
			$query[':mod'] = get_class($this);
			$query[':id'] = $id;

			self::$dbGetAll->execute($query);
			$out = self::$dbGetAll->fetchAll(PDO::FETCH_COLUMN, 0);
			foreach ($out as $k) {
				$retarr[$k] = $this->getConfig($k, $id);
			}

			if (isset($retarr)) {
				return $retarr;
			} else {
				return array();
			}
		}

		/**
		 * Returns a standard array of all IDs, excluding 'noid'.
		 * Due to font ambiguity (with LL in lower case and I in upper case looking identical in some situations) this uses 'ids' in lower case.
		 *
		 * @return array
		 */
		public function getAllids() {

			// Our pretend __construct();
			self::checkDatabase();

			$mod = get_class($this);
			$ret = self::$db->query("SELECT DISTINCT(`id`) FROM `".self::$dbname."` WHERE `module` = '$mod' AND `id` <> 'noid' ")->fetchAll(PDO::FETCH_COLUMN, 0);
			return $ret;
		}

		/**
		 * Delete all entries that match the ID specified
		 *
		 * This normally is used to remove an item.
		 *
		 * @param string $id Optional sub-group ID. 
		 * @return void
		 */
		public function delById($id = null) {

			self::checkDatabase();

			if ($id === null) {
				throw new Exception("Coder error. You can't delete a blank ID");
			}

			$query[':mod']= get_class($this);
			$query[':id'] = $id;
			self::$dbDelId->execute($query);
		}
	}
}

if (!class_exists('Database')) {
	class Database extends PDO {
		public function __construct() {
			global $amp_conf;
			$dsn = "mysql:host=".$amp_conf['AMPDBHOST'].";dbname=".$amp_conf['AMPDBNAME'];
			$username = $amp_conf['AMPDBUSER'];
			$password = $amp_conf['AMPDBPASS'];
			parent::__construct($dsn, $username, $password);
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

		/**
		 * COMPAT: Queries Database using PDO
		 *
		 * This is a IssabelPBX Compatibility hook for the global 'sql' function that 
		 * previously used PEAR::DB
		 *
		 * @param $sql string SQL String to run
		 * @param $type string Type of query
		 * @param $fetchmode int One of the PDO::FETCH_ methos (see http://www.php.net/manual/en/pdo.constants.php for info)
		 */

		public function sql($sql = null, $type = "query", $fetchmode = PDO::FETCH_BOTH) {
			if (!$sql)
				throw new Exception("No SQL Given to Database->sql()");

			switch ($type) {
			case "query":
				// Note that the basic PDO::query doesn't fetch. So no need for $fetchmode
				$res = $this->sql_query($sql);
				break;
			case "getAll":
				// Return the complete result set
				$res = $this->sql_getAll($sql, $fetchmode);
				break;
			case "getOne":
				// Return the first item of the first row
				$res = $this->sql_getOne($sql);
				break;
			default:
				throw new Exception("Unknown SQL query type of $type");
			}

			return $res;
		}

		/**
		 * Returns a PDOStatement object
		 *
		 * This is for compatibility with older code. I expect this will never be used,
		 * as PDO has much smarter ways of doing things.
		 *
		 * @param $sql string SQL String
		 * @return object PDOStatement object
		 */
		private function sql_query($sql) {
			return $this->query($sql);
		}

		/**
		 * Performs a SQL Query, and returns all results
		 *
		 * This should always return the exact same result as PEAR's $db->getAll query.
		 *
		 * @param $sql string SQL String
		 * @param $fetchmode int PDO::FETCH_* Method
		 * @return array|object Result of the SQL Query
		 */
		private function sql_getAll($sql, $fetchmode) {
			$res = $this->query($sql);
			return $res->fetchAll($fetchmode);
		}

		/**
		 * Perform a SQL Query, and return the first item of the first row.
		 *
		 * @param $sql string SQL String
		 * @return string
		 */

		private function sql_getOne($sql) {
			$res = $this->query($sql);
			$line = $res->fetch(PDO::FETCH_NUM);
			if (isset($line[0]))
				return $line[0];

			return false;
		}

		/**
		 * COMPAT: getMessage - returns an error message
		 *
		 * This will throw an exception, as it shouldn't be used and is a holdover from the PEAR $db object.
		 */
		public function getMessage() {
			// There is a PDO call for this.. I think.
			throw new Exception("getMessage was called on the DB Object");
		}

		/**
		 * COMPAT: isError - checks if the last query was successfull.
		 *
		 * This will throw an exception, as it shouldn't be used and is a holdover from the PEAR $db object.
		 */
		public function isError($result) {
			// Should check that the $result is an object, and it's a PDOStatement object, I think.
			throw new Exception("isError was called on the DB Object");
		}

		/**
		 * COMPAT: escapeSimple - Wraps the suppied string in quotes.
		 *
		 * This wraps the requested string in quotes, and returns it. It's a bad idea. You should be using
		 * prepared queries for this. At some point this will be deprecated and removed.
		 */
		public function escapeSimple($str = null) {
			// Using PDO::quote
			return $this->quote($str);
		}

		/**
		 * HELPER: getOne - Returns first result
		 *
		 * Returns the first result of the first row of the query. Handy shortcot when you're doing
		 * a query that only needs one item returned.
		 */
		public function getOne($sql = null) {
			if ($sql === null)
				throw new Exception("No SQL given to getOne");

			return $this->sql_getOne($sql);
		}
	}
}