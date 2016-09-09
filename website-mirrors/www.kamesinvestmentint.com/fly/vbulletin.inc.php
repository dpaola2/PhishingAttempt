<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: VBULLETIN.INC.PHP
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

$reefless -> loadClass('VBulletin', null, 'vbulletin');

if ( false === $rlVBulletin -> fetchImportLogs(true) )
{
	$errors = $lang['vbulletin_settingsEmpty'];
}

// register ajax methods
$rlXajax -> registerFunction(array('installProduct', $rlVBulletin, 'ajaxInstallProduct'));
$rlXajax -> registerFunction(array('importFromFlynax', $rlVBulletin, 'ajaxImportFromFlynax'));
$rlXajax -> registerFunction(array('importFromVBulletin', $rlVBulletin, 'ajaxImportFromVBulletin'));