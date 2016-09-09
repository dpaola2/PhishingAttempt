<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: REPORTBROKENLISTING.INC.PHP
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
if ($_GET['q'] == 'ext')
{
	/* system config */
	require_once( '../../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
	require_once( RL_LIBS . 'system.lib.php' );
	
	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );

	$reefless -> loadClass('Listings');
	$reefless -> loadClass('Common');
	$reefless -> loadClass( 'ListingTypes' );	
	$reefless -> loadClass( 'Json' );	

	$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.`ID`, `T1`.`Message`, `T1`.`Listing_ID`, `T1`.`Account_ID`, `T1`.`Date`, ";
	$sql .= "`T2`.`Username`, `T2`.`First_name`, `T2`.`Last_name` ";
	$sql .= "FROM `" . RL_DBPREFIX . "report_broken_listing` AS `T1` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T2` ON `T1`.`Account_ID` = `T2`.`ID` ";
	$data = $rlDb -> getAll( $sql );
	
	foreach ($data as $key => $value)
	{
		$data[$key]['Name'] = $value['First_name'] || $value['Last_name'] ? trim($value['First_name'] .' '. $value['Last_name']) : $value['Username'];
		unset($data[$key]['Username'], $data[$key]['First_name'], $data[$key]['Last_name']);
	}
	
	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;
	
	echo $rlJson -> encode( $output );
}
/* ext js action end */

else 
{	
	
	$reefless -> loadClass('ReportBrokenListing', null, 'reportBrokenListing');		
	
	/* register ajax methods */
	$rlXajax -> registerFunction( array( 'deletereportBrokenListing', $rlReportBrokenListing, 'ajaxDeletereportBrokenListing' ) );
	$rlXajax -> registerFunction( array( 'deleteListing', $rlReportBrokenListing, 'ajaxDeleteListing' ) );
}