<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLAUCTION.CLASS.PHP
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

class rlAuction extends reefless
{
	var $rlActions;

	var $calc;

	function rlAuction()
	{
		$this -> loadClass( 'Actions' );
		$this -> rlActions = &$GLOBALS['rlActions'];
	}

	function ajaxAddBid( $id = false, $rate = false )
	{
		global $_response, $account_info, $listing_data, $pages, $rlListingTypes, $rlListings, $rlSmarty, $tpl_settings;

		$this -> loadClass( 'Mail' );

		if ( !$id )
		{
			return $_response;
		}

		if( !defined( 'IS_LOGIN' ) )     	
		{
			$url = SEO_BASE . ($GLOBALS['config']['mod_rewrite'] ? $pages['login'] . '.html' : '?page=' . $pages['login']);
			$_response -> redirect( $url );
			
			return $_response;
		}

		if( !$GLOBALS['rlMail'] )
		{
			$this -> loadClass( 'Mail' );
		}

		$errors = false;

		if ( $rate < $listing_data['shc']['min_rate_bid'] )
		{
			$errors .= '<li>' . $GLOBALS['lang']['shc_rate_failed'] . '</li>';
		}

		if ( $account_info['ID'] == $listing_data['Account_ID'] )
		{
			$errors .= '<li>' . $GLOBALS['lang']['shc_add_item_owner_auction'] . '</li>';
		}

		$rate = (float)$rate;
		$id = (int)$id;

		if ( !$errors )
		{
			$sql = "SELECT MAX(`T1`.`Number`) AS `max`, MAX(`T1`.`Total`) AS `max_bid`, ";
			$sql .= "(SELECT `T2`.`Buyer_ID` FROM `" . RL_DBPREFIX . "shc_bids` AS `T2` WHERE `T2`.`ID` = MAX(`T1`.`ID`) LIMIT 1) AS `Buyer_ID` ";
			$sql .= "FROM `" . RL_DBPREFIX . "shc_bids` AS `T1` ";
			$sql .= "WHERE `Item_ID` = '{$id}' ";
			$sql .= "GROUP BY `T1`.`Item_ID` ";
			$sql .= "LIMIT 1";

			$last_bid_info = $this -> getRow( $sql );
			
			$sql = "";

			$number = (int)$last_bid_info['max'];
			$current_price = (float)$last_bid_info['max_bid'];
			$current_bid = $rate + (int)$listing_data['shc_bid_step'];

			$number++;
			$current_price = (float)$listing_data['shc_start_price'] + $rate;
			$current_price = round( $current_price, 2 );

			$insert = array(
					'Item_ID' => $id,
					'Dealer_ID' => (int)$listing_data['Account_ID'],
					'Buyer_ID' => (int)$account_info['ID'],
					'Number' => $number,
					'Total' => $rate,
					'Date' => 'NOW()'
				);

			if ( $this -> rlActions -> insertOne( $insert, 'shc_bids' ) )
			{
                /* increase total bids */
				$sql = "UPDATE `" . RL_DBPREFIX . "listings` SET `shc_total_bids` = `shc_total_bids` + 1, `shc_max_bid` = '{$rate}' WHERE `ID` = '{$id}' LIMIT 1";
				$this -> query( $sql );
				
				$message = $GLOBALS['lang']['shc_add_bid_success'];

				/* update count bidders */
				$bids = $this -> getBids( $id );
				$bidders = $this -> getUniqueBidders( $bids );

				/* get item details */
				$listing_type = $rlListingTypes -> types[$listing_data['Listing_type']];
				$listing_title = $rlListings -> getListingTitle( $listing_data['Category_ID'], $listing_data, $listing_type['Key'] );

				$listing_link = SEO_BASE;
				$listing_link .= $GLOBALS['config']['mod_rewrite'] ? $pages[$listing_type['Page_key']] . '/' . $listing_data['Cat_path'] . '/' . $rlSmarty -> str2path( $listing_title ) . '-' . $listing_data['ID'] . '.html' : '?page=' . $pages[$listing_type['Page_key']] . '&amp;id=' . $listing_data['ID'];

				/* send notification to previous member */
				if($last_bid_info['Buyer_ID'])
				{
					/* get buyer info */
					$sql = "SELECT `ID`, `First_name`, `Last_name`, `Username`, `Mail`, ";
					$sql .= "IF(`Last_name` <> '' AND `First_name` <> '', CONCAT(`First_name`, ' ', `Last_name`), `Username`) AS `Full_name` ";
					$sql .= "FROM `" . RL_DBPREFIX . "accounts` ";
					$sql .= "WHERE `ID` = '{$last_bid_info['Buyer_ID']}' LIMIT 1";

					$buyer_info = $this -> getRow( $sql );

					$increase_auction_bid = $GLOBALS['rlMail'] -> getEmailTemplate( 'increase_auction_bid' );

					$search = array('{bidder_name}', '{item}', '{your_last_bid}', '{current_bid}', '{date}', '{link}');
					$replacement = array($buyer_info['Full_name'], $listing_title, number_format($last_bid_info['max_bid'], 2), number_format($rate, 2), date('Y-m-d: H:i:s'), $listing_link);

					$increase_auction_bid['body'] = str_replace( $search, $replacement, $increase_auction_bid['body'] );
					$GLOBALS['rlMail'] -> send( $increase_auction_bid, $buyer_info['Mail'] );

					unset( $increase_auction_bid, $buyer_info, $search, $replacement );	
				}

				/* send notification to owner listing */
				$new_auction_bid = $GLOBALS['rlMail'] -> getEmailTemplate( 'new_auction_bid' );

				/* get buyer info */
				$sql = "SELECT `ID`, `First_name`, `Last_name`, `Username`, `Mail`, ";
				$sql .= "IF(`Last_name` <> '' AND `First_name` <> '', CONCAT(`First_name`, ' ', `Last_name`), `Username`) AS `Full_name` ";
				$sql .= "FROM `" . RL_DBPREFIX . "accounts` ";
				$sql .= "WHERE `ID` = '{$listing_data['Account_ID']}' LIMIT 1";

				$dealer_info = $this -> getRow( $sql );

				$search = array('{dealer_name}', '{item}', '{bidder}', '{current_bid}', '{date}', '{link}', '{start_price}');
				$replacement = array($dealer_info['Full_name'], $listing_title, $account_info['Full_name'], number_format($rate, 2) . ' ' .$GLOBALS['config']['system_currency'], date('Y-m-d: H:i:s'), $listing_link, number_format($listing_data['shc_start_price'], 2) . ' ' .$GLOBALS['config']['system_currency']);

				$new_auction_bid['body'] = str_replace( $search, $replacement, $new_auction_bid['body'] );
				$GLOBALS['rlMail'] -> send( $new_auction_bid, $dealer_info['Mail'] );

				unset( $new_auction_bid, $dealer_info );
			}

			/* refresh tab bid history */
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'bids', $this -> getBids( $listing_data['ID'] ) );
			$tpl = RL_PLUGINS . 'shoppingCart' . RL_DS . 'bids.tpl';
			$_response -> assign( "bid-history-list", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );

			$_response -> script("
					printMessage('notice', '{$message}');
					$('#rate_bid').val('');
					$('#total_bids').html('{$number}');
					$('#bh_bidders').html('{$bidders}');
					$('#bh_total_bids').html('" . count( $bids ) . "');
					$('#current_price').html('".number_format($rate, 2)."');
					$('#shc_min_bid').html('".str_replace("[total]", $current_bid, $GLOBALS['lang']['shc_min_bid'])."');
				");

			if($tpl_settings['type'] == 'responsive_42')
			{
				$_response -> script("$('#rate_bid').attr('placeholder', '".str_replace("[total]", $current_bid, $GLOBALS['lang']['shc_min_bid'])."');");	
			}			

			unset( $bids, $bidders, $number, $current_price );
		}
		else
		{
			$error_mes = '<ul>' . $errors . '</ul>';

			$_response -> script("
					$('#rate_bid').val('').focus();					
					printMessage('error', '{$error_mes}');
				");
		}

		return $_response;
	}

	function completeTransaction( $item_id = false, $plan_id = false, $account_id = false, $txn_id = null, $gateway = null, $total = false )
	{
		$this -> loadClass( 'Mail' );

		if( !$GLOBALS['rlCache'] )
		{
			$this -> loadClass( 'Cache' );
		}

		$txn_id = mysql_real_escape_string( $txn_id );
		$gateway = mysql_real_escape_string( $gateway );

		$item_id = (int)$item_id;
		$total = (float)$total;
		$account_id = (int)$account_id;

		if ( !$item_id || !$account_id )
		{
			return false;
		}

		$order_info = $this -> getAuctionInfo( $item_id, true, true );
		
		if ( $order_info )
		{
			$update = array(
				'fields' => array(	
						'pStatus' => 'paid',
						'Txn_ID' => $txn_id,
						'Pay_date' => 'NOW()'
					),
				'where' => array(
						'ID' => $item_id
					)
				);

			if ( $GLOBALS['rlActions'] -> updateOne( $update, 'shc_orders' ) )
			{
				/* save transaction details */
				$transaction = array(
					'Service' => 'auction',
					'Item_ID' => $item_id,
					'Account_ID' => $account_id,
					'Plan_ID' => 0,
					'Txn_ID' => $txn_id,
					'Total' => $total,
					'Gateway' => $gateway,
					'Date' => 'NOW()'
				);

				$GLOBALS['rlActions'] -> insertOne( $transaction, 'transactions' );

				/* send payment notification email to buyer	*/
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'shc_order_payment_accepted' );

				$order_info['Total'] = $GLOBALS['config']['system_currency_position'] == 'before' ? $GLOBALS['config']['system_currency'] . ' ' . number_format($order_info['Total'], 2) : $order_info['Total'] . ' ' . $GLOBALS['config']['system_currency']; 
				$order_info['Shipping_price'] = $GLOBALS['config']['system_currency_position'] == 'before' ? $GLOBALS['config']['system_currency'] . ' ' . number_format($order_info['Shipping_price'], 2) : $order_info['Shipping_price'] . ' ' . $GLOBALS['config']['system_currency'];

				$details = "<br />{$GLOBALS['lang']['shc_order_key']}: {$order_info['Order_key']}<br />
							{$GLOBALS['lang']['shc_item']}: {$order_info['title']}<br />
							{$GLOBALS['lang']['shc_dealer']}: {$order_info['dFull_name']}<br />
							{$GLOBALS['lang']['shc_total']}: {$order_info['Total']}<br />
							{$GLOBALS['lang']['shc_shipping_price']}: {$order_info['Shipping_price']}<br />
							{$GLOBALS['lang']['date']}: " . date( str_replace( array( 'b', '%' ), array( 'M', '' ), RL_DATE_FORMAT ) ) . "<br />
							{$GLOBALS['lang']['shc_txn_id']}: {$txn_id}<br />
							{$GLOBALS['lang']['shc_payment_method']}: {$gateway}<br />
							{$GLOBALS['lang']['shc_payment_status']}: {$GLOBALS['lang']['shc_paid']}
							{$GLOBALS['lang']['shc_shipping_status']}: {$GLOBALS['lang']['shc_' . $order_info['Shipping_status']]}<br /><br />
							{$GLOBALS['lang']['shc_shipping_method']}: {$GLOBALS['lang']['shc_shipping_' . $order_info['Shipping_method']]}<br />";

				$find = array( '{username}', '{order_key}', '{details}' );
				$replace = array( $order_info['bFull_name'], $order_info['Order_key'], $details );

				$mail_tpl['body'] = str_replace( $find, $replace, $mail_tpl['body'] );

				$GLOBALS['rlMail'] -> send( $mail_tpl, $order_info['bMail'] );

				/* send payment notification email to admin	*/
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'shc_order_payment_accepted_admin' );

				$details = "<br />{$GLOBALS['lang']['shc_order_key']}: {$order_info['Order_key']}<br />
							{$GLOBALS['lang']['shc_item']}: {$order_info['title']}<br />
							{$GLOBALS['lang']['shc_dealer']}: {$order_info['dFull_name']}<br />
							{$GLOBALS['lang']['shc_total']}: {$order_info['Total']} {$GLOBALS['config']['system_currency']}<br />                  
							{$GLOBALS['lang']['date']}: " . date( str_replace( array( 'b', '%' ), array( 'M', '' ), RL_DATE_FORMAT ) ) . "<br />
							{$GLOBALS['lang']['shc_txn_id']}: {$txn_id}<br />
							{$GLOBALS['lang']['shc_payment_method']}: {$gateway}<br />
							{$GLOBALS['lang']['shc_payment_status']}: {$GLOBALS['lang']['shc_paid']}<br /><br />";
				
				if($order_info['Shipping_method'])
				{
					$shipping = "<br />{$GLOBALS['lang']['shc_shipping_method']}: {$GLOBALS['lang']['shc_shipping_' . $order_info['Shipping_method']]}<br />
								{$GLOBALS['lang']['shc_shipping_price']}: {$order_info['Shipping_price']}<br />
								{$GLOBALS['lang']['shc_shipping_status']}: {$GLOBALS['lang']['shc_' . $order_info['Shipping_status']]}<br />
								{$GLOBALS['lang']['shc_country']}: {$order_info['Country']}<br />
								{$GLOBALS['lang']['shc_city']}: {$order_info['City']}<br />
								{$GLOBALS['lang']['shc_zip']}: {$order_info['Zip_code']}<br />
								{$GLOBALS['lang']['shc_address']}: {$order_info['Address']}<br />
								{$GLOBALS['lang']['shc_phone']}: {$order_info['Phone']}<br />>
								E-mail: {$order_info['Mail']}<br />
								{$GLOBALS['lang']['shc_name']}: {$order_info['Name']}<br />
								{$GLOBALS['lang']['shc_vat_no']}: {$order_info['Vat_no']}<br /
								{$GLOBALS['lang']['shc_comment']}: {$order_info['Comment']}<br /><br />";
				}
				else
				{
					$shipping = ' - ';
				}

				$find = array( '{username}', '{order_key}', '{details}', '{shipping}' );
				$replace = array( $order_info['dFull_name'], $order_info['Order_key'], $details, $shipping );

				$mail_tpl['body'] = str_replace( $find, $replace, $mail_tpl['body'] );
				$mail_tpl['subject'] = str_replace( '{order_key}', $order_info['Order_key'], $mail_tpl['subject'] );

				$GLOBALS['rlMail'] -> send( $mail_tpl, $GLOBALS['config']['notifications_email'] );

				/* send payment notification email to dealer */
				if ( $GLOBALS['config']['shc_method'] == 'multi' )
				{
					$GLOBALS['rlMail'] -> send( $mail_tpl, $order_info['dMail'] );
				}

				unset( $order_info );
			}
		}
	}

	function getBids( $item_id = false )
	{
		if ( !$item_id )
		{
			return false;
		}

		$sql = "SELECT `T1`.*, `T2`.`Username`, `T2`.`First_name`, `T2`.`Last_name` ";
		$sql .= "FROM `" . RL_DBPREFIX . "shc_bids` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T2` ON `T1`.`Buyer_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T1`.`Item_ID` = '{$item_id}' ";
		$sql .= "GROUP BY `T1`.`ID` ";
		$sql .= "ORDER BY `T1`.`ID` DESC";
		
		$bids = $this -> getAll( $sql );

		if ( $bids )
		{
			foreach( $bids as $bKey => $bVal )
			{
				$bids[$bKey]['bidder'] = $bVal['First_name'] && $bVal['Last_name'] ? $bVal['First_name'] . ' ' . $bVal['Last_name'] : $bVal['Username'];
			}
		}

		return $bids;
	}
	                                
	function getBidInfo( $id = false )
	{
	    if ( !$id )
		{
			return;
		}
		

		return false;
	}

	function getTimeLeft( &$listing, $output = false )
	{
		if ( !$listing )
		{
			return false;
		}

		$current_time = time();
		$shc_start_time = strtotime( $listing['shc_start_time'] ); 

		$auction_start = (int)$shc_start_time + ( $listing['shc_days'] * 86400 );

		$diff = $auction_start - $current_time;
		
		$days = floor( $diff / 86400 );
		$hours = floor( ( $diff % 86400 ) / 3600 );

		$date = (int)$days . 'd ' . (int)$hours . 'h';

		if($output)
		{
			$time_left = array(
					'text' => $date,
					'value' => (int)$diff
				);

			return $time_left;
		}

		return $date;		
	}

	function getUniqueBidders( &$bids )
	{
		if ( !$bids )
		{
			return 0;
		}
			
		$tmp = false;

		foreach( $bids as $bKey => $bVal )
		{
			$tmp[$bVal['Buyer_ID']]++;
		}

		return count( $tmp );
	}

	function getAuctionInfo( $item_id = false, $item_details = false, $buyer = false )
	{
		global $pages, $rlSmarty;

        if ( !$item_id )
		{
			return false;
		}

		$sql = "SELECT `T1`.*, `T2`.`Username` AS `dUsername`, `T2`.`Mail` AS `dMail`, `T2`.`Own_address` AS `dOwn_address`, `T4`.`Gateway`, ";
		$sql .= "IF(`T2`.`Last_name` <> '' AND `T2`.`First_name` <> '', CONCAT(`T2`.`First_name`, ' ', `T2`.`Last_name`), `T2`.`Username`) AS `dFull_name` ";
		
		if ( $buyer )
		{
			$sql .= ", `T3`.`Username` AS `bUsername`, `T3`.`Mail` AS `bMail`, `T3`.`Own_address` AS `bOwn_address`, ";
			$sql .= "IF(`T3`.`Last_name` <> '' AND `T3`.`First_name` <> '', CONCAT(`T3`.`First_name`, ' ', `T3`.`Last_name`), `T3`.`Username`) AS `bFull_name` ";
		}

		$sql .= "FROM `".RL_DBPREFIX."shc_orders` AS `T1` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."accounts` AS `T2` ON `T1`.`Dealer_ID` = `T2`.`ID` ";

		if ( $buyer )
		{
			$sql .= "LEFT JOIN `".RL_DBPREFIX."accounts` AS `T3` ON `T1`.`Buyer_ID` = `T3`.`ID` ";	
		}
		$sql .= "LEFT JOIN `".RL_DBPREFIX."transactions` AS `T4` ON `T1`.`Txn_ID` = `T4`.`Txn_ID` ";	
		$sql .= "WHERE `T1`.`ID` = '{$item_id}' ";
		$sql .= "LIMIT 1";

		$order_info = $this -> getRow( $sql );

		if ( $order_info )
		{
			$listing_data = $this->getOrderTitle( $order_info );

			$order_info['title'] = $listing_data['order_title'];
			$order_info['Main_photo'] = $listing_data['Main_photo'];
			$order_info['listing_link'] = SEO_BASE;
			$order_info['listing_link'] .= $GLOBALS['config']['mod_rewrite'] ? $pages[$listing_data['listing_type']['Page_key']] . '/' . $listing_data['Cat_path'] . '/' . $GLOBALS['rlSmarty'] -> str2path( $order_info['title'] ) . '-' . $listing_data['ID'] . '.html' : '?page=' . $pages[$listing_data['listing_type']['Page_key']] . '&amp;id=' . $listing_data['ID'];			

			if ( $item_details )
			{
				$order_info['item_details'] = $listing_data;
			}

			unset($listing_data);
			
			return $order_info;
		}

		return false;		
	}

	function getAuctionLiveInfo( $item_id = false, $item_details = false, $buyer = false )
	{
		global $pages, $rlSmarty, $rlListings, $rlListingTypes, $account_info;

        if ( !$item_id )
		{
			return false;
		}

		if ( !$rlListingTypes )
		{
			$this -> loadClass( 'ListingTypes' );
		}

		$sql = "SELECT `T1`.*, `T2`.`Path`, `T2`.`Type` AS `Listing_type`, `T2`.`Key` AS `Cat_key`, `T2`.`Type` AS `Cat_type`, `T2`.`Path` AS `Cat_path`, ";
		$sql .= "MAX(`T3`.`Total`) AS `total`, COUNT(`T3`.`ID`) AS `total_bids`, MAX(`T3`.`Date`) AS `last_date_bid`, ";
		$sql .= "`T4`.`Username` AS `dUsername`, `T4`.`Last_name` AS `dLast_name`, `T4`.`First_name` AS `dFirst_name`, `T4`.`Mail` AS `dMail`, `T4`.`Own_address` AS `dOwn_address`, ";
		$sql .= "(SELECT MAX(`Total`) FROM `" . RL_DBPREFIX . "shc_bids` WHERE `Item_ID` = `T1`.`ID` AND `Buyer_ID` = '{$account_info['ID']}' LIMIT 1) AS `my_total_price` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_bids` AS `T3` ON `T3`.`Item_ID` = `T1`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T4` ON `T1`.`Account_ID` = `T4`.`ID` ";
		$sql .= "WHERE `T1`.`ID` = '{$item_id}' ";
		$sql .= "LIMIT 1";

		$item_info = $this -> getRow( $sql );

		if ( $item_info )
		{
			if ( $item_details )
			{
				$listing_type = $GLOBALS['rlListingTypes'] -> types[$item_info['Listing_type']];
				$item_info['title'] = $rlListings -> getListingTitle( $item_info['Category_ID'], $item_info, $listing_type['Key'] ); 

				$item_info['listing_link'] = SEO_BASE;
				$item_info['listing_link'] .= $GLOBALS['config']['mod_rewrite'] ? $pages[$listing_type['Page_key']] . '/' . $item_info['Cat_path'] . '/' . $rlSmarty -> str2path( $item_info['title'] ) . '-' . $item_info['ID'] . '.html' : '?page=' . $pages[$listing_type['Page_key']] . '&amp;id=' . $item_info['ID'];
				
				$time_left = $this -> getTimeLeft( $item_info, true );
				$item_info['time_left'] = $time_left['value'] > 0 ? $time_left['text'] : $GLOBALS['lang']['shc_closed'];
			}

			return $item_info;
		}

		return false;		
	}

	function getOrderTitle( &$auction_info )
	{
		global $rlListingTypes, $rlListings;

		if ( !$auction_info )
		{
			return null;
		}

		if( !$GLOBALS['rlListingTypes'] )
		{
			$this -> loadClass( 'ListingTypes' );
		}

		$sql = "SELECT `T1`.*, `T2`.`Path`, `T2`.`Type` AS `Listing_type`, `T2`.`Key` AS `Cat_key`, `T2`.`Type` AS `Cat_type`, `T2`.`Path` AS `Cat_path` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T1`.`ID` = '{$auction_info['Item_ID']}' ";
		$sql .= "LIMIT 1";

		$listing_data = $this -> getRow( $sql );

		$listing_type = $GLOBALS['rlListingTypes'] -> types[$listing_data['Listing_type']];

		$listing_title = $GLOBALS['rlListings'] -> getListingTitle( $listing_data['Category_ID'], $listing_data, $listing_type['Key'] );		
		$listing_data['order_title'] = $listing_title . ' (#: ' . $listing_data['ID'] . ')';
		$listing_data['listing_type'] = $listing_type; 

		return $listing_data;
	}

	function getMyBids( $item_id = false )
	{
		global $account_info;

		if ( !$item_id )
		{
			return;
		}

		$sql = "SELECT * FROM `" . RL_DBPREFIX . "shc_bids` WHERE `Item_ID` = '{$item_id}' AND `Buyer_ID` = '{$account_info['ID']}' ORDER BY `ID` DESC";
		$bids = $this -> getAll( $sql );
		
		if ( $bids )
		{
			return $bids;
		}

		return false;
	}

	function getMyAuctions( $order = 'Date', $order_type = 'asc', $start = 0, $limit = false )
	{               
		global $account_info, $rlListings;
		
		$start = $start > 1 ? ($start - 1) * $limit : 0;

		$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.* ";
		$sql .= "FROM `" . RL_DBPREFIX . "shc_orders` AS `T1` ";
		$sql .= "WHERE `T1`.`Buyer_ID` = '{$account_info['ID']}' AND `T1`.`Type` = 'auction' ";

		if ( $order )
		{     
			$sql .= "ORDER BY `T1`.`{$order}` " . strtoupper( $order_type ) . " ";
		}
		else
		{
			$sql .= "ORDER BY `T1`.`Date` DESC ";
		}

		$sql .= "LIMIT {$start},{$limit}";

		$auctions = $this -> getAll( $sql );

		if ( empty( $auctions ) ) return false;

		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this -> calc = $calc['calc'];

		foreach ( $auctions as $key => $value )
		{
			$auctions[$key]['item_details'] = $rlListings -> getListing( $value['Item_ID'], true );
		}

		return $auctions;
	}

	function getNotWonAuctions( $order = 'Date', $order_type = 'asc', $start = 0, $limit = false, $live = false )
	{
		global $account_info, $rlListings, $rlListingTypes, $rlSmarty, $pages;

		if ( !$rlListingTypes )
		{
			$this -> loadClass( 'ListingTypes' );
		}

		$start = $start > 1 ? ($start - 1) * $limit : 0;
                      
		$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.*, `T2`.`Path`, `T2`.`Type` AS `Listing_type`, `T2`.`Key` AS `Cat_key`, `T2`.`Type` AS `Cat_type`, `T2`.`Path` AS `Cat_path`, ";
		$sql .= "MAX(`T5`.`Total`) AS `total`, COUNT(`T5`.`ID`) AS `total_bids`, MAX(`T5`.`Date`) AS `last_date_bid`, ";
		$sql .= "(SELECT COUNT(`T3`.`ID`) FROM `" . RL_DBPREFIX . "shc_bids` AS `T3` WHERE `T3`.`Item_ID` = `T1`.`ID` AND `T3`.`Buyer_ID` = '{$account_info['ID']}' LIMIT 1) AS `my_total_bids`, ";
		$sql .= "(SELECT MAX(`T6`.`Total`) FROM `" . RL_DBPREFIX . "shc_bids` AS `T6` WHERE `T6`.`Item_ID` = `T1`.`ID` AND `T6`.`Buyer_ID` = '{$account_info['ID']}' LIMIT 1) AS `my_total_price` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_bids` AS `T5` ON `T1`.`ID` = `T5`.`Item_ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_orders` AS `T4` ON `T1`.`ID` = `T4`.`Item_ID` AND `T4`.`Type` = 'auction' ";
		$sql .= "WHERE (`T4`.`Item_ID` IS NULL OR `T4`.`Item_ID` = '') ";
		$sql .= "AND (SELECT COUNT(`T3`.`ID`) FROM `" . RL_DBPREFIX . "shc_bids` AS `T3` WHERE `T3`.`Item_ID` = `T1`.`ID` AND `T3`.`Buyer_ID` = '{$account_info['ID']}' LIMIT 1) > 0 ";
		if ( $live )
		{
			$sql .= "AND (`T1`.`shc_auction_status` <> 'closed' AND  TIMESTAMPDIFF(HOUR, `T1`.`shc_start_time`, NOW()) < `T1`.`shc_days` * 24) ";
		}
		else
		{
			$sql .= "AND (`T1`.`shc_auction_status` = 'closed' OR TIMESTAMPDIFF(HOUR, `T1`.`shc_start_time`, NOW()) >= `T1`.`shc_days` * 24) ";
		}
		$sql .= "GROUP BY `T1`.`ID` ";
		
		if ( $order )
		{     
			$sql .= "ORDER BY `T1`.`{$order}` " . strtoupper( $order_type ) . " ";
		}
		else
		{
			$sql .= "ORDER BY `T1`.`Date` DESC ";
		}

		$sql .= "LIMIT {$start},{$limit}";

		$auctions = $this -> getAll( $sql );
		
		if ( empty( $auctions ) ) return false;

		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$this -> calc = $calc['calc'];

		foreach ( $auctions as $key => $value )
		{
			$listing_type = $GLOBALS['rlListingTypes'] -> types[$value['Listing_type']];

			$auctions[$key]['listing_title'] = $rlListings -> getListingTitle( $value['Category_ID'], $value, $listing_type['Key'] );

			$auctions[$key]['listing_link'] = SEO_BASE;
			$auctions[$key]['listing_link'] .= $GLOBALS['config']['mod_rewrite'] ? $pages[$listing_type['Page_key']] . '/' . $value['Cat_path'] . '/' . $rlSmarty -> str2path( $auctions[$key]['listing_title'] ) . '-' . $value['ID'] . '.html' : '?page=' . $pages[$listing_type['Page_key']] . '&amp;id=' . $value['ID'];
		}

		return $auctions;
	}

	function addShippingInfo( $data = false, &$auction_info )
	{
		if ( !$data )
		{
			return false;
		}
		
		$update = array(
				'fields' => array(
						'Weight' => (float)$data['Weight'],
						'Shipping_method' => $data['method'],
						'Country' => $data['country'],
						'Zip_code' => $data['zip'],
						'City' => $data['city'],
						'Vat_no' => $data['vat_no'],
						'Address' => $data['address'],
						'Name' => $data['name'],
						'Mail' => $data['email'],
						'Phone' => $data['phone'], 
						'Comment' => $data['comment']
					),
				'where' => array(
						'ID' => $auction_info['ID']
					)
			);

		if($data['method'] == 'ups')
		{
			$update['fields']['Package_type'] = $GLOBALS['config']['shc_method'] == 'single' ? $GLOBALS['config']['shc_ups_package_types'] : $dealer_info['shc_ups_package_type'];
			$update['fields']['Pickup_method'] = $GLOBALS['config']['shc_method'] == 'single' ? $GLOBALS['config']['shc_ups_pickup_methods'] : $dealer_info['shc_ups_pickup_method'];
			$update['fields']['UPSService'] = $data['ups_service'];
		}

		$action = $this -> rlActions -> updateOne( $update, 'shc_orders' );
		unset( $update, $data );

		if ( $action )
		{
			return true;
		}

		return false;
	}

	function ajaxRenewAuction( $item_id = false )
	{
		global $_response, $account_info, $rlMail, $rlListings, $rlListingTypes, $pages;

		if ( !$item_id )
		{
			return $_response;
		}

		if ( !$rlMail )
		{
			$this -> loadClass( 'Mail' );
		}

		$item_id = (int)$item_id;

		$sql = "SELECT * FROM `".RL_DBPREFIX."listings` WHERE `ID` = '{$item_id}' AND `shc_auction_status` = 'closed' LIMIT 1";
		$item_info = $this -> getRow( $sql );

		if ( $item_info )
		{
			/* update auction item */
			$update = array(
				'fields' => array(
						'shc_start_time'  => 'NOW()',	
						'shc_end_time' => '0000-00-00 00:00:00',	
						'shc_auction_status' => 'active',
						'shc_total_bids' => 0,
						'shc_max_bid' => 0,
						'shc_quantity' => 1,
						'shc_auction_won' => ''
					),	
				'where' => array(
						'ID' => $item_id
					)
			);

			$action = $this -> rlActions -> updateOne( $update, 'listings' );

			/* send notification to previous bidders */
			$sql = "SELECT `T1`.*, `T2`.`Mail`, ";
			$sql .= "IF(`T2`.`Last_name` <> '' AND `T2`.`First_name` <> '', CONCAT(`T2`.`First_name`, ' ', `T2`.`Last_name`), `T2`.`Username`) AS `Full_name` ";
			$sql .= "FROM `" . RL_DBPREFIX . "shc_bids` AS `T1` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T2` ON `T1`.`Buyer_ID` = `T2`.`ID` ";
			$sql .= "WHERE `T1`.`Item_ID` = '{$item_id}' ";
			$sql .= "GROUP BY `T1`.`Buyer_ID` ";
			
			$bidders = $this -> getAll( $sql );

			if ( $bidders )
			{
				if ( !$rlListingTypes )
				{
					$this -> loadClass( 'ListingTypes' );
				}

				$listing_type = $GLOBALS['rlListingTypes'] -> types[$listing_data['Listing_type']];
				$listing_title = $rlListings -> getListingTitle( $item_info['Category_ID'], $item_info, $listing_type['Key'] );	
				
				$listing_link = SEO_BASE;
				$listing_link .= $GLOBALS['config']['mod_rewrite'] ? $pages[$listing_type['Page_key']] . '/' . $item_info['Cat_path'] . '/' . $GLOBALS['rlSmarty'] -> str2path( $listing_title ) . '-' . $item_info['ID'] . '.html' : '?page=' . $pages[$listing_type['Page_key']] . '&amp;id=' . $item_info['ID'];

				/* delete old bids */
				$sql = "DELETE FROM `".RL_DBPREFIX."shc_bids` WHERE `Item_ID` = '{$item_id}' ";
				$this -> query( $sql );
				
				$renew_auction_bidder = $GLOBALS['rlMail'] -> getEmailTemplate( 'renew_auction_bidder' );

				foreach( $bidders as $bKey => $bVal )
				{
				 	$copy_renew_auction_bidder = $renew_auction_bidder;
					
					$search = array( '{username}', '{item}', '{start_price}', '{days}', '{date}', '{link}' );
					$replacement = array( $bVal['Full_name'], $listing_title, number_format($item_info['shc_start_price'], 2), $item_info['shc_days'], date( 'Y-m-d: H:i:s' ), $listing_link );

					$copy_renew_auction_bidder['body'] = str_replace( $search, $replacement, $copy_renew_auction_bidder['body'] );
					$GLOBALS['rlMail'] -> send( $copy_renew_auction_bidder, $bVal['Mail'] );

					unset( $copy_renew_auction_bidder );
				}
			}

			if ( $action )
			{
				$_response -> script("
						$('#{$item_id}-renew_auction').remove();
						printMessage('notice', '{$GLOBALS['lang']['shc_renew_auction_success']}');
					");
			}
			else
			{
				$_response -> script("
						printMessage('error', '{$GLOBALS['lang']['shc_renew_auction_failed']}');
					");
			}
		}

		return $_response;
	}

	function ajaxDeleteBid( $id = false )
	{
		global $_response;

		if( !$id )
		{
			return $_response;
		}

		$sql = "DELETE FROM `" . RL_DBPREFIX . "shc_bids` WHERE `ID` = '{$id}' LIMIT 1";

		if ( $this -> query( $sql ) )
		{  
			$_response -> script("
					auctionBidsGrid.reload();
					printMessage('notice', '{$GLOBALS['lang']['shc_bid_deleted_success']}');
				");
		}

		return $_response;
	}

	function setAutomaticallyRate()	
	{
		global $rlListingTypes, $rlListings;

        if ( !$GLOBALS['config']['shc_auto_rate'] )
		{
			return;
		}

		$sql = "SELECT `T1`.*, `T3`.`Path`, `T3`.`Type` AS `Listing_type`, `T3`.`Key` AS `Cat_key`, `T3`.`Type` AS `Cat_type`, `T3`.`Path` AS `Cat_path`, ";
		$sql .= "MAX(`T2`.`Total`) AS `max_bid`, MAX(`T2`,`Date`) AS `max_date`, COUNT(`T2`.`ID`) AS `total_bids`, MAX(`T2`.`ID`) AS `last_bid` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_bids` AS `T2` ON `T1`.`ID` = `T2`.`Item_ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "WHERE `T1`.`shc_auction_status` <> 'closed' AND TIMESTAMPDIFF(HOUR, `T1`.`shc_start_time`, UNIX_TIMESTAMP(NOW())) > `T1`.`shc_days` * 24 ";
		$sql .= "AND TIMESTAMPDIFF(HOUR, `T2`.`Date`, UNIX_TIMESTAMP(NOW())) > {$GLOBALS['config']['shc_auto_rate_period']} ";
		$sql .= "GROUP BY `T1`.`ID` ";
		$sql .= "ORDER BY `T2`.`Date` DESC ";
		$sql .= "LIMIT {$GLOBALS['config']['listings_number']}";

		$items = $this -> getAll( $sql );
		
		if ( $items ) 
		{
			$increase_auction_bid = $rlMail -> getEmailTemplate( 'increase_auction_bid_auto' );

			foreach( $items as $key => $val )
			{
				$current_bid = $val['max_bid'];
				$new_bid  = (float)$val['max_bid'] + (float)$val['shc_bid_step'];
				
				$insert = array(
						'Item_ID' => $val['ID'],
						'Dealer_ID' => $val['Account_ID'],
						'Number' => $val['total_bids'] + 1,
						'Total' => $new_bid,
						'Date' => 'NOW()'
					);

				if ( $this -> rlActions -> insertOne( $insert, 'shc_bids' ) )
				{
					$last_bid_info = $this -> fetch( '*', array( 'ID' => $val['ID'] ), false, 1, 'shc_bids', 'row' );

					if ( $last_bid_info['Byuer_ID'] )
					{ 
						/* get buyer info */
						$buyer_info = $this -> fetch( array( 'ID', 'Username', 'Last_name', 'First_name', 'Mail' ), array( 'ID' => $last_bid_info['Byuer_ID'] ), null, 1, 'accounts', 'row' );

						/* get item details */
						$listing_type = $rlListingTypes -> types[$value['Listing_type']];
						$listing_title = $rlListings -> getListingTitle( $value['Category_ID'], $value, $listing_type['Key'] );

						$listing_link = SEO_BASE;
						$listing_link .= $GLOBALS['config']['mod_rewrite'] ? $pages[$listing_data['listing_type']['Page_key']] . '/' . $value['Cat_path'] . '/' . $rlSmarty -> str2path( $auctions[$key]['listing_title'] ) . '-' . $value['ID'] . '.html' : '?page=' . $pages[$listing_data['listing_type']['Page_key']] . '&amp;id=' . $value['ID'];

						/* send notification to winner */
						$copy_increase_auction_bid = $increase_auction_bid;

						$search = array( '{item}', '{your_last_bid}', '{current_bid}', '{date}', '{link}' );
						$replacement = array( $listing_title, number_format($val['max_bid'], 2), number_format($new_bid, 2), date( 'Y-m-d: H:i:s' ), $listing_link );

						$copy_increase_auction_bid['body'] = str_replace( $search, $replacement, $copy_increase_auction_bid['body'] );
						$rlMail -> send( $copy_increase_auction_bid, $buyer_info['Mail'] );

						unset( $copy_increase_auction_bid, $buyer_info, $listing_title, $listing_link );						
					}
				}
			}				
		}

		unset($items);
	}

	function closeExipredItems()
	{
		global $rlMail, $rlListings, $rlListingTypes, $pages, $rlSmarty;

		if ( !$GLOBALS['rlListingTypes'] )
		{
			$this -> loadClass( 'ListingTypes' );
		}
		
		$sql = "SELECT `T1`.*, ";
		$sql .= "IF(TIMESTAMPDIFF(HOUR, `T1`.`shc_start_time`, NOW()) > `T1`.`shc_days` * 24, '1', '0') `expired`, ";
		$sql .= "COUNT(`T2`.`ID`) AS `total_bids`, MAX(`T2`.`Total`) AS `max_bid`, ";
		$sql .= "(SELECT `T5`.`Buyer_ID` FROM `" . RL_DBPREFIX . "shc_bids` AS `T5` WHERE `T5`.`ID` = MAX(`T2`.`ID`) LIMIT 1) AS `Buyer_ID`, ";
		$sql .= "MAX(`T2`.`ID`) AS `Bid_ID`, ";
		$sql .= "IF(`T3`.`Last_name` <> '' AND `T3`.`First_name` <> '', CONCAT(`T3`.`First_name`, ' ', `T3`.`Last_name`), `T3`.`Username`) AS `dFull_name`, `T3`.`Mail` AS `dMail`, ";
		$sql .= "`T4`.`Path` AS `Cat_path`, `T4`.`Key` AS `Cat_key`, `T4`.`Type` AS `Listing_type`, `T4`.`Type` AS `Cat_type` ";

		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_bids` AS `T2` ON `T1`.`ID` = `T2`.`Item_ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T3` ON `T1`.`Account_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";

	    $sql .= "WHERE `T1`.`shc_auction_status` <> 'closed' AND `T1`.`shc_mode` = 'auction' ";
		$sql .= "GROUP BY `T1`.`ID` ";
		$sql .= "ORDER BY `T1`.`ID` DESC LIMIT {$GLOBALS['config']['listings_number']}";

		$items = $this -> getAll( $sql );

		if ( $items ) 
		{
			$auction_close_dealer = $GLOBALS['rlMail'] -> getEmailTemplate( 'cron_auction_close_dealer' );
			$auction_close_dealer_ww = $GLOBALS['rlMail'] -> getEmailTemplate( 'cron_auction_close_dealer_ww' );
			$auction_winner = $GLOBALS['rlMail'] -> getEmailTemplate( 'cron_auction_winner' );

		 	foreach( $items as $key => $value )
			{
				$winner = false;

				/* get item titte */
				$listing_type = $GLOBALS['rlListingTypes'] -> types[$value['Listing_type']];
				$value['title'] = $GLOBALS['rlListings'] -> getListingTitle( $value['Category_ID'], $value, $listing_type['Key'] );	
				
				$listing_link = SEO_BASE;
				$listing_link .= $GLOBALS['config']['mod_rewrite'] ? $pages[$listing_type['Page_key']] . '/' . $value['Cat_path'] . '/' . $rlSmarty -> str2path( $value['title'] ) . '-' . $value['ID'] . '.html' : '?page=' . $pages[$listing_type['Page_key']] . '&amp;id=' . $value['ID'];

				if ( $value['expired'] )
				{
					/* close auction */
					$sql = "UPDATE `" . RL_DBPREFIX . "listings` SET `shc_auction_status` = 'closed', `shc_end_time` = NOW() WHERE `ID` = '{$value['ID']}' LIMIT 1";
					$this -> query( $sql );

					$sql = "SELECT * FROM `" . RL_DBPREFIX . "shc_bids` WHERE `ID` = '{$value['Bid_ID']}' LIMIT 1";
					$bid_info = $this -> getRow( $sql );

					/* define winner */
                    if ( $value['total_bids'] > 0 && $value['shc_reserved_price'] <= $value['max_bid'] )
					{
						$winner = true;

						/* get buyer info */
						$sql = "SELECT `ID`, `First_name`, `Last_name`, `Username`, `Mail`, ";
						$sql .= "IF(`Last_name` <> '' AND `First_name` <> '', CONCAT(`First_name`, ' ', `Last_name`), `Username`) AS `Full_name` ";
						$sql .= "FROM `" . RL_DBPREFIX . "accounts` ";
						$sql .= "WHERE `ID` = '{$value['Buyer_ID']}' LIMIT 1";

						$buyer_info = $this -> getRow( $sql );

						$insert = array(
								'Order_key' => $this -> generateHash( 8, 'upper' ) . '-D' . (int)$value['Account_ID'],
								'Type' => 'auction',
								'Item_ID' => (int)$value['ID'],
								'Txn_ID' => $this -> generateHash( 8, 'upper' ),
								'Dealer_ID' => (int)$value['Account_ID'],
								'Buyer_ID' => (int)$bid_info['Buyer_ID'],
								'Total' => (float)$value['max_bid'],
								'Date' => 'NOW()'
							);

						if ( $this -> rlActions -> insertOne( $insert, 'shc_orders' ) )
						{			
							/* set winner */
							$sql = "UPDATE `" . RL_DBPREFIX . "listings` SET `shc_auction_won` = '{$value['Buyer_ID']}' WHERE `ID` = '{$value['ID']}' LIMIT 1";
							$this -> query( $sql );
				
						 	/* send notification to winner */
							$copy_auction_winner = $auction_winner;

							$search = array( '{username}', '{owner}', '{item}', '{price}', '{date}', '{link}' );
							$replacement = array( $buyer_info['Full_name'], $value['dFull_name'], $value['title'], number_format($value['max_bid'], 2), date( 'Y-m-d H:i:s' ), $listing_link );

							$copy_auction_winner['body'] = str_replace( $search, $replacement, $copy_auction_winner['body'] );
							$GLOBALS['rlMail'] -> send( $copy_auction_winner, $buyer_info['Mail'] );
							unset( $copy_auction_winner, $search, $replacement );
						}
					}
						
					$details = "
{$GLOBALS['lang']['shc_item']}: {$value['title']}<br />
{$GLOBALS['lang']['shc_total']}: ".number_format($value['max_bid'], 2)." {$GLOBALS['config']['system_currency']}<br />
{$GLOBALS['lang']['shc_bids']}: {$value['total_bids']}<br />
{$GLOBALS['lang']['date']}: " . date('Y-m-d H:i:s') . "<br />
";					

					/* send notification to dealer */
					if ( $winner )
					{
						$copy_auction_close_dealer_ww = $auction_close_dealer_ww;

						$search = array('{dealer_name}', '{buyer}', '{details}');
						$replacement = array($value['dFull_name'], $buyer_info['Full_name'], $details);

						$copy_auction_close_dealer_ww['body'] = str_replace($search, $replacement, $copy_auction_close_dealer_ww['body']);
						$GLOBALS['rlMail'] -> send( $copy_auction_close_dealer_ww, $value['dMail'] );
						unset( $copy_auction_close_dealer_ww, $search, $replacement );
					}
					else
					{
						/* close without winner */
						$copy_auction_close_dealer = $auction_close_dealer;

						$search = array('{dealer_name}', '{details}');
						$replacement = array($value['dFull_name'], $details);

						$copy_auction_close_dealer['body'] = str_replace($search, $replacement, $copy_auction_close_dealer['body']);
						$GLOBALS['rlMail'] -> send( $copy_auction_close_dealer, $value['dMail'] );
						unset( $copy_auction_close_dealer, $search, $replacement );
					}					
				}

				unset( $buyer_info );
			}				
		}
	}
	
	function getSteps( $output = false )
	{
		global $rlSmarty;

		$steps = array(
				'cart' => array(
						'name' => $GLOBALS['lang']['shc_step_cart'],
						'caption' => true
					),
				'shipping' => array(
						'name' => $GLOBALS['lang']['shc_step_shipping'],
						'caption' => true, 
						'path' => 'shipping'
					),
				'confirmation' => array(
						'name' => $GLOBALS['lang']['shc_step_confirmation'],
						'caption' => true, 
						'path' => 'confirmation'
					),
				'checkout' => array(
						'name' => $GLOBALS['lang']['shc_step_checkout'],
						'caption' => true, 
						'path' => 'checkout'	
					),
				'done' => array(
						'name' => $GLOBALS['lang']['shc_step_done'], 
						'path' => 'done'
					),
			);

		if ( $output )
		{
			$rlSmarty -> assign_by_ref( 'shc_auction_steps', $steps );
			return;
		}

		return $steps;
	}
}

