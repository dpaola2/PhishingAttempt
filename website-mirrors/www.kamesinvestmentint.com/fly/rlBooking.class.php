<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLBOOKING.CLASS.PHP
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

class rlBooking extends reefless
{
	var $bookingType = 'specialTypeForReplaceInSearch';

	/**
	* @var validator class object
	**/
	var $rlValid;

	/**
	* @var notice class object
	**/
	var $rlNotice;

	/**
	* @var language class object
	**/
	var $rlLang;

	/**
	* @var Smarty class object
	**/
	var $rlSmarty;

	var $lCalc;

	var $rateRanges = array();

	var $use_time_frame = false;

	/**
	* class constructor
	*
	**/
	function rlBooking()
	{
		global $rlValid, $rlLang, $rlSmarty, $rlNotice;

		$this -> rlValid = &$rlValid;
		$this -> rlLang = &$rlLang;
		$this -> rlNotice = &$rlNotice;
		$this -> rlSmarty = &$rlSmarty;
	}

	/**
	* get Dates
	*
	* @package xAjax
	*
	* @param int $listing_id - listing id
	* @param int $month - start booking month
	* @param int $year - start booking year
	*
	**/
	function ajaxGetDates( $listing_id = false, $mode = false )
	{
		global $_response, $lang, $config, $tpl_settings;
		$listing_id = (int)$listing_id;

		if ( $config['booking_binding_plans'] && $config['booking_calendar_restricted'] )
		{
			$sql  = "SELECT `T1`.`Pay_date`,`T2`.`Listing_period` FROM `".RL_DBPREFIX."listings` AS `T1` ";
			$sql .= "LEFT JOIN `".RL_DBPREFIX."listing_plans` AS `T2` ON `T1`.`Plan_ID`=`T2`.`ID` ";
			$sql .= "WHERE `T1`.`ID`='{$listing_id}'";
			$plan_info = $this -> getRow($sql);

			if ( $plan_info['Listing_period'] == 0 )
			{
				$config['booking_calendar_restricted'] = 0;
			}
			else
			{
				$restriction_plan = date('Y-m-d', strtotime("+{$plan_info['Listing_period']} day"));
				$restriction_plan = mktime(0, 0, 0, substr($restriction_plan, 5, 2), substr($restriction_plan, 8, 2), substr($restriction_plan, 0, 4));
			}
		}

		if ( $tpl_settings['type'] == 'responsive_42' ) {
			$CountCalendars = 2;//fixed count
		}
		else {
			$CountCalendars = (int)( $config['booking_calendar_horizontal'] * $config['booking_calendar_vertical'] );
		}
		$curRange = mktime(0, 0, 0, substr(date('Y-m-d'), 5, 2), substr(date('Y-m-d'), 8, 2), substr(date('Y-m-d'), 0, 4));
		$BookingDays = array();

		if ( $mode )
		{
			$cnYear = (int)substr($_SESSION['booking_start_date'], 0, 4);
			$cnMonth = (int)substr($_SESSION['booking_start_date'], 5, 2);
			$zn = substr($mode,0,1);
			$wh = (substr($mode,1,1)) == 'Y' ? 0 : 1;
			if($wh == 0)
			{
				eval("\$cnYear = {$cnYear} {$zn} 1;");
			}
			else
			{
				eval("\$cnMonth = {$cnMonth} {$zn} 1;");
			}

			$startDate = date("Y-m-d", mktime (0, 0, 0, $cnMonth, 1, $cnYear));
			$checkRange = mktime(0, 0, 0, substr($startDate, 5, 2), substr($startDate, 8, 2), substr($startDate, 0, 4));
			$endDate = date("Y-m-d", mktime (0, 0, 0, $cnMonth + $CountCalendars, 0, $cnYear));

			if($checkRange <= $curRange)
			{
				$startDate = date("Y-m-d", mktime (0, 0, 0, date('m'), 1, date('Y')));
				$endDate = date("Y-m-d", mktime (0, 0, 0, date('m')+$CountCalendars, 0, date('Y')));
			}
			$_SESSION['booking_start_date'] = $startDate;
		}
		else
		{
			$startDate = date("Y-m-d", mktime (0, 0, 0, date('m'), 1, date('Y')));
			$endDate = date("Y-m-d", mktime (0, 0, 0, date('m')+$CountCalendars, 0, date('Y')));
			$_SESSION['booking_start_date'] = $startDate;
		}

		$userBook = $this->fetch('*', array('Listing_ID'=>"{$listing_id}"), "AND `Status`<>'refused'", null, 'listings_book');

		foreach ($userBook as $bKey => $book)
		{
			$JS .= "usBook[{$bKey}] = new Array();";
			$JS .= "usBook[{$bKey}][0] = '{$book['Status']}';";
			$JS .= "usBook[{$bKey}][1] = '{$book['From']}';";
			$JS .= "usBook[{$bKey}][2] = '{$book['To']}';";
		}

		$toDay = mktime(0, 0, 0, substr(date('Y-m-d'), 5, 2), substr(date('Y-m-d'), 8, 2), substr(date('Y-m-d'), 0, 4));
		$sesRange = mktime(0, 0, 0, substr($_SESSION['booking_start_date'], 5, 2), substr($_SESSION['booking_start_date'], 8, 2), substr($_SESSION['booking_start_date'], 0, 4));
		$nulled = true;
		$hack = false;

		$hack = ( $sesRange >= $curRange ) ? true : false;

		$iDateFrom = mktime(0, 0, 0, substr($startDate, 5, 2), substr($startDate, 8, 2), substr($startDate, 0, 4));
		$iDateTo = mktime(0, 0, 0, substr($endDate, 5, 2), substr($endDate, 8, 2), substr($endDate, 0, 4));

		if ($iDateTo>=$iDateFrom)
		{
			// first day
			$mYear = date('Y', $iDateFrom);
			$mMonth = date('m', $iDateFrom);
			$mDay = date('d', $iDateFrom);
			$MonthName = strtolower(date('F', $iDateFrom));
			$MonthName = $lang['booking_month_'.$MonthName];
			$missDay = date('N', $iDateFrom);
			$mktime = $iDateFrom;

			$BookingDays[$mMonth]['Year'] = $mYear;
			$BookingDays[$mMonth]['Name'] = $MonthName;

			for( $i=0; $i<$missDay-1; $i++ )
			{
				$BookingDays[$mMonth]['Days'][$i.'_miss'] = 'missed';
			}

			if ( date('Y-m-d') == date('Y-m-d', $mktime) )
			{
				$BookingDays[$mMonth]['Days'][$mDay]['Color'] = "T";
				$nulled = false;
			}
			elseif ($nulled === true && $hack === false)
			{
				$BookingDays[$mMonth]['Days'][$mDay]['Color'] = "U";
			}
			elseif ( $mktime > $restriction_plan && $config['booking_binding_plans'] && $config['booking_calendar_restricted'] )
			{
				$BookingDays[$mMonth]['Days'][$mDay]['Color'] = "R";
			}
			else
			{
				$BookingDays[$mMonth]['Days'][$mDay]['Color'] = "A";
			}

			if ($BookingDays[$mMonth]['Days'][$mDay]['Color'] != 'U' && $BookingDays[$mMonth]['Days'][$mDay]['Color'] != 'R')
			{
				$BookingDays[$mMonth]['Days'][$mDay]['mktime'] = $mktime;
			}

			$month_flag = $mMonth;
			$miss_added = false;

			// next days
			while( $iDateFrom < $iDateTo )
			{
				$mYear = date('Y', $iDateFrom);
				$mMonth = date('m', $iDateFrom);
				$mDay = date('d', $iDateFrom);
				$MonthName = strtolower(date('F', $iDateFrom));
				$MonthName = $lang['booking_month_'.$MonthName];
				$mktime = $iDateFrom;
				$BookingDays[$mMonth]['Year'] = $mYear;
				$BookingDays[$mMonth]['Name'] = $MonthName;

				if ( $month_flag != $mMonth )
				{
					$miss_added = false;
					$month_flag = $mMonth;
				}

				if ( $mMonth == $month_flag && $miss_added === false )
				{
					/* miss action */
					$missDay = date('N', $iDateFrom);
					for($j=0; $j < $missDay-1; $j++)
					{
						$BookingDays[$mMonth]['Days'][$j.'_miss'] = 'missed';
					}

					$miss_added = true;
				}

				if( date('Y-m-d') == date('Y-m-d', $mktime) )
				{
					$BookingDays[$mMonth]['Days'][$mDay]['Color'] = "T";
					$nulled = false;
				}
				elseif($nulled === true && $hack === false)
				{
					$BookingDays[$mMonth]['Days'][$mDay]['Color'] = "U";
				}
				elseif( $mktime > $restriction_plan && $config['booking_binding_plans'] && $config['booking_calendar_restricted'] )
				{
					$BookingDays[$mMonth]['Days'][$mDay]['Color'] = "R";
				}
				else
				{
					$BookingDays[$mMonth]['Days'][$mDay]['Color'] = "A";
				}

				/* close calendar none ranges */
				if ( !$this -> use_time_frame )
				{
					$rateInRange = false;
					foreach ( $this -> rateRanges as $rangeKey => $rangeValue )
					{
						if ( $mktime >= $rangeValue['from'] && $mktime <= $rangeValue['to'] )
						{
							$rateInRange = true;
							break;
						}
						else
						{
							$rateInRange = false;
						}

						$rangeValue['from'] = $rangeValue['from'] .' | '. date( 'Y-m-d H:i:s', $rangeValue['from'] );
						$rangeValue['to'] = $rangeValue['to'] .' | '. date( 'Y-m-d H:i:s', $rangeValue['to'] );
						$debugInfo['rateRanges'][$rangeKey] = $rangeValue;
					}

					if ( !$rateInRange )
					{
						$BookingDays[$mMonth]['Days'][$mDay]['Color'] = "U";
					}
				}
				/* close calendar none ranges end */

				if ( $BookingDays[$mMonth]['Days'][$mDay]['Color'] != 'U' && $BookingDays[$mMonth]['Days'][$mDay]['Color'] != 'R' )
				{
					$BookingDays[$mMonth]['Days'][$mDay]['mktime'] = $mktime;
				}

				$iDateFrom += 86400; // add 24 hours

				if ( $iDateFrom <= $iDateTo && $config['booking_calendar_restricted'] )
				{
					if ( $BookingDays[$mMonth]['Days'][$mDay]['Color'] == 'R' )
					{
						if ( !$mode )
						{
							$navigation['prev'] = 0;
							$navigation['next'] = 0;
						}
						else
						{
							$navigation['prev'] = 1;
							$navigation['next'] = 0;
						}
					}
					else
					{
						if ( !$mode )
						{
							$navigation['prev'] = 0;
							$navigation['next'] = 1;
						}
						else
						{
							$navigation['prev'] = 1;
							$navigation['next'] = 1;
						}
					}
					$this -> rlSmarty -> assign_by_ref('navigation', $navigation);
				}
			}
		}

		$this -> rlSmarty -> assign_by_ref('BookingDays', $BookingDays);

		$dir = $tpl_settings['type'] == 'responsive_42' && !defined('REALM') ? 'responsive'. RL_DS : '';
		$tpl = RL_PLUGINS .'booking'. RL_DS . $dir .'booking_calendar.tpl';
		$_response -> assign( "booking_calendar", 'innerHTML', $this -> rlSmarty -> fetch( $tpl, null, null, false ) );
		$_response->script('eval("'.$JS.'");paintUserBook();booking_mask("set");');

		if ( $tpl_settings['type'] == 'responsive_42' ) {
			$_response -> script("booking_mask('reset');");
		}
		else {
			$_response -> script("$('#calendar_load').stop().animate({opacity: 0}, function(){ booking_mask('reset'); });");
		}

		$binding_days = $this -> fetch('*', array('Listing_ID' => $listing_id, 'Status' => 'active'), null, null, 'booking_bindings', 'row');
		$_response -> script("bind_checkin = '".$binding_days['Checkin']."'; bind_checkout = '".$binding_days['Checkout']."';");

		if ( $mode )
		{
			$_response -> script("book_color(false, true);");
		}

		if ( $sesRange <= $curRange )
		{
			$_response -> script("$('#prevRange').fadeOut();");
		}
		else
		{
			$_response -> script("$('#prevRange').show();");
		}

		return $_response;
	}

