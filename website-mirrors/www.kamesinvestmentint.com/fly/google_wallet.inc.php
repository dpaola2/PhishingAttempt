<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: GOOGLE_WALLET.INC.PHP
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

$reefless->loadClass( 'JWT', null, 'googleWallet' );
$reefless->loadClass( 'GoogleWallet', null, 'googleWallet' );
$reefless->loadClass( 'Actions' );

/* payment info */
$payment = $_SESSION['complete_payment'];

$price = $payment['plan_info']['Price'];
$plan_id = $payment['plan_info']['ID'];
$item_id = $payment['item_id'];
$item_name = $payment['item_name'];
$callback_class = $payment['callback']['class'];
$callback_method = $payment['callback']['method'];
$callback_plugin = $payment['callback']['plugin'];
$cancel_url = $payment['callback']['cancel_url'];
$success_url = $payment['callback']['success_url'];

/* get listing info */
if ( !$callback_plugin )
{
	$listing = $rlListings -> getShortDetails( $item_id, $plan_info = true );
	$rlSmarty -> assign_by_ref( 'listing', $listing );
}

if(!$_SESSION['complete_payment']['txn_id'])
{
	$txn_id = $reefless -> generateHash( 8, 'upper' );
	$_SESSION['complete_payment']['txn_id'] = $txn_id;
}
else
{
	$txn_id = $_SESSION['complete_payment']['txn_id'];
}

$data = $plan_id .'|'. $item_id .'|'. (int)$account_info['ID'] .'|'. $price .'|'. $callback_class .'|'. $callback_method .'|'. RL_LANG_CODE .'|'. $callback_plugin . '|' . $txn_id;
$data = base64_encode( $data );

$request = array(
		'name' => $item_name,
		'description' => $item_name,
		'price' => (float)$price,
		'currencyCode' => $GLOBALS['config']['google_wallet_currency'],
		'sellerData' => $data
	);

$jwt_token = $rlGoogleWallet -> generateToken( $request );

$txn_info = array(
		'Txn_ID' => $txn_id,
		'total' => $price,
		'jwt' => $jwt_token
	);

$rlSmarty -> assign_by_ref( 'txn_info', $txn_info );

$rlXajax -> registerFunction( array( 'googleSuccessHandler', $rlGoogleWallet, 'ajaxGoogleSuccessHandler' ) );
$rlXajax -> registerFunction( array( 'googleFailureHandler', $rlGoogleWallet, 'ajaxGoogleFailureHandler' ) );