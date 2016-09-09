<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: MY_AUCTIONS.INC.PHP
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
$reefless -> loadClass( 'Listings' );
$reefless -> loadClass( 'ShoppingCart', null, 'shoppingCart' );
$reefless -> loadClass( 'Auction', null, 'shoppingCart' );

$item_id = (int)$_GET['item'];

/* define module */
$request = explode( '/', $_GET['rlVareables'] );
$requestModule = array_pop( $request );
$module = $requestModule ? $requestModule : $_GET['module'];

$rlSmarty -> assign_by_ref( 'auction_mod', $module );

if ( $item_id )
{
	/* get auction info */
	if ( $module == 'live' )
	{
		$auction_info = $rlAuction -> getAuctionLiveInfo( $item_id, true );
	}
	else
	{
		$auction_info = $rlAuction -> getAuctionInfo( $item_id, true );
	}

	/* get my bids */
	$auction_info['bids'] = $rlAuction -> getMyBids( $module == 'live' ? $auction_info['ID'] : $auction_info['Item_ID'] );

	$rlSmarty -> assign_by_ref( 'auction_info', $auction_info );

	/* add bread crumbs item */
	unset($bread_crumbs[count($bread_crumbs) - 1]);

	$bread_crumbs[] = array(
		'name' => $GLOBALS['lang']['pages+name+shc_auctions'],
		'title' => $GLOBALS['lang']['pages+name+shc_auctions'],
		'path' => $pages['shc_auctions'] . ( $module ? '/' . $module : '' )
	);

	$bread_crumbs[] = array(
		'name' => $auction_info['Txn_ID'] ? $GLOBALS['lang']['shc_auction_number'] . $auction_info['Txn_ID'] : $GLOBALS['lang']['shc_auction_details']
	);
}
else
{
    $tabs = array(
			'winnerbids' => array(
				'key' => 'winnerbids',
				'name' => $GLOBALS['lang']['shc_winner_bids']
			),
			'live' => array(
				'key' => 'live',
				'name' => $GLOBALS['lang']['shc_member_bids']
			),
			'dontwin' => array(
				'key' => 'dontwin',
				'name' => $GLOBALS['lang']['shc_dont_win']
			)
		);
		
	$rlSmarty -> assign_by_ref( 'tabs', $tabs );
	
	/* current page */
	$pInfo['current'] = (int)$_GET['pg'];

	/* fields for sorting */
	$sorting = array(
		'total' => array(
			'name' => $lang['shc_price'],
			'field' => 'Total'
		),
		'status' => array(
			'name' => $lang['status'],
			'field' => 'Status'
		),
		'date' => array(
			'name' => $lang['date'],
			'field' => 'Date'
		)
	);
	$rlSmarty -> assign_by_ref( 'sorting', $sorting );

	// define sort field
	$sort_by = empty ( $_GET['sort_by'] ) ? $_SESSION['mb_sort_by'] : $_GET['sort_by'];
	if ( !empty( $sorting[$sort_by] ) )
	{
		$order_field = $sorting[$sort_by]['field'];
	}
	$_SESSION['mb_sort_by'] = $sort_by;
	$rlSmarty -> assign_by_ref( 'sort_by', $sort_by );

	// define sort type
	$sort_type = empty( $_GET['sort_type'] ) ? $_SESSION['mb_sort_type'] : $_GET['sort_type'];
	$sort_type = in_array( $sort_type, array( 'asc', 'desc' ) ) ? $sort_type : false;
	$_SESSION['mb_sort_type'] = $sort_type;
	$rlSmarty -> assign_by_ref( 'sort_type', $sort_type );	

	switch ( $module )
	{
		case 'dontwin' :
			$auctions = $rlAuction -> getNotWonAuctions( $order_field, $sort_type, $pInfo['current'], $config['shc_orders_per_page'] );
			break;

		case 'live' :
			$auctions = $rlAuction -> getNotWonAuctions( $order_field, $sort_type, $pInfo['current'], $config['shc_orders_per_page'], true );
			break;

		default: 
			$auctions = $rlAuction -> getMyAuctions( $order_field, $sort_type, $pInfo['current'], $config['shc_orders_per_page'] );
			break;
	}

	$rlSmarty -> assign_by_ref( 'auctions', $auctions );

	$pInfo['calc'] = $rlAuction -> calc;
	$rlSmarty -> assign_by_ref( 'pInfo', $pInfo );

	$rlHook -> load( 'phpShcAuctionsBottom' );
}
