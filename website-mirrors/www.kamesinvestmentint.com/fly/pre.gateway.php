<?php                                    

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: PRE.GATEWAY.PHP
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

$data = $plan_id .'|'. $item_id .'|'. $account_id .'|'. $crypted_price .'|'. $callback_class .'|'. $callback_method .'|'. $cancel_url .'|'. $success_url .'|'. RL_LANG_CODE;
$item = urlencode(base64_encode($data));

$reefless->loadClass('SMSCoin', null, 'smsCoin');
$TransID = $rlSMSCoin->generateNumber(8);

$s_sign = $GLOBALS['config']['smscoin_s_purse']."::".$TransID."::".$price."::".$GLOBALS['config']['smscoin_s_clear_amount']."::".$GLOBALS['config']['smscoin_s_description']."::".$GLOBALS['config']['smscoin_keyword'];
$s_sign = md5($s_sign);

if(empty($GLOBALS['config']['smscoin_language']))
{
	$payment_url = 'http://service.smscoin.com/bank/';
}
else
{
	$payment_url = 'http://service.smscoin.com/language/'.$GLOBALS['config']['smscoin_language'].'/bank/';
}
?>

<form action="<?php echo $payment_url; ?>" name="payment_form" method="post">
	<input name="s_purse" type="hidden" value="<?php echo $GLOBALS['config']['smscoin_s_purse']; ?>" />
	<input name="s_order_id" type="hidden" value="<?php echo $TransID; ?>" />
	<input name="s_amount" type="hidden" value="<?php echo $price; ?>" />
	<input name="s_clear_amount" type="hidden" value="<?php echo $GLOBALS['config']['smscoin_s_clear_amount']; ?>" />
	<input name="s_description" type="hidden" value="<?php echo $GLOBALS['config']['smscoin_s_description']; ?>" />
	<input name="s_sign" type="hidden" value="<?php echo $s_sign; ?>" />
	<input name="item" type="hidden" value="<?php echo $item; ?>" />
</form>