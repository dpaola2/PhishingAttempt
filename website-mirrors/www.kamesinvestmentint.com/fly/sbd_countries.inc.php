<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: SBD_COUNTRIES.INC.PHP
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
	
	/* date update */
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );
		
		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br($_GET['value']) );
		$id = $rlValid -> xSql( $_GET['id'] );
		$key = $rlValid -> xSql( $_GET['key'] );

		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'Code' => $id
			)
		);
		
		$rlActions -> updateOne( $updateData, 'sbd_countries');
		
		/* update hook */
		$reefless -> loadClass('Listings');
		$reefless -> loadClass('SearchByDistance', null, 'search_by_distance');
		$rlSearchByDistance -> updateBox();
		
		exit;
	}
	
	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );
	
	$langCode = $rlValid -> xSql( $_GET['lang_code'] );
	$phrase = $rlValid -> xSql( $_GET['phrase'] );

	$rlDb -> setTable( 'sbd_countries' );
	$data = $rlDb -> fetch( '*', null, "ORDER BY `Code` ASC", array( $start, $limit ) );
	$rlDb -> resetTable();
	
	foreach ($data as $key => $value)
	{
		$data[$key]['Status'] = $GLOBALS['lang'][$data[$key]['Status']];
		$data[$key]['Name'] = $lang['sbd_countries+name+sbd_country_'. $value['Code']];
	}

	$count = $rlDb -> getRow( "SELECT COUNT(`Code`) AS `count` FROM `" . RL_DBPREFIX . "sbd_countries`" );
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;

	echo $rlJson -> encode( $output );
}
/* ext js action end */

else
{
	/* get all languages */
	$allLangs = $GLOBALS['languages'];
	$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );
		
	$reefless -> loadClass('Listings');
	$reefless -> loadClass('SearchByDistance', null, 'search_by_distance');
	
	/* register ajax methods */
	$rlXajax -> registerFunction( array( 'updateCountry', $rlSearchByDistance, 'ajaxUpdateCountry' ) );
	$rlXajax -> registerFunction( array( 'addCountry', $rlSearchByDistance, 'ajaxAddCountry' ) );
	$rlXajax -> registerFunction( array( 'editFillIn', $rlSearchByDistance, 'ajaxEditFillIn' ) );
	$rlXajax -> registerFunction( array( 'removeCountry', $rlSearchByDistance, 'ajaxRemoveCountry' ) );
}