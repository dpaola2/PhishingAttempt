<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: APEXTACCOUNTSUPDATE.PHP
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

global $field, $value, $id, $reefless, $rlVBulletin, $rlDb;

$id = (int)$id;
if ( ($field == 'Status' && $value == 'active') && $id )
{
	$sql  = "SELECT `Username`, `Password_tmp`, `Mail` FROM `". RL_DBPREFIX ."accounts` ";
	$sql .= "WHERE `ID` = {$id} LIMIT 1";
	$account = $rlDb -> getRow($sql);

	if ( !empty($account['Password_tmp']) )
	{
		$reefless -> loadClass('VBulletin', null, 'vbulletin');
		if ( false === $rlVBulletin -> vbUserExists($account['Username']) )
		{
			$rlVBulletin -> createAccount($account['Username'], $account['Password_tmp'], $account['Mail']);
		}
	}
}