<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: MY_CREDITS.INC.PHP
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

// detect purchase page
if ( $config['mod_rewrite'] )
{
	$purchasePage = $_GET['nvar_1'] == 'purchase' ? true : false;
}
else
{
	$purchasePage = isset($_GET['purchase']) ? true : false;
}
$rlSmarty -> assign('purchasePage', $purchasePage);

// purchase page trigger
if ( $purchasePage )
{
	// add bread crumbs item
	$bread_crumbs[] = array(
		'name' => $lang['paygc_purchase_credits']
	);
	$page_info['name'] = $lang['paygc_purchase_credits'];

	if ( $_POST['submit'] )
	{
		if ( !empty($_POST['credits']) )
		{
			$credit_id = (int)$_POST['credits'];
		}
		else
		{
			$errors[] = $lang['paygc_empty_credit'];
		}

		$gateway = $_POST['gateway'];
	    if ( empty($gateway) )
		{
			$errors[] = $lang['notice_payment_gateway_does_not_chose'];
		}

		if ( !empty($errors) )
		{
			$rlSmarty -> assign_by_ref('errors', $errors);
		}
		else
		{
			$credit_info = $rlDb -> fetch(array('ID', 'Price', 'Credits', 'Position', 'Status'), array('ID' => $credit_id), null, 1, 'credits_manager', 'row');

			$cancel_url  = SEO_BASE;
			$cancel_url .= $config['mod_rewrite'] ? $page_info['Path'] .'.html?canceled' : '?page='. $page_info['Path'] .'&amp;canceled';

			$success_url  = SEO_BASE;
			$success_url .= $config['mod_rewrite'] ? $page_info['Path'] .'.html?completed' : '?page='. $page_info['Path'] .'&amp;completed';

			$complete_payment_info = array(
				'item_name' => $credit_info['Credits'] .' '. $lang['paygc_credits'],
				'gateway' => $gateway,
				'service' => 'credits',
				'item_id' => $credit_id,
				'plan_info' => $credit_info,
				'account_id' => (int)$account_info['ID'],
				'callback' => array(
					'class' => 'rlPayAsYouGoCredits',
					'method' => 'completeTransaction',
					'cancel_url' => $cancel_url,
					'success_url' => $success_url,
					'plugin' => 'payAsYouGoCredits'
				)
			);
			$_SESSION['complete_payment'] = $complete_payment_info;

			// redirect to checkout
			$redirect  = SEO_BASE;
			$redirect .= $config['mod_rewrite'] ? $pages['payment'] .'.html' : '?page='. $pages['payment'];
			$reefless -> redirect(null, $redirect);
			exit;
		}
	}

	// get credits list
	$sql = "SELECT * FROM `". RL_DBPREFIX ."credits_manager` WHERE `Status` = 'active' ORDER BY `Price` ASC";
	$credits = $rlDb -> getAll($sql);

	foreach($credits as $key => $val)
	{
		$credits[$key]['Price_one'] = round(($val['Price'] / $val['Credits']), 2);
	}
	$rlSmarty -> assign_by_ref('credits', $credits);
}
else
{
	/* get expiration date */
	$days = ceil((int)$config['paygc_period'] * 30.5);
	$sql  = "SELECT `ID`, `Total_credits`, `paygc_pay_date`, DATE_ADD(`paygc_pay_date`, INTERVAL {$days} DAY) AS `Expiration_date` ";
	$sql .= "FROM `". RL_DBPREFIX ."accounts` WHERE `ID` = '{$account_info['ID']}'";

	$creditsInfo = $rlDb -> getRow($sql);
	$rlSmarty -> assign_by_ref('creditsInfo', $creditsInfo);

    // set notifications
	if ( isset($_GET['completed']) )
	{
		$rlSmarty -> assign_by_ref('pNotice', $lang['paygc_payment_completed']);
	}

	if ( isset($_GET['canceled']) )
	{
		$errors[] = $lang['paygc_payment_canceled'];
		$rlSmarty -> assign_by_ref('errors', $errors);
	}
}