<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PEAR DB driver for PHP's rqlite databases
 * https://github.com/rqlite/rqlite
 * based on the PEAR sqlite extension, with special hacks for IssabelPBX
 *
 * LICENSE: MIT
 *
 * @category   Database
 * @package    DB
 * @author     Nicolás Gudiño <asternic@gmail.com>
 * @copyright  2022 Nicolás Gudiño
 * @license    https://opensource.org/licenses/MIT
 */

/**
 * Obtain the DB_common class so it can be extended from
 */
require_once 'DB/common.php';

/**
 * The methods PEAR DB uses to interact with PHP's sqlite extension
 * for interacting with SQLite databases
 *
 * These methods overload the ones declared in DB_common.
 *
 * NOTICE:  This driver needs PHP's track_errors ini setting to be on.
 * It is automatically turned on when connecting to the database.
 * Make sure your scripts don't turn it off.
 *
 * @category   Database
 * @package    DB
 * @author     Urs Gehrig <urs@circle.ch>
 * @author     Mika Tuupola <tuupola@appelsiini.net>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2007 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0 3.0
 * @version    Release: 1.11.0
 * @link       http://pear.php.net/package/DB
 */
class DB_rqlite extends DB_common
{
    // {{{ properties

    /**
     * The DB driver type (mysql, oci8, odbc, etc.)
     * @var string
     */
    var $phptype = 'rqlite';

    /**
     * The database syntax variant to be used (db2, access, etc.), if any
     * @var string
     */
    var $dbsyntax = 'rqlite';

    /**
     * The capabilities of this DB implementation
     *
     * The 'new_link' element contains the PHP version that first provided
     * new_link support for this DBMS.  Contains false if it's unsupported.
     *
     * Meaning of the 'limit' element:
     *   + 'emulate' = emulate with fetch row by number
     *   + 'alter'   = alter the query
     *   + false     = skip rows
     *
     * @var array
     */
    var $features = array(
        'limit'         => 'alter',
        'new_link'      => false,
        'numrows'       => true,
        'pconnect'      => true,
        'prepare'       => false,
        'ssl'           => false,
        'transactions'  => false,
    );

    /**
     * A mapping of native error codes to DB error codes
     *
     * {@internal  Error codes according to sqlite_exec.  See the online
     * manual at http://sqlite.org/c_interface.html for info.
     * This error handling based on sqlite_exec is not yet implemented.}}
     *
     * @var array
     */
    var $errorcode_map = array(
    );

    /**
     * The raw database connection created by PHP
     * @var resource
     */
    var $connection;

    /**
     * The DSN information for connecting to a database
     * @var array
     */
    var $dsn = array();


    /**
     * SQLite data types
     *
     * @link http://www.sqlite.org/datatypes.html
     *
     * @var array
     */
    var $keywords = array (
        'BLOB'      => '',
        'BOOLEAN'   => '',
        'CHARACTER' => '',
        'CLOB'      => '',
        'FLOAT'     => '',
        'INTEGER'   => '',
        'KEY'       => '',
        'NATIONAL'  => '',
        'NUMERIC'   => '',
        'NVARCHAR'  => '',
        'PRIMARY'   => '',
        'TEXT'      => '',
        'TIMESTAMP' => '',
        'UNIQUE'    => '',
        'VARCHAR'   => '',
        'VARYING'   => '',
    );

    /**
     * The most recent error message from $php_errormsg
     * @var string
     * @access private
     */
    var $_lasterror = '';
    var $_apcuAvailable;
    var $_uniqueid;

    // }}}
    // {{{ constructor

    /**
     * This constructor calls <kbd>parent::__construct()</kbd>
     *
     * @return void
     */
    function __construct()
    {
        parent::__construct();
        $this->$_apcuAvailabe = function_exists('apcu_enabled') && apcu_enabled();
        $this->_uniqueid = sprintf("%08x", abs(crc32($_SERVER['REMOTE_ADDR'] . $_SERVER['REQUEST_TIME'] . $_SERVER['REMOTE_PORT'])));
    }


