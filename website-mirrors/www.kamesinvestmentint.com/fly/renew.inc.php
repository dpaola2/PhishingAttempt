<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RENEW.INC.PHP
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

// set bread_crumbs
$bread_crumbs[1] = array('name' => $lang['pages+name+my_banners'], 'path' => $pages['my_banners']);
$bread_crumbs[2] = array('name' => $lang['pages+name+banners_renew'], 'path' => $pages['banners_renew']);

$bannerId = (int)$_GET['id'];
if ( $bannerId )
{
	$sql  = "SELECT `T1`.`ID`, `T1`.`Plan_ID`, `T1`.`Account_ID`, `T1`.`Date_to`, `T1`.`Status`, `T2`.`Plan_Type`, `T2`.`Period`, `T2`.`Price` ";
	$sql .= "FROM `". RL_DBPREFIX ."banners` AS `T1` ";
	$sql .= "LEFT JOIN `". RL_DBPREFIX ."banner_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
	$sql .= "WHERE `T1`.`ID` = '{$bannerId}'";
	$bannerInfo = $rlDb -> getRow($sql);

	if ( !empty($bannerInfo) )
	{
		if ( $bannerInfo['Account_ID'] == $account_info['ID'] )
		{
			$rlSmarty -> assign_by_ref('bannerInfo', $bannerInfo);
			$setStatus = !$config['banners_auto_approval'] ? 'pending' : 'active';

			// get banner plans
			$planInfo = $rlBanners -> getBannerPlans('ID', $bannerInfo['Plan_ID'], 'row');
			$rlSmarty -> assign('planInfo', array($planInfo['ID'] => $planInfo));

			// get box info
			$b_box = $bannerData['Box'];
			$boxInfo = $rlDb -> getRow("SELECT `Side`, `Banners` FROM `". RL_DBPREFIX ."blocks` WHERE `Key` = '{$b_box}' AND `Plugin` = 'banners'");
			$boxSide = $boxInfo['Side'];
			$boxInfo = unserialize($boxInfo['Banners']);

			$bannerData['Box'] = array(
				'side' => $lang[$boxSide],
				'name' => $lang['blocks+name+'. $b_box],
				'width' => $boxInfo['width'],
				'height' => $boxInfo['height']
			);

			// type info
			$b_type = $bannerData['Type'];
			$bannerData['Type'] = array(
				'key' => $b_type,
				'name' => $lang['banners_bannerType_'. $b_type]
			);

			$_POST['plan'] = $planInfo['ID'];

			if ( $_POST['submit'] )
			{
				if ( $bannerInfo['Price'] == 0 )
				{
					$now = time();
					$date_to = $bannerInfo['Period'] != 0 ? ($bannerInfo['Plan_Type'] == 'period' ? $now + ($bannerInfo['Period'] * 86400) : $bannerInfo['Date_to'] + $bannerInfo['Period']) : 0;
					$sql  = "UPDATE `". RL_DBPREFIX ."banners` SET `Pay_date` = '{$now}', `Status` = '{$setStatus}', `Date_to` = '{$date_to}' ";
					$sql .= "WHERE `ID` = '{$bannerId}'";

					if ( $rlDb -> query($sql) )
					{
						// send notify to admin
						if ( !$config['banners_auto_approval'] )
						{
							$reefless -> loadClass('Mail');

							$mail_tpl = $rlMail -> getEmailTemplate('banners_admin_banner_edited');
							$m_find = array('{username}', '{link}', '{date}', '{status}');
							$m_replace = array(
								$account_info['Username'], 
								'<a href="'. RL_URL_HOME . ADMIN .'/index.php?controller=banners&amp;filter='. $bannerId .'">'. $lang['banners+name+'. $bannerId] . '</a>', 
								date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)), $lang['pending']
							);
							$mail_tpl['body'] = str_replace($m_find, $m_replace, $mail_tpl['body']);
							$rlMail -> send($mail_tpl, $config['notifications_email']);
						}

						// save notice
						$reefless -> loadClass('Notice');
						$rlNotice -> saveNotice($lang['banners_noticeBannerUpgraded']);

						// redirect to related controller
						$redirect = SEO_BASE;
						$redirect .= $config['mod_rewrite'] ? $pages['my_banners'] .'.html' : '?page='. $pages['my_banners'];
						$reefless -> redirect(null, $redirect);
					}
					else
					{
						$sError = true;
					}
				}
				else
				{
					$gateway = $_POST['gateway'];
					if ( !$gateway )
					{
						$errors[] = $lang['notice_payment_gateway_does_not_chose'];
					}
					else
					{
						// get banner title
						$bannerTitle = $lang['banners+name+'. $bannerId];

						// save payment details
						$itemName = $lang['banners_planType'];

						$cancel_url = SEO_BASE;
						$cancel_url .= $config['mod_rewrite'] ? $page_info['Path'] .'.html?id='. $bannerId .'&canceled' : '?page='. $page_info['Path'] .'&id='. $bannerId .'&canceled';

						$success_url = SEO_BASE;
						$success_url .= $config['mod_rewrite'] ? $pages['my_banners'] .'.html' : '?page='. $pages['my_banners'];

						$complete_payment_info = array(
							'item_name' => $itemName .' #'. $bannerInfo['ID'] .' ('. $bannerTitle .')',
							'category_id' => 0,
							'plan_info' => $planInfo,
							'item_id' => $bannerInfo['ID'],
							'account_id' => $bannerInfo['Account_ID'],
							'gateway' => $gateway,
							'callback' => array(
								'plugin' => 'banners',
								'class' => 'rlBanners',
								'method' => 'upgradeBanner',
								'cancel_url' => $cancel_url,
								'success_url' => $success_url
							)
						);
						$_SESSION['complete_payment'] = $complete_payment_info;

						$rlHook -> load('addBannerCheckoutPreRedirect');

						// redirect
						$redirect = SEO_BASE;
						$redirect .= $config['mod_rewrite'] ? $pages['payment'] .'.html' : '?page='. $pages['payment'];
						$reefless -> redirect(null, $redirect);
					}
				}
			}
		}
		else
		{
			$sError = true;
		}
	}
	else
	{
		$sError = true;
	}
}
else
{
	$sError = true;
}