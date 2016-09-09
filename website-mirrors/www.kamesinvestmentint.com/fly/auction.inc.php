<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: AUCTION.INC.PHP
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

/* ext js action */
if ( $_GET['q'] == 'ext' )
{
	// system config
	require_once( '../../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL .'ext_header.inc.php' );
	require_once( RL_LIBS .'system.lib.php' );

	/* date update */
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );
		$reefless -> loadClass( 'Mail' );
		$reefless -> loadClass( 'ShoppingCart', null, 'shoppingCart' );
		
		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br( $_GET['value'] ) );
		$id = $rlValid -> xSql( $_GET['id'] );
		$key = $rlValid -> xSql( $_GET['key'] );

		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);
		
        if ( $field == 'shc_auction_status' )
		{
			/* send notification to dealer */
			
			/* send notification to bidders */
		}

		$rlActions -> updateOne( $updateData, 'listings' );

		exit;
	}

	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );
	$module = $rlValid -> xSql( $_GET['module'] );
	$item_id = (int)$_GET['item_id'];

	$reefless -> loadClass( 'Listings' );
	$reefless -> loadClass( 'Common' );
	$reefless -> loadClass( 'Auction', null, 'shoppingCart' );                       

    if ( $module == 'bids' )
	{           
		$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.*, `T2`.`Username` AS `bUsername`, `T2`.`First_name` AS `bFirst_name`, `T2`.`Last_name` AS `bLast_name`, ";
		$sql .= "`T3`.`Username` AS `dUsername`, `T3`.`First_name` AS `dFirst_name`, `T3`.`Last_name` AS `dLast_name` ";
		$sql .= "FROM `" . RL_DBPREFIX . "shc_bids` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T2` ON `T1`.`Buyer_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T3` ON `T1`.`Dealer_ID` = `T3`.`ID` ";
		$sql .= "WHERE `T1`.`Item_ID` = '{$item_id}' ";
		$sql .= "GROUP BY `T1`.`ID` ";    
		$sql .= "ORDER BY `T1`.`Date` DESC LIMIT {$start}, {$limit}";

		$data = $rlDb -> getAll( $sql );
		$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" ); 
	}
	else
	{
		$search = $rlValid -> xSql( $_GET['search'] );
		$username = $rlValid -> xSql( $_GET['username'] );
		$shc_auction_status = $rlValid -> xSql( $_GET['shc_auction_status'] );
		$shc_payment_status = $rlValid -> xSql( $_GET['shc_payment_status'] );
		$has_winner = $rlValid -> xSql( $_GET['has_winner'] );

		$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.*, ";
		$sql .= "COUNT(`T2`.`ID`) AS `total_bids`, ";
		$sql .= "`T3`.`Username`, `T3`.`Last_name`,  `T3`.`First_name`, `T4`.`Key` AS `Category_key`, `T4`.`Type` AS `Listing_type`, ";
		$sql .= "`T5`.`pStatus` ";

		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_bids` AS `T2` ON `T1`.`ID` = `T2`.`Item_ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T3` ON `T1`.`Account_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_orders` AS `T5` ON `T1`.`ID` = `T5`.`Item_ID` ";
	    $sql .= "WHERE `T1`.`Status` <> 'trash' AND `T1`.`shc_mode` = 'auction' ";

        if ( $search )
		{
			if ( $username )
			{
				$sql .= " AND `T3`.`Username` LIKE '%{$username}%' ";
			}
			if ( $shc_auction_status )
			{
				$sql .= " AND `T1`.`shc_auction_status` LIKE '{$shc_auction_status}' ";
			}
			if ( $shc_payment_status && $has_winner == 'yes' )
			{
				$sql .= " AND `T5`.`pStatus` LIKE '{$shc_payment_status}' ";
			}
			elseif ( $has_winner == 'no' )
			{
				$sql .= " AND `T5`.`Item_ID` <> `T1`.`ID` ";
			}
		}

		$sql .= "GROUP BY `T1`.`ID` ";
		$sql .= "ORDER BY `T2`.`Date` DESC LIMIT {$start}, {$limit}";

		$data = $rlDb -> getAll( $sql );
		$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" ); 

	    foreach ( $data as $key => $value )
		{  
			$left_time =  $rlAuction -> getTimeLeft( $value, true );
			
			if($value['shc_auction_status'] == 'closed' && !$value['shc_auction_won'] )
			{
				$data[$key]['pStatus'] = $GLOBALS['lang']['shc_no_winner'];
			}
			else
			{
				$data[$key]['pStatus'] = $value['pStatus'] ? $lang['shc_' . $value['pStatus']] : $GLOBALS['lang']['shc_progress'];
			}
			$data[$key]['Item'] = $rlListings -> getListingTitle( $data[$key]['Category_ID'], $data[$key], $value['Listing_type'] );
			$data[$key]['Username'] = empty( $data[$key]['Account_ID'] ) ? $lang['administrator'] : $data[$key]['Username'];
			$data[$key]['left_time'] = $left_time['value'] > 0 ? $left_time['text'] : 0;

			$price = explode( "|", $value['price'] );
			$data[$key]['Price'] = (float)$price[0];
		} 	
	}
	
	$output['total'] = $count['count'];
	$output['data'] = $data;

	$reefless -> loadClass( 'Json' );
	echo $rlJson -> encode( $output );

	exit;
}
if ( isset( $_GET['action'] ) )
{
	$reefless -> loadClass( 'ListingTypes' );
	$reefless -> loadClass( 'Listings' );
	$reefless -> loadClass( 'ShoppingCart', null, 'shoppingCart' );
	$reefless -> loadClass( 'Auction', null, 'shoppingCart' );

	// get all languages
	$allLangs = $GLOBALS['languages'];
	$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );

	if ( $_GET['action'] == 'details' )
	{
		$bcAStep[0] = array( 'name' => $lang['shc_auction'], 'Controller' => 'shopping_cart', 'Vars' => 'module=auction' );
		$bcAStep[1] = array( 'name' => $lang['shc_auction_details'] );

	}

	$item_id = (int)$_GET['item_id'];

	/* get status auction */
	$sql = "SELECT `ID`, `shc_auction_won`, `shc_auction_status` FROM `" . RL_DBPREFIX . "listings` WHERE `ID` = '{$item_id}' LIMIT 1";
	$status_info = $rlDb -> getRow( $sql );

	if ( $status_info['shc_auction_won'] && $status_info['shc_auction_status'] == 'closed' )
	{	
		$sql = "SELECT `T1`.*, `T2`.`Username` AS `dUsername`, `T2`.`Mail` AS `dMail`, `T2`.`Own_address` AS `dOwn_address`, `T4`.`Gateway`, ";
		$sql .= "IF(`T2`.`Last_name` <> '' AND `T2`.`First_name` <> '', CONCAT(`T2`.`First_name`, ' ', `T2`.`Last_name`), `T2`.`Username`) AS `dFull_name`, ";
		$sql .= "`T3`.`Username` AS `bUsername`, `T3`.`Mail` AS `bMail`, `T3`.`Own_address` AS `bOwn_address`, ";
		$sql .= "IF(`T3`.`Last_name` <> '' AND `T3`.`First_name` <> '', CONCAT(`T3`.`First_name`, ' ', `T3`.`Last_name`), `T3`.`Username`) AS `bFull_name` ";
		$sql .= "FROM `" . RL_DBPREFIX . "shc_orders` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T2` ON `T1`.`Dealer_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T3` ON `T1`.`Buyer_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "transactions` AS `T4` ON `T1`.`Txn_ID` = `T4`.`Txn_ID` ";		
		$sql .= "WHERE `T1`.`Item_ID` = '{$item_id}' ";
		$sql .= "LIMIT 1";

		$auction_info = $rlDb -> getRow( $sql );

		if ( $auction_info )
		{
			$auction_info['item_details'] = $rlAuction -> getOrderTitle( $auction_info );	
		}
	}
	else
	{
		$auction_info = $rlAuction -> getAuctionLiveInfo( $item_id, true );
		
	}

	$rlSmarty -> assign_by_ref( 'status_info', $status_info );
	$rlSmarty -> assign_by_ref( 'auction_info', $auction_info );
}
else
{
	/* auction statuses */
	$auction_status = array(
			'active' => array(
					'key' => 'active',
					'name' => $GLOBALS['lang']['active']
				),
			'closed' => array(
					'key' => 'closed',
					'name' => $GLOBALS['lang']['shc_closed']
				)
		);
		
	$rlSmarty -> assign_by_ref( 'shc_auction_status', $auction_status );

	/* payment statuses */
	$payment_status = array(
			'paid' => array(
					'key' => 'paid',
					'name' => $GLOBALS['lang']['shc_paid']
				),
			'unpaid' => array(
					'key' => 'unpaid',
					'name' => $GLOBALS['lang']['shc_unpaid']
				)
		);
		
	$rlSmarty -> assign_by_ref( 'shc_payment_status', $payment_status );

	
	$bcAStep[] = array( 'name' => $lang['shc_auction'] );
}


$reefless -> loadClass( 'Auction', null, 'shoppingCart' );

$rlXajax -> registerFunction( array( 'deleteItem', $rlAuction, 'ajaxDeleteItem' ) );
$rlXajax -> registerFunction( array( 'deleteBid', $rlAuction, 'ajaxDeleteBid' ) );