    function &getAssoc($query, $force_array = false, $params = array(), $fetchmode = DB_FETCHMODE_DEFAULT, $group = false) {
        $params = (array)$params;

        if (sizeof($params) > 0) {
            $res = $this->_select($query,$params,$fetchmode);
        } else {
            $res = $this->_select($query);
        }

        if (DB::isError($res)) {
            return $res;
        }

        if ($fetchmode == DB_FETCHMODE_DEFAULT) {
            $fetchmode = $this->fetchmode;
        }
        $cols = count($res['results'][0]['columns']);

        if ($cols < 2) {
            $e = new DB_Error(DB_ERROR_TRUNCATED);
            return $e;
        }

        $results = array();
        if ($cols > 2 || $force_array) {

            if($fetchmode == DB_FETCHMODE_ASSOC || $fetchmode == DB_FETCHMODE_OBJECT) {
                $colnames = $res['results'][0]['columns'];
                $newarray=array();
                if(isset($res['results'][0]['values'])) {
                    foreach($res['results'][0]['values'] as  $key=>$row) {
                        $newrow=array();
                        foreach($row as $idx=>$val) {
                            $newrow[$colnames[$idx]]=$val;
                        }
                        $newarray[]=$newrow;
                    }
                } else {
                    $newarray=array();
                }

                $res['results'][0]['values']=$newarray;

            }

            $final = array();
            if($fetchmode == DB_FETCHMODE_ARRAY) {
                foreach($res['results'][0]['values'] as $idx=>$row) {
                    $key = array_shift($row);
                    $final[$key]=$row;
                } 
            } else {
                foreach($res['results'][0]['values'] as $idx=>$row) {
                    $key = array_shift($row);
                    $final[$key]=(object)$row;
                } 
            } 
        } else {
            foreach($res['results'][0]['values'] as $idx=>$row) {
                $key = array_shift($row);
                if ($group) {
                    $final[$key][]=$row[0];
                } else {
                    $final[$key]=$row[0];
                }
            }
        }
        return $final; 
    }

    function &getCol($query, $col = 0, $params = array()) {
        $params = (array)$params;
        if (count($params)) {
            $res = $this->_select($query,$params,$fetchmode);
        } else {
            $res = $this->_select($query);
        }

        if (DB::isError($res)) {
            return $res;
        }

        if(is_numeric($col)) {
            $value = $res['results'][0]['values'][0][$col];
            return array($value);
        } else {
            $colnames = $res['results'][0]['columns'];
            $newarray=array();
            if(isset($res['results'][0]['values'])) {
                foreach($res['results'][0]['values'] as  $key=>$row) {
                    $newrow=array();
                    foreach($row as $idx=>$val) {
                        $newrow[$colnames[$idx]]=$val;
                    }
                    $newarray[]=$newrow[$col];
                }
            } else {
                $newarray=array();
            }
            return $newarray;
        }
    }

    function &getRow($query,$params=array(),$fetchmode = DB_FETCHMODE_DEFAULT) {
        $params = (array)$params;
        if (count($params)) {
            $res = $this->_select($query,$params,$fetchmode);
        } else {
            $res = $this->_select($query);
        }
        if (DB::isError($res)) {
            return $res;
        }
        return $res['results'][0]['values'][0];
    }

    function &getOne($query,$params=array(),$fetchmode = DB_FETCHMODE_DEFAULT) {
        $params = (array)$params;
        if (count($params)) {
            $res = $this->_select($query,$params,$fetchmode);
        } else {
            $res = $this->_select($query);
        }
        if (DB::isError($res)) {
            return $res;
        }
        return $res['results'][0]['values'][0][0];
    }

