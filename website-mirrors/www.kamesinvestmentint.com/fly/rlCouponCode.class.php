<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLCOUPONCODE.CLASS.PHP
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

class rlCouponCode extends reefless
{	
	/**
	* check coupone code
	* 
	* @param $coupon - coupon code
	* 
	* @return confirmate or error
	**/
	function ajaxCheckCouponCode($coupon, $plan_id, $diffuse = false, $renew = false, $type = false)
	{
		global $_response, $lang, $pages, $config, $account_info, $tpl_settings;

		if ( $diffuse )
		{
			if(!empty($renew))
			{
				/* link to form  */
				$link = SEO_BASE;
				$link .= $config['mod_rewrite'] ? $pages['payment'] .'.html' : 'index.php?page='. $pages['Path'];
				$_response -> script("$('[name=\"payment\"]').attr('action','".$link."');");
			}
			unset($_SESSION['coupon_code']);
			$_response -> script("$('#coupon_code_info').hide();$('#coupon_code').show();");
		}
		else
		{
			$plan_id = (int)$plan_id;
			$GLOBALS['rlValid'] -> sql($coupon);

			if ($type == 'listing')
			{
				$plan_price = $this -> getOne('Price', "`Status` = 'active' AND `ID` = '{$plan_id}'" , 'listing_plans');
			}
			elseif ($type == 'banner')
			{
				$plan_price = $this -> getOne('Price',  "`Status` = 'active' AND `ID` = '{$plan_id}'" , 'banner_plans');
			}
			elseif ($type == 'invoice')
			{
				$invoceInfo = $this -> fetch(array('Total', 'Account_ID'), array('ID' => $plan_id), null, 1, 'invoices', 'row');
				$plan_price = $invoceInfo['Total'];
			}

			$sql =  "SELECT *, UNIX_TIMESTAMP(`Date_from`) AS `Date_from`, UNIX_TIMESTAMP(`Date_to`) AS `Date_to` FROM `" . RL_DBPREFIX . "coupon_code` WHERE `Code` = '{$coupon}'";
			$coupon_info = $this -> getRow( $sql );

			unset($_SESSION['coupon_code']);

			if( !empty($coupon_info) )
			{
				$checkup = $this -> fetch(array('Coupon_ID', 'Account_ID'), array('Coupon_ID' => $coupon_info['ID'], 'Account_ID' => $account_info['ID']), null, null, 'coupon_users');
				
				if ($plan_price > 0 && $coupon_info['Using_limit'] > count($checkup) || $coupon_info['Using_limit'] == '0' )
				{
					if($coupon_info['Account_or_type']=='type' && !in_array($account_info['Type'], explode(',', $coupon_info['Account_type'])) || $coupon_info['Account_or_type']=='account' && $account_info['Username'] != $coupon_info['Username'] )
					{
						$error = $lang['coupon_not_account'];
					}
					elseif($invoceInfo['Account_ID']!=$account_info['ID'] && $type == 'invoice')
					{
						$error = $lang['coupon_not_account'];
					}
					elseif($coupon_info['Sticky'] == 0 && !in_array($plan_id, explode(',', $coupon_info['Plan_ID'])) && $type == 'listing' )
					{
						$error = $lang['coupon_not_plan'];
					}
					elseif($coupon_info['StickyBanners'] == 0 && !in_array($plan_id, explode(',', $coupon_info['BannersPlan_ID'])) && $type == 'banner' )
					{
						$error = $lang['coupon_not_plan'];
					}
					elseif($coupon_info['Used_date'] == 'yes' && ( $coupon_info['Date_from'] >= mktime() || mktime() >= $coupon_info['Date_to']))
					{
						$error = $lang['coupon_expired'];
					}
				}
				elseif($coupon_info['Using_limit'] <= count($checkup) && $coupon_info['Using_limit'] != '0')
				{
					$error = $lang['your_coupon_limit_is_over'];
				}
				else
				{
					$error = $lang['coupon_code_is_incorrect'];
				}
			}
			else
			{
				$error = $lang['coupon_not_found'];
			}
			
			if($error)
			{
				$_response -> script("printMessage('error', '{$error}');");
			}
			else
			{
				if($coupon_info['Type'] == 'cost')
				{
					$total = $plan_price - $coupon_info['Discount'];
					$discount = $coupon_info['Discount'];
				}
				elseif ($coupon_info['Type'] == 'persent')
				{
					$total = $plan_price-(($plan_price /100 ) * $coupon_info['Discount']);
					$discount = $coupon_info['Discount'] . '%';
				}
				if($total<0)
				{
					$total = 0;
				}
				
				$coupon_price_info['price'] = $plan_price;
				$coupon_price_info['discount'] = $discount;
				$coupon_price_info['total'] = $total;
				
				$_SESSION['coupon_code'] = $coupon;
				
				$GLOBALS['rlSmarty'] -> assign_by_ref( 'coupon_price_info', $coupon_price_info );
				$GLOBALS['rlSmarty'] -> assign_by_ref( 'coupon_code', $coupon );
				
				
				if ( $tpl_settings['type'] == 'responsive_42' ) {
					$tpl = RL_PLUGINS .'coupon'. RL_DS . 'coupon_price_info_responsive_42.tpl';
				}
				else {
					$tpl = RL_PLUGINS .'coupon'. RL_DS . 'coupon_price_info.tpl';
				}
				
				if(!empty($renew) && $total == 0)
				{
					$referor = $_SERVER['HTTP_REFERER'];
					$_response -> script("$('[name=\"payment\"]').attr('action','".$referor."');");
				}
				
				$_response -> script("$('#coupon_code').hide();$('#coupon_code_info').show();");
				$_response -> assign( 'coupon_code_info', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );
			}
			
			$_response -> script( "$('#check_coupon').val('{$lang['apply']}');" );
		}
		return $_response;
	}
	
	
	/**
	* edit price 
	*
	* @param $coupon - coupon code
	* @param $plan price - plan price
	* 
	* @return prise with discount
	**/
	function editPrice( $plan_price, $coupon )
	{
		$price = $plan_price['Price'];
		
		if(!empty($coupon))
		{
			$coupon_info = $this -> fetch('*', array('Code' => $coupon, 'Status' => 'active'), "AND ( `Used_date` = 'no' OR UNIX_TIMESTAMP(`Date_from`) < UNIX_TIMESTAMP(NOW()) AND UNIX_TIMESTAMP(`Date_to`) > UNIX_TIMESTAMP(NOW()))", 1, 'coupon_code', 'row');
			if($coupon_info)
			{
				// $checkup = $this -> fetch(array('Coupon_ID', 'Account_ID'), array('Plan_ID' => $plan_price['ID'], 'Coupon_ID' => $coupon_info['ID'], 'Account_ID' =>$account_info['ID']), null, null, 'coupon_users');
				if($price > 0) 
				{
					if($coupon_info['Type'] == 'cost')
					{
						$price = $price - $coupon_info['Discount'];
					}
					elseif ($coupon_info['Type'] == 'persent')
					{
						$price = $price-(($price * $coupon_info['Discount'])/100);
					}
				}
				if($price<0)
				{
					$price = 0;
				}
			}
		}
		return	$price;		
	}
	/**
	* delete coupon code
	*
	* @package xAjax
	*
	* @param int $coupon_id - coupon id
	*
	**/
	function ajaxDeleteCoupon( $coupon_id = false )
	{
		global $_response;
		$coupon_id = (int)$coupon_id;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$_response -> redirect( RL_URL_HOME . ADMIN . '/index.php?action=session_expired' );
			return $_response;
		}
		
