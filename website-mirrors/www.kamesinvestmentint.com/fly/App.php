<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: APP.PHP
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

if ( strtolower($_SERVER['REQUEST_METHOD']) === 'post')
{
	require_once('..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'config.inc.php');
	require_once(RL_PLUGINS .'iFlynaxConnect'. RL_DS .'config.inc.php');
	require_once(RL_IPHONE_CONTROLLERS .'control.inc.php');

	// load system configurations
	$config = $rlConfig -> allConfig();
	$GLOBALS['config'] = &$config;

	// define global info
	$globalInfo = array();

	// check availability
	if ( $GLOBALS['aHooks']['iFlynaxConnect'] )
	{
		// response to ping request
		if ( $_POST['controller'] == 'ping' )
		{
			$iPhone -> printAsText('true');
		}

		// simulate post from QUERY_STRING
		if ( !empty($_FILES) && false !== strpos($_SERVER['QUERY_STRING'], 'synch_code') && empty($_POST))
		{
			foreach(explode('&', $_SERVER['QUERY_STRING']) as $query)
			{
				list($key, $value) = explode('=', $query, 2);
				$_POST[$key] = $value;
			}
		}

		// define lang code
		define('RL_LANG_CODE', $iPhone -> getSiteLanguage());

		// load all fronEnd phrases
		$lang = $rlLang -> getLangBySide('frontEnd', RL_LANG_CODE);
		$GLOBALS['lang'] = &$lang;

		// check security token
		if ( empty($config['iFlynaxConnect_synch_code']) || $_POST['synch_code'] != $config['iFlynaxConnect_synch_code'] )
		{
			$error = array('errorMessage' => $lang['iFlynaxConnect_error_synch_code']);
			$iPhone -> printAsXml($error);
		}
	}
	else
	{
		$error = array('errorMessage' => 'iFlynax failed to connect to website');
		$iPhone -> printAsXml($error);
	}

	define('RL_DATE_FORMAT', $rlDb -> getOne('Date_format', "`Code` = '". RL_LANG_CODE ."'", 'languages'));
	define('RL_TPL_BASE', RL_URL_HOME .'templates/'. $config['template'] .'/');

	// load system libs
	require_once(RL_LIBS .'system.lib.php');

	// login attempts control
	if ( version_compare($config['rl_version'], '4.0.1', '>') )
	{
		$reefless -> loginAttempt();
	}

	// simulate user logged
	if ( isset($_POST['accountToken']) && !empty($_POST['accountToken']) )
	{
		// check token
		if ( !$rlAccount -> isLogin() )
		{
			$token = $rlValid -> xSql($_POST['accountToken']);
			if ( false === $iPhone -> loginWithToken($token) )
			{
				$globalInfo['reLogin'] = true;
			}
		}
	}

	// check user login
	if ( $rlAccount -> isLogin() )
	{
		$account_info = $_SESSION['account'];
		define('IS_LOGIN', true);
	}

	// account abilities handler
	$deny_pages = array();
	$reefless -> loadClass('ListingTypes', null, false, true);
	foreach( $rlListingTypes -> types as $listingType )
	{
		if ( !in_array($listingType['Key'], $_SESSION['abilities']) )
		{
			array_push($deny_pages, 'my_'. $listingType['Key']);
		}

		// count admin only types
		$admin_only_types += $listingType['Admin_only'] ? 1 : 0;
	}
	unset($listingType);

	if ( empty($_SESSION['abilities']) || empty($rlListingTypes -> types) || $admin_only_types == count($rlListingTypes -> types) )
	{
		array_push($deny_pages, 'add_listing');
		array_push($deny_pages, 'payment_history');
		array_push($deny_pages, 'my_packages');
	}

	$controller = $_POST['controller'];
	if ( file_exists(RL_IPHONE_CONTROLLERS . $controller .'.inc.php') )
	{
		require_once(RL_IPHONE_CONTROLLERS . $controller .'.inc.php');
	}
}