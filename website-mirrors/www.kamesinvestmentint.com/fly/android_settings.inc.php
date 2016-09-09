<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: ANDROID_SETTINGS.INC.PHP
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

if ( $_POST['submit'] ) {
	$post_config = $_POST['config'];
	
	$update = array();
	
	foreach ($post_config as $key => $value)
	{
		if ($value['d_type'] == 'int')
		{
			$value['value'] = (int)$value['value'];
		}
		
		$rlValid -> sql($value['value']);
		
		$row['where']['Key'] = $key;
		$row['fields']['Default'] = $value['value'];
		array_push($update, $row);
	}
	
	$reefless -> loadClass('Actions');
	
	if ( $rlActions -> update($update, 'config') )
	{
		$reefless -> loadClass('Notice');
		
		$aUrl = array('controller' => $controller);
		
		$rlNotice -> saveNotice($lang['config_saved']);
		$reefless -> redirect($aUrl);
	}
}

$group_id = $rlDb -> getOne('ID', "`Key` = 'androidConnect'", 'config_groups');

/* get all configs */
$configsLsit = $rlDb -> fetch('*', array('Group_ID' => $group_id), "ORDER BY `Position`", null, 'config');
$configsLsit = $rlLang -> replaceLangKeys( $configsLsit, 'config', array( 'name', 'des' ), RL_LANG_CODE, 'admin' );
$rlAdmin -> mixSpecialConfigs($configsLsit);

$rlSmarty -> assign_by_ref('configs', $configsLsit);