<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: QUICK_SEARCH.INC.PHP
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

$queryData = $_POST['f'];
$query = trim($queryData['keyword_search']);
$query = preg_replace('/(\\s)\\1+/', ' ', $query);
$query = str_replace('%', '', $query);
$data = array();

if ( !empty( $query ) )
{
	$reefless -> loadClass('Search');
	$rlSearch -> fields['keyword_search'] = array(
		'Key' => 'keyword_search',
		'Type' => 'text'
	);

	$pInfo['current'] = (int)$_POST['pg'];
	$listings = $rlSearch -> search($queryData, false, $pInfo['current'], $config['iFlynaxConnect_listings_per_page']);

	if ( !empty($listings) )
	{
		foreach( $listings as $key => $listing)
		{
			array_push($data, array(
					'title' => $listing['title'],
					'id' => (int)$listing['ID']
				)
			);
		}
		unset( $listings );
	}
}

$iPhone -> printAsXml($data);