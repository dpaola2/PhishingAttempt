<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: {version}
 *	LICENSE: RETAIL - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: xxxxxxxxxxxx.com
 *	FILE: RLDEBUG.CLASS.PHP
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

class rlDebug extends reefless
{
	/**
	* debug class constructor
	**/
	function rlDebug()
	{
		if ( RL_DEBUG === true )
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			set_error_handler(array($this, 'errorHandler'), E_ALL);
		}
		else 
		{
			error_reporting(E_ERROR);
			ini_set('display_errors', 0);
			set_error_handler(array($this, 'errorHandler'), E_ALL);
		}
		
		register_shutdown_function(array($this, 'fatalErrorHandler'));
		
		if ( RL_DB_DEBUG )
		{
			unset($_SESSION['sql_debug_time']);
		}
		
		if ( !function_exists('file_put_contents') )
		{
			function file_put_contents($filename, $data) {
				$f = @fopen($filename, 'w');
				if (!$f) {
					return false;
				} else {
					$bytes = fwrite($f, $data);
					fclose($f);
					return $bytes;
				}
			}
		}
	}
	
	/**
	* fatal error handler
	**/
	function fatalErrorHandler() {
		$error = error_get_last();
		
		if ( !$error )
			return;
		
		$error_file = $error['file'];
		$error_line = $error['line'];
		$exit = false;
		
		$error_type = 'Fatal Error';
		
		switch ($error['type']) {
			case E_PARSE:
				$error_type = 'Parse Error';
				
				break;
				
			case E_STRICT:
				$exit = true;
				$error_type = 'Strict Suggestion';
				
				break;

			case 8192;
				$exit = true;
				$error_type = 'Depricated run-time notices';
				
				break;

			default:
				$exit = true;
				break;
		}
		
		if ( $exit )
			return;
		
		if ( RL_DEBUG !== true ) {
			echo 'Fatal error occurred, please look into the error logs or contact <a href="https://support.flynax.com/tickets/index.php?_m=tickets&_a=submit">Flynax Support</a>.<br />';
		}
		
		/* save log */
		if ( $error ) {
			$this -> logger($error['message'], $error_file, $error_line, $error_type, false);
		}
	}
	
	/**
	* error handler (logger), display and log the errors
	*
	* @param standard errors parameters
	**/
	function errorHandler($errno, $errstr, $errfile, $errline)
	{
		/* if notices ocured then ignore */
		// 8192 - E_DEPRECATED
		// 16384 - E_USER_DEPRECATED
		if ( in_array($errno, array(E_NOTICE, E_USER_NOTICE, E_WARNING, E_STRICT, E_USER_WARNING, 8192, 16384)) )
			return true;

		switch ($errno) {
			case E_PARSE:
				$error_type = "Parse error";
			
				break;
				
			default:
				$error_type = "System error";
			
				break;
		}

		file_put_contents( RL_TMP . 'errorLog/errors.log', $error_type. ": " .$errstr. " on line# " .$errline. " (file: " .$errfile. ")". PHP_EOL , FILE_APPEND );

		echo "<span style='font-family: tahoma; font-size: 12px;'>";
		echo "<h3>{$error_type} occurred</h2> <b>$errstr</b><br />";
		echo "line# <font color='green'><b>$errline</b></font><br />";
		echo "file: <font color='green'><b>$errfile</b></font><br />";
		echo "PHP version: " . PHP_VERSION . " <br /></span>";

		return true;
	}
	
	/**
	* save system errors / warnings
	*
	* @param string $errstr - error message
	* @param string $errfile - file
	* @param string $errline - error line
	* @param string $errorType - error type
	*
	* @todo write the errors
	**/
	function logger($errstr, $errfile = __FILE__, $errline = __LINE__, $errorType = 'Warning', $trace = true)
	{
		/* override error file and line in case of proper debug backtrace */
		$error = debug_backtrace();
		if ( $error && $trace ) {
			$errfile = $error[0]['file'];
			$errline = $error[0]['line'];
			$errorType = 'DEBUG';
		}

		$message = date('d M (h:i:s)') .' | '. $errorType .": ". $errstr ." on line# ". $errline ." (file: " .$errfile. ")". PHP_EOL;
		file_put_contents(RL_TMP . 'errorLog/errors.log', $message, FILE_APPEND);
	}
}
