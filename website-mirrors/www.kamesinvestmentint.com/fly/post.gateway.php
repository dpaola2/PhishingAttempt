<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: POST.GATEWAY.PHP
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

$file = fopen('error.log', 'a');

if($file)
{                      
	$line = "\n\n".date('Y.m.d H:i:s').":\n";
    fwrite($file, $line);

	foreach($_POST as $p_key => $p_val)
	{                 
		$line = "{$p_key} => {$p_val}\n";
    	fwrite($file, $line);
	}

	fclose($file);
}

if($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'post')
{
	require_once('../../../includes/config.inc.php');

	/* system controller */
	require_once(RL_INC . 'control.inc.php');

	/* load system configurations */
	$config = $rlConfig->allConfig();

	if(!empty($_POST['item']))
	{ 	                                                
		$reefless->loadClass('SMSCoin', null, 'smsCoin');
                              
		$items = explode('|', base64_decode(urldecode($_POST['item'])));
		$plan_id = $items[0];
		$item_id = $items[1];
		$account_id = $items[2];
		$crypted_sum = $items[3];
		$callback_class = $items[4];
		$callback_method = $items[5];
		$cancel_url = $items[6];
		$success_url = $items[7];
		$lang_code = $items[8];
                                                                                                       
		$total = $_POST['s_amount'];
		$txn_id = $_POST['s_order_id'];

		define('RL_LANG_CODE', $lang_code);
		define('RL_DATE_FORMAT', $rlDb -> getOne('Date_format', "`Code` = '{$config['lang']}'", 'languages'));

		$lang = $rlLang->getLangBySide('frontEnd', RL_LANG_CODE);
		$GLOBALS['lang'] = $lang;

		// listing data
		if(empty($item_id) || empty($plan_id) || empty($total))
		{
			$errors = true;
		}

		// success params
        if(isset($_POST['s_status']) && !empty($_POST['s_sign']))
		{
	        $s_sign = $GLOBALS['config']['smscoin_keyword']."::".$GLOBALS['config']['smscoin_s_purse']."::".$txn_id."::".$total."::".$GLOBALS['config']['smscoin_s_clear_amount']."::".$_POST['s_status']; 
			$s_sign = md5($s_sign);

			// hash code
			if($s_sign != $_POST['s_sign'])
			{
				$errors = true;
			}
  
		 	if($_POST['s_status'] == 1 && !$errors)
			{
				$reefless->redirect(null, $success_url);
				exit;
			}	
			else
			{
				$reefless->redirect(null, $cancel_url);
				exit;
			}
		}
		else
		{	
			// handler params
			if(!empty($_POST['s_sign_v2']))
			{
	            $s_sign_v2 = $GLOBALS['config']['smscoin_keyword']."::".$GLOBALS['config']['smscoin_s_purse']."::".$txn_id."::".$total."::".$GLOBALS['config']['smscoin_s_clear_amount']."::".$_POST['s_inv']."::".$_POST['s_phone']; 
				$s_sign_v2 = md5($s_sign_v2);

				// hash code
				if($s_sign_v2 != $_POST['s_sign_v2'])
				{
					$errors = true;
				}

				if(!$errors)
				{     
					$reefless->loadClass(str_replace('rl', '', $callback_class));
					$$callback_class->$callback_method($item_id, $plan_id, $account_id, $txn_id, 'smscoin', $total);
				}
				exit();       
			}
		}
	}
}