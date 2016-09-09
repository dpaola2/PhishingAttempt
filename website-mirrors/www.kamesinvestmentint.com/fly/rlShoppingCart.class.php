<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLSHOPPINGCART.CLASS.PHP
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

class rlShoppingCart extends reefless 
{
	var $rlActions;
	var $payment_plugins_hook;
	var $payment_plugins;

	/**
	* cart list items DOM tpl file
	**/
	var $cart_list_dom = 'my_cart_block.tpl';

	function rlShoppingCart()
	{
		global $tpl_settings;

		if ( $tpl_settings['type'] == 'responsive_42' ) {
			$this -> cart_list_dom = 'cart_items_responsive_42.tpl';
		}

		$this -> loadClass( 'Actions' );
		$this -> loadClass( 'Auction', false, 'shoppingCart' );

		$this -> rlActions = &$GLOBALS['rlActions'];
	}

	function install()
	{
		global $rlCache;

		/* create tables */
		$this -> query( "DROP TABLE IF EXISTS `" . RL_DBPREFIX . "shc_orders`;" );
		$sql = "CREATE TABLE `" . RL_DBPREFIX . "shc_orders` (
			`ID` int(11) NOT NULL auto_increment,
			`Type` enum('shop','auction') NOT NULL default 'shop',
			`Item_ID` int(11) NOT NULL default '0',
			`Txn_ID` varchar(50) NOT NULL default '',
			`Order_key` varchar(50) NOT NULL default '',
			`Total`  double NOT NULL default '0',
			`Dealer_ID`  int(11) NOT NULL default '0',
			`Buyer_ID` int(11) NOT NULL default '0',  
			`Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`Pay_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`pStatus` enum('paid','unpaid','pending') NOT NULL default 'unpaid',
			`Shipping_status` enum('pending','processing','shipped','declined','open','delivered') NOT NULL default 'pending',
			`Shipping_price` double NOT NULL default '0',  
			`Shipping_method` varchar(30) NOT NULL default '',
			`Weight`  double NOT NULL default '0',
			`UPSService`  varchar(100) NOT NULL default '',
			`Package_type`  varchar(50) NOT NULL default '',
			`Pickup_method`  varchar(50) NOT NULL default '',
			`Length`  int(4) NOT NULL default '0',
			`Width`  int(4) NOT NULL default '0',
			`Height`  int(4) NOT NULL default '0',
			`Country` varchar(50) NOT NULL default '',
			`State` varchar(50) NOT NULL default '',
			`Zip_code` varchar(10) NOT NULL default '',
			`City` varchar(50) NOT NULL default '',
			`Address` varchar(255) NOT NULL default '',
			`Name` varchar(50) NOT NULL default '',
			`Mail` varchar(70) NOT NULL default '',
			`Phone` varchar(50) NOT NULL default '',
			`Vat_no` varchar(50) NOT NULL default '',
			`Comment` text NOT NULL,
		  PRIMARY KEY (`ID`)
		) DEFAULT CHARSET=utf8";

		$this -> query( $sql );

