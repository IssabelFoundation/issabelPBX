<?php
if(!class_exists('issabelpbx_DB_mysqli') && class_exists('DB_mysqli')) {
	class issabelpbx_DB_mysqli extends DB_mysqli
	{
		function insert_id() {
			$result = @mysqli_insert_id($this->connection);
			if (!$result) {
				return $this->mysqliRaiseError();
			} else {
				return $result;
			}
		}
	}
}

if(!class_exists('issabelpbx_DB_mysql') && class_exists('DB_mysql')) {
	class issabelpbx_DB_mysql extends DB_mysql
	{
		function insert_id() {
			$result = @mysql_insert_id($this->connection);
			if (!$result) {
				return $this->mysqlRaiseError();
			} else {
				return $result;
			}
		}
	}
}

if(!class_exists('issabelpbx_DB_sqlite') && class_exists('DB_sqlite')) {
	class issabelpbx_DB_sqlite extends DB_sqlite
	{
		function insert_id() {
			$result = @sqlite_last_insert_rowid($this->connection);
			if (!$result) {
				return $this->sqliteRaiseError();
			} else {
				return $result;
			}
		}
	}
}