	/**
	* 
	**/
	function ajaxBookNow($listing_id, $from, $to, $formData, $amount = false) {
		global $_response, $aHooks, $config, $lang, $pages;

		$listing_id = (int)$listing_id;
		$from = (int)$from;
		$to = (int)$to;
		$formData = $this -> rlValid -> xSql($formData);
		$amount = (double)$amount;

		$insert  = "INSERT INTO `". RL_DBPREFIX ."listings_book` (`Listing_ID`,`From`,`To`, `Amount`) ";
		$insert .= "VALUES ('{$listing_id}','{$from}','{$to}', '{$amount}')";
		$this -> query( $insert );

		$id = mysql_insert_id();
		$renter_id = (int)$_SESSION['id'];
		$fields = '';
		$values = '';

		foreach( $formData as $key => $data ) {
			$fields .= "`{$data['name']}`, ";
			$values .= "'{$data['value']}', ";
		}

		$fields = substr( $fields, 0, -2 );
		$values = substr( $values, 0, -2 );

		$sql_info  = "SELECT `T1`.*, `T2`.`Mail`, `T3`.`ID` AS `Category_ID`, `T3`.`Path` AS `Category_Path`, `T3`.`Type` AS `Listing_Type` ";
		$sql_info .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql_info .= "LEFT JOIN `". RL_DBPREFIX ."accounts` AS `T2` ON `T1`.`Account_ID` = `T2`.`ID` ";
		$sql_info .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql_info .= "WHERE `T1`.`ID` = {$listing_id}";
		$listing_info = $this -> getRow( $sql_info );

		$insert_info  = "INSERT INTO `". RL_DBPREFIX ."booking_requests` ( `Book_ID`, `Owner_ID`, `Renter_ID`, `Date`, {$fields} ) ";
		$insert_info .= "VALUES ( '{$id}', '{$listing_info['Account_ID']}', '{$renter_id}', NOW(), {$values} )";
		$this -> query( $insert_info );

		if ( $config['booking_notify_admin_by_email'] || $config['booking_notify_email'] ) {
			$this -> loadClass('Mail');
			$this -> loadClass('Listings');

			$date_format = str_replace('%', '', RL_DATE_FORMAT);
			list($ad_price, $ad_currency) = explode('|', $listing_info['price'], 2);
			$adaptPrice = $lang['data_formats+name+'. $ad_currency] .' '. $this -> rlSmarty -> str2money($amount);
			$requestDate = date(str_replace('b', 'M', $date_format));
			$checkIn = date( str_replace('b', 'M', $date_format), $from );
			$checkOut = date( str_replace('b', 'M', $date_format), $to );
			$clientData = '';

			//
			$listing_type = $listing_info['Listing_Type'];
			$ad_title = $GLOBALS['rlListings'] -> getListingTitle($listing_info['Category_ID'], $listing_info, $listing_type);
			$ad_link = SEO_BASE;

			if ($config['mod_rewrite']) {
				$ad_link .= $pages['lt_'. $listing_type] .'/'. $listing_info['Category_Path'] .'/'. $this->rlSmarty->str2path($ad_title) .'-'. $listing_id .'.html';
			}
			else {
				$ad_link .= '?page='. $pages['lt_'. $listing_type] .'&id='. $listing_id;
			}
			$ad_link = '<a href="'. $ad_link .'">'. $ad_title .'</a>';

			foreach( $formData as $key => $data ) {
				$clientData .= $lang['booking_fields+name+'. $data['name']] .": {$data['value']}<br />";
			}

			$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'booking_new_request_notify' );
			$mail_replace = array(
				'{date}' => $requestDate,
				'{link}' => $ad_link,
				'{checkin}' => $checkIn,
				'{checkout}' => $checkOut,
				'{amount}' => $adaptPrice,
				'{details}' => $clientData,
			);
			$mail_tpl['body'] = str_replace(array_keys($mail_replace), array_values($mail_replace), $mail_tpl['body'] );

			if ( $config['booking_notify_admin_by_email'] ) {
				$GLOBALS['rlMail'] -> send($mail_tpl, $config['notifications_email']);
			}

			if ( $config['booking_notify_email'] ) {
				$GLOBALS['rlMail'] -> send($mail_tpl, $listing_info['Mail']);
			}
		}
		$this -> ajaxGetDates($listing_id);

