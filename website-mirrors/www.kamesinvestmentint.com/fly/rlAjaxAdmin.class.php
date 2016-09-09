<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: {version}
 *	LICENSE: RETAIL - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: xxxxxxxxxxxx.com
 *	FILE: RLAJAXADMIN.CLASS.PHP
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

class rlAjaxAdmin extends reefless
{
	/**
	* @var language class object
	**/
	var $rlLang;
	
	/**
	* @var validator class object
	**/
	var $rlValid;
	
	/**
	* @var configurations class object
	**/
	var $rlConfig;
	
	/**
	* @var administrator controller class object
	**/
	var $rlAdmin;
	
	/**
	* class constructor
	**/
	function rlAjaxAdmin()
	{
		global $rlLang, $rlValid, $rlConfig, $rlAdmin;
		
		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
		$this -> rlConfig = & $rlConfig;
		$this -> rlAdmin = & $rlAdmin;
	}
	
	/**
	* check admin panel logining
	*
	* @package ajax
	*
	* @param mixed $user - admin username
	* @param MD5 $pass - admin user password in HEX format
	* @param varchar $lang - language inerface
	*
	**/
	function ajaxLogIn( $user = null, $pass = null, $language = null )
	{
		global $_response, $config, $lang, $rlActions, $reefless;
		
		/* login attempts control - error and exit */
		if ( $reefless -> attemptsLeft <= 0 && $config['security_login_attempt_admin_module'] )
		{
			$msg = str_replace('{period}', '<b>'. $config['security_login_attempt_admin_period'] .'</b>', $lang['login_attempt_error']);
			$_response -> script("
				$('#logo').next().fadeOut('normal', function(){
					$(this).remove();
					var msg = '<div class=\"error hide\"><div class=\"inner\"><div class=\"icon\"></div>{$msg}</div></div>';
					$('#logo').after(msg).next().fadeIn();
				});
			");
			
			return $_response;
			exit;
		}
		
		$_response -> setCharacterEncoding('UTF-8');

		$this -> rlValid -> sql($user);
		$pass = md5($pass);
		$user_info = $this -> fetch('*', array('User' => $user, 'Pass' => $pass, 'Status' => 'active'), null, null, 'admins', 'row');

		/* login attempts control - save attempts */
		if ( $config['security_login_attempt_admin_module'] )
		{
			$insert = array(
				'IP' => $_SERVER['REMOTE_ADDR'],
				'Date' => 'NOW()',
				'Status' => !empty($user_info) ? 'success' : 'fail',
				'Interface' => 'admin',
				'Username' => $user
			);
			
			$rlActions -> insertOne($insert, 'login_attempts');
		}

		if ( !empty($user_info) )
		{
			$this -> rlAdmin -> LogIn($user_info);

			$query_string = $_SESSION['query_string'] ? '?' . $_SESSION['query_string'] : '';
			$pos = strpos($_SESSION['query_string'], 'session_expired');
			
			if ( $pos !== false )
			{
				$query_string = '?'. substr($_SESSION['query_string'], 0, $pos);
			}
			
			$query_string = $query_string ? $query_string .'&language=' .$language : '?language=' .$language;
			$_response -> redirect( RL_URL_HOME . ADMIN . '/index.php' . $query_string );
		}
		else
		{
			//set message
			$message = $lang['rl_logging_error'];
			
			/* login attempts control - show warning */
			if ( $config['security_login_attempt_admin_module'] )
			{
				if ( $reefless -> attempts > 0 )
				{
					$message .= '<br />' . $reefless -> attemptsMessage;
				}
			}
			$_response -> script( "fail_alert('#login_notify', '{$message}')" );
			
			//hide loading
			$_response -> script( "$('#login_button').val('{$lang['login']}')" );
		}
		
		return $_response;
	}
	
	/**
	* administrator log out
	*
	* @package ajax
	* 
	**/
	function ajaxLogOut( $user = null, $pass = null, $lang = null )
	{
		global $_response;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		$this -> rlAdmin -> LogOut( $user_info );
		$_response -> redirect( RL_URL_HOME . ADMIN . '/' );

		return $_response;
	}
	
	
}