<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: NEW_LISTINGS.INC.PHP
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

$reefless -> loadClass('ListingTypes');
$reefless -> loadClass('Listings');

$pInfo['current'] = (int)$_POST['pg'];

// get requested type
foreach( $rlListingTypes -> types as $type )
{
	$defaultType = !$defaultType ? $type['Key'] : $defaultType;
	if ( isset($_GET[$type['Key']]) )
	{
		$requestedType = $type['Key'];
		break;
	}
}

$requestedType = $_POST['type'];
$lTypes = array_keys($rlListingTypes -> types);
$default = $lTypes[1];
$requestedType = $requestedType ? $requestedType : $default;
$listings = $rlListings -> getRecentlyAdded($pInfo['current'], $config['iFlynaxConnect_listings_per_page'], $requestedType);

// build next page
$pInfo['calc'] = $rlListings -> calc;
$pInfo['total'] = count( $listings );
$pInfo['per_page'] = $config['iFlynaxConnect_listings_per_page'];
$nextPage = $iPhone -> paging($pInfo);

$data = array();
if ( !empty($listings) )
{
	$data = $iPhone -> buildListingsShortForm($listings);

	if ( $nextPage > 0 )
	{
		array_push($data, $iPhone -> getTextLoadMore($nextPage));
	}
}
$iPhone -> printAsXml($data);