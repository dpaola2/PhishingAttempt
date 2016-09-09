<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLPAYASYOUGOCREDITS.CLASS.PHP
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

class rlPayAsYouGoCredits extends reefless 
{
	/**
	* Update credits for Account
	*/
	function updateCreditsForAccount()
	{
		global $profile_data;

		if ( !isset($_POST['Total_credits']) )
			return false;

		$balance = (float)$_POST['Total_credits'];
		$where = $_GET['action'] == 'add' ? "`Username` = '{$profile_data['username']}'" : "`ID` = '". intval($_GET['account']) ."'";
		$this -> query("UPDATE `". RL_DBPREFIX ."accounts` SET `Total_credits` = '{$balance}', `paygc_pay_date` = UNIX_TIMESTAMP() WHERE {$where} LIMIT 1");
	}

	/**
	* Complete payment transaction
	*
	* @param int $item_id    - credit item ID
	* @param int $plan_id    - plan ID (not used)
	* @param int $account_id - account ID
	* @param string $txn_id  - txn ID
	* @param string $dateway - gateway name
	* @param double $total   - total summ
	*
	* @return bool - true/false
	*/
	function completeTransaction($item_id = false, $plan_id = false, $account_id = false, $txn_id = null, $gateway = null, $total = false)
	{
		global $rlValid;

		$rlValid -> sql($txn_id);
		$rlValid -> sql($gateway);
		$item_id = (int)$item_id;

		$account_info = $this -> fetch(array('Username', 'First_name', 'Last_name', 'Mail', 'Total_credits'), array('ID' => $account_id), null, 1, 'accounts', 'row');
		$credit_info = $this -> fetch(array('ID', 'Credits'), array('ID' => $item_id), null, 1, 'credits_manager', 'row');

		if ( !empty($account_info) && !empty($credit_info) )
		{
			$this -> loadClass('Actions');
			$this -> loadClass('Mail');

			$account_update = array(
				'fields' => array(
					'Total_credits' => round($account_info['Total_credits'] + $credit_info['Credits'], 2),
					'paygc_pay_date' => time(),
				),
				'where' => array(
					'ID' => $account_id
				)
			);

			if($GLOBALS['rlActions']->updateOne($account_update, 'accounts'))
			{
				$package_name = $GLOBALS['lang']['credits_manager+name+credit_package_' . $credit_info['ID']];

				/* send payment notification email */
				$account_name = $account_info['First_name'] || $account_info['Last_name'] ? $account_info['First_name'] .' '. $account_info['Last_name'] : $account_info['Username'];

				$search = array('{username}', '{gateway}', '{txn}', '{item}', '{price}', '{date}');
				$replace = array($account_name, $gateway, $txn_id, $package_name, $total, date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)));

				$mail_tpl = $GLOBALS['rlMail']->getEmailTemplate('payment_accepted');

				$mail_tpl['body'] = str_replace($search, $replace, $mail_tpl['body']);
				$GLOBALS['rlMail']->send($mail_tpl, $account_info['Mail']);

				/* send admin notification */
				$mail_tpl = $GLOBALS['rlMail']->getEmailTemplate('admin_listing_paid');
				$search = array('{id}', '{username}', '{gateway}', '{txn}', '{item}', '{price}', '{date}');
				$replace = array($item_id, $account_name, $gateway, $txn_id, $package_name, $total, date( str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)));

				$mail_tpl['body'] = str_replace($search, $replace, $mail_tpl['body']);
				$GLOBALS['rlMail']->send($mail_tpl, $GLOBALS['config']['notifications_email']);

				/* save transaction details */
				$transaction = array(
					'Service' => 'credits',
					'Item_ID' => $item_id,
					'Account_ID' => $account_id,
					'Plan_ID' => 0,
					'Txn_ID' => $txn_id,
					'Total' => $total,
					'Gateway' => $gateway,
					'Date' => time()
				);
				$GLOBALS['rlActions'] -> insertOne($transaction, 'transactions');

				return true;
			}
		}
		return false;
	}

	/**
	* Delete credit item by ID
	*
	* @param int $id - credit id
	* @package AJAX
	*/
	function ajaxDeleteCreditItem($id = false)
	{
		global $_response, $lang;

		$id = (int)$id;
		if ( !$id )
			return $_response;

		$this -> query("DELETE FROM `". RL_DBPREFIX ."credits_manager` WHERE `ID` = '{$id}' LIMIT 1");

		// update config
		$sql  = "UPDATE `". RL_DBPREFIX ."config` SET `Default` = ROUND((SELECT MAX(@Price_one:=`Price`/`Credits`) AS `MaxPriceCredit` ";
		$sql .= "FROM `". RL_DBPREFIX ."credits_manager` LIMIT 1), 2) WHERE `Key` = 'paygc_rate_hide' LIMIT 1";
		$this -> query($sql);

		// print message, update grid
		$_response -> script("
			creditsGrid.reload();
			printMessage('notice', '{$lang['item_deleted']}');
		");

		return $_response;
	}
}