    function &getAll($query, $params = array(), $fetchmode = DB_FETCHMODE_DEFAULT)
    {
        // compat check, the params and fetchmode parameters used to
        // have the opposite order

        // show columns hack
        if(preg_match("/^show columns from ([^ ]*)( ?(where field) ?= ?\"([^\"]*)\")?/i",$query,$matches)) {
            $results = $this->tableInfo($matches[1]);
            $final=array();
            foreach($results as $key=>$data) {
                $record = array();
                $record[]=$data['name'];
                $record[]=$data['type'];
                if(preg_match("/NULL/",$data['flags'])) {
                    $record[]='YES';
                } else {
                    $record[]='NO';
                }
                if(preg_match("/primary_key/",$data['flags'])) {
                    $record[]='PRI';
                } else {
                    $record[]='';
                }
                $record[]='';
                $record[]='';

                if(isset($matches[4])) {
                    if($matches[4]==$data['name']) {
                        $final[]=$record;
                    }
                } else {
                    $final[]=$record;
                }
            }
            return $final;
        }

        if(preg_match("/^show tables like ([^ ]*)/i",$query,$matches)) {
            $table = substr($matches[1],1,-1);
            $return = $this->getAll("PRAGMA table_list($table)");
            return $return;
        }

        if (!is_array($params)) {
            if (is_array($fetchmode)) {
                if ($params === null) {
                    $tmp = DB_FETCHMODE_DEFAULT;
                } else {
                    $tmp = $params;
                }
                $params = $fetchmode;
                $fetchmode = $tmp;
            } elseif ($params !== null) {
                $fetchmode = $params;
                $params = array();
            }
        }

        $params = (array)$params;
        if (count($params)) {
            $res = $this->_select($query,$params,$fetchmode);
        } else {
            $res = $this->_select($query);
        }
        if (DB::isError($res)) {
            return $res;
        }

        if($fetchmode == DB_FETCHMODE_ASSOC || $fetchmode == DB_FETCHMODE_OBJECT) {
            // return associative array
            $colnames = $res['results'][0]['columns'];
            $newarray=array();
            if(isset($res['results'][0]['values'])) {
                foreach($res['results'][0]['values'] as  $key=>$row) {
                    $newrow=array();
                    foreach($row as $idx=>$val) {
                        $newrow[$colnames[$idx]]=$val;
                    }
                    $newarray[]=$newrow;
                }
            } else {
                $newarray=array();
            }
            if($fetchmode == DB_FETCHMODE_OBJECT) {
                return (object)$newarray;
            } else {
                return $newarray;
            }
        } else {
            // return array
            return $res['results'][0]['values'];
        }
    }


    // }}}
    // {{{ connect()

    /**
     * Connect to the database server, log in and open the database
     *
     * Don't call this method directly.  Use DB::connect() instead.
     *
     * PEAR DB's sqlite driver supports the following extra DSN options:
     *   + mode  The permissions for the database file, in four digit
     *            chmod octal format (eg "0600").
     *
     * Example of connecting to a database in read-only mode:
     * <code>
     * require_once 'DB.php';
     * 
     * $dsn = 'sqlite:///path/and/name/of/db/file?mode=0400';
     * $options = array(
     *     'portability' => DB_PORTABILITY_ALL,
     * );
     * 
     * $db = DB::connect($dsn, $options);
     * if (PEAR::isError($db)) {
     *     die($db->getMessage());
     * }
     * </code>
     *
     * @param array $dsn         the data source name
     * @param bool  $persistent  should the connection be persistent?
     *
     * @return int  DB_OK on success. A DB_Error object on failure.
     */
    function connect($dsn, $persistent = false)
    {

        // There is no connect method as we do not have a resource

        $this->dsn = $dsn;
        if ($dsn['dbsyntax']) {
            $this->dbsyntax = $dsn['dbsyntax'];
        }

        if (!$dsn['database']) {
            return $this->sqliteRaiseError(DB_ERROR_ACCESS_VIOLATION);
        }

        $this->_select("SELECT TIME()");
        return DB_OK;
    }

    // }}}
    // {{{ disconnect()

    /**
     * Disconnects from the database server
     *
     * @return bool  TRUE on success, FALSE on failure
     */
    function disconnect()
    {
        return 0;
    }

    // }}}
    // {{{ simpleQuery()

    /**
     * Sends a query to the database server
     *
     * NOTICE:  This method needs PHP's track_errors ini setting to be on.
     * It is automatically turned on when connecting to the database.
     * Make sure your scripts don't turn it off.
     *
     * @param string  the SQL query string
     *
     * @return mixed  + a PHP result resrouce for successful SELECT queries
     *                + the DB_OK constant for other successful queries
     *                + a DB_Error object on failure
     */
    function simpleQuery($query)
    {
        $ismanip = $this->_checkManip($query);
        $this->last_query = $query;
        $query = $this->modifyQuery($query);
        $this->query($query);
        return DB_OK;
    }

    // }}}
    // {{{ nextResult()

    /**
     * Move the internal sqlite result pointer to the next available result
     *
     * @param resource $result  the valid sqlite result resource
     *
     * @return bool  true if a result is available otherwise return false
     */
    function nextResult($result)
    {
        return false;
    }

    // }}}
    // {{{ fetchInto()

