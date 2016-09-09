<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: TPLFOOTER.PHP
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

global $rlDb, $rlSmarty, $pages, $lang, $config, $account_info;

$sql = "SELECT * FROM `".RL_DBPREFIX."invoices` WHERE `Account_ID` = '{$account_info['ID']}' AND `pStatus` = 'unpaid' ORDER BY `Date` DESC LIMIT 5";
$invoices = $rlDb -> getAll( $sql );

$calc = count($invoices);

$link = SEO_BASE;
$link .= $config['mod_rewrite'] ? $pages['invoices'] .'/'. $invoices[0]['Txn_ID'] .'.html' : 'index.php?page=' . $pages['invoices'] . '&item=' . $invoices[0]['ID'] ;

$invoice_link = '<a href="'.$link.'">'.$lang['here'].'</a>';

$GLOBALS['rlSmarty'] -> assign('unpaid_invoices', $calc);
$GLOBALS['rlSmarty'] -> assign('invoice_link', $invoice_link);
$GLOBALS['rlSmarty'] -> display(RL_ROOT . 'plugins' . RL_DS . 'invoices' . RL_DS . 'tplFooter.tpl');