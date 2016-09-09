<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: {version}
 *	LICENSE: RETAIL - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: xxxxxxxxxxxx.com
 *	FILE: RLDB.CLASS.PHP
 *
 *	The software is a commercial product delivered under single, non-exclusive, 
 *	non-transferable license for one domain or IP address. Therefore distribution, 
 *	sale or transfer of the file in whole or in part without permission of Flynax 
 *	respective owners is considered to be illegal and breach of Flynax License End 
 *	User Agreement. 
 *
 *	You are not allowed to remove this information from the file without permission
 *	of Flynax respective owners.
 *
 *	Flynax Classifieds Software 2014 |  All copyrights reserved. 
 *
 *	http://www.flynax.com/
 *
 ******************************************************************************/

class rlDb
{
	/**
	* current table name
	*
	* @var string
	**/
	var $tName = null;

	/**
	* current mysql API version
	*
	* @var string
	**/
	var $mysqlVer = null;

	/**
	* mysql calculate found rows
	*
	* @var bool
	**/
	var $calcRows = false;

	/**
	* sql query start time
	*
	* @var bool
	**/
	var $start = 0;

	/**
	* rows mapping
	*
	* Example: 
	*/
	var $outputRowsMap = false;

	/**
	* open mysql connection and select database
	* 
	* @param uses define variables
	**/
	function connect($host, $port = 3306, $user, $pass, $base_name)
	{
		if ( !mysql_connect($host .":". $port, $user, $pass) )
		{
			die("Could not connect to MySQL server");
		}

		$db = mysql_select_db($base_name);

		if ( !$db )
		{
			die("Unknown MySQL database");
		}

		$this -> mysqlVer = version_compare("4.1", mysql_get_server_info(), "<=") ? "5" : "4";

		if ( $this -> mysqlVer == 5 )
		{
			if ( function_exists('mysql_set_charset') )
			{
				mysql_set_charset('utf8');
			}
			else
			{
				$this -> query("SET NAMES `utf8`");
			}
		}

		$this -> query("SET sql_mode = ''");
	}

	/**
	* set current table name
	*
	* @param string $nama - tabel name
	**/
	function setTable( $name )
	{
		$this -> tName = $name;
	}

	/**
	* reset table name
	**/
	function resetTable()
	{
		$this -> tName = null;
	}

	/**
	* run mySQL query
	*
	* @param string $sql - mySQL query string
	*
	* @return data as associative array
	**/
	function query( $sql = false )
	{
		$this -> calcTime('start');

		$res = mysql_query($sql);

		if ( !$res )
		{
			$this -> error($sql);
		}

		$this -> calcTime('end', $sql);

		return $res;
	}

	/**
	* get all data from the table
	*
	* @param string $sql - mySQL query string
	* @param mixed $outputMap - 'index_key' || array('index_key', 'value_row_key')
	* Example:
	*     'Key': return: ['key1' => all_selected_rows], etc...
	*     array('Key', 'Path'):  return: ['key1' => 'Path'], etc...
	*
	* @return data as associative array
	**/
	function getAll( $sql, $outputMap = false )
	{
		$res = $this -> query($sql);

		// mapping
		$map_index = $map_value = false;
		if ( $outputMap )
		{
			if ( is_string($outputMap) )
			{
				$map_index = trim($outputMap);
			}
			else if ( is_array($outputMap) && 2 === count($outputMap) )
			{
				$map_index = trim($outputMap[0]);
				$map_value = trim($outputMap[1]);
			}
		}

		// Convert to array
		$ret = array();
		while ( $row = mysql_fetch_assoc($res) )
		{
			// Add to array
			if ( $map_index && array_key_exists($map_index, $row) )
			{
				$ret[$row[$map_index]] = ($map_value && array_key_exists($map_value, $row)) ? $row[$map_value] : $row;
			}
			else
			{
				array_push($ret, $row);
				unset($row);
			}
		}

		return $ret;
	}

	/**
	* get one field of result row
	*
	* @param string $field - field name
	* @param string $where - select condition
	* @param string $table - table name
	* @param string $prefix - table prefix
	*
	* @return data as associative array
	**/
	function getOne( $field = false, $where = null, $table = null, $prefix = false )
	{
		if ( $table == null )
		{
			if ( $this -> tName != null )
			{
				$table = $this -> tName;
			}
			else
			{
				return $this -> tableNoSel();
			}
		}

		if ( !$field || !$where )
		{
			return false;
		}

		$prefix = $prefix ? $prefix : RL_DBPREFIX;
		$sql = "SELECT `{$field}` FROM `{$prefix}{$table}` WHERE {$where} LIMIT 1";
		$res = $this -> query($sql);

		$ret = mysql_result($res, 0, $field);

		return $ret;
	}

	/**
	* get one row from the table
	*
	* @param string $sql - mySQL query string
	* @param string $field - return only it
	*
	* @return data as associative array / string
	**/
	function getRow( $sql = false, $field = false )
	{
		if ( !(bool)preg_match('/LIMIT\s+[0-9]+/', $sql) && !is_numeric(strpos($sql, 'SHOW')) )
		{
			$sql .= ' LIMIT 1';
		}

		$res = $this -> query($sql);
		$row = mysql_fetch_assoc($res);

		if ( $field !== false )
		{
			return $row[$field];
		}
		return $row;
	}

