<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: MY_PURCHASES.INC.PHP
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

$reefless -> loadClass( 'Notice' );
$reefless -> loadClass( 'Actions' );
$reefless -> loadClass( 'ShoppingCart', null, 'shoppingCart' );

$item_id = (int)$_GET['item'];

if ( $item_id )
{
	/* get order info	*/
	$sql = "SELECT `T1`.*, `T2`.`Gateway`, `T3`.`Username` AS `bUsername`, `T3`.`Own_address` AS `bOwn_address`, ";
	$sql .= "`T4`.`Username` AS `dUsername`, `T4`.`Own_address` AS `dOwn_address`, ";
	$sql .= "GROUP_CONCAT(SUBSTRING_INDEX(`T5`.`Item`, ', $', 1) ORDER BY `T5`.`Date` DESC SEPARATOR '<br />') AS `title`, ";

	$sql .= "IF(`T3`.`Last_name` <> '' AND `T3`.`First_name` <> '', CONCAT(`T3`.`First_name`, ' ', `T3`.`Last_name`), `T3`.`Username`) AS `bFull_name`, ";
	$sql .= "IF(`T4`.`Last_name` <> '' AND `T4`.`First_name` <> '', CONCAT(`T4`.`First_name`, ' ', `T4`.`Last_name`), `T4`.`Username`) AS `dFull_name` ";

	$sql .= "FROM `" . RL_DBPREFIX . "shc_orders` AS `T1` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T3` ON `T1`.`Buyer_ID` = `T3`.`ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T4` ON `T1`.`Dealer_ID` = `T4`.`ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_order_details` AS `T5` ON `T1`.`Order_key` = CONCAT(`T5`.`Order_key`, '-D', `T1`.`Dealer_ID`) ";
	$sql .= "WHERE `T1`.`ID` = '{$item_id}' AND `T1`.`Buyer_ID` = '{$account_info['ID']}' AND `T1`.`Type` = 'shop' ";
	$sql .= "LIMIT 1";

	$order_info = $rlDb -> getRow( $sql );	

	if ( !empty( $order_info['Order_key'] ) )
	{
		$bread_crumbs[] = array(
				'name' => $GLOBALS['lang']['shc_view_order_details'] . ' (#'.$order_info['Order_key'].')'
			);

		$total = 0;
		$order_key = explode( "-", $order_info['Order_key'] );

		$order_info['pStatus'] = $lang['shc_' . $order_info['pStatus']];
		$order_info['Shipping_status'] = $lang['shc_' . $order_info['Shipping_status']];

		$sql = "SELECT `T1`.*, `T3`.`Path`, `T3`.`Type` AS `Listing_type`, `T3`.`Key` AS `Cat_key`, `T2`.`Main_photo`, `T2`.`shc_quantity` ";
		$sql .= "FROM `" . RL_DBPREFIX . "shc_order_details` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listings` AS `T2` ON `T1`.`Item_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T2`.`Category_ID` = `T3`.`ID` ";
		$sql .= "WHERE `T1`.`Order_key` = '{$order_key[0]}' ";
		
		if ( $GLOBALS['config']['shc_method'] == 'multi' )
		{
			$sql .= "AND `T1`.`Dealer_ID` = '{$order_info['Dealer_ID']}' ";
		}

		$sql .= "ORDER BY `T1`.`Date` DESC";

		$order_details = $rlDb -> getAll( $sql );
		
		if ( !empty( $order_details ) )
		{
			foreach( $order_details as $iKey => $iVal )
			{
				$order_details[$iKey]['total'] = round( ( $iVal['Quantity'] * $iVal['Price'] ), 2 );
				$total += $order_details[$iKey]['total'];
			}

			$total = round( $total, 2 );
			$order_info['items'] = $order_details; 
		}

		$rlSmarty -> assign_by_ref( 'order_info', $order_info );
		$rlSmarty -> assign( 'total', $total );

		/* enable print page */
		$print_url = SEO_BASE;
		$print_url .= $config['mod_rewrite'] ? $pages['shc_print'] .'.html?item=item-purchased&id=' . $item_id : '?page='. $pages['shc_print'] . '&item=item-purchased&id=' . $item_id;

		$navIcons[] = '<a title="'. $lang['print_page'] .'" ref="nofollow" class="print" href="'. $print_url . '"> <span></span> </a>';
		$rlSmarty -> assign_by_ref( 'navIcons', $navIcons );
	}                 
	else
	{
		$sError = true;
	}
}
else
{
	if ( isset( $_GET['canceled'] ) || isset( $_GET['completed'] ) )
	{
		if ( isset( $_GET['completed'] ) )
		{
			$rlNotice -> saveNotice( $lang['invoices_payment_completed'] );
		}
		if ( isset( $_GET['canceled'] ) )
		{
			$errors[] = $lang['invoices_payment_canceled'];
			$rlSmarty -> assign_by_ref( 'errors', $errors );
		}
	}

	$pInfo['current'] = (int)$_GET['pg'];
	$page = $pInfo['current'] ? $pInfo['current'] - 1 : 0;

	$from = $page * $config['shc_orders_per_page'];
	$limit = $config['shc_orders_per_page'];

	$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT `T1`.*,  ";
	$sql .= "GROUP_CONCAT(SUBSTRING_INDEX(`T3`.`Item`, ', $', 1) ORDER BY `T3`.`Date` DESC SEPARATOR '<br />') AS `title` ";
	$sql .= "FROM `".RL_DBPREFIX."shc_orders` AS `T1` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_order_details` AS `T3` ON `T1`.`Order_key` = CONCAT(`T3`.`Order_key`, '-D', `T1`.`Dealer_ID`) ";
	$sql .= "WHERE `T1`.`Buyer_ID` = '{$account_info['ID']}' AND `T1`.`Type` = 'shop' ";

	$rlHook -> load( 'shcPurchasesSqlWhere', $sql );

	$sql .= "GROUP BY `T1`.`ID` ";
	$sql .= "ORDER BY `Date` DESC LIMIT {$from}, {$limit}";
	$orders = $rlDb-> getAll( $sql );

	$calc = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `calc`" );

	foreach($orders as $oKey => $oVal)
	{
		if($oVal['Type'] == 'auction')
		{
			$listing = $rlListings -> getListing( $oVal['Item_ID'], true );
			
			if($listing)
			{
				$orders[$oKey]['title'] = $listing['listing_title'];
			}
			
			unset($listing);
		}
	}

	$pInfo['calc'] = $calc['calc'];
	$rlSmarty -> assign_by_ref( 'pInfo', $pInfo );

	$rlHook -> load( 'phpShcPurchasesBottom' );	

	$rlSmarty -> assign_by_ref( 'orders', $orders );
}