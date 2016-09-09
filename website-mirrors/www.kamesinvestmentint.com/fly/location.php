<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: LOCATION.PHP
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

require_once('../../../includes/config.inc.php');
require_once(RL_CLASSES .'rlDb.class.php');
require_once(RL_CLASSES .'reefless.class.php');

$reefless = new reefless;
$reefless -> loadClass('Json');

$country = $_REQUEST['country'];
$postal_code = $_REQUEST['postal_code'];

$url = 'http://205.234.232.103/~flgeocod/geocode.php?domain&license&country={code}&postal_code={zip}';
$url = str_replace(array('{code}', '{zip}'), array($country, $postal_code), $url);

$response = $reefless -> getPageContent($url);

header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: "*"');
header('X-Powered-By: Flynax API');
header('Content-Type: application/json; charset=utf-8');

//
if ( $response )
	echo $response;
else
	echo '[]';