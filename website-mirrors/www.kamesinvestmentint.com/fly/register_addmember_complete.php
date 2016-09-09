<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: REGISTER_ADDMEMBER_COMPLETE.PHP
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

if ( VB_AREA != 'Flynax' )
{
	$configLocation = FLYNAX_ROOT .'includes/config.inc.php';
	if ( file_exists($configLocation) )
	{
		require_once($configLocation);

		$connectIfNecessary = ($vbulletin -> config['Database']['dbname'] != RL_DBNAME);
		if ( $connectIfNecessary )
		{
			$vbulletin -> db -> connect(RL_DBNAME, RL_DBHOST, RL_DBPORT, RL_DBUSER, RL_DBPASS);
		}

		$sql = "SELECT `Default` FROM `". RL_DBPREFIX ."config` WHERE `Key` = 'vbulletin_flynax_account_type'";
		$accountType = $vbulletin -> db -> query_first($sql);
		$accountType = $accountType['Default'];

		$username = stripslashes($vbulletin -> GPC['username']);
		$password = $vbulletin -> GPC['password_md5'];
		$email = $vbulletin -> GPC['email'];

		$ownAddress = preg_replace("/[^a-z0-9]+/i", '-', $username);
		$ownAddress = preg_replace('/\-+/', '-', $ownAddress);
		$ownAddress = strtolower($ownAddress);
		$ownAddress = trim($ownAddress, '-');
		$ownAddress = trim($ownAddress, '/');
		$ownAddress = trim($ownAddress);

		$insert  = "INSERT INTO `". RL_DBPREFIX ."accounts` ( `Type`, `Username`, `Own_address`, `Password`, `Mail`, `Date` ) ";
		$insert .= "VALUES ( '{$accountType}', '{$username}', '{$ownAddress}', '{$password}', '{$email}', NOW() )";
		$vbulletin -> db -> query_write($insert);

		// auto login
		if ( !$vbulletin -> options['moderatenewmembers'] )
		{
			define('FL_AUTO_LOGIN', true);
			require_once(RL_PLUGINS .'vbulletin'. RL_DS .'vb_hooks'. RL_DS .'login_verify_success.php');
		}

		if ( $connectIfNecessary )
		{
			$dbMaster = $vbulletin -> config['MasterServer'];
			$vbulletin -> db -> connect($vbulletin -> config['Database']['dbname'], $dbMaster['servername'], $dbMaster['port'], $dbMaster['username'], $dbMaster['password']);
		}
	}
}