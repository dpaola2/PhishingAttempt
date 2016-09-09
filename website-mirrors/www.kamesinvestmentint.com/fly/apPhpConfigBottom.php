<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: APPHPCONFIGBOTTOM.PHP
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

global $reefless, $rlDb, $lang, $configs;

$groupID = (int)$rlDb -> getOne('ID', "`Key` = 'vbulletin_config' AND `Plugin` = 'vbulletin'", 'config_groups');
if ( !empty( $configs[$groupID] ) )
{
	foreach( $configs[$groupID] as $key => $entry )
	{
		if ( $entry['Key'] == 'vbulletin_destination' )
		{
			$destinations[0] = array('ID' => 'inside', 'name' => 'Flynax directory');
			$destinations[1] = array('ID' => 'outsite', 'name' => 'Forum directory');
			$destinations[2] = array('ID' => 'parallel', 'name' => 'The same level as Flynax');
			$destinations[3] = array('ID' => 'root', 'name' => 'Set a Root path');
			$configs[$groupID][$key]['Values'] = $destinations;
		}
		else if ( $entry['Key'] == 'vbulletin_flynax_account_type' )
		{
			$accountTypes = array();
			$tmpTypes = $rlDb -> getAll("SELECT `Key` FROM `". RL_DBPREFIX ."account_types` WHERE `Status` = 'active' ORDER BY `Position`");
			foreach( $tmpTypes as $tKey => $tEntry )
			{
				array_push( $accountTypes, array( 'ID' => $tEntry['Key'], 'name' => $lang["account_types+name+{$tEntry['Key']}"] ) );
			}
			unset( $tmpTypes );

			$configs[$groupID][$key]['Values'] = $accountTypes;
		}
		else if ( $entry['Key'] == 'vbulletin_user_group' )
		{
			$reefless -> loadClass('VBulletin', null, 'vbulletin');
			$configs[$groupID][$key]['Values'] = $GLOBALS['rlVBulletin'] -> fetchUserGroups();
		}
	}
}