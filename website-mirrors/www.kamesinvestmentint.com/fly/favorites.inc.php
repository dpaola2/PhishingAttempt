<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: FAVORITES.INC.PHP
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

$data = array();

if ( isset($_POST['ids']) && !empty($_POST['ids']) )
{
	$reefless -> loadClass('Listings');
	$reefless -> loadClass('Actions');

	$_COOKIE['favorites'] = $_POST['ids'];
	$pInfo['current'] = (int)$_POST['pg'];
	$listings = $rlListings -> getMyFavorite('ID', 'asc', $pInfo['current'], $config['iFlynaxConnect_listings_per_page']);

	if ( !empty($listings) )
	{
		// build next page
		$pInfo['calc'] = $rlListings -> calc;
		$pInfo['total'] = count($listings);
		$pInfo['per_page'] = $config['iFlynaxConnect_listings_per_page'];
		$nextPage = $iPhone -> paging($pInfo);

		$data = $iPhone -> buildListingsShortForm($listings);

		if ( $nextPage > 0 )
		{
			array_push($data, $iPhone -> getTextLoadMore($nextPage));
		}
		unset($listings);
	}
}

$iPhone -> printAsXml($data);