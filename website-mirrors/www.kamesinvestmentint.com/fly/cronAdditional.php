<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: CRONADDITIONAL.PHP
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

global $rlDb, $config;

if ( $config['paygc_period'] > 0 )
{
	$hours = (int)$config['paygc_period'] * 30.5 * 24; // 30.5 average for 30 and 31
	$sql  = "SELECT `ID`, `Total_credits`, IF(`paygc_pay_date` = '0', '0', IF(TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(`paygc_pay_date`), NOW()) > {$hours}, '1', '0')) `expired` "; // above 30 min
	$sql .= "FROM `". RL_DBPREFIX ."accounts` WHERE `Status` = 'active'";
	$accounts = $rlDb -> getAll($sql);

	foreach( $accounts as $key => $account )
	{
		if ( $account['expired'] )
		{
			$sql  = "UPDATE `". RL_DBPREFIX ."accounts` SET `Total_credits` = '0', `paygc_pay_date` = '0' ";
			$sql .= "WHERE `{$account['ID']}` LIMIT 1";
			$rlDb -> query($sql);
		}
	}
}