<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RV_LISTINGS.INC.PHP
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

global $reefless, $account_info, $rlSmarty, $rlXajax;

$reefless -> loadClass('RecentlyViewed', null, 'recentlyViewed');

$rv_listings_ids =  $rlDb -> getOne('rv_listings', "`ID` = '{$account_info['ID']}'", 'accounts');

if ( substr($rv_listings_ids, -1, 1) == ',' )
	$rv_listings_ids = substr_replace( $rv_listings_ids, '', strrpos($rv_listings_ids, ',') );

$tmp_rv_listings = $rlRecentlyViewed -> getRvListings( $rv_listings_ids, false, true );

if ( $tmp_rv_listings ) {
	/* removing inactive listings from storage and DB */
	foreach ($tmp_rv_listings as $key => $value) {
		if ( $value['Listing_status'] == 'active' && $value['Category_status'] == 'active' && $value['Owner_status'] == 'active' ) {
			$rv_listings[] = $value;
			$rv_ids = $rv_ids ? $rv_ids . "," . $value['ID'] : $value['ID'];
		} else {
			$inactive_listings = true;
		}
	}

	if ( $inactive_listings ) {
		if ( $account_info['ID'] )
			$rlDb -> query("UPDATE `". RL_DBPREFIX ."accounts` SET `rv_listings` = '{$rv_ids}' WHERE `ID` = '{$account_info['ID']}' LIMIT 1");

		$st_listings = "[";

		foreach ($rv_listings as $key => $listing) {
			$st_listings .= "['" . $listing['ID'] . "','" . $listing['Main_photo'] . "','" . $pages['lt_' . $listing['Listing_type']] . "','" . $listing['Path'] . "','" . addslashes( $listing['listing_title'] ) . "']";
			$st_listings .= $key != count($rv_listings) - 1 ? ',' : '';
		}

		$st_listings .= "]";

		$storage_postfix = str_replace( array('http://', 'https://', 'www.'), array('', '', ''), RL_URL_HOME );
		$storage_postfix = str_replace( array('.', '/'), array('_', '_'), $storage_postfix );

		$rlSmarty -> assign( 'inactive_listings', 1 );
		$rlSmarty -> assign( 'st_listings', $st_listings );

		$rv_listings_ids = $rv_ids;
	}
}

$pInfo['current'] = (int)$_GET['pg'];
$pInfo['calc'] = count(explode(',', $rv_listings_ids));
$rlSmarty -> assign_by_ref( 'pInfo', $pInfo );

$rv_listings = $rlRecentlyViewed -> getRvListings( $rv_listings_ids, $pInfo['current'] );
$rlSmarty -> assign('listings', $rv_listings);

$rlXajax -> registerFunction(array('removeRvListing', $rlRecentlyViewed, 'ajaxRemoveRvListing'));
$rlXajax -> registerFunction(array('removeAllRvListings', $rlRecentlyViewed, 'ajaxRemoveAllRvListings'));
$rlXajax -> registerFunction(array('loadRvListings', $rlRecentlyViewed, 'ajaxLoadRvListings'));