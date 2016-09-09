<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: SHOPPING_CART.INC.PHP
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
		
        if ( $field == 'Shipping_status' )
		{
			/* send notification to buyer */
			$order_info = $rlShoppingCart -> getOrderShortInfo( $id );
			
			if ( !empty( $order_info ) )
			{
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'shc_shipping_status_changed' );
				
				$find = array( '{username}', '{order_key}', '{status}' );
				$replace = array( $order_info['dFull_name'], $order_info['Order_key'], $GLOBALS['lang']['shc_' . $value] );
				
				$mail_tpl['body'] = str_replace( $search, $replace, $mail_tpl['body'] );
				$GLOBALS['rlMail'] -> send( $mail_tpl, $order_info['Mail'] );
			}
		}

		$rlActions -> updateOne( $updateData, 'shc_orders' );

		exit;
	}

	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );

	$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.*, `T2`.`Gateway`, `T3`.`Username` AS `bUsername`, `T3`.`Last_name` AS `bLast_name`,  `T3`.`First_name` AS `bFirst_name`, ";
	$sql .= "`T4`.`Username` AS `dUsername`, `T4`.`Last_name` AS `dLast_name`,  `T4`.`First_name` AS `dFirst_name`, ";
	$sql .= "GROUP_CONCAT(SUBSTRING_INDEX(`T5`.`Item`, ', $', 1) ORDER BY `T5`.`Date` DESC SEPARATOR '<br />') AS `title` ";
	$sql .= "FROM `" . RL_DBPREFIX . "shc_orders` AS `T1` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T3` ON `T1`.`Buyer_ID` = `T3`.`ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T4` ON `T1`.`Dealer_ID` = `T4`.`ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_order_details` AS `T5` ON `T1`.`Order_key` = CONCAT(`T5`.`Order_key`, '-D', `T1`.`Dealer_ID`) AND `T5`.`Dealer_ID` = `T1`.`Dealer_ID` ";

    $sql .= "WHERE `T1`.`pStatus` <> 'trash' AND `T1`.`Type` = 'shop' ";
	$sql .= "GROUP BY `T1`.`ID` ";
	$sql .= "ORDER BY `T2`.`Date` DESC LIMIT {$start}, {$limit}";

	$data = $rlDb -> getAll( $sql );
	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );

	foreach( $data as $key => $val )
	{
		$data[$key]['pStatus'] = $lang['shc_' . $val['pStatus']];
		$data[$key]['Shipping_status'] = $lang['shc_' . $val['Shipping_status']];
	}

	$output['total'] = $count['count'];
	$output['data'] = $data;

	$reefless -> loadClass( 'Json' );
	echo $rlJson -> encode( $output );
	exit();
}

$reefless -> loadClass( 'ShoppingCart', null, 'shoppingCart' );

if ( isset( $_GET['action'] ) )
{
	// get all languages
	$allLangs = $GLOBALS['languages'];
	$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );

	if ( $_GET['action'] == 'view' )
	{
		$item_id = (int)$_GET['item'];

		$bcAStep = $lang['shc_view_order_details'];

		/* get transaction info	*/
		$sql = "SELECT `T1`.*, `T2`.`Gateway`, `T3`.`Username` AS `bUsername`, `T3`.`Last_name` AS `bLast_name`,  `T3`.`First_name` AS `bFirst_name`, ";
		$sql .= "`T4`.`Username` AS `dUsername`, `T4`.`Last_name` AS `dLast_name`,  `T4`.`First_name` AS `dFirst_name`, ";
		$sql .= "GROUP_CONCAT(SUBSTRING_INDEX(`T5`.`Item`, ', $', 1) ORDER BY `T5`.`Date` DESC SEPARATOR '<br />') AS `title` ";
		$sql .= "FROM `" . RL_DBPREFIX . "shc_orders` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "transactions` AS `T2` ON `T1`.`Txn_ID` = `T2`.`Txn_ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T3` ON `T1`.`Buyer_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T4` ON `T1`.`Dealer_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "shc_order_details` AS `T5` ON `T1`.`Order_key` = CONCAT(`T5`.`Order_key`, '-D', `T1`.`Dealer_ID`) ";
		$sql .= "WHERE `T1`.`ID` = '{$item_id}' ";
		$sql .= "LIMIT 1";

		$order_info = $rlDb -> getRow( $sql );	

		if ( !empty( $order_info ) )
		{
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
				foreach ( $order_details as $iKey => $iVal )
				{
					$order_details[$iKey]['total'] = round( ( $iVal['Quantity'] * $iVal['Price'] ), 2 );
					$total += $order_details[$iKey]['total'];
				}

				$total = round( $total, 2 );
				$order_info['items'] = $order_details; 
			}
		}
		$rlSmarty -> assign_by_ref( 'order_info', $order_info );
		$rlSmarty -> assign( 'total', $total );
	}
}

if ( isset( $_GET['module'] ) )
{
	if ( is_file( RL_PLUGINS . 'shoppingCart' . RL_DS .'admin'. RL_DS . $_GET['module'] . '.inc.php' ) )
	{
		require_once( RL_PLUGINS . 'shoppingCart' . RL_DS . 'admin' . RL_DS . $_GET['module'] . '.inc.php' );
	}
	else
	{
		$sError = true;
	}
}
else
{
	/* register ajax methods */
	$rlXajax -> registerFunction( array( 'deleteOrder', $rlShoppingCart, 'ajaxDeleteOrder' ) );
}