    /**
     * Places a row from the result set into the given array
     *
     * Formating of the array and the data therein are configurable.
     * See DB_result::fetchInto() for more information.
     *
     * This method is not meant to be called directly.  Use
     * DB_result::fetchInto() instead.  It can't be declared "protected"
     * because DB_result is a separate object.
     *
     * @param resource $result    the query result resource
     * @param array    $arr       the referenced array to put the data in
     * @param int      $fetchmode how the resulting array should be indexed
     * @param int      $rownum    the row number to fetch (0 = first row)
     *
     * @return mixed  DB_OK on success, NULL when the end of a result set is
     *                 reached or on failure
     *
     * @see DB_result::fetchInto()
     */
    function fetchInto($result, &$arr, $fetchmode, $rownum = null)
    {
        // TODO
        if ($rownum !== null) {
            if (!@sqlite_seek($this->result, $rownum)) {
                return null;
            }
        }
        if ($fetchmode & DB_FETCHMODE_ASSOC) {
            $arr = @sqlite_fetch_array($result, SQLITE_ASSOC);
            if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE && $arr) {
                $arr = array_change_key_case($arr, CASE_LOWER);
            }

            /* Remove extraneous " characters from the fields in the result.
             * Fixes bug #11716. */
            if (is_array($arr) && count($arr) > 0) {
                $strippedArr = array();
                foreach ($arr as $field => $value) {
                    $strippedArr[trim($field, '"')] = $value;
                }
                $arr = $strippedArr;
            }
        } else {
            $arr = @sqlite_fetch_array($result, SQLITE_NUM);
        }
        if (!$arr) {
            return null;
        }
        if ($this->options['portability'] & DB_PORTABILITY_RTRIM) {
            /*
             * Even though this DBMS already trims output, we do this because
             * a field might have intentional whitespace at the end that
             * gets removed by DB_PORTABILITY_RTRIM under another driver.
             */
            $this->_rtrimArrayValues($arr);
        }
        if ($this->options['portability'] & DB_PORTABILITY_NULL_TO_EMPTY) {
            $this->_convertNullArrayValuesToEmpty($arr);
        }
        return DB_OK;
    }

    // }}}
    // {{{ freeResult()

    /**
     * Deletes the result set and frees the memory occupied by the result set
     *
     * This method is not meant to be called directly.  Use
     * DB_result::free() instead.  It can't be declared "protected"
     * because DB_result is a separate object.
     *
     * @param resource $result  PHP's query result resource
     *
     * @return bool  TRUE on success, FALSE if $result is invalid
     *
     * @see DB_result::free()
     */
    function freeResult(&$result)
    {
        // We do not have results/resource
        return true;
    }

    // }}}
    // {{{ numCols()

    /**
     * Gets the number of columns in a result set
     *
     * This method is not meant to be called directly.  Use
     * DB_result::numCols() instead.  It can't be declared "protected"
     * because DB_result is a separate object.
     *
     * @param resource $result  PHP's query result resource
     *
     * @return int  the number of columns.  A DB_Error object on failure.
     *
     * @see DB_result::numCols()
     */
    function numCols($result)
    {
        // TODO
        $cols = @sqlite_num_fields($result);
        if (!$cols) {
            return $this->sqliteRaiseError();
        }
        return $cols;
    }

    // }}}
    // {{{ numRows()

    /**
     * Gets the number of rows in a result set
     *
     * This method is not meant to be called directly.  Use
     * DB_result::numRows() instead.  It can't be declared "protected"
     * because DB_result is a separate object.
     *
     * @param resource $result  PHP's query result resource
     *
     * @return int  the number of rows.  A DB_Error object on failure.
     *
     * @see DB_result::numRows()
     */
    function numRows($result)
    {
        // TODO
        $rows = @sqlite_num_rows($result);
        if ($rows === null) {
            return $this->sqliteRaiseError();
        }
        return $rows;
    }

    // }}}
    // {{{ affected()

    /**
     * Determines the number of rows affected by a data maniuplation query
     *
     * 0 is returned for queries that don't manipulate data.
     *
     * @return int  the number of rows.  A DB_Error object on failure.
     */
    function affectedRows()
    {
        if($this->_apcuAvailable) {
        $affected = apcu_fetch('rows_affected_'.$this->_uniqueid,$ok);
        if($ok) { return $affected; } else { return 0; }
        } else {
            return -1;
        }
    }


    // }}}
    // {{{ dropSequence()

    /**
     * Deletes a sequence
     *
     * @param string $seq_name  name of the sequence to be deleted
     *
     * @return int  DB_OK on success.  A DB_Error object on failure.
     *
     * @see DB_common::dropSequence(), DB_common::getSequenceName(),
     *      DB_sqlite::nextID(), DB_sqlite::createSequence()
     */
    function dropSequence($seq_name)
    {
        return $this->query('DROP TABLE ' . $this->getSequenceName($seq_name));
    }

    /**
     * Creates a new sequence
     *
     * @param string $seq_name  name of the new sequence
     *
     * @return int  DB_OK on success.  A DB_Error object on failure.
     *
     * @see DB_common::createSequence(), DB_common::getSequenceName(),
     *      DB_sqlite::nextID(), DB_sqlite::dropSequence()
     */
    function createSequence($seq_name)
    {
        $seqname = $this->getSequenceName($seq_name);
        $query   = 'CREATE TABLE ' . $seqname .
                   ' (id INTEGER UNSIGNED PRIMARY KEY) ';
        $result  = $this->query($query);
        if (DB::isError($result)) {
            return($result);
        }
        $query   = "CREATE TRIGGER ${seqname}_cleanup AFTER INSERT ON $seqname
                    BEGIN
                        DELETE FROM $seqname WHERE id<LAST_INSERT_ROWID();
                    END ";
        $result  = $this->query($query);
        if (DB::isError($result)) {
            return($result);
        }
    }

    // }}}
    // {{{ nextId()

    /**
     * Returns the next free id in a sequence
     *
     * @param string  $seq_name  name of the sequence
     * @param boolean $ondemand  when true, the seqence is automatically
     *                            created if it does not exist
     *
     * @return int  the next id number in the sequence.
     *               A DB_Error object on failure.
     *
     * @see DB_common::nextID(), DB_common::getSequenceName(),
     *      DB_sqlite::createSequence(), DB_sqlite::dropSequence()
     */
    function nextId($seq_name, $ondemand = true)
    {
        $seqname = $this->getSequenceName($seq_name);

        do {
            $repeat = 0;
            $this->pushErrorHandling(PEAR_ERROR_RETURN);
            $result = $this->query("INSERT INTO $seqname (id) VALUES (NULL)");
            $this->popErrorHandling();
            if ($result === DB_OK) {
                $id = @sqlite_last_insert_rowid($this->connection);
                if ($id != 0) {
                    return $id;
                }
            } elseif ($ondemand && DB::isError($result) &&
                      $result->getCode() == DB_ERROR_NOSUCHTABLE)
            {
                $result = $this->createSequence($seq_name);
                if (DB::isError($result)) {
                    return $this->raiseError($result);
                } else {
                    $repeat = 1;
                }
            }
        } while ($repeat);

        return $this->raiseError($result);
    }

    // }}}
    // {{{ getDbFileStats()

    /**
     * Get the file stats for the current database
     *
     * Possible arguments are dev, ino, mode, nlink, uid, gid, rdev, size,
     * atime, mtime, ctime, blksize, blocks or a numeric key between
     * 0 and 12.
     *
     * @param string $arg  the array key for stats()
     *
     * @return mixed  an array on an unspecified key, integer on a passed
     *                arg and false at a stats error
     */
    function getDbFileStats($arg = '')
    {
        $stats = stat($this->dsn['database']);
        if ($stats == false) {
            return false;
        }
        if (is_array($stats)) {
            if (is_numeric($arg)) {
                if (((int)$arg <= 12) & ((int)$arg >= 0)) {
                    return false;
                }
                return $stats[$arg ];
            }
            if (array_key_exists(trim($arg), $stats)) {
                return $stats[$arg ];
            }
        }
        return $stats;
    }

    // }}}
    // {{{ escapeSimple()

    /**
     * Escapes a string according to the current DBMS's standards
     *
     * In SQLite, this makes things safe for inserts/updates, but may
     * cause problems when performing text comparisons against columns
     * containing binary data. See the
     * {@link http://php.net/sqlite_escape_string PHP manual} for more info.
     *
     * @param string $str  the string to be escaped
     *
     * @return string  the escaped string
     *
     * @since Method available since Release 1.6.1
     * @see DB_common::escapeSimple()
     */
    function escapeSimple($str)
    {
        $str2 = preg_replace("/'/","''",$str);
        return $str2;
    }

    // }}}
    // {{{ modifyLimitQuery()

    /**
     * Adds LIMIT clauses to a query string according to current DBMS standards
     *
     * @param string $query   the query to modify
     * @param int    $from    the row to start to fetching (0 = the first row)
     * @param int    $count   the numbers of rows to fetch
     * @param mixed  $params  array, string or numeric data to be used in
     *                         execution of the statement.  Quantity of items
     *                         passed must match quantity of placeholders in
     *                         query:  meaning 1 placeholder for non-array
     *                         parameters or 1 placeholder per array element.
     *
     * @return string  the query string with LIMIT clauses added
     *
     * @access protected
     */
    function modifyLimitQuery($query, $from, $count, $params = array())
    {
        return "$query LIMIT $count OFFSET $from";
    }

    // }}}
    // {{{ modifyQuery()

    /**
     * Changes a query string for various DBMS specific reasons
     *
     * This little hack lets you know how many rows were deleted
     * when running a "DELETE FROM table" query.  Only implemented
     * if the DB_PORTABILITY_DELETE_COUNT portability option is on.
     *
     * @param string $query  the query string to modify
     *
     * @return string  the modified query string
     *
     * @access protected
     * @see DB_common::setOption()
     */
    function modifyQuery($query)
    {
        if ($this->options['portability'] & DB_PORTABILITY_DELETE_COUNT) {
            if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $query)) {
                $query = preg_replace('/^\s*DELETE\s+FROM\s+(\S+)\s*$/',
                                      'DELETE FROM \1 WHERE 1=1', $query);
            }
        }
        return $query;
    }

    // }}}
    // {{{ sqliteRaiseError()

    /**
     * Produces a DB_Error object regarding the current problem
     *
     * @param int $errno  if the error is being manually raised pass a
     *                     DB_ERROR* constant here.  If this isn't passed
     *                     the error information gathered from the DBMS.
     *
     * @return object  the DB_Error object
     *
     * @see DB_common::raiseError(),
     *      DB_sqlite::errorNative(), DB_sqlite::errorCode()
     */
    function sqliteRaiseError($errno = null)
    {
        // TODO
        $native = $this->errorNative();
        if ($errno === null) {
            $errno = $this->errorCode($native);
        }

        $errorcode = @sqlite_last_error($this->connection);
        $userinfo = "$errorcode ** $this->last_query";

        return $this->raiseError($errno, null, null, $userinfo, $native);
    }

    // }}}
    // {{{ errorNative()

    /**
     * Gets the DBMS' native error message produced by the last query
     *
     * {@internal This is used to retrieve more meaningfull error messages
     * because sqlite_last_error() does not provide adequate info.}}
     *
     * @return string  the DBMS' error message
     */
    function errorNative()
    {
        return $this->_lasterror;
    }

    // }}}
    // {{{ errorCode()

    /**
     * Determines PEAR::DB error code from the database's text error message
     *
     * @param string $errormsg  the error message returned from the database
     *
     * @return integer  the DB error number
     */
    function errorCode($errormsg)
    {
        static $error_regexps;
        
        // PHP 5.2+ prepends the function name to $php_errormsg, so we need
        // this hack to work around it, per bug #9599.
        $errormsg = preg_replace('/^sqlite[a-z_]+\(\): /', '', $errormsg);
        
        if (!isset($error_regexps)) {
            $error_regexps = array(
                '/^no such table:/' => DB_ERROR_NOSUCHTABLE,
                '/^no such index:/' => DB_ERROR_NOT_FOUND,
                '/^(table|index) .* already exists$/' => DB_ERROR_ALREADY_EXISTS,
                '/PRIMARY KEY must be unique/i' => DB_ERROR_CONSTRAINT,
                '/is not unique/' => DB_ERROR_CONSTRAINT,
                '/columns .* are not unique/i' => DB_ERROR_CONSTRAINT,
                '/uniqueness constraint failed/' => DB_ERROR_CONSTRAINT,
                '/may not be NULL/' => DB_ERROR_CONSTRAINT_NOT_NULL,
                '/^no such column:/' => DB_ERROR_NOSUCHFIELD,
                '/no column named/' => DB_ERROR_NOSUCHFIELD,
                '/column not present in both tables/i' => DB_ERROR_NOSUCHFIELD,
                '/^near ".*": syntax error$/' => DB_ERROR_SYNTAX,
                '/[0-9]+ values for [0-9]+ columns/i' => DB_ERROR_VALUE_COUNT_ON_ROW,
            );
        }
        foreach ($error_regexps as $regexp => $code) {
            if (preg_match($regexp, $errormsg)) {
                return $code;
            }
        }
        // Fall back to DB_ERROR if there was no mapping.
        return DB_ERROR;
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table
     *
     * @param string         $result  a string containing the name of a table
     * @param int            $mode    a valid tableInfo mode
     *
     * @return array  an associative array with the information requested.
     *                 A DB_Error object on failure.
     *
     * @see DB_common::tableInfo()
     * @since Method available since Release 1.7.0
     */
    function tableInfo($result, $mode = null)
    {
        if (is_string($result)) {
        $id = $this->getAll("PRAGMA table_info('$result')",DB_FETCHMODE_ASSOC);
        if (DB::isError($id)) {
            return $id;
        }
            $got_string = true;
        } else {
            $this->last_query = '';
            return $this->raiseError(DB_ERROR_NOT_CAPABLE, null, null, null,
                                     'This DBMS can not obtain tableInfo' .
                                     ' from result sets');
        }

        if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE) {
            $case_func = 'strtolower';
        } else {
            $case_func = 'strval';
        }

        $count = count($id);
        $res   = array();

        if ($mode) {
            $res['num_fields'] = $count;
        }

        for ($i = 0; $i < $count; $i++) {
            if (strpos($id[$i]['type'], '(') !== false) {
                $bits = explode('(', $id[$i]['type']);
                $type = $bits[0];
                $len  = rtrim($bits[1],')');
            } else {
                $type = $id[$i]['type'];
                $len  = 0;
            }

            $flags = '';
            if ($id[$i]['pk']) {
                $flags .= 'primary_key ';
                if (strtoupper($type) == 'INTEGER') {
                    $flags .= 'auto_increment ';
                }
            }
            if ($id[$i]['notnull']) {
                $flags .= 'not_null ';
            }
            if ($id[$i]['dflt_value'] !== null) {
                $flags .= 'default_' . rawurlencode($id[$i]['dflt_value']);
            }
            $flags = trim($flags);

            $res[$i] = array(
                'table' => $case_func($result),
                'name'  => $case_func($id[$i]['name']),
                'type'  => $type,
                'len'   => $len,
                'flags' => $flags,
            );

            if ($mode & DB_TABLEINFO_ORDER) {
                $res['order'][$res[$i]['name']] = $i;
            }
            if ($mode & DB_TABLEINFO_ORDERTABLE) {
                $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
            }
        }

        if(count($res)==0) { 
            return ""; 
        } else {
           return $res;
        }
    }

    // }}}
    // {{{ getSpecialQuery()

    /**
     * Obtains the query string needed for listing a given type of objects
     *
     * @param string $type  the kind of objects you want to retrieve
     * @param array  $args  SQLITE DRIVER ONLY: a private array of arguments
     *                       used by the getSpecialQuery().  Do not use
     *                       this directly.
     *
     * @return string  the SQL query string or null if the driver doesn't
     *                  support the object type requested
     *
     * @access protected
     * @see DB_common::getListOf()
     */
    function getSpecialQuery($type, $args = array())
    {
        if (!is_array($args)) {
            return $this->raiseError('no key specified', null, null, null,
                                     'Argument has to be an array.');
        }

        switch ($type) {
            case 'master':
                return 'SELECT * FROM sqlite_master;';
            case 'tables':
                return "SELECT name FROM sqlite_master WHERE type='table' "
                       . 'UNION ALL SELECT name FROM sqlite_temp_master '
                       . "WHERE type='table' ORDER BY name;";
            case 'schema':
                return 'SELECT sql FROM (SELECT * FROM sqlite_master '
                       . 'UNION ALL SELECT * FROM sqlite_temp_master) '
                       . "WHERE type!='meta' "
                       . 'ORDER BY tbl_name, type DESC, name;';
            case 'schemax':
            case 'schema_x':
                /*
                 * Use like:
                 * $res = $db->query($db->getSpecialQuery('schema_x',
                 *                   array('table' => 'table3')));
                 */
                return 'SELECT sql FROM (SELECT * FROM sqlite_master '
                       . 'UNION ALL SELECT * FROM sqlite_temp_master) '
                       . "WHERE tbl_name LIKE '{$args['table']}' "
                       . "AND type!='meta' "
                       . 'ORDER BY type DESC, name;';
            case 'alter':
                /*
                 * SQLite does not support ALTER TABLE; this is a helper query
                 * to handle this. 'table' represents the table name, 'rows'
                 * the news rows to create, 'save' the row(s) to keep _with_
                 * the data.
                 *
                 * Use like:
                 * $args = array(
                 *     'table' => $table,
                 *     'rows'  => "id INTEGER PRIMARY KEY, firstname TEXT, surname TEXT, datetime TEXT",
                 *     'save'  => "NULL, titel, content, datetime"
                 * );
                 * $res = $db->query( $db->getSpecialQuery('alter', $args));
                 */
                $rows = strtr($args['rows'], $this->keywords);

                $q = array(
                    'BEGIN TRANSACTION',
                    "CREATE TEMPORARY TABLE {$args['table']}_backup ({$args['rows']})",
                    "INSERT INTO {$args['table']}_backup SELECT {$args['save']} FROM {$args['table']}",
                    "DROP TABLE {$args['table']}",
                    "CREATE TABLE {$args['table']} ({$args['rows']})",
                    "INSERT INTO {$args['table']} SELECT {$rows} FROM {$args['table']}_backup",
                    "DROP TABLE {$args['table']}_backup",
                    'COMMIT',
                );

                /*
                 * This is a dirty hack, since the above query will not get
                 * executed with a single query call so here the query method
                 * will be called directly and return a select instead.
                 */
                foreach ($q as $query) {
                    $this->query($query);
                }
                return "SELECT * FROM {$args['table']};";
            default:
                return null;
        }
    }

    function &query($query, $params = array()) {
        $comando = "execute";
        $query = preg_replace("/INSERT IGNORE/i","INSERT OR IGNORE",$query);
        $query = preg_replace("/AUTO_INCREMENT/","AUTOINCREMENT",$query);
        $query = preg_replace("/^desc /","describe ",$query);
        $is_schema=0;
        if(preg_match("/^describe (.*)/i",$query,$matches)==1) {
            $query = "SELECT * FROM ".$matches[1]." LIMIT 1";
            $is_schema=1;
            $comando = "query";
        }
        if(preg_match("/^show columns from (.*)/i",$query,$matches)==1) {
            $query = "SELECT * FROM ".$matches[1]." LIMIT 1";
            $is_schema=2;
            $comando = "query";
        }

        $data = array();
        if(count($params)>0){
            $dat = array();
            $dat[] = $query;
            foreach($params as $val) {
                $dat[] = $val;
            }
            $data[]=$dat;
        } else {
            if(!is_array($query)) {
                $data[] = $query;
            } else {
                $data = $query;
            }
        }
        $data_string = json_encode($data);

        $headers=array('Content-Type: application/json','Content-Length: ' . strlen($data_string));
        $ch = curl_init($this->dsn['database']."/db/$comando");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);
        curl_close($ch);
    
        if($is_schema==0) {
            $dat = json_decode($return,1);
            if(isset($dat['results'][0]['error'])) {
                $e = new DB_Error($dat['results'][0]['error']);
                return $e;
            }
            if($this->_apcuAvailable) {
                if(isset($dat['results'][0]['rows_affected'])) {
                    apcu_store('rows_affected_'.$this->_uniqueid,$dat['results'][0]['rows_affected'],2);
                }
            }
            return $dat;
        } else {
            $dat = json_decode($return,1);
            $result = array();
            foreach($dat['results'][0]['columns'] as $idx=>$fieldname) {
                if($is_schema==1) {
                    $result[$fieldname]=array($dat['results'][0]['types'][$idx]);
                } else {
                    $result[]=array($fieldname,$dat['results'][0]['types'][$idx]);
                }
            }
            return $result;
        }
    }

    private function _select($query,$params=array()) {

        $query = preg_replace("/^desc /","describe ",$query);
        if(preg_match("/^describe (.*)/i",$query,$matches)==1) {
            $query = "SELECT * FROM ".$matches[1]." LIMIT 1";
            $is_schema=1;
            $comando = "query";
        }

        $data = array();
        if(count($params)>0){
            $dat = array();
            $dat[] = $query;
            foreach($params as $val) {
                $dat[] = $val;
            }
            $data[]=$dat;
        } else {
            if(!is_array($query)) {
                $data[] = $query;
            } else {
                $data = $query;
            }
        }

        $data_string = json_encode($data);
        $headers=array('Content-Type: application/json','Content-Length: ' . strlen($data_string));
        $ch = curl_init($this->dsn['database']."/db/query");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);
        curl_close($ch);
        $dat = json_decode($return,1);
        if(isset($dat['results'][0]['error'])) {
            $e = new DB_Error($dat['results'][0]['error']);
            return $e;
        }
        return $dat;
    }

    // }}}
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */

?>
