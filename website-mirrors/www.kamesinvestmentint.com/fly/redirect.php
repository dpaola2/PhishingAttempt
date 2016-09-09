<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: REDIRECT.PHP
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

$file = fopen( 'response.redirect.log', 'a' );

if ( $file )
{                      
	$line = "\n\n" . date( 'Y.m.d H:i:s' ) . ":\n";
    fwrite( $file, $line );

	foreach( $_REQUEST as $p_key => $p_val )
	{                 
		$line = "{$_SERVER['REQUEST_METHOD']} {$p_key} => {$p_val}\n";
    	fwrite( $file, $line );
	}

	fclose( $file );
}

require_once( '../../../includes/config.inc.php' );

/* system controller */
require_once( RL_INC . 'control.inc.php' );

$reefless -> loadClass( 'Cache' );

/* load system configurations */
$config = $rlConfig -> allConfig();

$lang_code = $_GET['lang'];

define( 'RL_LANG_CODE', $lang_code );

$reefless -> loadClass( 'PagSeguro', null, 'pagSeguro' );

$seo_base = RL_URL_HOME;
$seo_base .= $lang_code == $config['lang'] ? '' : $lang_code . '/';

$lang = $rlLang -> getLangBySide( 'frontEnd', RL_LANG_CODE );
$GLOBALS['lang'] = $lang;

$TransactionID = $_GET['transaction_id'];

$transaction = $rlPagSeguro -> getTransaction( $TransactionID );

if ( $transaction )
{
	$txn_id = $transaction -> getReference();
	$status = $transaction -> getStatus() -> getTypeFromValue();

	$items = $rlPagSeguro -> getPaymentDetails( $txn_id, true );

	if( $items )
	{
		$cancel_url = $items[6];
		$success_url = $items[7];

		/* paid */
		if ( $status == 'PAID' || $status == 'AVAILABLE' )
		{
			$reefless -> redirect( false, str_replace( "&amp;", "&", $success_url ) );
			exit;
		}
		/* waiting */
		elseif( $status == 'WAITING_PAYMENT' || $status == 'IN_ANALYSIS' )
		{
			$redirect = RL_URL_HOME;
			$redirect .= $config['mod_rewrite'] ? $pages['pagseguro'] . '.html' : '?page=' . $pages['pagseguro'];

			$reefless -> redirect( false, $redirect );
			exit;
		}
		/* canceled */
		else
		{
			$reefless -> redirect( false, str_replace( "&amp;", "&", $cancel_url ) );
			exit;
		}
	}
}

$reefless -> redirect( false, RL_URL_HOME );
exit;