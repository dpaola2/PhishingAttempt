<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: ACCOUNT_MENU.INC.PHP
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

$rlDb -> setTable('pages');
$menus = $rlDb -> fetch(array('Key', 'Menus', 'Deny'), array('Status' => 'active'), "AND `Plugin` = '' AND FIND_IN_SET('2', `Menus` ) > 0 ORDER BY `Position`");
$data = array();

if ( !empty($menus) )
{
	$menus = $rlLang -> replaceLangKeys($menus, 'pages', array('name'));
	foreach( $menus as $key => $item )
	{
		if ( (!in_array($_SESSION['type_id'], explode(',', $item['Deny'])) || !$_SESSION['type_id'] ) && (!in_array( $item['Key'], $deny_pages) || !$deny_pages) )
		{
			$skipPages = array('my_profile', 'my_favorites', 'my_packages', 'my_messages');
			if ( (false !== strpos($item['Key'], 'add_') || false !== strpos($item['Key'], 'my_') ) && !in_array($item['Key'], $skipPages) )
			{
				array_push($data, array(
						'key' => $item['Key'],
						'name' => $item['name'],
						'controller' => (false !== strpos($item['Key'], 'add_')) ? 1 : 2 // 1 = add_listing, 2 = my_listings
					)
				);
			}
		}
	}
	unset($menus);
}
$iPhone -> printAsXml($data);