	/**
	* select data by criteria from the table
	*
	* @param array $fields - fields names array: array( 'field1', 'field2', 'field3')
	* @param array $where  - array of selected criterias:
	* 			array(
	*			  'field name' => 'value',	 
	*			  'field name' => 'value'	 
	*				  )
	* @param string $options  - options string: "ORDER BY `field` "
	* @param int|array $limit - limit parametrs: int (rows number) or array( 'from', 'rows' )
	* @param string $table    - table name
	* @param string $action   - selected type: all table content or one row
	*
	* @return data as associative array
	**/
	function fetch( $fields = '*', $where = null, $options = null, $limit = null, $table = null, $action = 'all' )
	{
		if ( $table == null )
		{
			if ( $this -> tName != null )
			{
				$table = $this -> tName;
			}
			else
			{
				return $this -> tableNoSel();
			}
		}

		$query = "SELECT ";

		if ( $this -> calcRows )
		{
			$query .= "SQL_CALC_FOUND_ROWS ";
		}

		if ( is_array($fields) )
		{
			foreach ($fields as $sel_field)
			{
				$query .= "`{$sel_field}`,";
			}
			$query = substr( $query, 0, -1 );
		}
		else
		{
			$query .= " * ";
		}

		$query .= " FROM `". RL_DBPREFIX . $table ."` ";

		if ( is_array($where) )
		{
			$query .= " WHERE ";

			foreach ( $where as $key => $value )
			{
				$GLOBALS['rlValid'] -> sql($value);
				$query .= " (`{$key}` = '{$value}') AND";
			}
			$query = substr( $query, 0, -3 );
		}

		if ( $options != null )
		{
			$query .= " " .$options. " ";
		}

		if ( is_array($limit) )
		{
			$qStart = (int)$limit[0];
			$qLimit = (int)$limit[1];
			$query .= " LIMIT {$qStart}, {$qLimit} ";
		}
		else
		{
			if ( $action == 'row' && empty($limit) )
			{
				$limit = 1;
			}

			if ( !empty($limit) )
			{
				$query .= " LIMIT {$limit} ";
			}
		}

		if ( $action == 'row')
		{
			$output = $this -> getRow($query);
		}
		else
		{
			$output = $this -> getAll($query, $this -> outputRowsMap);
			$this -> outputRowsMap = false;
		}

		if ( $this -> calcRows )
		{
			$calc = $this -> getRow("SELECT FOUND_ROWS() AS `calc`");
			$this -> foundRows = $calc['calc'];
		}

		return $output;
	}

	/**
	* print error statement
	*
	* @param string $query - error qeury
	**/
	function error( $query = false )
	{
		$error_line = $error_file = $error_function = $error_class = 'Undefined';
		$errors = debug_backtrace();
		$index = 2;

		foreach ($errors as $error_index => $error) {
			if ( !$error['class'] )
				$index = $error_index - 1;
			
			break;
		}

		$error = $errors[$index];
		
		$GLOBALS['rlDebug'] -> logger(mysql_error(), $error['file'], $error['line'], 'Mysql Error', false);
		
		if ( !RL_DB_DEBUG && !RL_DEBUG)
		{
			die('MYSQL ERROR');
		}
		
		if ( isset($_POST['xjxfun']) || $_GET['q'] == 'ext' || defined('ANDROID_APP') )
		{
			echo 'MYSQL ERROR'. PHP_EOL;
			echo 'Error: '. mysql_error() . PHP_EOL;
			echo 'Query: '. $query . PHP_EOL;
			if ( $error['function'] )
			{
				echo 'Function: '. $error['function'] . PHP_EOL;
			}
			if ( $error['class'] )
			{
				echo 'Class: '. $error['class'] . PHP_EOL;
			}
			if ( $error['file'] )
			{
				echo 'File: '. $error['file'] .' (line# '. $error['line'] .')'. PHP_EOL;
			}
		}
		else
		{
			echo '<table style="width: 100%;font-family: Arial;font-size: 14px;">';
			echo '<tr><td colspan="2" style="color: red;font-weight: bold;">MYSQL ERROR</td></tr>';
			echo '<tr><td style="width: 90px;">Error:</td><td>'. mysql_error() .'</td></tr>';
			echo '<tr><td>Query:</td><td>'. $query .'</td></tr>';
			if ( $error['function'] )
			{
				echo '<tr><td>Function:</td><td>'. $error['function'] .'</td></tr>';
			}
			if ( $error['class'] )
			{
				echo '<tr><td>Class:</td><td>'. $error['class'] .'</td></tr>';
			}
			if ( $error['file'] )
			{
				echo '<tr><td>File:</td><td>'. $error['file'] .' (line# '. $error['line'] .')</td></tr>';
			}
			echo '</table>';
		}

		exit;
	}

	/**
	* calculate query time
	*
	* @param string $mode - start or end of the query
	**/
	function calcTime( $mode = 'start', $sql = false )
	{
		if ( !RL_DB_DEBUG )
		{
			return false;
		}

		if ( $mode == 'start' )
		{
			$time = microtime();
			$time = explode(" ", $time);
			$time = $time[1] + $time[0];
			$this -> start = $time;
		}
		else
		{
			if ( !$_SESSION['sql_debug_time'] )
				$_SESSION['sql_debug_time'] = 0;

			$time = microtime();
			$time = explode(" ", $time);
			$time = $time[1] + $time[0];
			$finish = $time;
			$totaltime = ($finish - $this -> start);
			$_SESSION['sql_debug_time'] += $totaltime;
			printf ("The query took %f seconds to load.<br />", $totaltime);
			$backtrace = debug_backtrace();
			$level = count($backtrace);
			$log = $level > 1 ? $backtrace[$level-3] : $backtrace[0];
			echo $log['file'] ."({$log['line']}) / function: {$log['function']}<br />";
			echo $sql.'<br /><br />';
		}
	}

	/**
	* display no table selected error
	*
	* @todo show error, write logs
	**/
	function tableNoSel()
	{
		RlDebug::logger( "SQL query can't be run, it isn't table name selected", null, null, 'Warning' );
		return 'Table not selected, see error log';
	}
}