		$this -> query( "DROP TABLE IF EXISTS `" . RL_DBPREFIX . "shc_order_details`;" );
		$sql = "CREATE TABLE `" . RL_DBPREFIX . "shc_order_details` (
			`ID` int(11) NOT NULL auto_increment,
			`Order_key` varchar(50) NOT NULL default '',
			`Order_ID` int(11) NOT NULL default '0',
			`Dealer_ID` int(11) NOT NULL default '0',
			`Buyer_ID` int(11) NOT NULL default '0',
			`Item_ID` int(11) NOT NULL default '0',
			`Item` varchar(255) NOT NULL default '',
			`Price` double NOT NULL default '0',
			`Delivery` double NOT NULL default '0',
			`Quantity` int(11) NOT NULL default '1',
			`Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`Status` enum('active','completed') NOT NULL default 'active', 
		  PRIMARY KEY (`ID`)
		) DEFAULT CHARSET=utf8";

		$this -> query( $sql );

		$this -> query( "DROP TABLE IF EXISTS `" . RL_DBPREFIX . "shc_bids`;" );
		$sql = "CREATE TABLE `" . RL_DBPREFIX . "shc_bids` (
			`ID` int(11) NOT NULL auto_increment,
			`Item_ID` int(11) NOT NULL default '0',
			`Dealer_ID` int(11) NOT NULL default '0',
			`Buyer_ID` int(11) NOT NULL default '0',
			`Number` int(11) NOT NULL default '0',
			`Total` double NOT NULL default '0',
			`Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  PRIMARY KEY (`ID`)
		) DEFAULT CHARSET=utf8";

		$this -> query( $sql );

		/* add group fields */
		$group = array(
				'Key' => "shopping_cart",
				'Display' => 1,
				'Status' => 'active'
			);

		if ( $this -> rlActions -> insertOne( $group, 'listing_groups' ) )
		{
			$group_id = mysql_insert_id();
		}

		/* add listting fields  */
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_mode` ENUM( 'auction', 'fixed', 'listing' ) DEFAULT 'listing' NOT NULL;";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_start_price` DOUBLE DEFAULT '0' NOT NULL;";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_reserved_price` DOUBLE DEFAULT '0' NOT NULL;";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_bid_step` DOUBLE DEFAULT '0' NOT NULL;";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_max_bid` DOUBLE DEFAULT '0' NOT NULL;";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_quantity` INT DEFAULT '0' NOT NULL;";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_weight` DOUBLE DEFAULT '0' NOT NULL;";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_days` INT DEFAULT '0' NOT NULL;";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_available` ENUM( '0', '1' ) DEFAULT '0' NOT NULL;";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_start_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';";
		$this -> query( $sql );
                                                                                                              
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_auction_status` ENUM( 'active', 'closed' ) DEFAULT 'active' NOT NULL;";
		$this -> query( $sql );
		
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_total_bids` INT DEFAULT '0' NOT NULL;";
		$this -> query( $sql );
                                                                                                          
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_end_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `shc_auction_won` VARCHAR(50) NOT NULL;";
		$this -> query( $sql );

		$listing_fields[] = array(
				'Key' => "shc_start_price",
				'Type' => "price",
				'Default' => '',
				'Required' => 0,
				'Values' => ''
			);
			

		$listing_fields[] = array(
				'Key' => "shc_reserved_price",
				'Type' => "price",
				'Default' => '',
				'Required' => 0,
				'Values' => ''
			);

		$listing_fields[] = array(
				'Key' => "shc_bid_step",
				'Type' => "price",
				'Default' => '',
				'Required' => 0,
				'Values' => ''
			);

		$listing_fields[] = array(
				'Key' => "shc_quantity",
				'Type' => "number",
				'Default' => '',
				'Required' => 0,
				'Values' => 1
			);

		$listing_fields[] = array(
				'Key' => "shc_weight",
				'Type' => "number",
				'Default' => '',
				'Required' => 0,
				'Values' => 1
			);

		$listing_fields[] = array(
				'Key' => "shc_days",
				'Type' => "number",
				'Default' => '',
				'Required' => 0,
				'Values' => 1
			);

		$listing_fields[] = array(
				'Key' => "shc_available",
				'Type' => "bool",
				'Default' => 1,
				'Required' => 0,
				'Values' => ''
			);

		$this -> rlActions -> insert( $listing_fields, 'listing_fields' );

		/* add account payment fields */		
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_paypal_email` VARCHAR(100) NOT NULL;";
		$this -> query( $sql );
		
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_paypal_enable` ENUM('0','1') NOT NULL DEFAULT '0';";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_2co_id` VARCHAR(100) NOT NULL;";
		$this -> query( $sql );
		                      
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_2co_enable` ENUM('0','1') NOT NULL DEFAULT '0';";
		$this -> query( $sql );
		                                                                                                            
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_allowed_shipping_methods` VARCHAR(255) NOT NULL;";
		$this -> query( $sql );
		                                                                                      
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_ups_pickup_methods` VARCHAR(5) NOT NULL;";
		$this -> query( $sql );
		                                                                                      
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_ups_classification` VARCHAR(5) NOT NULL;";
		$this -> query( $sql );
		                                                                                      
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_ups_package_type` VARCHAR(5) NOT NULL;";
		$this -> query( $sql );
                                                                
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_ups_origin` VARCHAR(5) NOT NULL;";
		$this -> query( $sql );
		                                 
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_ups_services` VARCHAR(255) NOT NULL;";
		$this -> query( $sql );
		         
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_ups_quote_type` VARCHAR(50) NOT NULL;";
		$this -> query( $sql );

		/* listing type (1.0.2) */
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listing_types` ADD `shc_module` ENUM( '0', '1' ) NOT NULL DEFAULT '1' AFTER `Status`";
		$this -> query($sql);

		/* load currency rates */
		$this -> loadCurrencyRates( true );
	}

	function uninstall()
	{
		global $rlCache;

		/* delete listing group */ 
		$group_id = $this -> getOne( "ID", "`Key` = 'shopping_cart'", 'listing_groups' );

		$sql = "DELETE FROM `" . RL_DBPREFIX . "listing_groups` WHERE `Key` = 'shopping_cart'";
		$this -> query( $sql );

		/* delete listing fields */		
        $sql = "SHOW COLUMNS FROM `" . RL_DBPREFIX . "listings` WHERE `Field` RLIKE 'shc_(.*)$'";
		$lfields = $this->getAll( $sql );

		if ( !empty( $lfields ) )
		{
			foreach( $lfields as $lfKey => $lfVal )
			{
				if($lfVal['Field'])
				{
					$sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` DROP `{$lfVal['Field']}`";
					$this->query($sql);
				}
			}
		}

		$sql = "SELECT `ID`,`Key` FROM `" . RL_DBPREFIX . "listing_fields` WHERE `Key` RLIKE 'shc_(.*)$' GROUP BY `ID` ";
		$listings_fields = $this -> getAll( $sql );

		if($listings_fields)
		{
			foreach( $listings_fields as $lfKey => $lfVal )
			{
				$lfield_ids[] = $lfVal['ID'];
			}

			$sql = "DELETE FROM `" . RL_DBPREFIX . "listing_fields` WHERE `ID` = '" . implode( "' OR `ID` = '", $lfield_ids ) . "' ";
			$this -> query( $sql );
		}

		/* delete tables */
		$sql = "DROP TABLE IF EXISTS `" . RL_DBPREFIX . "shc_orders`";
		$this -> query( $sql );

		$sql = "DROP TABLE IF EXISTS `" . RL_DBPREFIX . "shc_order_details`";
		$this -> query( $sql );

		$sql = "DROP TABLE IF EXISTS `" . RL_DBPREFIX . "shc_bids`";
		$this -> query( $sql );

		/* delete account fields */
        $sql = "SHOW COLUMNS FROM `" . RL_DBPREFIX . "accounts` WHERE `Field` RLIKE 'shc_(.*)$'";
		$account_fields = $this->getAll($sql);

		$default_fields = array('shc_paypal_email', 'shc_paypal_enable', 'shc_2co_id', 'shc_2co_enable', 'shc_allowed_shipping_methods', 'shc_ups_pickup_methods', 'shc_ups_classification', 'shc_ups_package_type', 'shc_ups_origin', 'shc_ups_services', 'shc_ups_quote_type');

		if(!empty($account_fields))
		{
			foreach($account_fields as $afKey => $afVal)
			{
				if ( $afVal['Field'] && in_array( $afVal['Field'], $default_fields ) )
				{
					$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` DROP `{$afVal['Field']}`";
					$this -> query( $sql );
				}
			}
		}

		unset( $account_fields, $lfields, $listings_fields );

		/* listing type (1.0.2) */
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "listing_types` DROP `shc_module`";
		$this -> query( $sql );

		$this -> clearCookie( false, true );
		
		if ( $GLOBALS['config']['cache'] )
		{
			$rlCache -> updateSubmitForms();
		}
	}

	function completeTransaction( $item_id = false, $plan_id = false, $account_id = false, $txn_id = null, $gateway = null, $total = false )
	{
		$this -> loadClass( 'Actions' );
		$this -> loadClass( 'Mail' );

		$txn_id = mysql_real_escape_string( $txn_id );
		$gateway = mysql_real_escape_string( $gateway );

		$item_id = (int)$item_id;
		$total = (float)$total;
		$account_id = (int)$account_id;

		if ( !$item_id || !$account_id )
		{
			return false;
		}

		$order_info = $this -> getOrderShortInfo( $item_id );

		if ( !empty( $order_info ) )
		{
			$order_key = explode( "-", $order_info['Order_key'] );

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
				/* check autions */
				$sql = "SELECT `T1`.`ID`, `T1`.`Item_ID`, `T2`.`shc_mode` ";
				$sql .= "FROM `" . RL_DBPREFIX . "shc_order_details` AS `T1` ";
				$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listings` AS `T2` ON `T1`.`Item_ID` = `T2`.`ID` ";
				$sql .= "WHERE `T1`.`Order_key` = '{$order_key[0]}' AND `T1`.`Dealer_ID` = '{$order_info['Dealer_ID']}' ";
				$sql .= "GROUP BY `T2`.`ID` ";

				$shc_items = $this -> getAll( $sql );

				if($shc_items)
				{
					$auction_bought_now = $GLOBALS['rlMail'] -> getEmailTemplate( 'auction_bought_now' );

					foreach($shc_items as $shcKey => $shcVal)
					{
						if($shcVal['shc_mode'] == 'auction')
						{
							$update = array(
									'fields' => array(
											'shc_auction_status' => 'closed',
											'shc_end_time' => 'NOW()',
											'shc_quantity' => 0
										),
									'where' => array(
											'ID' => (int)$shcVal['Item_ID']
										)
								);

							if($this -> rlActions -> updateOne( $update, 'listings' ) )
							{
								/* send notifications to bidders */
								$sql = "SELECT `T1`.`ID`, `T2`.`Mail`,  ";
								$sql .= "IF(`T2`.`Last_name` <> '' AND `T2`.`First_name` <> '', CONCAT(`T2`.`First_name`, ' ', `T2`.`Last_name`), `T2`.`Username`) AS `Full_name` ";
								$sql .= "FROM `" . RL_DBPREFIX . "shc_bids` AS `T1` ";
								$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T2` ON `T1`.`Buyer_ID` = `T2`.`ID` ";
								$sql .= "WHERE `T1`.`Item_ID` = '{$shcVal['Item_ID']}' ";
								$sql .= "GROUP BY `T1`.`Item_ID`";

								$bidders = $this -> getAll( $sql );

								foreach($bidders as $bKey => $bVal)
								{
							 		$copy_auction_bought_now = $auction_bought_now;

									$search = array('{bidder_name}', '{item}', '{date}');
									$replace = array($bVal['Full_name'], $order_info['title'], date('Y-m-d H:i:s'));

									$copy_auction_bought_now['body'] = str_replace( $search, $replace, $copy_auction_bought_now['body']);
									$GLOBALS['rlMail'] -> send( $copy_auction_bought_now, $bVal['Mail'] );

									unset($copy_auction_bought_now);
								}                             
							}
						}
					}
				}
				
				/* update order details	*/
				$sql = "UPDATE `" . RL_DBPREFIX . "shc_order_details` SET `Order_ID` = '{$item_id}', `Status` = 'completed' WHERE `Order_key` = '{$order_key[0]}' AND `Dealer_ID` = '{$order_info['Dealer_ID']}'";
				$this -> query( $sql );

				/* save transaction details */
				$transaction = array(
					'Service' => 'shoppingCart',
					'Item_ID' => $item_id,
					'Account_ID' => $account_id,
					'Plan_ID' => 0,
					'Txn_ID' => $txn_id,
					'Total' => $total,
					'Gateway' => $gateway,
					'Date' => 'NOW()'
				);

				$GLOBALS['rlActions'] -> insertOne( $transaction, 'transactions' );

				$items = $this -> getItems( $GLOBALS['config']['shc_count_items_block'], $order_key[0] );

				if ( $GLOBALS['config']['shc_method'] == 'multi' && count($items) > 0 )
				{
					$this -> updateCookie( $items );	
				}
				else
				{
					$this -> clearCookie();
				}

				/* send payment notification email to buyer	*/
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'shc_order_payment_accepted' );

				$order_info['Total'] = $GLOBALS['config']['system_currency_position'] == 'before' ? $GLOBALS['config']['system_currency'] . ' ' . number_format($order_info['Total'], 2) : $order_info['Total'] . ' ' . $GLOBALS['config']['system_currency']; 
				$order_info['Shipping_price'] = $GLOBALS['config']['system_currency_position'] == 'before' ? $GLOBALS['config']['system_currency'] . ' ' . number_format($order_info['Shipping_price'], 2) : $order_info['Shipping_price'] . ' ' . $GLOBALS['config']['system_currency'];

				/* add order details to mail */
				$td_style = "style=\"border-bottom: 1px dotted silver; padding: 3px 0px 3px 0px;\"";

				$items_details = "<br /><br />{$GLOBALS['lang']['shc_details']}<br /><table width=\"100%\">";
				$items_details .= "<tr style=\"height: 27px; background: #F1F1F1;\"><td {$td_style}>{$GLOBALS['lang']['name']}</td><td {$td_style}>{$GLOBALS['lang']['shc_total']}</td><td {$td_style}>{$GLOBALS['lang']['shc_quantity']}</td></tr>";

				$sql = "SELECT * FROM `".RL_DBPREFIX."shc_order_details` ";
				$sql .= "WHERE `Order_key` = '{$order_key[0]}' ";

				if($GLOBALS['config']['shc_method'] == 'multi')
				{
					$sql .= "AND `Dealer_ID` = '{$order_info['Dealer_ID']}'";
				}

                $order_items = $this->getAll($sql);
				
				foreach($order_items as $itKey => $itVal)
				{                   
					$items_details .= "<tr style=\"height: 27px;\"><td {$td_style}>{$itVal['Item']}</td><td align=\"center\" {$td_style}>".($itVal['Price'] * $itVal['Quantity'])."</td><td align=\"center\" {$td_style}>{$itVal['Quantity']}</td></tr>";
					
				}
				/* shipping price */
				$items_details .= "<tr style=\"height: 27px;\"><td align=\"right\" {$td_style}>{$GLOBALS['lang']['shc_shipping_price']}</td><td colspan=\"2\" align=\"center\" {$td_style}>{$order_info['Shipping_price']}</td></tr>";
				
				/* total price */
				$items_details .= "<tr style=\"height: 27px;\"><td align=\"right\" {$td_style}>{$GLOBALS['lang']['shc_total_cost']}</td><td colspan=\"2\" align=\"center\" {$td_style}>{$order_info['Total']}</td></tr>";
				$items_details .= "</table><br />";

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
				$replace = array( $order_info['bFull_name'], $order_info['Order_key'], $details . $items_details );

				$mail_tpl['body'] = str_replace( $find, $replace, $mail_tpl['body'] );

				$GLOBALS['rlMail'] -> send( $mail_tpl, $order_info['bMail'] );

				/* send payment notification email to admin	*/
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'shc_order_payment_accepted_admin' );

				$details = "<br />{$GLOBALS['lang']['shc_order_key']}: {$order_info['Order_key']}<br />
							{$GLOBALS['lang']['shc_item']}: {$order_info['title']}<br />
							{$GLOBALS['lang']['shc_dealer']}: {$order_info['dFull_name']}<br />
							{$GLOBALS['lang']['shc_total']}: {$order_info['Total']}<br />                  
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

				$shipping .= $items_details;

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

				unset( $order_info, $order_items );
			}
		}
	}

	function ajaxAddItem( $item_id = false )
	{
		global $_response, $rlSmarty, $account_info, $rlListings, $tpl_settings, $config;

		$item_id = (int)$item_id;
		if ( !$item_id )
		{
			return $_response;
		}
 
		/* get listing info */
		$sql = "SELECT `T1`.*, `T2`.`Path`, `T2`.`Type` AS `Listing_type`, `T2`.`Key` AS `Cat_key`, `T2`.`Type` AS `Cat_type` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T1`.`ID` = '{$item_id}' AND `T1`.`Status` = 'active' ";

		$listing_info = $this -> getRow( $sql );

		/* check quantity */
		if ( $listing_info['shc_quantity'] <= 0 )
		{   
			$_response -> script( "printMessage('error', '{$GLOBALS['lang']['shc_not_availble']}');" );	
			$_response -> script( "$('#df_field_shc_quantity').addClass('quantity_not_availble');" );

			return $_response;
		}
                               
		if ( $account_info['ID'] == $listing_info['Account_ID'] && defined('IS_LOGIN') )
		{
			$_response -> script( "printMessage('error', '{$GLOBALS['lang']['shc_add_item_owner']}');" );
			return $_response;
		}

		/* adapt price */
		$price = explode( "|", $listing_info['price'] );
		$delivery = explode( "|", $listing_info['shc_delivery'] );

		$listing_title = $rlListings -> getListingTitle( $listing_info['Category_ID'], $listing_info, $listing_info['Cat_type'] );

		if ( !empty( $item_id ) )
		{
			/* check item */
			$sql = "SELECT `ID`,`Quantity` FROM `" . RL_DBPREFIX . "shc_order_details` WHERE `Order_key` = '{$_COOKIE['shc_txn_id']}' AND `Item_ID` = '{$item_id}' AND `Status` = 'active' LIMIT 1";
			$item_info = $this -> getRow( $sql );

			if(empty( $item_info['ID'] ) )
			{
				$insert = array(
						'Order_key' => $_COOKIE['shc_txn_id'],
						'Item_ID' => $item_id,
						'Item' => $listing_title,
						'Price' => (float)$price[0],
						'Delivery' => (float)$delivery[0],
						'Date' => 'NOW()',
						'Dealer_ID' => $listing_info['Account_ID'],
						'Buyer_ID' => defined( 'IS_LOGIN' ) ? $account_info['ID'] : 0,
						'Quantity' => 1
					);

				$action = $this -> rlActions -> insertOne( $insert, 'shc_order_details' );
				unset( $insert );
			}
			else
			{
				$update = array(
						'fields' => array(	
								'Quantity' => $item_info['Quantity'] + 1
							),
						'where' => array(
								'ID' => $item_info['ID']
							)
					);

				$action = $this -> rlActions -> updateOne( $update, 'shc_order_details' );
				unset( $update, $item_info );
			}

			if($action)
			{
				$items = $this->getItems($GLOBALS['config']['shc_count_items_block']);

				if(count($items) > 0)
				{
					$sql = "UPDATE `" . RL_DBPREFIX . "listings` SET `shc_quantity` = `shc_quantity` - 1 WHERE `ID` = '{$listing_info['ID']}'";
					$this -> query( $sql );

					if(!empty($items))
					{
						$rlSmarty -> assign_by_ref('shcItems', $items);
						$content = $rlSmarty->fetch(RL_PLUGINS . 'shoppingCart' . RL_DS . $this -> cart_list_dom, null, null, false);
						$_response->assign('shopping_cart_block', 'innerHTML', $content);
						$this->updateCookie($items);
					}
					
					$sql = "SELECT COUNT(`ID`) AS `count`, SUM(`Price` * `Quantity`) AS `total` FROM `" . RL_DBPREFIX . "shc_order_details` WHERE `Order_key` = '{$_COOKIE['shc_txn_id']}' AND `Status` = 'active' ";
					$total_info = $this -> getRow( $sql );
					
					/* refresh quantity */
					$_response -> script("
						$('#df_field_shc_quantity td.value').html(" . ( $listing_info['shc_quantity'] - 1 ) . ");
						printMessage('notice', '{$GLOBALS['lang']['shc_add_item_notice']}');
					");

					if ( $tpl_settings['type'] == 'responsive_42' ) {
						$total_text = number_format(round($total_info['total'], 2), 2);
						if ( $config['system_currency_position'] == 'before' ) {
							$total_text = $config['system_currency'] .' '. $total_text; 
						}
						else {
							$total_text = $total_text .' '. $config['system_currency'];
						}

						if ( $tpl_settings['name'] == 'general_flatty' )
						{
							$_response -> script("
								$('.cart-box-container').removeClass('empty');
								$('.cart-box-container > span.button > span.count').text('". intval($total_info['count']) ."');
								$('.cart-box-container > span.button > span.summary').text('{$total_text}');
							");
						}
						else
						{
							$_response -> script("
								$('.cart-box-container-static').removeClass('empty');
								$('.cart-box-container-static').parent().parent('section').find('h3').html('{$GLOBALS['lang']['blocks+name+shc_my_cart']}' + ' (". intval($total_info['count']) ." {$GLOBALS['lang']['shc_count_items']} / {$total_text})');
							");
						}
					}
					else {
						$_response -> script("
							$('#shc-my-cart>div.inner>a.shc-my-cart').html('".(int)$total_info['count']." {$GLOBALS['lang']['shc_count_items']} / ".number_format(round($total_info['total'], 2), 2)." {$GLOBALS['config']['system_currency']}".($GLOBALS['config']['template'] == 'general_sky' ? " <span class=\"arrow\"></span>" : "")."');
						");
					}
				}
				else
				{
					$_response -> script("printMessage('error', '{$GLOBALS['lang']['shc_add_item_error']}');");
				}
			}
			else
			{
				$_response -> script("printMessage('error', '{$GLOBALS['lang']['shc_add_item_error']}');");
			}
		}

		return $_response;
	}

	function ajaxDeleteItem($item_id = false, $listing_id = false, $page = false)
	{
		global $_response, $account_info, $rlSmarty, $tpl_settings, $config;

		$item_id = (int)$item_id;
		$listing_id = (int)$listing_id;

		if(!$item_id || !$listing_id)
		{
			return $_response;
		}

		/* get item details	*/
		$sql = "SELECT * FROM `" . RL_DBPREFIX . "shc_order_details` WHERE `ID` = '{$item_id}' LIMIT 1";
		$item_info = $this -> getRow($sql);

		$sql = "DELETE FROM `" . RL_DBPREFIX . "shc_order_details` WHERE `ID` = '{$item_id}' AND `Item_ID` = '{$listing_id}' LIMIT 1";

		if($this->query($sql))
		{
			/* update quantity	*/
			$sql = "UPDATE `" . RL_DBPREFIX . "listings` SET `shc_quantity` = `shc_quantity` + " . ( (int)$item_info['Quantity'] ) . " WHERE `ID` = '{$item_info['Item_ID']}'";
			$this->query($sql);

			$items = $this->getItems($GLOBALS['config']['shc_count_items_block']);

			if(!$page)
			{
				$rlSmarty->assign_by_ref('shcItems', $items);
				$content = $rlSmarty->fetch(RL_PLUGINS . 'shoppingCart' . RL_DS . $this -> cart_list_dom, null, null, false);
				$_response->assign('shopping_cart_block', 'innerHTML', $content);
			}

			$this->updateCookie($items);
			
			/* get total info by cart */	
			$sql = "SELECT COUNT(`ID`) AS `count`, SUM(`Price` * `Quantity`) AS `total` FROM `" . RL_DBPREFIX . "shc_order_details` WHERE `Order_key` = '{$_COOKIE['shc_txn_id']}' AND `Status` = 'active' ";
			$total_info = $this->getRow($sql);

			/* refresh quantity */
			$_response->script("
				$('#df_field_shc_quantity td.value').html(" . ($listing_info['shc_quantity'] + (int)$item_info['Quantity']) . ");
				printMessage('notice', '{$GLOBALS['lang']['shc_delete_item_notice']}');
			");

			if ( $tpl_settings['type'] == 'responsive_42' ) {
				$total_text = number_format(round($total_info['total'], 2), 2);
				if ( $config['system_currency_position'] == 'before' ) {
					$total_text = $config['system_currency'] .' '. $total_text; 
				}
				else {
					$total_text = $total_text .' '. $config['system_currency'];
				}

				if ( intval($total_info['count']) == 0 ) {
					$_response -> script("$('.cart-box-container').addClass('empty')");
				}

				$_response -> script("
					$('.cart-box-container > span.button > span.count').text('". intval($total_info['count']) ."');
					$('.cart-box-container > span.button > span.summary').text('{$total_text}');
				");
			}
			else {
				$_response->script("
					$('#shc-my-cart>div.inner>a.shc-my-cart').text('".(int)$total_info['count']." {$GLOBALS['lang']['shc_count_items']} / ".number_format(round($total_info['total'], 2), 2)." {$GLOBALS['config']['system_currency']}".($GLOBALS['config']['template'] == 'general_sky' ? "<span class=\"arrow\"></span>" : "")."');
				");
			}
			
			if($page)
			{
				$_response->redirect();
			}
		}

		return $_response;
	}

	function getItems($limit = false, $order_key = false)
	{
		global $account_info, $rlListingTypes, $rlSmarty, $pages;

		if(!$order_key)
		{
			$order_key = $_COOKIE['shc_txn_id'];
		}

		$sql = "SELECT `T1`.*, `T3`.`Path`, `T3`.`Type` AS `Listing_type`, `T3`.`Key` AS `Cat_key`, `T2`.`Main_photo`, `T2`.`shc_quantity`, `T2`.`shc_weight` AS `weight` ";
		$sql .= "FROM `" . RL_DBPREFIX . "shc_order_details` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listings` AS `T2` ON `T1`.`Item_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T2`.`Category_ID` = `T3`.`ID` ";
		$sql .= "WHERE `T1`.`Order_key` = '{$order_key}' AND `T1`.`Status` = 'active' ";

		$sql .= "ORDER BY `T1`.`Date` DESC ";

		if($limit)
		{
			$sql .=  "LIMIT " . $limit;
		}

		$items = $this->getAll($sql);

		if(items)
		{
			if(!$GLOBALS['rlListingTypes']) 
			{
				$this->loadClass('ListingTypes');
			}
			
			foreach($items as $iKey => $iVal)
			{
				$listing_type = $GLOBALS['rlListingTypes']->types[$iVal['Listing_type']];

				$items[$iKey]['listing_link'] = SEO_BASE;
				$items[$iKey]['listing_link'] .= $GLOBALS['config']['mod_rewrite'] ? $pages[$listing_type['Page_key']] . '/' . $iVal['Path'] . '/' . $GLOBALS['rlSmarty'] -> str2path( $iVal['Item'] ) . '-' . $iVal['Item_ID'] . '.html' : '?page=' . $pages[$listing_type['Page_key']] . '&amp;id=' . $iVal['Item_ID'];
			}

			return $items;
		}

		return false;
	}

	function updateCookie(&$items)
	{
		global $pages, $rlSmarty, $rlListingTypes;

		foreach($items as $iKey => $iVal)
		{
			$listing_type = $rlListingTypes->types[$iVal['Listing_type']];

			$items[$iKey]['listing_link'] = SEO_BASE;
			$items[$iKey]['listing_link'] .= $GLOBALS['config']['mod_rewrite'] ? $pages[$listing_type['Page_key']] . '/' . $iVal['Path'] . '/' . $rlSmarty -> str2path( $iVal['Item'] ) . '-' . $iVal['Item_ID'] . '.html' : '?page=' . $pages[$listing_type['Page_key']] . '&amp;id=' . $iVal['Item_ID'];
		}

		$_SESSION['shc_items'] = serialize($items);
	}

	function updateItems(&$items, $data = false, $dealer = false)
	{
		if($data)
		{
			foreach($items as $iKey => $iVal)
			{
				if($dealer && $iVal['Dealer_ID'] != $dealer)
				{
					continue;
				}

				if(!empty($data[$iVal['ID']]))
				{
					$update_items[] = array(
							'fields' => array(	
									'Quantity' => $data[$iVal['ID']]
								),
							'where' => array(
									'ID' => $iVal['ID']
								)
						);
				}
			}

			$action = $this->rlActions->update($update_items, 'shc_order_details');

			return $action;
		}

		return false;
	}

	function createOrder($data = false, $total = false, $dealer = false, $order_info = false)
	{
		global $account_info, $dealer_info;

		if(!$data || !$dealer)
		{
			return false;	
		}

		$dealer = (int)$dealer;

		if(!$order_info)
		{
			$insert = array(
					'Order_key' => $_COOKIE['shc_txn_id'] . '-D' . $dealer,
					'Total' => (float)$total,
					'Dealer_ID' => $dealer,
					'Buyer_ID' => (int)$account_info['ID'],
					'Weight' => (float)$data['weight'],
					'Date' => 'NOW()', 
					'Shipping_method' => $data['method'],
					'Country' => $data['country'],
					'Zip_code' => $data['zip'],
					'State' => $data['state'],
					'City' => $data['city'],
					'Vat_no' => $data['vat_no'],
					'Address' => $data['address'],
					'Name' => $data['name'],
					'Mail' => $data['email'],
					'Phone' => $data['phone'], 
					'Comment' => $data['comment']
				);

			if($data['method'] == 'ups')
			{
				$insert['Package_type'] = $GLOBALS['config']['shc_method'] == 'single' ? $GLOBALS['config']['shc_ups_package_types'] : $dealer_info['shc_ups_package_type'];
				$insert['Pickup_method'] = $GLOBALS['config']['shc_method'] == 'single' ? $GLOBALS['config']['shc_ups_pickup_methods'] : $dealer_info['shc_ups_pickup_method'];
				$insert['UPSService'] = $data['ups_service'];
			}

			$action = $this -> rlActions -> insertOne( $insert, 'shc_orders' );

			if ( $action )
			{
				$item_id = mysql_insert_id();
			}

			unset( $insert );
		}
		else
		{
			$update = array(
					'fields' => array(
							'Total' => (float)$total, 
							'Weight' => (float)$data['weight'],
							'Shipping_method' => $data['method'],
							'Country' => $data['country'],
							'State' => $data['state'],
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
							'ID' => $order_info['ID']
						)
				);

			if($data['method'] == 'ups')
			{
				$update['fields']['Package_type'] = $GLOBALS['config']['shc_method'] == 'single' ? $GLOBALS['config']['shc_ups_package_types'] : $dealer_info['shc_ups_package_type'];
				$update['fields']['Pickup_method'] = $GLOBALS['config']['shc_method'] == 'single' ? $GLOBALS['config']['shc_ups_pickup_methods'] : $dealer_info['shc_ups_pickup_method'];
				$update['fields']['UPSService'] = $data['ups_service'];
			}

			$action = $this -> rlActions -> updateOne( $update, 'shc_orders' );
			$item_id = $order_info['ID'];
			unset( $update, $order_info );
		}

		if ( $action )
		{
			$sql = "SELECT * FROM `" . RL_DBPREFIX . "shc_orders` WHERE `ID` = '{$item_id}' LIMIT 1";	
			$order_info = $this -> getRow( $sql );

			$_SESSION['order_info'] = $order_info;
		}

		return $action;

		return false;
	}

	function getOrderTitle( $item_id = false )
	{
		if ( !$item_id )
		{
			return;
		}

		if ( $_SESSION['order_info'] )
		{
			$order_info = $_SESSION['order_info'];
		}
        else
		{
			$sql = "SELECT * FROM `".RL_DBPREFIX."shc_orders` WHERE `ID` = '{$item_id}' LIMIT 1";
			$order_info = $this -> getRow( $sql );
		}

		if ( $order_info )
		{
			$order_key = explode( "-", $order_info['Order_key'] );

			$sql = "SELECT GROUP_CONCAT(SUBSTRING_INDEX(`Item`, ', $', 1) ORDER BY `Date` DESC SEPARATOR '<br />') AS `title` FROM `" . RL_DBPREFIX . "shc_order_details` "; /* SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `Item` ORDER BY `Date` DESC), ',', 1) AS `title` */
			$sql .= "WHERE `Order_key` = '{$order_key[0]}' ";

			if($GLOBALS['config']['shc_method'] == 'multi')
			{
				$sql .= "AND `Dealer_ID` = '{$order_info['Dealer_ID']}' ";
			}
			$sql .= "LIMIT 1";

			$title = $this -> getRow( $sql );

			if ( !empty( $title['title'] ) )
			{
				return $title['title'];
			}
		}

		return false;
	}

	function getOrderShortInfo( $id = false )
	{
		$id = (int)$id;

		if ( !$id )
		{
			return false;
		}

		$sql = "SELECT `T1`.*, `T2`.`Gateway`, `T3`.`Username` AS `bUsername`, `T3`.`Last_name` AS `bLast_name`,  `T3`.`First_name` AS `bFirst_name`, `T3`.`Mail` AS `bMail`, ";
		$sql .= "`T4`.`Username` AS `dUsername`, `T4`.`Last_name` AS `dLast_name`,  `T4`.`First_name` AS `dFirst_name`, `T4`.`Mail` AS `dMail`, ";

		$sql .= "IF(`T3`.`Last_name` <> '' AND `T3`.`First_name` <> '', CONCAT(`T3`.`First_name`, ' ', `T3`.`Last_name`), `T3`.`Username`) AS `bFull_name`, ";
		$sql .= "IF(`T4`.`Last_name` <> '' AND `T4`.`First_name` <> '', CONCAT(`T4`.`First_name`, ' ', `T4`.`Last_name`), `T4`.`Username`) AS `dFull_name`, ";

		$sql .= "GROUP_CONCAT(DISTINCT SUBSTRING_INDEX(`T5`.`Item`, ', $', 1) ORDER BY `T5`.`Date` DESC SEPARATOR '<br />') AS `title` ";
		$sql .= "FROM `" . RL_DBPREFIX . "shc_orders` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T3` ON `T1`.`Buyer_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T4` ON `T1`.`Dealer_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_order_details` AS `T5` ON `T1`.`Order_key` = CONCAT(`T5`.`Order_key`, '-D', `T1`.`Dealer_ID`) ";
		$sql .= "WHERE `T1`.`ID` = '{$id}' ";
		$sql .= "LIMIT 1";

		$order_info = $this -> getRow( $sql );

		if ( !empty( $order_info ) )
		{
			return $order_info;
		}

		return false;
	}

	function test()
	{
		$update = array(
				'fields' => array(
						'shc_mode' => 1,
						'shc_available' => 1,
						'shc_delivery' => '15|dollar',
						'shc_quantity' => 10
					),
				'where' => array(
						'Status' => 'active'
					)
			);

		return $this -> rlActions -> updateOne( $update, 'listings' );
	}
	
	function ajaxDeleteOrder( $item_id = false )
	{
		global $_response, $account_info;

		$item_id = (int)$item_id;

		if ( !$item_id )
		{
			return $_response;
		}

		$this -> loadClass( 'Mail' );

		/* get order info */
		$order_info = $this -> getOrderShortInfo( $item_id );

		if ( !empty( $order_info ) )
		{
			/* delete order items */
			$sql = "DELETE FROM `" . RL_DBPREFIX . "shc_order_details` WHERE `Order_ID` = '{$item_id}' AND (`Dealer_ID` = '{$order_info['Dealer_ID']}' OR `Dealer_ID` = '0')";
			$this -> query( $sql );

			/* delete order */
			$sql = "DELETE FROM `" . RL_DBPREFIX . "shc_orders` WHERE `ID` = '{$item_id}' LIMIT 1";

			if ( $this -> query( $sql ) )
			{
				$order_info['Total'] = $GLOBALS['config']['system_currency_position'] == 'before' ? $GLOBALS['config']['system_currency'] . ' ' . $order_info['Total'] : $order_info['Total'] . ' ' . $GLOBALS['config']['system_currency']; 
				$order_info['Shipping_price'] = $GLOBALS['config']['system_currency_position'] == 'before' ? $GLOBALS['config']['system_currency'] . ' ' . $order_info['Shipping_price'] : $order_info['Shipping_price'] . ' ' . $GLOBALS['config']['system_currency'];

				$details = "<br />Order Key: {$order_info['Order_key']}<br />
							{$GLOBALS['lang']['shc_total']}: ".number_format($order_info['Total'], 2)." {$GLOBALS['config']['system_currency']}<br />
							{$GLOBALS['lang']['shc_shipping_price']}: ".number_format($order_info['Shipping_price'], 2)." {$GLOBALS['config']['system_currency']}<br />
							{$GLOBALS['lang']['date']}: ".date('Y-m-d')."<br />
							{$GLOBALS['lang']['shc_payment_status']}: {$GLOBALS['lang']['shc_' . $order_info['pStatus']]}<br />
							{$GLOBALS['lang']['shc_shipping_status']}: {$GLOBALS['lang']['shc_' . $order_info['Shipping_status']]}<br /><br />";

				/* send notification buyer */
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'shc_order_remove_by_admin' ); 
	                                                                               
				$find = array( '{username}', '{order_key}', '{details}' );
				$replace = array( $order_info['bFull_name'], $order_info['Order_key'], $details );

				$mail_tpl['body'] = str_replace( $find, $replace, $mail_tpl['body'] );

				$GLOBALS['rlMail'] -> send( $mail_tpl, $order_info['bMail'], null, $GLOBALS['config']['site_main_email'], $_SESSION['sessAdmin']['name'] );

				/* send notification dealer */
				if ( $GLOBALS['config']['shc_method'] == 'multi' )
				{
					$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'shc_order_remove_by_admin_dealer' ); 

					$find = array( '{username}', '{order_key}', '{details}' );
					$replace = array( $order_info['dFull_name'], $order_info['Order_key'], $details );

					$mail_tpl['body'] = str_replace( $find, $replace, $mail_tpl['body'] );

					$GLOBALS['rlMail'] -> send( $mail_tpl, $order_info['dMail'], null, $GLOBALS['config']['site_main_email'], $_SESSION['sessAdmin']['name'] );
				}

				if ( defined( 'REALM' ) )
				{
					$_response -> script("
						shoppingCartGrid.reload();
						printMessage('notice', '{$GLOBALS['lang']['shc_notice_order_delete']}');
					");
				}
			}
		}

		return $_response;
	}

	/* delete orders and items related with listing */
	function deleteListingRelations( &$listing_info )
	{
		if ( !$listing_info && !$GLOBALS['config']['trash'] )
		{
			return;
		}

		/* delete auction orders */
		$sql = "DELETE FROM `" . RL_DBPREFIX . "shc_orders` WHERE `Item_ID` = '{$listing_info['ID']}'";
		$this->query( $sql );

		/* delete bids */
		$sql = "DELETE FROM `" . RL_DBPREFIX . "shc_bids` WHERE `Item_ID` = '{$listing_info['ID']}'";
		$this->query( $sql );

		/* delete shopping cart orders */
		$sql = "SELECT `T1`.*, `T2`.`ID` AS `Order_ID` ";
		$sql .= "FROM `" . RL_DBPREFIX . "shc_order_details` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_orders` AS `T2` ON `T2`.`Order_key` = CONCAT(`T1`.`Order_key`, '-D', `T1`.`Dealer_ID`) ";
		$sql .= "WHERE  `T1`.`Item_ID` = '{$listing_info['ID']}' ";
		$sql .= "GROUP BY `T1`.`ID`";

		$orders = $this -> getAll( $sql );

		if( $orders )
		{
			foreach( $orders as $okey => $oVal )
			{
				$sql = "DELETE FROM `" . RL_DBPREFIX . "shc_orders` WHERE `ID` = '{$oVal['Order_ID']}'";
				$this -> query( $sql );
			}
		}

		/* delete order details */
		$sql = "DELETE FROM `" . RL_DBPREFIX . "shc_order_details` WHERE `Item_ID` = '{$listing_info['ID']}'";
		$this -> query( $sql );

		unset( $orders );
	}

	function clearCookie( $update = false, $uninstall = false )
	{
		$items = $uninstall ? false : $this -> getItems();

		if ( $items )
		{
			$_SESSION['shc_items'] = $items;
		}

		if ( $GLOBALS['config']['shc_method'] == 'single' || ( $GLOBALS['config']['shc_method'] == 'multi' && count( $items ) <= 0 ) )
		{		
			if ( $update )
			{
				setcookie( 'shc_txn_id', $this -> generateHash( 8, 'upper' ), time() + 61516800, '/' );
			}
			else
			{
				setcookie( 'shc_txn_id', '', time() - 3600, '/' );
			}                                                                                 
		}

		unset( $_SESSION['order_info'], $_SESSION['shc_dealer'], $_SESSION['shc_items'] );
	}

	function adaptPaymentDetails( $dealer_info = false )
	{
		if ( !$dealer_info )
		{
			return false;
		}

		$GLOBALS['rlHook'] -> load( 'shoppingCartAdaptPaymentDetails' );
	}

	function getPaymentGateways( $output = false, $dealer_id = false, $all = false )
	{
		global $account_info;

		if ( !$this -> payment_plugins )
		{
			if ( $GLOBALS['config']['shc_method'] == 'multi' )
			{
				$dealer_info = $this -> fetch( '*', array( 'ID' =>  $dealer_id ), null, 1, 'accounts', 'row' ); 
			}

			$payment_plugins = array();

			$sql = "SELECT * FROM `" . RL_DBPREFIX . "hooks` WHERE `Name` = 'paymentGateway'";
			$hooks = $this -> getAll( $sql );

			/* system payment gateways */
			$payment_plugins['paypal'] = array(
					'name' => 'PayPal',
					'key'  => 'paypal'
				);
			$payment_plugins['2co'] = array(
					'name' => '2Checkout',
					'key'  => '2co'
				);

			if ( !empty( $hooks ) )
			{
				foreach( $hooks as $hKey => $hVal )
				{					
			        $sql = "SHOW COLUMNS FROM `" . RL_DBPREFIX . "accounts` WHERE `Field` RLIKE 'shc_{$hVal['Plugin']}_(.*)$'";
					$account_tmp_fields = $this -> getAll( $sql );

					if ( !empty( $account_tmp_fields ) )
					{
						$payment_plugins[$hVal['Plugin']] = array(
								'name' => $hVal['Plugin'],
								'key' => $hVal['Plugin'],
							);
					}
				}
			}

			if ( $GLOBALS['config']['shc_method'] == 'multi' )
			{        	                   
				foreach( $payment_plugins as $key => $value )
				{
					if ( !$dealer_info['shc_' . $key . '_enable'] )
					{
						unset( $payment_plugins[$key] );
					}
				}	
			}

			$GLOBALS['rlHook'] -> load( 'shoppingCartGetPaymentGateways', $payment_plugins );

			$this -> payment_plugins = $payment_plugins; 
		}

		if ( $output )
		{
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'shc_payment_gateways', $this -> payment_plugins );
			return;
		}

		return $this -> payment_plugins;
	}

	function checkPaymentDetails( &$gateway, &$errors, &$payment )
	{
		if( !$gateway )
		{
			return false;
		}

		$shc_orders = $this -> fetch( array( 'ID', 'Dealer_ID' ), array( 'ID' => $payment['item_id'] ), null, 1, 'shc_orders', 'row' );
		$account_info = $this -> fetch( '*', array( 'ID' =>  $shc_orders['Dealer_ID'] ), null, 1, 'accounts', 'row' );

		switch( $gateway )
		{
			case 'paypal' :
				if ( !empty( $account_info['shc_paypal_email'] ) )
				{
					$GLOBALS['config']['paypal_account_email'] = $account_info['shc_paypal_email'];
				}
				else
				{
					$errors[] = $GLOBALS['lang']['shc_incorrect_payment_details'];
				}	

				break;
			
			case '2co' :
				if ( !empty( $account_info['shc_2co_id'] ) )
				{
					$GLOBALS['config']['2co_id'] = $account_info['shc_2co_id'];
				}
				else
				{
					$errors[] = $GLOBALS['lang']['shc_incorrect_payment_details'];
				}

				break;
		}

		$GLOBALS['rlHook'] -> load( 'shoppingCartCheckPaymentDetails', $account_info );
	}

	function saveAccountSettings()
	{
		global $errors, $error_fields, $account_info;

		$this -> getShippingMethods( true, false, true );

		/* simulate post data */
		if(!$_POST['form'])
		{
			$profile_info = $this -> fetch( '*', array( 'ID' =>  $account_info['ID'] ), null, 1, 'accounts', 'row' );
			
			foreach( $profile_info as $pKey => $pVal )
			{
				if ( substr_count( $pKey, 'shc_' ) )
				{
					$_POST['shc'][$pKey] = $pVal;
				}
			}
		}

		if ( $_POST['form'] == 'settings' )
		{  
            $data = $_POST['shc'];

			foreach( $data as $key => $val )
			{
				if ( !isset( $account_info[$key] ) )
				{
					unset( $data[$key] );
				}
				
				if ( is_array( $val ) )
				{
					$data[$key] = implode( ",", $val );
				}
			}

			$update = array(
					'fields' => $data,
					'where' => array(
							'ID' => $account_info['ID']
						)
				);

			if ( $this -> rlActions -> updateOne( $update, 'accounts' ) )
			{
				$this -> loadClass( 'Notice' );
				$GLOBALS['rlNotice'] -> saveNotice( $GLOBALS['lang']['notice_profile_edited'] );
				$this -> refresh();
			}
		}
	}

	function ajaxChangeShippingStatus( $status = false, $item_id = false )
	{
		global $_response, $account_info;

		$item_id = (int)$item_id;

		if ( !$item_id || !$status )
		{
			return $_response;
		}

		$update = array(
			'fields' => array(	
					'Shipping_status' => $status
				),
			'where' => array(
					'ID' => $item_id,
					'Dealer_ID' => $account_info['ID']
				)
		);

		if ( $this -> rlActions -> updateOne( $update, 'shc_orders' ) )
		{
			/* send notification to buyer */
			$order_info = $this -> getOrderShortInfo( $item_id );

			if ( !empty( $order_info ) )
			{
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'shc_shipping_status_changed' );

				$find = array( '{username}', '{order_key}', '{status}' );
				$replace = array( $order_info['bFull_name'], $order_info['Order_key'], $GLOBALS['lang']['shc_' . $status] );

				$mail_tpl['body'] = str_replace( $find, $replace, $mail_tpl['body'] );
				$GLOBALS['rlMail'] -> send( $mail_tpl, $order_info['bMail'] );
			}

			unset( $order_info );

			$_response -> script( "printMessage('notice', '{$GLOBALS['lang']['shc_notice_shipping_status_changed']}');" );
		}
		else
		{
			$_response -> script( "printMessage('error', '{$GLOBALS['lang']['shc_shipping_status_failed']}');" );
		}

		$_response -> script( "" );

		return $_response;
	}

	function isPaid( $item_id = false )
	{
		$item_id = (int)$item_id;

		if ( !$item_id )
		{
			return false;
		}

		$sql = "SELECT `ID`,`pStatus` FROM `" . RL_DBPREFIX . "shc_orders` WHERE `ID` = '{$item_id}' LIMIT 1";
		$order_info = $this -> getRow( $sql );

		if ( $order_info['pStatus'] == 'paid' )
		{
			return true;
		}

		return false;
	}

	function assingPriceFormat()
	{
		global $listing_id;

		if ( !$listing_id )
		{
			return;
		}

		$data = $_POST['fshc'];

		if ( $data )
		{
			switch( $data['shc_mode'] )
			{
				case 'auction' :
					$fields = array(
							'shc_mode' => $data['shc_mode'],
							'shc_start_price' => (float)$data['shc_start_price'],
							'shc_reserved_price' => (float)$data['shc_reserved_price'],
							'shc_bid_step' => (float)$data['shc_bid_step'],
							'shc_days' => (int)$data['shc_days'],
							'shc_quantity' => $data['shc_quantity'] ? (int)$data['shc_quantity'] : 1,
							'shc_available' => (int)$data['shc_available'],
							'shc_weight' => (float)$data['shc_weight'],
							'shc_start_time' => 'NOW()'
						);
					break;

				case 'fixed' :
					$fields = array(
							'shc_mode' => $data['shc_mode'],             
							'shc_start_price' => 0,             
							'shc_reserved_price' => 0,
							'shc_bid_step' => 0,
							'shc_days' => 0,
							'shc_quantity' => (int)$data['shc_quantity'],
							'shc_available' => (int)$data['shc_available'],
							'shc_weight' => (float)$data['shc_weight'],
							'shc_start_time' => 0
						);
					break;

				case 'listing' :
					$fields = array(
							'shc_mode' => $data['shc_mode'],             
							'shc_start_price' => 0,
							'shc_reserved_price' => 0,
							'shc_bid_step' => 0,
							'shc_days' => 0,
							'shc_available' => 0,
							'shc_start_time' => 0
						);
					break;
			}

			if($data['shc_mode'] == 'auction' && $data['shc_edit'] && !$data['shc_update_start_time'] && !$data['shc_first_edit'])
			{
				unset($fields['shc_start_time']);
			} 

			$update = array(
				'fields' => $fields,
				'where' => array(
					'ID' => (int)$listing_id
				)
			);


			$this -> rlActions -> updateOne( $update, 'listings' );
		}
	}

	function simulatePostData( &$listing )
	{
		$_POST['fshc']['shc_mode'] = $listing['shc_mode'];
		$_POST['fshc']['shc_start_price'] = $listing['shc_start_price'];
		$_POST['fshc']['shc_reserved_price'] = $listing['shc_reserved_price'];
		$_POST['fshc']['shc_bid_step'] = $listing['shc_bid_step'];
		$_POST['fshc']['shc_days'] = $listing['shc_days'];
		$_POST['fshc']['shc_quantity'] = $listing['shc_quantity'];
		$_POST['fshc']['shc_weight'] = $listing['shc_weight'];
		$_POST['fshc']['shc_available'] = $listing['shc_available'];

		unset( $start_price );
	}

	function ajaxClearShoppingCart()
	{
		global $_response, $tpl_settings;

		if ( $_COOKIE['shc_txn_id'] )
		{
			unset( $_SESSION['shc_items'] );

			$sql = "SELECT * FROM `" . RL_DBPREFIX . "shc_order_details` WHERE `Order_key` = '{$_COOKIE['shc_txn_id']}'";
			$items_tmp = $this -> getAll( $sql );

			$sql = "DELETE FROM `" . RL_DBPREFIX . "shc_order_details` WHERE `Order_key` = '{$_COOKIE['shc_txn_id']}'";
			if ( $this -> query( $sql ) )
			{
				/* return quantity */
                foreach( $items_tmp as $iKey => $iVal)
				{
					$sql = "UPDATE `" . RL_DBPREFIX . "listings` SET `shc_quantity` = `shc_quantity` + 1 WHERE `ID` = '{$iVal['Item_ID']}' LIMIT 1";
					$this -> query( $sql );
				}

				unset( $items_tmp );
				
				$_response -> script("
					printMessage('notice', '{$GLOBALS['lang']['shc_cart_clear_success']}');
				");

				if ( $tpl_settings['type'] == 'responsive_42' ) 
				{
					if ( $tpl_settings['name'] == 'general_flatty' )
					{
						$_response -> script("
							$('#shopping_cart_block').html('<li class=\"info\">{$GLOBALS['lang']['shc_empty_cart']}</li>');
							$('.cart-box-container').addClass('empty');
							$('.cart-box-container > span.button > span.count').text('0');
							$('.cart-box-container > span.button > span.summary').text('0.00');
						");
					}
					else
					{
						$total = $GLOBALS['config']['system_currency_position'] == 'before' ? $GLOBALS['config']['system_currency'] . '0.00' : $GLOBALS['config']['system_currency'] . '0.00';

						$_response -> script("
							$('.cart-box-container-static').parent().parent('section').find('h3').html('{$GLOBALS['lang']['blocks+name+shc_my_cart']}' + ' (0 {$GLOBALS['lang']['shc_count_items']} / {$total})')
							$('#shopping_cart_block').html('<li class=\"info\">{$GLOBALS['lang']['shc_empty_cart']}</li>');
							$('.cart-box-container-static').addClass('empty');
						");
					}
				}
				else 
				{
					$_response -> script("
						$('#shopping_cart_block').html('<div class=\"info\">{$GLOBALS['lang']['shc_empty_cart']}</div>');						
						$('#shc-my-cart>div.inner>a.shc-my-cart').html('0 {$GLOBALS['lang']['shc_count_items']} / 0.00 {$GLOBALS['config']['system_currency']}".($GLOBALS['config']['template'] == 'general_sky' ? " <span class=\"arrow\"></span>" : "")."');
					");
				}
			}
			$this -> clearCookie( true );	
		}

		return $_response;
	}

	function ajaxCheckAccountSettings()
	{
		global $_response, $account_info;

		if ( !defined( 'IS_LOGIN' ) )
		{
			return $_response;		
		}		

		$payment_gateways = $this -> getPaymentGateways( false, $account_info['ID'] );

		
		if ( count( $payment_gateways ) <= 0 || empty( $account_info['shc_allowed_shipping_methods'] ) )
		{
			$_response -> script( "printMessage('warning', '{$GLOBALS['lang']['shc_account_settings_empty']}');" );
		}

		return $_response;
	}

	function getSteps()
	{
		$steps = array(
				'cart' => array(
						'name' => $GLOBALS['lang']['shc_step_cart'],
						'caption' => true
					),
				'auth' => array(
						'name' => $GLOBALS['lang']['shc_step_cart'],
						'caption' => true,
						'path' => 'auth'
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

		return $steps;
	}

	function getShippingStatuses()
	{
		$shipping_statuses = array(	
				array( 'Key' => 'pending', 'name' => $GLOBALS['lang']['shc_pending'] ),
				array( 'Key' => 'processing', 'name' => $GLOBALS['lang']['shc_processing'] ),
				array( 'Key' => 'shipped', 'name' => $GLOBALS['lang']['shc_shipped'] ),
				array( 'Key' => 'declined', 'name' => $GLOBALS['lang']['shc_declined'] ),
				array( 'Key' => 'open', 'name' => $GLOBALS['lang']['shc_open'] ),
				array( 'Key' => 'delivered', 'name' => $GLOBALS['lang']['shc_delivered'] )
			);

		return $shipping_statuses;
	}

	function getShippingMethods( $output = false, $dealer_id = false, $all = false )
	{
		global $rlSmarty;

		if ( $GLOBALS['config']['shc_method'] == 'multi' )
		{
			$dealer_info = $this -> fetch( '*', array( 'ID' =>  $dealer_id ), null, 1, 'accounts', 'row' ); 
		}

		$list = array(
				'pickup' => array(
						'key' => 'pickup',
						'name' => $GLOBALS['lang']['shc_shipping_pickup']
					),
				'courier' => array(
						'key' => 'courier',
						'name' => $GLOBALS['lang']['shc_shipping_courier']
					),
				'dhl' => array(
						'key' => 'dhl',
						'name' => $GLOBALS['lang']['shc_shipping_dhl']
					),
				'ups' => array(
						'key' => 'ups',
						'name' => $GLOBALS['lang']['shc_shipping_ups']
					)
			);

	   	$GLOBALS['rlHook'] -> load( 'shoppingCartShippingMethods' ); 	

		if ( !defined( 'REALM' ) && !$all )
		{
			/* check configuration of shipping methods */
			if( !$GLOBALS['config']['shc_dhl_site_id'] || !$GLOBALS['config']['shc_dhl_password'] )
			{
				unset($list['dhl']);
			}
            if ( !$GLOBALS['config']['shc_ups_key'] || !$GLOBALS['config']['shc_ups_username'] || !$GLOBALS['config']['shc_ups_password'] )
			{
				unset($list['ups']);
			}
			
			$allowed_methods = $GLOBALS['config']['shc_method'] == 'single' ? explode( ",", $GLOBALS['config']['shc_allowed_shipping_methods'] ) : explode( ",", $dealer_info['shc_allowed_shipping_methods']);

			foreach( $list as $key => $value )
			{
				if ( !in_array( $key, $allowed_methods ) )
				{
					unset( $list[$key] );
				}
			}
		}

		if ( $output )
		{
			$rlSmarty -> assign_by_ref( 'shc_shipping_methods', $list );
			return;
		}

		return $list;	
	}

	function getCountryCode( $country = false )
	{
		if ( !$country )
		{
			return false;
		}

		$countries = $this -> getCountriesList();
		$code = false;
		$country = str_replace( "_", "", $country );

		foreach( $countries as $cKey )
		{
			if ( strtolower( $country ) == strtolower( $cKey -> Country_name ) )
			{   
				$code = trim( $cKey -> Country_code );
				break;
			}
		}

		unset( $countries );

		return $code;
	}

	/**
	* Get countries list
	*/
	function getCountriesList()
	{
		$countries = '[
			{"Country_code":"AF","Country_name":"Afghanistan"},{"Country_code":"AX","Country_name":"Aland Islands"},{"Country_code":"AL","Country_name":"Albania"},
			{"Country_code":"DZ","Country_name":"Algeria"},{"Country_code":"AS","Country_name":"American Samoa"},{"Country_code":"AD","Country_name":"Andorra"},
			{"Country_code":"AO","Country_name":"Angola"},{"Country_code":"AI","Country_name":"Anguilla"},{"Country_code":"AQ","Country_name":"Antarctica"},
			{"Country_code":"AG","Country_name":"Antigua and Barbuda"},{"Country_code":"AR","Country_name":"Argentina"},{"Country_code":"AM","Country_name":"Armenia"},
			{"Country_code":"AW","Country_name":"Aruba"},{"Country_code":"AU","Country_name":"Australia"},{"Country_code":"AT","Country_name":"Austria"},
			{"Country_code":"AZ","Country_name":"Azerbaijan"},{"Country_code":"BS","Country_name":"Bahamas"},{"Country_code":"BH","Country_name":"Bahrain"},
			{"Country_code":"BD","Country_name":"Bangladesh"},{"Country_code":"BB","Country_name":"Barbados"},{"Country_code":"BY","Country_name":"Belarus"},
			{"Country_code":"BE","Country_name":"Belgium"},{"Country_code":"BZ","Country_name":"Belize"},{"Country_code":"BJ","Country_name":"Benin"},
			{"Country_code":"BM","Country_name":"Bermuda"},{"Country_code":"BT","Country_name":"Bhutan"},{"Country_code":"BO","Country_name":"Bolivia"},
			{"Country_code":"BA","Country_name":"Bosnia and Herzegovina"},{"Country_code":"BW","Country_name":"Botswana"},
			{"Country_code":"BV","Country_name":"Bouvet Island"},{"Country_code":"BR","Country_name":"Brazil"},{"Country_code":"IO","Country_name":"British Indian Ocean Territory"},
			{"Country_code":"BN","Country_name":"Brunei Darussalam"},{"Country_code":"BG","Country_name":"Bulgaria"},{"Country_code":"BF","Country_name":"Burkina Faso"},
			{"Country_code":"BI","Country_name":"Burundi"},{"Country_code":"KH","Country_name":"Cambodia"},{"Country_code":"CM","Country_name":"Cameroon"},
			{"Country_code":"CA","Country_name":"Canada"},{"Country_code":"CV","Country_name":"Cape Verde"},{"Country_code":"KY","Country_name":"Cayman Islands"},
			{"Country_code":"CF","Country_name":"Central African Republic"},{"Country_code":"TD","Country_name":"Chad"},{"Country_code":"CL","Country_name":"Chile"},
			{"Country_code":"CN","Country_name":"China"},{"Country_code":"CX","Country_name":"Christmas Island"},{"Country_code":"CC","Country_name":"Cocos (Keeling) Islands"},
			{"Country_code":"CO","Country_name":"Colombia"},{"Country_code":"KM","Country_name":"Comoros"},{"Country_code":"CG","Country_name":"Congo"},
			{"Country_code":"CD","Country_name":"Congo, The Democratic Republic of the"},{"Country_code":"CK","Country_name":"Cook Islands"},
			{"Country_code":"CR","Country_name":"Costa Rica"},{"Country_code":"CI","Country_name":"Cote D\'Ivoire"},{"Country_code":"HR","Country_name":"Croatia"},
			{"Country_code":"CU","Country_name":"Cuba"},{"Country_code":"CY","Country_name":"Cyprus"},{"Country_code":"CZ","Country_name":"Czech Republic"},
			{"Country_code":"DK","Country_name":"Denmark"},{"Country_code":"DJ","Country_name":"Djibouti"},{"Country_code":"DM","Country_name":"Dominica"},
			{"Country_code":"DO","Country_name":"Dominican Republic"},{"Country_code":"TL","Country_name":"East Timor"},{"Country_code":"EC","Country_name":"Ecuador"},
			{"Country_code":"EG","Country_name":"Egypt"},{"Country_code":"SV","Country_name":"El Salvador"},{"Country_code":"GQ","Country_name":"Equatorial Guinea"},
			{"Country_code":"ER","Country_name":"Eritrea"},{"Country_code":"EE","Country_name":"Estonia"},{"Country_code":"ET","Country_name":"Ethiopia"},
			{"Country_code":"FK","Country_name":"Falkland Islands (Malvinas)"},{"Country_code":"FO","Country_name":"Faroe Islands"},{"Country_code":"FJ","Country_name":"Fiji"},
			{"Country_code":"FI","Country_name":"Finland"},{"Country_code":"FR","Country_name":"France"},{"Country_code":"GF","Country_name":"French Guiana"},
			{"Country_code":"PF","Country_name":"French Polynesia"},{"Country_code":"TF","Country_name":"French Southern Territories"},{"Country_code":"GA","Country_name":"Gabon"},
			{"Country_code":"GM","Country_name":"Gambia"},{"Country_code":"GE","Country_name":"Georgia"},{"Country_code":"DE","Country_name":"Germany"},
			{"Country_code":"GH","Country_name":"Ghana"},{"Country_code":"GI","Country_name":"Gibraltar"},{"Country_code":"GR","Country_name":"Greece"},
			{"Country_code":"GL","Country_name":"Greenland"},{"Country_code":"GD","Country_name":"Grenada"},{"Country_code":"GP","Country_name":"Guadeloupe"},
			{"Country_code":"GU","Country_name":"Guam"},{"Country_code":"GT","Country_name":"Guatemala"},{"Country_code":"GG","Country_name":"Guernsey"},
			{"Country_code":"GN","Country_name":"Guinea"},{"Country_code":"GW","Country_name":"Guinea-Bissau"},{"Country_code":"GY","Country_name":"Guyana"},
			{"Country_code":"HT","Country_name":"Haiti"},{"Country_code":"HM","Country_name":"Heard Island and McDonald Islands"},
			{"Country_code":"VA","Country_name":"Holy See (Vatican City State)"},{"Country_code":"HN","Country_name":"Honduras"},{"Country_code":"HK","Country_name":"Hong Kong"},
			{"Country_code":"HU","Country_name":"Hungary"},{"Country_code":"IS","Country_name":"Iceland"},{"Country_code":"IN","Country_name":"India"},
			{"Country_code":"ID","Country_name":"Indonesia"},{"Country_code":"IR","Country_name":"Iran, Islamic Republic of"},{"Country_code":"IQ","Country_name":"Iraq"},
			{"Country_code":"IE","Country_name":"Ireland"},{"Country_code":"IM","Country_name":"Isle of Man"},{"Country_code":"IL","Country_name":"Israel"},
			{"Country_code":"IT","Country_name":"Italy"},{"Country_code":"JM","Country_name":"Jamaica"},{"Country_code":"JP","Country_name":"Japan"},
			{"Country_code":"JE","Country_name":"Jersey"},{"Country_code":"JO","Country_name":"Jordan"},{"Country_code":"KZ","Country_name":"Kazakhstan"},
			{"Country_code":"KE","Country_name":"Kenya"},{"Country_code":"KI","Country_name":"Kiribati"},{"Country_code":"KP","Country_name":"Korea, Democratic People\'s Republic of"},
			{"Country_code":"KR","Country_name":"Korea, Republic of"},{"Country_code":"KW","Country_name":"Kuwait"},{"Country_code":"KG","Country_name":"Kyrgyzstan"},
			{"Country_code":"LA","Country_name":"Lao People\'s Democratic Republic"},{"Country_code":"LV","Country_name":"Latvia"},{"Country_code":"LB","Country_name":"Lebanon"},
			{"Country_code":"LS","Country_name":"Lesotho"},{"Country_code":"LR","Country_name":"Liberia"},{"Country_code":"LY","Country_name":"Libyan Arab Jamahiriya"},
			{"Country_code":"LI","Country_name":"Liechtenstein"},{"Country_code":"LT","Country_name":"Lithuania"},{"Country_code":"LU","Country_name":"Luxembourg"},
			{"Country_code":"MO","Country_name":"Macau"},{"Country_code":"MK","Country_name":"Macedonia"},{"Country_code":"MG","Country_name":"Madagascar"},
			{"Country_code":"MW","Country_name":"Malawi"},{"Country_code":"MY","Country_name":"Malaysia"},{"Country_code":"MV","Country_name":"Maldives"},
			{"Country_code":"ML","Country_name":"Mali"},{"Country_code":"MT","Country_name":"Malta"},{"Country_code":"MH","Country_name":"Marshall Islands"},
			{"Country_code":"MQ","Country_name":"Martinique"},{"Country_code":"MR","Country_name":"Mauritania"},{"Country_code":"MU","Country_name":"Mauritius"},
			{"Country_code":"YT","Country_name":"Mayotte"},{"Country_code":"MX","Country_name":"Mexico"},{"Country_code":"FM","Country_name":"Micronesia, Federated States of"},
			{"Country_code":"MD","Country_name":"Moldova, Republic of"},{"Country_code":"MC","Country_name":"Monaco"},{"Country_code":"MN","Country_name":"Mongolia"},
			{"Country_code":"ME","Country_name":"Montenegro"},{"Country_code":"MS","Country_name":"Montserrat"},{"Country_code":"MA","Country_name":"Morocco"},
			{"Country_code":"MZ","Country_name":"Mozambique"},{"Country_code":"MM","Country_name":"Myanmar"},{"Country_code":"NA","Country_name":"Namibia"},
			{"Country_code":"NR","Country_name":"Nauru"},{"Country_code":"NP","Country_name":"Nepal"},{"Country_code":"NL","Country_name":"Netherlands"},
			{"Country_code":"AN","Country_name":"Netherlands Antilles"},{"Country_code":"NC","Country_name":"New Caledonia"},{"Country_code":"NZ","Country_name":"New Zealand"},
			{"Country_code":"NI","Country_name":"Nicaragua"},{"Country_code":"NE","Country_name":"Niger"},{"Country_code":"NG","Country_name":"Nigeria"},
			{"Country_code":"NU","Country_name":"Niue"},{"Country_code":"NF","Country_name":"Norfolk Island"},{"Country_code":"MP","Country_name":"Northern Mariana Islands"},
			{"Country_code":"NO","Country_name":"Norway"},{"Country_code":"OM","Country_name":"Oman"},{"Country_code":"PK","Country_name":"Pakistan"},
			{"Country_code":"PW","Country_name":"Palau"},{"Country_code":"PS","Country_name":"Palestinian Territory"},{"Country_code":"PA","Country_name":"Panama"},
			{"Country_code":"PG","Country_name":"Papua New Guinea"},{"Country_code":"PY","Country_name":"Paraguay"},{"Country_code":"PE","Country_name":"Peru"},
			{"Country_code":"PH","Country_name":"Philippines"},{"Country_code":"PN","Country_name":"Pitcairn"},{"Country_code":"PL","Country_name":"Poland"},
			{"Country_code":"PT","Country_name":"Portugal"},{"Country_code":"PR","Country_name":"Puerto Rico"},{"Country_code":"QA","Country_name":"Qatar"},
			{"Country_code":"RE","Country_name":"Reunion"},{"Country_code":"RO","Country_name":"Romania"},{"Country_code":"RU","Country_name":"Russian Federation"},
			{"Country_code":"RW","Country_name":"Rwanda"},{"Country_code":"SH","Country_name":"Saint Helena"},{"Country_code":"KN","Country_name":"Saint Kitts and Nevis"},
			{"Country_code":"LC","Country_name":"Saint Lucia"},{"Country_code":"PM","Country_name":"Saint Pierre and Miquelon"},
			{"Country_code":"VC","Country_name":"Saint Vincent and the Grenadines"},{"Country_code":"WS","Country_name":"Samoa"},{"Country_code":"SM","Country_name":"San Marino"},
			{"Country_code":"ST","Country_name":"Sao Tome and Principe"},{"Country_code":"SA","Country_name":"Saudi Arabia"},{"Country_code":"SN","Country_name":"Senegal"},
			{"Country_code":"RS","Country_name":"Serbia"},{"Country_code":"SC","Country_name":"Seychelles"},{"Country_code":"SL","Country_name":"Sierra Leone"},
			{"Country_code":"SG","Country_name":"Singapore"},{"Country_code":"SK","Country_name":"Slovakia"},{"Country_code":"SI","Country_name":"Slovenia"},
			{"Country_code":"SB","Country_name":"Solomon Islands"},{"Country_code":"SO","Country_name":"Somalia"},{"Country_code":"ZA","Country_name":"South Africa"},
			{"Country_code":"GS","Country_name":"South Georgia and the South Sandwich Islands"},{"Country_code":"ES","Country_name":"Spain"},
			{"Country_code":"LK","Country_name":"Sri Lanka"},{"Country_code":"SD","Country_name":"Sudan"},{"Country_code":"SR","Country_name":"Suriname"},
			{"Country_code":"SJ","Country_name":"Svalbard and Jan Mayen"},{"Country_code":"SZ","Country_name":"Swaziland"},{"Country_code":"SE","Country_name":"Sweden"},
			{"Country_code":"CH","Country_name":"Switzerland"},{"Country_code":"SY","Country_name":"Syrian Arab Republic"},
			{"Country_code":"TW","Country_name":"Taiwan (Province of China)"},{"Country_code":"TJ","Country_name":"Tajikistan"},{"Country_code":"TZ","Country_name":"Tanzania, United Republic of"},
			{"Country_code":"TH","Country_name":"Thailand"},{"Country_code":"TG","Country_name":"Togo"},{"Country_code":"TK","Country_name":"Tokelau"},{"Country_code":"TO","Country_name":"Tonga"},
			{"Country_code":"TT","Country_name":"Trinidad and Tobago"},{"Country_code":"TN","Country_name":"Tunisia"},{"Country_code":"TR","Country_name":"Turkey"},
			{"Country_code":"TM","Country_name":"Turkmenistan"},{"Country_code":"TC","Country_name":"Turks and Caicos Islands"},{"Country_code":"TV","Country_name":"Tuvalu"},
			{"Country_code":"UG","Country_name":"Uganda"},{"Country_code":"UA","Country_name":"Ukraine"},{"Country_code":"AE","Country_name":"United Arab Emirates"},
			{"Country_code":"GB","Country_name":"United Kingdom"},{"Country_code":"US","Country_name":"United States"},{"Country_code":"UM","Country_name":"United States Minor Outlying Islands"},
			{"Country_code":"UY","Country_name":"Uruguay"},{"Country_code":"UZ","Country_name":"Uzbekistan"},{"Country_code":"VU","Country_name":"Vanuatu"},
			{"Country_code":"VE","Country_name":"Venezuela"},{"Country_code":"VN","Country_name":"Vietnam"},{"Country_code":"VG","Country_name":"Virgin Islands, British"},
			{"Country_code":"VI","Country_name":"Virgin Islands, U.S."},{"Country_code":"WF","Country_name":"Wallis and Futuna"},{"Country_code":"EH","Country_name":"Western Sahara"},
			{"Country_code":"YE","Country_name":"Yemen"},{"Country_code":"ZM","Country_name":"Zambia"},{"Country_code":"ZW","Country_name":"Zimbabwe"}
		]';
		
		$countries = preg_replace( '/(\n|\t|\r)?/', '', $countries );

		$this -> loadClass( 'Json' );
		return $GLOBALS['rlJson'] -> decode( $countries );
	}

	function getStatesUS( $output = false )
	{
		global $rlSmarty;

		$states_tmp = '[
			{"State_code":"AL","State_name":"Alabama"},{"State_code":"AK","State_name":"Alaska"},{"State_code":"AZ","State_name":"Arizona"},
			{"State_code":"AR","State_name":"Arkansas"},{"State_code":"CA","State_name":"California"},{"State_code":"CO","State_name":"Colorado"},
			{"State_code":"CT","State_name":"Connecticut"},{"State_code":"DE","State_name":"Delaware"},{"State_code":"FL","State_name":"Florida"},
			{"State_code":"GA","State_name":"Georgia"},{"State_code":"HI","State_name":"Hawaii"},{"State_code":"ID","State_name":"Idaho"},			
			{"State_code":"IL","State_name":"Illinois"},{"State_code":"IN","State_name":"Indiana"},{"State_code":"IA","State_name":"Iowa"},
			{"State_code":"KS","State_name":"Kansas"},{"State_code":"KY","State_name":"Kentucky"},{"State_code":"LA","State_name":"Louisiana"},
			{"State_code":"ME","State_name":"Maine"},{"State_code":"MD","State_name":"Maryland"},{"State_code":"MA","State_name":"Massachusetts"},
			{"State_code":"MI","State_name":"Michigan"},{"State_code":"MN","State_name":"Minnesota"},{"State_code":"MS","State_name":"Mississippi"},
			{"State_code":"MO","State_name":"Missouri"},{"State_code":"MT","State_name":"Montana"},{"State_code":"NE","State_name":"Nebraska"},
			{"State_code":"NV","State_name":"Nevada"},{"State_code":"NH","State_name":"New Hampshire"},{"State_code":"NJ","State_name":"New Jersey"},
			{"State_code":"NM","State_name":"New Mexico"},{"State_code":"NY","State_name":"New York"},{"State_code":"NC","State_name":"North Carolina"},
			{"State_code":"ND","State_name":"North Dakota"},{"State_code":"OH","State_name":"Ohio"},{"State_code":"OK","State_name":"Oklahoma"},
			{"State_code":"OR","State_name":"Oregon"},{"State_code":"PA","State_name":"Pennsylvania"},{"State_code":"RI","State_name":"Rhode Island"},
			{"State_code":"SC","State_name":"South Carolina"},{"State_code":"SD","State_name":"South Dakota"},{"State_code":"TN","State_name":"Tennessee"},
			{"State_code":"TS","State_name":"Texas"},{"State_code":"UT","State_name":"Utah"},{"State_code":"VT","State_name":"Vermont"},
			{"State_code":"VA","State_name":"Virginia"},{"State_code":"WA","State_name":"Washington"},{"State_code":"WV","State_name":"West Virginia"},
			{"State_code":"WI","State_name":"Wisconsin"},{"State_code":"WY","State_name":"Wyoming"},{"State_code":"DC","State_name":"District of Columbia"},
			{"State_code":"AS","State_name":"American Samoa"},{"State_code":"GU","State_name":"Guam"},{"State_code":"MP","State_name":"Northern Mariana Islands"},
			{"State_code":"PR","State_name":"Puerto Rico"}
		]';

		$states_tmp = preg_replace( '/(\n|\t|\r)?/', '', $states_tmp );

		if ( !$GLOBALS['rlJson'] )
		{
			$this -> loadClass( 'Json' );
		}

		$states_tmp = $GLOBALS['rlJson'] -> decode( $states_tmp );

		foreach( $states_tmp as $k => $v )
		{
			$states[$k] = array(
				'code' => $v -> State_code,
				'name' => $v -> State_name
			);
		}

		if( $output )
		{
			$rlSmarty -> assign_by_ref( 'shc_states', $states );
		}

		return $states;
	}

	function loadCurrencyRates( $first = false )
	{
		if ( !$GLOBALS['config']['shc_currency_rate_url'] && !$first )
		{
			return;
		}

		$content = $this -> getPageContent( $GLOBALS['config']['shc_currency_rate_url'] ? $GLOBALS['config']['shc_currency_rate_url'] : 'http://themoneyconverter.com/rss-feed/USD/rss.xml' );

		$this -> loadClass( 'Rss' );
		$GLOBALS['rlRss'] -> items_number = 300;
		$GLOBALS['rlRss'] -> createParser( $content );
		$rates = $GLOBALS['rlRss'] -> getRssContent();

		if ( !empty( $rates ) )
		{
			$rates_data['USD'] = array(
					'Rate' => 1,
					'Key' => 'dollar',
					'Country' => "United States",
					'Code' => "USD",
					'Symbol' => '$'
				);
			
			foreach ( $rates as $rate )
			{
				/* get currency code */
				preg_match('/(.*)\/.*/', $rate['title'], $code_matches);
				$code = $code_matches[1];

				/* get rate */
				preg_match('/.*\=\s([0-9\.]*)\s(.*)/', $rate['description'], $matches);
				$rate = $matches[1];
				$country = $matches[2];
				switch ($code){
					case 'EUR':
						$symbol = '&euro;';
						$key = 'euro';
						break;
					case 'GBP':
						$symbol = '&pound;';
						$key = 'ps';
						break;
					default:
						$symbol = '';
						$key = $code;
						break;
				}

				if($code != 'USD')
				{
					$rates_data[$code] = array(
							'Rate' => round((float)$rate, 3),
							'Key' => $key,
							'Country' => trim($country),
							'Code' => $code,
							'Symbol' => $symbol
						);
				}
			}

			if($rates_data)
			{
				$hook_code = <<< SHC
\$shcRates = '{rates}';
\$GLOBALS['shcRates'] = unserialize(trim(\$shcRates));
SHC;

				$hook_code = str_replace('{rates}', serialize($rates_data), $hook_code);

				$hook_info = $this->getRow( "SELECT `Name` FROM `" . RL_DBPREFIX . "hooks` WHERE `Name` = 'shoppingCartCurrencyRates' LIMIT 1" );
				
				if($hook_info['Name'])
				{
					$update = array(
							'fields' => array(
									'Code' => $hook_code
								),
							'where' => array(
									'Name' => 'shoppingCartCurrencyRates'
								)
						);

					$this -> rlActions -> updateOne( $update, 'hooks', array('Code') );

					unset($update);
				}
				else
				{
					$insert = array(
							'Name' => 'shoppingCartCurrencyRates',
							'Plugin' => 'shoppingCart',
							'Code' => $hook_code 
						);

					$this -> rlActions -> insertOne($insert, 'hooks',  array('Code') );

					unset($insert);
				}
			}

			unset($rates_data, $hook_code, $rates, $content);
		}
	}
}