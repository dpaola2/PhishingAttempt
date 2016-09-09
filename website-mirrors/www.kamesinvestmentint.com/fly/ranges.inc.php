<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RANGES.INC.PHP
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

header('Content-Type: application/json;charset=UTF-8;');

require_once('../../includes/config.inc.php');
require_once(RL_CLASSES .'rlDb.class.php' );
require_once(RL_CLASSES .'reefless.class.php');

$rlDb = new rlDb();
$reefless = new reefless();
$reefless -> connect(RL_DBHOST, RL_DBPORT, RL_DBUSER, RL_DBPASS, RL_DBNAME);
$reefless->loadClass('Valid');

$listing_id = (int)$_GET['id'];
$data = $rlDb -> fetch(array('ID','From','To','Price'), array('Listing_ID' => $listing_id), "ORDER BY `From`", null, 'booking_rate_range');

if ( !empty($data) )
{
	$reefless -> loadClass('Valid');
	$GLOBALS['config']['price_delimiter'] = ',';

	foreach($data as $key => $rate)
	{
		$data[$key]['From'] = date('M d, Y', $rate['From']);
		$data[$key]['To'] = date('M d, Y', $rate['To']);
		$data[$key]['Price'] = $rlValid -> str2money($data[$key]['Price']);
	}
}

$reefless -> loadClass('Json');
echo $rlJson -> encode(array('ranges' => $data));