		if ( !$coupon_id )
		{
			return $_response;
		}
		
		// delete poll
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "coupon_code` WHERE `ID` = '{$coupon_id}' LIMIT 1");
		
		$_response -> script("
				CouponCodeGrid.reload();
				printMessage('notice', '{$GLOBALS['lang']['item_deleted']}')
			");
		return $_response;
	}

	/**
	 * @hook addBannerCheckoutPreRedirect
	 * @place add_banner.inc.php
	 * @plugin Banners
	 */
	function hookAddBannerCheckoutPreRedirect() {
		global $config, $nextStep, $planInfo, $page_info, $lang, $pages, $bannerId, $bannerInfo, $account_info;

		$price = $this->editPrice($planInfo, $_POST['coupon_code']);

		if ($price == 0) {
			$step = array_pop($steps);

			// redirect to related controller
			$redirect = SEO_BASE;

			if ($page_info['Key'] == 'add_banner') {

				$this->bannersInsertCouponUsersIfNecessary($account_info['ID'], $planInfo['ID']);
				$redirect.= $config['mod_rewrite'] ? $page_info['Path'] . '/' . $nextStep['path'] . '.html' : '?page=' . $page_info['Path'] . '&step=' . $nextStep['path'];
			}
			else {

				$now = time();
				$setStatus = !$config['banners_auto_approval'] ? 'pending' : 'active';
				$date_to = $bannerInfo['Period'] != 0 ? ($bannerInfo['Plan_Type'] == 'period' ? $now + ($bannerInfo['Period'] * 86400) : $bannerInfo['Date_to'] + $bannerInfo['Period']) : 0;

				$sql = "UPDATE `" . RL_DBPREFIX . "banners` SET `Pay_date` = {$now}, `Status` = '{$setStatus}', `Date_to` = {$date_to} WHERE `ID` = {$bannerId}";
				$this->query($sql);

				$this->bannersInsertCouponUsersIfNecessary($account_info['ID'], $planInfo['ID']);

				$this->loadClass('Notice');
				$GLOBALS['rlNotice']->saveNotice($lang['banners_noticeBannerUpgraded']);

				$redirect.= $config['mod_rewrite'] ? $pages['my_banners'] . '.html' : '?page=' . $pages['my_banners'];
			}
			$this->redirect(null, $redirect);
		}
	}

	/**
	 * Banners insert coupon users if necessary
	 *
	 * @param int $account_id - User account ID
	 * @param int $plan_id - Plan ID
	 */
	function bannersInsertCouponUsersIfNecessary($account_id = false, $plan_id = false) {
		global $account_info;

		if (!$plan_id || !$account_id) return;

		$coupon_id = (int)$this->getOne('ID', "`Using_limit` <> 0 AND `Code` = '{$_SESSION['coupon_code']}'", 'coupon_code');

		if ($coupon_id) {
			$sql = "INSERT INTO `" . RL_DBPREFIX . "coupon_users` (`Coupon_ID`, `Account_ID`, `BannersPlan_ID`) VALUES ";
			$sql.= "({$coupon_id}, {$account_id}, {$plan_id})";

			$this -> query($sql);
		}
		unset($_SESSION['coupon_code']);
	}
}
