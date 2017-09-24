<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// database.lib.php
// Database abstraction class, provides the IDatabase interface
// MySQL database connection

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Global timing variables
$gDBcount = 0;
$gDBtime = 0;

// Debug only
if (!isset($UNB['ShowSql'])) $UNB['ShowSql'] = false;

// Handles the database connection. This is the database abstraction layer. You
// can replace this file by a copy of it to support other database systems than
// MySQL. This class uses the 'mysql' PHP extension.
//
class IDatabase
{

// -------------------- Public variables --------------------

var $server = '';      // port number may be added automatically!
var $user = '';
var $password = '';
var $dbname = '';      // database name
var $tblprefix = '';   // entire string, for tables like "bb1_Users", this must be "bb1_"

var $version = 0;      // MySQL server version
var $useUTF8 = true;   // Use UTF-8 character set if available

// -------------------- Private variables --------------------

/** @var mysqli|false */
var $conn = false;
/** @var mysqli_result|false */
var $result = false;
var $tablestatus = false;

var $q_table = '';
var $q_where = '';
var $q_order = '';
var $q_limit = '';
var $q_group = '';
var $q_having = '';

// -------------------- Statistics --------------------

// Reset internal statistics counters
//
function ResetStat()
{
	global $gDBcount, $gDBtime;

	$gDBcount = 0;
	$gDBtime = 0;
	return true;
}

// Get the total number of database queries so far
//
function GetCount()
{
	global $gDBcount;

	return $gDBcount;
}

// Get the total amount of time spent in queries so far
//
// in factor = (int) Factor to multiply the number of microseconds with. 1000 will return milliseconds
// in decimals = (int) Round to this number of decimals
//
// returns (float) time with specified precision
//
function GetTime($factor = 1000, $decimals = 1)
{
	global $gDBcount, $gDBtime;

	// Clean parameters
	$factor = intval($factor);
	$decimals = intval($decimals);

	if ($decimals == -1)
		return $gDBtime * $factor;
	else
		return round($gDBtime * $factor, $decimals);
}

// -------------------- DB server methods --------------------

// Establish connection to DB server
//
function Open()
{
	if ($this->server == '')
	{
		die('<b>UNB Error:</b> No server set for database connection.<br />');
	}
	if ($this->user == '')
	{
		die('<b>UNB Error:</b> No username set for database connection.<br />');
	}
	if ($this->dbname == '')
	{
		die('<b>UNB Error:</b> No database name set for database connection.<br />');
	}

	if (!function_exists('mysqli_connect'))
	{
		die('<b>UNB Error:</b> MySQLi PHP extension is not available. Check the <a href="http://newsboard.unclassified.de/docs/install#req">requirements</a>.<br />');
	}

	$this->conn = mysqli_connect($this->server, $this->user, $this->password);   // port 3306
	if ($this->conn == false)
	{
		UnbErrorLog('Cannot connect to database: ' . mysqli_connect_error());
		die('<b>UNB Error:</b> Cannot connect to database. Check the error log for details.');
	}

	if (!mysqli_select_db($this->conn, $this->dbname))
	{
		UnbErrorLog('Cannot switch to my database: ' . mysqli_error($this->conn));
		die('<b>UNB Error:</b> Cannot switch to my database. Check the error log for details.');
	}

	$version = $this->FastQuery1st('', 'VERSION()');
	$va = explode('-', $version);
	$va = explode('.', $va[0]);
	$this->version = (intval($va[0]) << 8 | intval($va[1])) << 8 | intval($va[2]);

	if ($this->version >= 0x040100)
	{
		if ($this->useUTF8)
		{
			$this->Exec('SET CHARACTER SET utf8');
			$this->Exec('SET NAMES utf8');
		}
		else
		{
			$this->Exec('SET CHARACTER SET latin1');
			$this->Exec('SET NAMES latin1');
		}
		$this->Exec('SET SESSION sql_mode=\'MYSQL40\'');
	}

	return true;
}

// Terminate connection to DB server
//
function Close()
{
	if (!$this->IsConnected()) return false;
	mysqli_close($this->conn);
	return true;
}

// Forget login account data, for security reasons
//
function Forget()
{
	$this->user = '';
	$this->password = '';
	$this->dbname = '';
}

// Check if we're connected
//
function IsConnected()
{
	return ($this->conn != false);
}

// Tell the last error
//
function LastError()
{
	return mysqli_error($this->conn);
}

// -------------------- Raw DB access via SQL --------------------

// Run an SQL query on the database
//
// in sql = (string) SQL query code
//
// returns (bool) success
//
function Exec($sql)
{
	global $gDBcount, $gDBtime, $UNB;

	if (!$this->conn) return false;

	$measure_time = rc('foot_db_time') ? true : false;
	if ($measure_time === true)
	{
		$start = debugGetMicrotime();
	}

	$this->result = mysqli_query($this->conn, $sql);

	if ($measure_time === true)
	{
		$end = debugGetMicrotime();
		$gDBtime += $end - $start;
		$gDBcount++;
	}

	// Log any query error to the error log for later recovery
	$error = mysqli_error($this->conn);
	if ($error) UnbErrorLog('Database query error: ' . $error . "\nSQL: " . $sql);

	if ($UNB['ShowSql'])
	{
		echo '<small><b>SQL:</b> ' . htmlspecialchars($sql);
		if ($measure_time === true) echo ' <b>(' . round(($end - $start) * 1000) . ')</b>';
		if ($error) echo ' - <b>Error:</b> ' . htmlspecialchars($error);
		echo '</small><br />';
	}

	if ($this->result === false) return false;

	return true;
}

// -------------------- Table definition manipulation --------------------

// Remove a table from our database
//
function RemoveTable($name)
{
	// Clean parameters
	$name = trim(strval($name));
	if ($name == '') return false;

	$name = $this->tblprefix . $name;
	return $this->Exec('DROP TABLE IF EXISTS `' . $name . '`');
}

// Rename a table
//
function RenameTable($name, $newname)
{
	// Clean parameters
	$name = trim(strval($name));
	$newname = trim(strval($newname));
	if ($name == '') return false;
	if ($newname == '') return false;

	$name = $this->tblprefix . $name;
	$newname = $this->tblprefix . $newname;
	return $this->Exec('RENAME TABLE `' . $name . '` TO `' . $newname . '`');
}

// Create a new table in our database
//
// in fields = (string) columns definition: "<name> <type>[, <name> <type>...]"
//                      (see AddField(), MySQL Doc: 6.5.3)
//
function CreateTable($name, $fields, $remove = false)
{
	// Clean parameters
	$name = trim(strval($name));
	$fields = trim(strval($fields));
	if ($name == '') return false;
	if ($fields == '') return false;
	if ($remove) $this->RemoveTable($name);

	if ($this->version >= 0x040100)
	{
		if ($this->useUTF8)
		{
			$extra = 'ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci';
		}
		else
		{
			$extra = 'ENGINE = MYISAM CHARACTER SET latin1';
		}
	}

	$name = $this->tblprefix . $name;
	return $this->Exec('CREATE TABLE `' . $name . '` (' . $fields . ')' . $extra);
}

// Add a field (column) to a table
//
// in type = (string) column data type: "bool", "int[(length)] [unsigned]",
//                    "varchar(length) [binary]", "text" (MySQL Doc: 6.2)
// in where = position to insert the new column
//            (int) 0: before first column
//            (string) col name: insert new column after column named $where
//            (default) add column at the end
//
function AddField($table, $name, $type, $where = '')
{
	// Clean parameters
	$table = trim(strval($table));
	$name = trim(strval($name));
	$type = trim(strval($type));
	if ($table == '') return false;
	if ($name == '') return false;
	if ($type == '') return false;

	if ($where === 0) $where = ' FIRST';
	elseif ($where != '') $where = ' AFTER ' . $where;

	$table = $this->tblprefix . $table;
	return $this->Exec('ALTER TABLE `' . $table . '` ADD `' . $name . '` ' . $type . $where);
}

// Alter a field (column) name or type in a table
//
// in table = (string) table name
// in oldname = (string) current column name
// in name = (string) new column name
// in type = (string) new column type: "bool", "int[(length)] [unsigned]",
//                    "varchar(length) [binary]", "text" (MySQL Doc: 6.2)
//
function ChangeField($table, $oldname, $name, $type)
{
	// Clean parameters
	$table = trim(strval($table));
	$oldname = trim(strval($oldname));
	$name = trim(strval($name));
	$type = trim(strval($type));
	if ($table == '') return false;
	if ($oldname == '') return false;
	if ($name == '') return false;
	if ($type == '') return false;

	$table = $this->tblprefix . $table;
	return $this->Exec('ALTER TABLE `' . $table . '` CHANGE `' . $oldname . '` `' . $name . '` ' . $type);
}

// Remove a field (column) from a table
//
function RemoveField($table, $name)
{
	// Clean parameters
	$table = trim(strval($table));
	$name = trim(strval($name));
	if ($table == '') return false;
	if ($name == '') return false;

	$table = $this->tblprefix . $table;
	return $this->Exec('ALTER TABLE `' . $table . '` DROP `' . $name . '`');
}

// Add an index to a table
//
// in type = (string) index type: "index", "primary key", "unique", "fulltext" (MySQL Doc: 6.5.4)
// in name = (string) names of fields (columns) to create index over
//                    (comma-separated, also a single name possible)
//
function AddIndex($table, $type, $name)
{
	// Clean parameters
	$table = trim(strval($table));
	$type = trim(strval($type));
	$name = trim(strval($name));
	if ($table == '') return false;
	if ($type == '') return false;
	if ($name == '') return false;

	$table = $this->tblprefix . $table;
	if (!is_array($name)) $name = array($name);
	$fields = '';
	foreach ($name as $n)
	{
		$fields .= ($fields ? ', ' : '') . '`' . $n . '`';
	}
	return $this->Exec('ALTER TABLE `' . $table . '` ADD ' . $type . ' (' . $fields . ')');
}

// Remove an index from a table
//
// in name = (string) name of the index to remove
//
function RemoveIndex($table, $name = '')
{
	// Clean parameters
	$table = trim(strval($table));
	$name = trim(strval($name));
	if ($table == '') return false;

	if (!$name) $name = 'PRIMARY KEY';
	else        $name = 'INDEX `' . $name . '`';

	$table = $this->tblprefix . $table;
	return $this->Exec('ALTER TABLE `' . $table . '` DROP ' . $name);
}

// Get a list of all columns in a table
//
// in table = (string) table name
//
// returns a 2-dimensional array(Field => array(Field, Type, Null, Key, Default, Extra))
//
function ListTableCols($table)
{
	// Clean parameters
	$table = trim(strval($table));
	if ($table == '') return false;

	$table = $this->tblprefix . $table;
	if (!$this->Exec('DESCRIBE `' . $table . '`')) return false;
	$record = $this->GetRecord();
	$arr = array();
	do
	{
		$arr[$record['Field']] = $record;
	}
	while ($record = $this->GetRecord());
	return $arr;
}

// -------------------- Row access methods -- reading --------------------

// Reset all parameters for a new query
//
function NewQuery()
{
	$this->q_table = '';
	$this->q_where = '';
	$this->q_order = '';
	$this->q_limit = '';
	$this->q_group = '';
	$this->q_having = '';
	return true;
}

// Set new table name for queries (FROM section)
//
// in name = (string) single table name
//           (array) array of multiple table joins: (MySQL Doc: 7.4.1.1)
//                   array((string) join type (empty for first table!),
//                         (string) table name,
//                         (string) alias,
//                         (string) condition (empty for first table!))
//
function SetTable($name)
{
	$this->q_table = $name;
	return true;
}

// Set new criteria for queries (WHERE section)
//
function SetWhere($name)
{
	$this->q_where = $name;
	return true;
}

// Set new order for queries (ORDER BY section)
//
function SetOrder($name)
{
	$this->q_order = $name;
	return true;
}

// Set new limit for queries (LIMIT section)
//
function SetLimit($name)
{
	$this->q_limit = $name;
	return true;
}

// Set new group for queries (GROUP BY section)
//
function SetGroup($name)
{
	$this->q_group = $name;
	return true;
}

// Set new having for queries (HAVING section)
//
function SetHaving($name)
{
	$this->q_having = $name;
	return true;
}

// Perform query on given fields
//
// in fields = (string) list of column names. "*" selects all columns. Alias definitions are allowed
//
// returns (bool) success
//
function QueryFields($fields = '*')
{
	// Clean parameters
	$fields = trim(strval($fields));
	if ($fields == '') return false;
	#if ($this->q_table == '') return false;

	if (is_array($this->q_table))
	{
		$table = '`' . $this->tblprefix . $this->q_table[0][1] . '`';
		if ($this->q_table[0][2])
			$table .= ' AS ' . $this->q_table[0][2];

		for ($i = 1; $i < sizeof($this->q_table); $i++)
		{
			$table .= ' ' . $this->q_table[$i][0] . ' JOIN `' .
				$this->tblprefix . $this->q_table[$i][1] . '`';
			if ($this->q_table[$i][2])
				$table .= ' AS ' . $this->q_table[$i][2];
			if ($this->q_table[$i][3])
				$table .= ' ON ' . $this->q_table[$i][3];
		}
	}
	elseif ($this->q_table)
	{
		$table = '`' . $this->tblprefix . $this->q_table . '`';
	}

	// build SQL query
	$q = 'SELECT ' . $fields;
	if ($table != '') $q .= ' FROM ' . $table;
	if ($this->q_where != '') $q .= ' WHERE ' . $this->q_where;
	if ($this->q_group != '') $q .= ' GROUP BY ' . $this->q_group;
	if ($this->q_having != '') $q .= ' HAVING ' . $this->q_having;
	if ($this->q_order != '') $q .= ' ORDER BY ' . $this->q_order;
	if ($this->q_limit != '') $q .= ' LIMIT ' . $this->q_limit;

	return $this->Exec($q);
}

// Get next recordset of previous query
//
// returns (array) recordset as array(column => value)
//
function GetRecord()
{
	return mysqli_fetch_array($this->result);

	/*
	global $gDBtime, $UNB;

	if ($this->result === false) return false;
	if (rc('foot_db_time'))
	{
		$start = debugGetMicrotime();
	}

	$record = mysql_fetch_array($this->result);

	if (rc('foot_db_time'))
	{
		$end = debugGetMicrotime();
		$gDBtime += $end - $start;
	}
	#if ($UNB['ShowSql']) echo "<small><b>GetRecord:</b> (" . round(($end - $start) * 1000) . ")</small><br />";
	return $record;
	*/
}

// Perform query and get first record in one call
// Further calls of GetRecord are possible
// Parameters are the same as with the above Set*() function calls
//
function FastQuery($table, $fields = '*', $where = '', $order = '', $limit = '', $group = '', $having = '')
{
	$this->NewQuery();
	$this->SetTable($table);
	$this->SetWhere($where);
	$this->SetOrder($order);
	$this->SetLimit($limit);
	$this->SetGroup($group);
	$this->SetHaving($having);
	if ($this->QueryFields($fields) === false) return false;
	return mysqli_fetch_array($this->result);
	#return $this->GetRecord();
}

// Same as FastQuery but return the first column's value
// Further calls of GetRecord are possible
//
function FastQuery1st($table, $fields = '*', $where = '', $order = '', $limit = '', $group = '', $having = '')
{
	$record = $this->FastQuery($table, $fields, $where, $order, $limit, $group, $having);
	if ($record === false) return false;
	else return $record[0];
}

// Same as FastQuery1st, but copies all records in an array
//
// in key = (string) record's column name to use for array's key. values in this column should be unique
//          (int) record's column index to use for array's key. values in this column should be unique
//
function FastQuery1stArray($table, $fields = '*', $where = '', $order = '', $limit = '', $group = '', $key = false, $having = '')
{
	// Clean parameters
	if ($key !== false) $key = trim(strval($key));
	if ($key === '') $key = false;

	$record = $this->FastQuery($table, $fields, $where, $order, $limit, $group, $having);
	if ($record === false) return false;
	$arr = array();
	do
	{
		if ($key === false) array_push($arr, $record[0]);
		else                $arr[$record[$key]] = $record[0];
	}
	while ($record = mysqli_fetch_array($this->result));
	#while ($record = $this->GetRecord());

	return $arr;
}

// Same as FastQuery1stArray, but copies records ENTIRELY into the array
//
// in key = (string) record's column name to use for array's key. values in this column should be unique
//          (int) record's column index to use for array's key. values in this column should be unique
//
function FastQueryArray($table, $fields = '*', $where = '', $order = '', $limit = '', $group = '', $key = false, $having = '')
{
	// Clean parameters
	if ($key !== false) $key = trim(strval($key));
	if ($key === '') $key = false;

	$record = $this->FastQuery($table, $fields, $where, $order, $limit, $group, $having);
	if ($record === false) return false;
	$arr = array();
	do
	{
		if ($key === false) array_push($arr, $record);
		else                $arr[$record[$key]] = $record;
	}
	while ($record = mysqli_fetch_array($this->result));
	#while ($record = $this->GetRecord());

	return $arr;
}

// -------------------- Row access methods -- writing --------------------

// Add a new record
//
// in fields = (array) associative array ("field" => "value"...)
//             (string) string for SET part of SQL query.
//                      Caller must pay attention to `-quote field names and UnbDbQuote() values as neccessary!
//
function AddRecord($fields, $table = '')
{
	if ($table == '') $table = $this->q_table;
	if ($table == '') return false;

	// build SQL query
	$table = $this->tblprefix . $table;
	$q = 'INSERT INTO `' . $table . '` SET ';

	if (is_array($fields))
	{
		$pos = 0;
		reset($fields);
		while (list ($key, $value) = each ($fields))
		{
			#$num = is_numeric($value);
			$num = false;   // let the DBMS decide whether it is numeric or not...

			if ($pos >= 1) $q .= ', ';

			$q .= '`' . $key . '`=';
			$q .= ($num ? '' : '"');
			$q .= UnbDbEncode($value);
			$q .= ($num ? '' : '"');

			$pos++;
		}
	}
	elseif (is_string($fields))
	{
		$q .= $fields;
	}
	else
		return false;   // input type not supported

	return $this->Exec($q);
}

// Change a record
//
// in fields = (array) associative array ("field" => "value"...)
//             (string) string for SET part of SQL query.
//                      Caller must pay attention to `-quote field names and UnbDbQuote() values as neccessary!
// in where = (string) condition of recordsets to update
//
function ChangeRecord($fields, $where = '', $table = '')
{
	if ($table == '') $table = $this->q_table;
	if ($table == '') return false;

	// build SQL query
	$table = $this->tblprefix . $table;
	$q = 'UPDATE `' . $table . '` SET ';

	if (is_array($fields))
	{
		$n = 0;
		foreach ($fields as $key => $value)
		{
			if (is_array($value))
			{
				if ($value[0])   // need quoting
				{
					$q .= ($n++ ? ', ' : '') . '`' . $key . '`="' . UnbDbEncode($value[1]) . '"';
				}
				else   // don't quote (advanced expression)
				{
					$q .= ($n++ ? ', ' : '') . '`' . $key . '`=' . $value[1];
				}
			}
			else
			{
				$q .= ($n++ ? ', ' : '') . '`' . $key . '`="' . UnbDbEncode($value) . '"';
			}
		}
	}
	elseif (is_string($fields))
	{
		$q .= $fields;
	}
	else
		return false;   // input type not supported

	if ($where != '') $q .= ' WHERE ' . $where;

	return $this->Exec($q);
}

// Delete a record
//
// in where = (string) condition of recordsets to update
//
function RemoveRecord($where = '', $table = '', $limit = '')
{
	if ($table == '') $table = $this->q_table;
	if ($table == '') return false;

	// build SQL query
	$table = $this->tblprefix . $table;
	$q = 'DELETE FROM `' . $table . '`';

	if ($where != '') $q .= ' WHERE ' . $where;
	if ($limit != '') $q .= ' LIMIT ' . $limit;

	return $this->Exec($q);
}

// Get the number of rows affected by the last operation
//
function AffectedRows()
{
	return mysqli_affected_rows($this->conn);
}

// Get database table size, depends on table_status()
//
// in table = (string) table name
// in ts = (array) returned from prevoius table_status() call
//
function GetTableSize($table)
{
	if ($this->tablestatus === false)
	{
		$this->Exec('SHOW TABLE STATUS');
		$this->tablestatus = array();
		while ($record = $this->GetRecord())
		{
			array_push($this->tablestatus, $record);
		}
	}

	foreach ($this->tablestatus as $a)
	{
		if (!strcasecmp($a['Name'], $this->tblprefix . $table)) return $a['Data_length'] + $a['Index_length'];
	}
	return false;
}

}  // class

// Encode values for use in SQL queries
//
// Masks the follwing characters: \ ' " \n \r \t
//
function UnbDbEncode($str, $forLIKE = false)
{
	// Clean parameters
	$str = strval($str);

	$str = str_replace('\\', '\\\\', $str);
	$str = str_replace('\'', '\\\'', $str);
	#$str = str_replace('\'', '\'\'', $str);   // ANSI SQL syntax, not supported by all MySQLs
	$str = str_replace('"', '\\"', $str);
	$str = str_replace("\n", '\\n', $str);
	$str = str_replace("\r", '\\r', $str);
	$str = str_replace("\t", '\\t', $str);

	if ($forLIKE)
	{
		$str = str_replace('%', '\\%', $str);
		$str = str_replace('_', '\\_', $str);
	}
	return $str;
}

?>