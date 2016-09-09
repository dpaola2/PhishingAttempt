<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: {version}
 *	LICENSE: RETAIL - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: xxxxxxxxxxxx.com
 *	FILE: HOOK.INC.PHP
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

global $page_info, $lang, $bread_crumbs, $rlSmarty, $account_menu, $mobile_account_menu_deny, $config;

/* override necessary configs */
$config['listing_feilds_position'] = 1;
$config['img_crop_interface'] = 0;

/* rename login page after user login */
if ( $page_info['Key'] == 'login' && defined('IS_LOGIN') && IS_LOGIN )
{
	$page_info['name'] = $lang['blocks+name+account_area'];
	$bread_crumbs[1]['name'] = $lang['blocks+name+account_area'];
	$bread_crumbs[1]['title'] = $lang['blocks+name+account_area'];
}

/* add bread crubms item */
$account_area_pages = array(
				'my_profile', 
				'payment', 
				'add_listing', 
				'my_messages', 
				'my_listings', 
				'my_packages', 
				'payment_history');
if ( in_array($page_info['Key'], $account_area_pages) )
{
	$bread_crumbs = array_reverse($bread_crumbs);
	$last = array_pop($bread_crumbs);
	$bread_crumbs[] = array(
		'name' => $lang['blocks+name+account_area'],
		'title' => $lang['blocks+name+account_area'],
		'path' => 'login'
	);
	
	$bread_crumbs[] = $last;
	$bread_crumbs = array_reverse($bread_crumbs);
	unset($last);
}

/* remove unnecessary items from the account menu */
$mobile_account_menu_deny = array('remote_adverts', 'saved_search', 'invoices');

if ( defined('IS_LOGIN') && IS_LOGIN )
{
	foreach ($account_menu as $account_menu_key => $account_menu_item)
	{
		if ( in_array($account_menu_item['Key'], $mobile_account_menu_deny) )
		{
			unset($account_menu[$account_menu_key]);
		}
	}
}