<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: LOGIN_VERIFY_SUCCESS.PHP
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

if ( VB_AREA != 'Flynax' || (defined('FL_AUTO_LOGIN') && FL_AUTO_LOGIN === true) )
{
	if ( !in_array($vbulletin -> GPC['logintype'], array('cplogin', 'modcplogin')) || (defined('FL_AUTO_LOGIN') && FL_AUTO_LOGIN === true) )
	{
		$configLocation = FLYNAX_ROOT .'includes/config.inc.php';
		if ( file_exists($configLocation) )
		{
			require_once($configLocation);

			$connectIfNecessary = ($vbulletin -> config['Database']['dbname'] != RL_DBNAME && !defined('FL_AUTO_LOGIN'));
			if ( $connectIfNecessary )
			{
				$vbulletin -> db -> connect(RL_DBNAME, RL_DBHOST, RL_DBPORT, RL_DBUSER, RL_DBPASS);
			}

			// get account information
			$sql = "SELECT `T1`.*, `T2`.`Abilities`, `T2`.`ID` AS `Type_ID`, `T2`.`Own_location` ";
			$sql .= "FROM `". RL_DBPREFIX ."accounts` AS `T1` ";
			$sql .= "LEFT JOIN `". RL_DBPREFIX ."account_types` AS `T2` ON `T1`.`Type` = `T2`.`Key` ";
			$sql .= "WHERE `T1`.`Username` = '{$username}' AND `T1`.`Status` <> 'trash'";
			$query = $vbulletin -> db -> query_read_slave($sql);
			$account = $vbulletin -> db -> fetch_array($query);

			if ( !empty($account) )
			{
				session_start();

				// check abilities
				$abilities = explode(',', $account['Abilities']);
				$abilities = empty($abilities[0]) ? false : $abilities;

				// do not use this data in future please
				$_SESSION['id'] = $account['ID'];
				$_SESSION['username'] = $account['Username'];
				$_SESSION['password'] = md5( $account['Password'] );
				$_SESSION['type'] = $account['Type'];
				$_SESSION['type_id'] = $account['Type_ID'];
				$_SESSION['abilities'] = $abilities;

				unset($account['Confirm_code']);
				$account['Password'] = md5($account['Password']);
				$account['Full_name'] = $account['First_name'] || $account['Last_name'] ? $account['First_name'] .' '. $account['Last_name'] : $account['Username'];

				// use this only
				$account['Abilities'] = $abilities;
				$_SESSION['account'] = $account;
			}

			if ( $connectIfNecessary )
			{
				$dbMaster = $vbulletin -> config['MasterServer'];
				$vbulletin -> db -> connect($vbulletin -> config['Database']['dbname'], $dbMaster['servername'], $dbMaster['port'], $dbMaster['username'], $dbMaster['password']);
			}
		}
	}
}