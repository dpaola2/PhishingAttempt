<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: AVAILABILITY_LISTINGS.INC.PHP
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

$data['check_availability'] = $_SESSION['booking_availability'] = $_POST['availability'];

$reefless -> loadClass('Search');
$reefless -> loadClass('Listings');
$reefless -> loadClass('Booking', false, 'booking');

$pInfo['current'] = (int)$_GET['pg'];

$sorting = array(
	'type' => array(
		'name' => $lang['listing_type'],
		'field' => 'Listing_type',
		'Key' => 'Listing_type',
		'Type' => 'select'
	),
	'category' => array(
		'name' => $lang['category'],
		'field' => 'Category_ID',
		'Key' => 'Category_ID',
		'Type' => 'select'
	),
	'post_date' => array(
		'name' => $lang['join_date'],
		'field' => 'Date',
		'Key' => 'Date'
	)
);
$rlSmarty -> assign_by_ref('sorting', $sorting);

/* define sort field */
$sort_by = $_SESSION['booking_search_sort_by'] = empty($_REQUEST['sort_by']) ? $_SESSION['booking_search_sort_by'] : $_REQUEST['sort_by'];

if ( !empty($sorting[$sort_by]) )
{
	$data['sort_by'] = $sort_by;
	$rlSmarty -> assign_by_ref('sort_by', $sort_by);
}

// define sort type
$sort_type = $_SESSION['booking_search_sort_type'] = empty($_REQUEST['sort_type']) ? $_SESSION['booking_search_sort_type'] : $_REQUEST['sort_type'];
if ( $sort_type )
{
	$data['sort_type'] = $sort_type = in_array($sort_type, array('asc', 'desc')) ? $sort_type : false ;
	$rlSmarty -> assign_by_ref( 'sort_type', $sort_type );
}

// prepare search
$rlSearch -> fields['check_availability'] = array(
	'Key' => 'check_availability',
	'Type' => 'date',
	'Default' => 'single'
);

// search
$listings = $rlSearch -> search($data, $rlBooking -> bookingType, $pInfo['current'], $config['listings_per_page']);

$rlSmarty -> assign_by_ref('listings', $listings);

$pInfo['calc'] = $rlSearch -> calc;
$rlSmarty -> assign_by_ref('pInfo', $pInfo);

if ( $listings )
{
	$page_info['name'] = str_replace(array('{number}', '{type}'), array($pInfo['calc'], 'Availability Listings'), $lang['listings_found']);
}

// register ajax methods
$rlXajax -> registerFunction(array('addToFavorite', $rlListings, 'ajaxAddToFavorite'));

if ( defined('IS_LOGIN') )
{
	$rlXajax -> registerFunction(array('restoreFavorite', $rlListings, 'ajaxRestoreFavorite'));
}