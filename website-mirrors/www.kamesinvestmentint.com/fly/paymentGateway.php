<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: PAYMENTGATEWAY.PHP
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

global $rlSmarty, $page_info;

require_once(RL_PLUGINS .'smsCoin'. RL_DS .'countries.php');

if($GLOBALS['config']['smscoin_module'])   
{
	$key = strtolower(str_replace(" ", "_", $_SESSION['GEOLocationData']->Country_name));
	$country_price = (float)$sc_countries[$key];
	$GLOBALS['rlSmarty']->assign('country_price', $country_price);

	if($page_info['Controller'] == 'add_listing')
	{
		global $plan_info;
		
		$GLOBALS['rlSmarty']->assign('plan_price', $plan_info['Price']);
	}
    $GLOBALS['rlSmarty']->display(RL_ROOT . 'plugins' . RL_DS . 'smsCoin' . RL_DS . 'smscoin_payment_block.tpl');
}