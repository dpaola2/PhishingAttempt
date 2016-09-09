<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: PROFILE_UPDATEPASSWORD_COMPLETE.PHP
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

		//TODO:

		if ( $connectIfNecessary )
		{
			$dbMaster = $vbulletin -> config['MasterServer'];
			$vbulletin -> db -> connect($vbulletin -> config['Database']['dbname'], $dbMaster['servername'], $dbMaster['port'], $dbMaster['username'], $dbMaster['password']);
		}
	}
}