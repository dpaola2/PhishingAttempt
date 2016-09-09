<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: REGISTERSUCCESS.PHP
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

global $profile_data, $config, $account_types;

if ( $config['vbulletin_use_module'] )
{
	$allow_vb_create = true;
	if ( version_compare($config['rl_version'], '4.0.1', '>') )
	{
		$type_id = $GLOBALS['rlDb'] -> getOne('ID', "`Key` = '{$config['vbulletin_flynax_account_type']}'", 'account_types');

		// $account_types[$type_id]['Admin_confirmation']
		$allow_vb_create = $account_types[$type_id]['Email_confirmation'] ? false : true;
	}
	else
	{
		$allow_vb_create = $config['account_email_confirmation'] ? false : true;
	}

	if ( $allow_vb_create )
	{
		$GLOBALS['reefless'] -> loadClass('VBulletin', null, 'vbulletin');
		$GLOBALS['rlVBulletin'] -> createAccount($profile_data['username'], $profile_data['password'], $profile_data['mail']);
	}
}