		$_response -> script("printMessage('notice', '{$GLOBALS['lang']['booking_request_send']}');");

		return $_response;
	}

	/**
	* 
	**/
	function ajaxOwnerResult( $request_id, $result , $body_text)
	{
		global $_response, $lang, $config, $pages;

		$body_text = $this -> rlValid -> xSql($body_text);

		if ( empty($body_text) )
		{
			$_response -> script("printMessage('error', '{$lang['booking_error_fields_empty']}');");
			return $_response;
		}

		$this -> loadClass('Mail');

		$sql_requests = "UPDATE `". RL_DBPREFIX ."booking_requests` SET `Status`='readed' WHERE `Book_ID` = {$request_id}";
		$this->query( $sql_requests );

		$sql  = "SELECT `T2`.`first_name` AS `Renter_name`,`T2`.`email` AS `Renter_mail`,`T3`.`First_name` AS `Owner_fname`, ";
		$sql .= "`T3`.`Last_name` AS `Owner_lname`,`T3`.`Username` AS `Owner_uname`,`T3`.`Mail` AS `Owner_mail`, `T1`.`Listing_ID` ";
		$sql .= "FROM `". RL_DBPREFIX ."listings_book` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."booking_requests` AS `T2` ON `T1`.`ID`=`T2`.`Book_ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."accounts` AS `T3` ON `T2`.`Owner_ID`=`T3`.`ID` ";
		$sql .= "WHERE `T1`.`ID`='{$request_id}' ";
		$request_info = $this -> getRow( $sql );

		$renter = $request_info['Renter_name'];

		if ($request_info['Owner_fname'] || $request_info['Owner_lname'])
		{
			$owner = $request_info['Owner_fname'].' '.$request_info['Owner_lname'];
		}
		else
		{
			$owner = $request_info['Owner_uname'];
		}
		$req_result = 'process';

		//
		$this->loadClass('Listings');
		$listing_info = $GLOBALS['rlListings']->getShortDetails($request_info['Listing_ID']);
		$ad_title = $listing_info['listing_title'];

		$ad_link = SEO_BASE;
		if ($config['mod_rewrite']) {
			$ad_link .= $pages['lt_'. $listing_info['Listing_type']] .'/'. $listing_info['Category_path'] .'/'. $this->rlSmarty->str2path($ad_title) .'-'. $listing_info['ID'] .'.html';
		}
		else {
			$ad_link .= '?page='. $pages['lt_'. $listing_info['Listing_type']] .'&id='. $listing_info['ID'];
		}
		$ad_link = '<a href="'. $ad_link .'">'. $ad_title .'</a>';

		if ( $result == 'accept' )
		{
			$this->query("UPDATE `". RL_DBPREFIX ."listings_book` SET `Status` = 'booked' WHERE `ID` = {$request_id}");
			$mess = $lang['booking_req_accepted'];
			$req_result = 'booked';

			$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'booking_accepted_request' );
			$mail_tpl['body'] = str_replace( array('{link}','{renter}', '{BODY}', '{owner}'), array($ad_link, $renter, $body_text, $owner), $mail_tpl['body'] );
			$GLOBALS['rlMail'] -> send( $mail_tpl, $request_info['Renter_mail'], false, $request_info['Owner_mail'] );
		}
		else
		{
			$this->query("UPDATE `". RL_DBPREFIX ."listings_book` SET `Status` = 'refused' WHERE `ID` = {$request_id}");
			$mess = $lang['booking_req_refused'];
			$req_result = 'refused';

			$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'booking_refused_request' );
			$mail_tpl['body'] = str_replace( array('{link}','{renter}', '{BODY}', '{owner}'), array($ad_link, $renter, $body_text, $owner), $mail_tpl['body'] );
			$GLOBALS['rlMail'] -> send( $mail_tpl, $request_info['Renter_mail'], false, $request_info['Owner_mail'] );
		}

		$req_result = str_replace( array('process','booked','refused'),
		array($GLOBALS['lang']['booking_processed'],
		$lang['booking_accepted'], $lang['booking_refused']), $req_result );

		$_response -> script("printMessage('notice', '{$mess}');$('#owRes').html('{$req_result}');$('#owner_actions').slideUp('fast');");

		return $_response;
	}

	/**
	* 
	**/
	function createGradient( $c_r, $c_g, $c_b, $o_r, $o_g, $o_b, $imgName )
	{
		require_once( RL_PLUGINS . 'booking' . RL_DS . 'admin' . RL_DS . 'gdplus_gradients.php' );

		$gradient = new gdplus_gradients();
		$gradient -> addcolor( $c_r, $c_g, $c_b, 10 );
		$gradient -> addcolor( $o_r, $o_g, $o_b, 50 );
		$gradient -> addcolor( $c_r, $c_g, $c_b, 90 );

		$display = $gradient -> buildgradient(40, 20);

		imagepng( $display, RL_PLUGINS . 'booking' . RL_DS . 'img' . RL_DS . $imgName . '.png' );
		ImageDestroy( $display );
	}

	/**
	* get booking fields
	*
	* @return array - fields list
	**/
	function getBookingFields()
	{
		$adapt = $this -> fetch('*', array('Status'=>'active'), "ORDER BY `Position`", null, 'booking_fields');
		$adapt = $this -> rlLang -> replaceLangKeys( $adapt, 'booking_fields', array( 'name', 'default', 'description' ) );

		return $adapt;
	}

	/**
	* 
	**/
	function ajaxSaveDesc($rate_id, $value, $mode=false)
	{
		global $_response, $tpl_settings;

		$listing_id = defined('REALM') ? (int)$_GET['listing_id'] : (int)$_GET['id'];
		$value = $this -> rlValid -> xSql($value);

		if ( $mode )
		{
			if ( $this -> getOne('Value',"`Key`='booking_range+regular+desc+{$listing_id}'",'lang_keys') )
			{
				$this -> query("UPDATE `".RL_DBPREFIX."lang_keys` SET `Value`='{$value}' WHERE `Key`='booking_range+regular+desc+{$listing_id}'");
			}
			else
			{
				$ins = "INSERT INTO `".RL_DBPREFIX."lang_keys` (`Key`,`Value`,`Module`,`Status`,`Plugin`) VALUES ('booking_range+regular+desc+{$listing_id}','{$value}','common','active','booking')";
				$this -> query($ins);
			}
		}
		else
		{
			$rate_info = $this -> getRow("SELECT `From`,`To` FROM `".RL_DBPREFIX."booking_rate_range` WHERE `ID`='{$rate_id}'");
			if ( !empty( $rate_info ) )
			{
				if ( $this -> getOne('Value',"`Key`='booking_range+desc+{$rate_info['From']}_{$rate_info['To']}'",'lang_keys') )
				{
					$this -> query("UPDATE `".RL_DBPREFIX."lang_keys` SET `Value`='{$value}' WHERE `Key`='booking_range+desc+{$rate_info['From']}_{$rate_info['To']}'");
				}
				else
				{
					$ins = "INSERT INTO `".RL_DBPREFIX."lang_keys` (`Key`,`Value`,`Module`,`Status`,`Plugin`) VALUES ('booking_range+desc+{$rate_info['From']}_{$rate_info['To']}','{$value}','common','active','booking')";
					$this -> query($ins);
				}
			}
		}
		$this -> getRateRange($listing_id, true);

		$dir = $tpl_settings['type'] == 'responsive_42' && !defined('REALM') ? 'responsive'. RL_DS : '';
		$tpl = RL_PLUGINS . 'booking' . RL_DS . $dir .'rate_range.tpl';
		$_response -> assign( "rate_range_obj", 'innerHTML', $this -> rlSmarty -> fetch( $tpl, null, null, false ) );
		$_response -> script("printMessage('notice', '{$GLOBALS['lang']['booking_edit_desc_notify']}');current_field=1;qtip_init();");

		return $_response;
	}

	/**
	* 
	**/
	function getRateRange($listing_id, $owner = false)
	{
		global $_response, $listing_data;

		$rate_range = $this -> fetch('*', array('Listing_ID' => $listing_id), "ORDER BY `From`", null, 'booking_rate_range');
		$range_regular_desc = $this -> getOne('Value', "`Key`='booking_range+regular+desc+{$listing_id}'", 'lang_keys');
		$this -> rlSmarty -> assign_by_ref('range_regular_desc', $range_regular_desc);

		foreach($rate_range as $rKey => $range)
		{
			$rate_desc = $this -> getOne('Value', "`Key`='booking_range+desc+{$range['From']}_{$range['To']}'", 'lang_keys');
			if ( !empty( $rate_desc ) )
			{
				$rate_range[$rKey]['desc'] = $rate_desc;
			}
		}

		if ( !defined('REALM') )
		{
			$GLOBALS['rlHook'] -> load('bookingPreRateRange');
		}

		if ( $owner === true )
		{
			$no_errors = $this -> getRow("SHOW COLUMNS FROM `" . RL_DBPREFIX . "listings` LIKE 'time_frame'");
			$select_rtf = '';

			if ( $no_errors )
			{
				$select_rtf = ",`time_frame`";
			}

			$def_price = $this -> getRow("SELECT `{$GLOBALS['config']['booking_price_field']}`{$select_rtf} FROM `". RL_DBPREFIX ."listings` WHERE `ID` = '{$listing_id}'");
			$expPrice = explode('|', $def_price[$GLOBALS['config']['booking_price_field']]);
			$price_cel = 0;

			$cur_time_frame = unserialize( $GLOBALS['config']['booking_time_frame'] );

			if ( $def_price['time_frame'] )
			{
				$this -> use_time_frame = true;

				switch( $def_price['time_frame'] )
				{
					case $cur_time_frame['day']:
						$price_cel = $expPrice[0];
					break;

					case $cur_time_frame['week']:
						$price_cel = $expPrice[0] / 7;
					break;

					case $cur_time_frame['month']:
						$price_cel = $expPrice[0] / date('t');
					break;

					case $cur_time_frame['year']:
						$price_cel = $expPrice[0] / 365;
					break;

					default:
						$this -> use_time_frame = false;
					break;
				}
				$this -> rlSmarty -> assign('use_time_frame', $this -> use_time_frame);
			}

			$adaptPrice['name'] = $GLOBALS['lang']['data_formats+name+'. $expPrice[1]] .' '. $this -> rlSmarty -> str2money($price_cel);
			$adaptPrice['currency'] = $GLOBALS['lang']['data_formats+name+'. $expPrice[1]];
			$adaptPrice['value'] = $price_cel;

			$this -> rlSmarty -> assign_by_ref('defPrice', $adaptPrice);
			$this -> rlSmarty -> assign_by_ref('rate_range', $rate_range);
		}
		else
		{
			$expPrice = explode('|', $listing_data[$GLOBALS['config']['booking_price_field']]);
			$price_cel = 0;

			$cur_time_frame = unserialize($GLOBALS['config']['booking_time_frame']);

			if ( $listing_data['time_frame'] )
			{
				$this -> use_time_frame = true;

				switch($listing_data['time_frame'])
				{
					case $cur_time_frame['day']:
						$price_cel = $expPrice[0];
					break;

					case $cur_time_frame['week']:
						$price_cel = $expPrice[0] / 7;
					break;

					case $cur_time_frame['month']:
						$price_cel = $expPrice[0] / date('t');
					break;

					case $cur_time_frame['year']:
						$price_cel = $expPrice[0] / 365;
					break;

					default:
						$this -> use_time_frame = false;
					break;
				}
				$this -> rlSmarty -> assign('use_time_frame', $this -> use_time_frame);
			}

			$adaptPrice['name'] = $GLOBALS['lang']['data_formats+name+'. $expPrice[1]] .' '. $this -> rlSmarty -> str2money($price_cel);
			$adaptPrice['currency'] = $GLOBALS['lang']['data_formats+name+'. $expPrice[1]];
			$adaptPrice['value'] = $price_cel;
			$this -> rlSmarty -> assign_by_ref('defPrice', $adaptPrice);

			if(!empty($rate_range))
			{
				$this -> rlSmarty -> assign_by_ref('rate_range', $rate_range);
				$usRange = array();

				foreach ($rate_range as $rKey => $range)
				{
					if ( $rate_range[$rKey]['Price'] != 0 )
					{
						$price = explode('|', $rate_range[$rKey]['Price']);
						$rate_range[$rKey]['Price'] = $GLOBALS['lang']['data_formats+name+'. $price[1]] .' '. $this -> rlSmarty -> str2money($price[0]);

						$JS .= "usRange[{$rKey}] = new Array();";
						$JS .= "usRange[{$rKey}][0] = '{$range['From']}';";
						$JS .= "usRange[{$rKey}][1] = '{$range['To']}';";
						$JS .= "usRange[{$rKey}][2] = '{$range['Price']}';";

						$usRange[$rKey]['from'] = $range['From'];
						$usRange[$rKey]['to'] = $range['To'];
					}
					else
					{
						$close .= "closeRange[{$rKey}] = new Array();";
						$close .= "closeRange[{$rKey}][0] = '{$range['From']}';";
						$close .= "closeRange[{$rKey}][1] = '{$range['To']}';";
					}
				}

				$this -> rateRanges = $usRange;

				$_response -> script('eval("'.$JS.'"); eval("'.$close.'");');
			}

			return $_response;
		}
	}

	/**
	* 
	**/
	function ajaxSaveRateRange($listing_id, $ranges, $mode)
	{
		global $_response, $lang, $tpl_settings;

		$listing_id = (int)$listing_id;
		$this -> rlValid -> sql($ranges);
		$count = 0;
		$index = 0;
		$key = 0;

		foreach($ranges as $range)
		{
			if ( $count % 4 == 0 )
			{
				$index++;
				$key = 0;
			}

			$adapt_range[$index][$key] = $range['value'];
			$count++;
			$key++;
		}

		$insert_range = "INSERT INTO `". RL_DBPREFIX ."booking_rate_range` (`Listing_ID`,`From`,`To`,`Price`) VALUES ";
		foreach($adapt_range as $key => $range)
		{
			$From = mktime(0, 0, 0, substr($range[0],3,2), substr($range[0],0,2), substr($range[0],6,4));
			$To = mktime(0, 0, 0, substr($range[1],3,2), substr($range[1],0,2), substr($range[1],6,4));
			$insert_range .= "('{$listing_id}', '{$From}', '{$To}', '{$range[2]}'), ";

			if ( !empty($range[3]) )
			{
				$this -> query("INSERT INTO `".RL_DBPREFIX."lang_keys` (`Key`,`Value`,`Module`,`Status`) VALUES ('booking_range+desc+{$From}_{$To}','{$range[3]}','common','active')");
			}
		}
		$insert_range = substr($insert_range, 0, -2) .';';
		$this -> query($insert_range);

		if ( !$mode )
		{
			$this -> getRateRange($listing_id, true);

			$dir = $tpl_settings['type'] == 'responsive_42' && !defined('REALM') ? 'responsive'. RL_DS : '';
			$tpl = RL_PLUGINS . 'booking' . RL_DS . $dir .'rate_range.tpl';
			$_response -> assign("rate_range_obj", 'innerHTML', $this -> rlSmarty -> fetch($tpl, null, null, false));
			$_response -> script("printMessage('notice', '{$lang['booking_rate_range_added']}');current_field=1;qtip_init();");
		}
		else
		{
			$_response -> script("bookingRateRangesList.reload();$('[name=item_submit]').val('{$lang['add']}');$('#ranges_form').resetForm();");
			$_response -> script("printMessage('notice', '{$lang['booking_rate_range_added']}');$('#ranges_action_add').slideUp('normal');");
		}

		return $_response;
	}

	/**
	* 
	**/
	function ajaxDeleteRateRange($rate_id = false, $mode = false)
	{
		global $_response, $lang, $page_info, $tpl_settings;

		$mode = in_array($page_info['Key'], array('add_listing', 'edit_listing')) ? true : $mode;

		$rate_id = (int)$rate_id;
		$rate_info = $this -> getRow("SELECT `From`,`To` FROM `". RL_DBPREFIX ."booking_rate_range` WHERE `ID`='{$rate_id}'");
		$this -> query("DELETE FROM `". RL_DBPREFIX ."booking_rate_range` WHERE `ID`='{$rate_id}'");
		$this -> query("DELETE FROM `". RL_DBPREFIX ."lang_keys` WHERE `Key`='booking_range+desc+{$rate_info['From']}_{$rate_info['To']}'");

		if ( $mode === false )
		{
			$listing_id = $_GET['id'] ? (int)$_GET['id'] : (int)$_GET['listing_id'];
			$this -> getRateRange($listing_id, true);

			$dir = $tpl_settings['type'] == 'responsive_42' && !defined('REALM') ? 'responsive'. RL_DS : '';
			$tpl = RL_PLUGINS . 'booking' . RL_DS . $dir .'rate_range.tpl';
			$_response -> assign("rate_range_obj", 'innerHTML', $this -> rlSmarty -> fetch($tpl, null, null, false));
			$_response -> script("printMessage('notice', '{$lang['booking_rate_range_removed']}');current_field=1;qtip_init();");
		}
		else
		{
			$_response -> script('$("#rrange_'. $rate_id .'").fadeOut("slow", function() { $("#rrange_'. $rate_id .'").remove(); });');
			$_response -> script("printMessage('notice', '{$lang['booking_rate_range_removed']}');bookingRateRangesList.reload();");
		}
		return $_response;
	}

	/**
	* 
	**/
	function ajaxDeleteRequestAP($id = false)
	{
		global $_response;

		/* get booking details id */
		$book_id = $this -> getOne('Book_ID', "`ID` = '{$id}'", 'booking_requests');

		/* remove request */
		$this -> query("DELETE FROM `". RL_DBPREFIX ."booking_requests` WHERE `ID` = '{$id}' LIMIT 1");

		/* remove book details */
		$this -> query("DELETE FROM `". RL_DBPREFIX ."listings_book` WHERE `ID` = '{$book_id}' LIMIT 1");

		$_response -> script("printMessage('notice', '{$GLOBALS['lang']['ext_booking_request_removed']}');bookingRequestsGrid.reload();");

		return $_response;
	}

	/**
	* 
	**/
	function ajaxDeleteRequest($id = false)
	{
		global $_response;

		if ( !$this -> getOne('ID', "`Renter_ID` = '{$_SESSION['id']}' AND `ID` = '{$id}'", 'booking_requests') )
		{
			return $_response;
		}

		/* get booking details id */
		$book_id = $this -> getOne('Book_ID', "`ID` = '{$id}'", 'booking_requests');

		/* remove request */
		$this -> query("DELETE FROM `". RL_DBPREFIX ."booking_requests` WHERE `ID` = '{$id}' LIMIT 1");

		/* remove book details */
		$this -> query("DELETE FROM `". RL_DBPREFIX ."listings_book` WHERE `ID` = '{$book_id}' LIMIT 1");

		$_response -> script("printMessage('notice', '{$GLOBALS['lang']['ext_booking_request_removed']}');removeRequest({$id});");

		return $_response;
	}

	/**
	* 
	**/
	function ajaxSaveBindingDays($listing_id = false, $form = false)
	{
		global $_response, $tpl_settings;

		foreach($form as $value)
		{
			if($value['name'] == 'in')
			{
				$checkin .= $value['value'] . ',';
			}
			else
			{
				$checkout .= $value['value'] . ',';
			}
		}

		$checkin = trim($checkin, ',');
		$checkout = trim($checkout, ',');
		$bind_info = $this -> getOne( 'ID', "`Listing_ID`='{$listing_id}'", 'booking_bindings' );

		if( $bind_info )
		{
			$this -> query("UPDATE `". RL_DBPREFIX ."booking_bindings` SET `Checkin`='{$checkin}', `Checkout`='{$checkout}' WHERE `ID`='{$bind_info}'");
		}
		else
		{
			$this -> query("INSERT INTO `". RL_DBPREFIX ."booking_bindings` (`Listing_ID`,`Checkin`,`Checkout`) VALUES ('{$listing_id}','{$checkin}','{$checkout}')");
		}

		$binding_days = $this -> fetch('*', array('Listing_ID'=>$listing_id, 'Status'=>'active'), null, null, 'booking_bindings', 'row');
		$this -> rlSmarty -> assign_by_ref('binding_days', $binding_days);

		$dir = $tpl_settings['type'] == 'responsive_42' && !defined('REALM') ? 'responsive'. RL_DS : '';
		$tpl = RL_PLUGINS . 'booking' . RL_DS . $dir .'binding_days.tpl';
		$_response -> assign( "bindings_obj", 'innerHTML', $this -> rlSmarty -> fetch( $tpl, null, null, false ) );

		$mess = $GLOBALS['lang']['booking_bindings_saved'];
		$_response -> script("printMessage('notice', '{$mess}')");
		$_response -> call("bind_edit()");

		return $_response;
	}

	/**
	* 
	**/
	function callFromKeywordSearch()
	{
		ob_start();
		debug_print_backtrace();
		$backtrace = ob_get_contents();
		ob_end_clean();

		return (strpos($backtrace, 'rlSearch->searchTest') !== false);
	}

	/**
	* hook: listingsModifyFieldSearch
	**/
	function modifyFieldSearch()
	{
		global $sql;

		if ( $this -> callFromKeywordSearch() ) return;

		$sql .= "IF(`BT1`.`Listing_ID` > 0, 1, 0) `Booking`, ";
	}

	/**
	* hook: listingsModifyJoinSearch
	**/
	function modifyJoinSearch()
	{
		global $sql, $data;

		if ( $this -> callFromKeywordSearch() ) return;

		// mktime($hour, $minute, $second, $month, $day, $year);
		$_SESSION['booking_availability'] = $data['check_availability'];
		$from = date_parse($data['check_availability']['from']);
		$to = date_parse($data['check_availability']['to']);

		$from = mktime(-1, 0, 0, $from['month'], $from['day'], $from['year'], 0);
		$to = mktime(-1, 0, 0, $to['month'], $to['day'], $to['year'], 0);
		//
		$from += 43200; //desc

		$sql .= "LEFT JOIN `".RL_DBPREFIX."listings_book` AS `BT1` ON `T1`.`ID` = `BT1`.`Listing_ID` AND `BT1`.`Status` <> 'refused' AND ";
		$sql .= " (({$from} BETWEEN `BT1`.`From` AND `BT1`.`To`) OR ({$to} BETWEEN `BT1`.`From` AND `BT1`.`To`)) ";
	}

	/**
	* hook: listingsModifyWhereSearch
	**/
	function modifyWhereSearch()
	{
		global $sql, $data, $page_info;

		if ( $this -> callFromKeywordSearch() ) return;

		if ( !empty($data['check_availability']['from']) )
		{
			$replace  ="AND UNIX_TIMESTAMP(`T1`.`check_availability`) >= UNIX_TIMESTAMP('{$data['check_availability']['from']}') ";
		}

		if ( !empty($data['check_availability']['to']) )
		{
			$replace .= "AND UNIX_TIMESTAMP(`T1`.`check_availability`) <= UNIX_TIMESTAMP('{$data['check_availability']['to']}') ";
		}

		$sql = str_replace($replace, " ", $sql);
		$sql = str_replace(", LOWER(`T1`.`check_availability`)", " ", $sql);

		if ( !empty($data['check_availability']['from']) || !empty($data['check_availability']['to']) )
		{
			if ( $page_info['Key'] == 'availability_listings' )
			{
				$sql = str_replace("AND `T3`.`Type` = '{$this->bookingType}'", "AND `T1`.`booking_module` = '1'", $sql);
			}
			else
			{
				$sql .= "AND `T1`.`booking_module` = '1' ";
			}
		}
	}

	/**
	* hook: listingsModifyGroupSearch
	**/
	function modifyGroupSearch()
	{
		global $sql;

		if ( $this -> callFromKeywordSearch() ) return;

		if ( strpos($sql, 'GROUP') === false )
		{
			$sql .= "HAVING `Booking` = '0' ";
		}
		else
		{
			$sql = str_replace('GROUP BY `T1`.`ID`', "GROUP BY `T1`.`ID` HAVING `Booking` = '0' ", $sql);
		}
	}

	/**
	* 
	**/
	function prepareBookingTab()
	{
		global $listing_data, $config, $rlXajax;

		if ( $listing_data['booking_module'] )
		{
			$rateRanges = $this -> getOne('ID', "`Listing_ID` = '{$listing_data['ID']}'", 'booking_rate_range');

			if ( !empty($rateRanges) || $listing_data['time_frame'] )
			{
				define('RL_DISPLAY_CALENDAR', 1);
			}
			else
			{
				define('RL_DISPLAY_CALENDAR', 0);
			}

			if ( defined('RL_DISPLAY_CALENDAR') && RL_DISPLAY_CALENDAR === 1 )
			{
				if ( $config['booking_binding_plans'] )
				{
					if ( in_array($listing_data['Plan_ID'], explode(',', $config['booking_plans'])) )
					{
						define('RL_DISPLAY_CALENDAR', 1);
					}
					else
					{
						define('RL_DISPLAY_CALENDAR', 0);
					}
				}
				else
				{
					define('RL_DISPLAY_CALENDAR', 1);
				}
			}
		}
		else
		{
			define('RL_DISPLAY_CALENDAR', 0);
		}

		if ( RL_DISPLAY_CALENDAR === 1 )
		{
			$this -> getRateRange((int)$listing_data['ID']);
			$this -> rlSmarty -> assign('fields', $this -> getBookingFields());

			$rlXajax -> registerFunction(array('getDates', $this, 'ajaxGetDates'));
			$rlXajax -> registerFunction(array('bookNow', $this, 'ajaxBookNow'));
		}
	}

	/**
	* 
	**/
	function saveRateRangesFromListing()
	{
		global $listing_id;

		if ( $listing_id )
		{
			$rates = $this -> rlValid -> xSql($_POST['b']);
			if ( !empty($rates) && $_POST['f']['booking_module'])
			{
				$insert_range = "INSERT INTO `". RL_DBPREFIX ."booking_rate_range` ( `Listing_ID`, `From`, `To`, `Price` ) VALUES ";
				foreach($rates as $key => $range)
				{
					if ( $range['from'] && $range['to'] && $range['price'] )
					{
						$from = mktime(0, 0, 0, substr($range['from'], 3, 2), substr($range['from'], 0, 2), substr($range['from'], 6, 4));
						$to = mktime(0, 0, 0, substr($range['to'], 3, 2), substr($range['to'], 0, 2), substr($range['to'], 6, 4));
						$price = (double)$range['price'];

						$insert_range .= "( '{$listing_id}', '{$from}', '{$to}', '{$price}' ),";
					}
				}
				$insert_range = rtrim($insert_range, ',');
				$this -> query($insert_range);
			}
		}
	}

	/**
	* 
	**/
	function uninstall()
	{
		global $config, $rlCache;

		$this -> query("DROP TABLE `". RL_DBPREFIX ."booking_rate_range`");
		$this -> query("DROP TABLE `". RL_DBPREFIX ."booking_fields`");
		$this -> query("DROP TABLE `". RL_DBPREFIX ."booking_requests`");
		$this -> query("DROP TABLE `". RL_DBPREFIX ."listings_book`");
		$this -> query("DROP TABLE `". RL_DBPREFIX ."booking_bindings`");

		$this -> query("DELETE FROM `". RL_DBPREFIX ."listing_groups` WHERE `Key` = 'booking_rates'");
		$this -> query("DELETE FROM `". RL_DBPREFIX ."listing_fields` WHERE `Key` = 'check_availability' OR `Key` = 'booking_module'");
		$this -> query("ALTER TABLE `". RL_DBPREFIX ."listings` DROP `booking_module`");

		// search booking fields
		list($availabilityID, $moduleID) = explode('|', $config['booking_fields_ids'], 2);

		$sql  = "SELECT `ID`, `Fields` FROM `". RL_DBPREFIX ."listing_relations` WHERE FIND_IN_SET('{$moduleID}', `Fields`) > 0";
		$fields = $this -> getAll($sql);
		if ( !empty($fields) )
		{
			$removeIds = array();
			foreach($fields as $key => $value)
			{
				$mass = explode(',', $value['Fields']);
				if ( count($mass) == 1 )
				{
					array_push($removeIds, $value['ID']);
				}
				else
				{
					$f_id = array_search($moduleID, $mass);
					unset($mass[$f_id]);

					$this -> query("UPDATE `". RL_DBPREFIX ."listing_relations` SET `Fields` = '". implode(',', $mass) ."' WHERE `ID` = '{$value['ID']}'");
				}
			}

			if ( !empty($removeIds) )
			{
				$this -> query("DELETE FROM `". RL_DBPREFIX ."listing_relations` WHERE `ID` IN (". implode(',', $removeIds) .")");
			}
		}

		// update cache
		$rlCache -> updateForms();
	}
}