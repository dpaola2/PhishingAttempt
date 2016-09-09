<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: TESTIMONIALS.INC.PHP
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
	if ( $_GET['action'] == 'update' )
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
				'ID' => $id
			)
		);
		
		$rlActions -> updateOne( $updateData, 'testimonials');
		exit;
	}
	
	/* data read */
	$limit = (int)$_GET['limit'];
	$start = (int)$_GET['start'];
	
	$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.* ";
	$sql .= "FROM `". RL_DBPREFIX ."testimonials` AS `T1` ";
	$sql .= "LEFT JOIN `". RL_DBPREFIX ."accounts` AS `T2` ON `T1`.`Account_ID` = `T2`.`ID` ";
	$sql .= "ORDER BY `T1`.`ID` DESC ";
	$sql .= "LIMIT {$start}, {$limit}";
	$data = $rlDb -> getAll($sql);
	
	$count = $rlDb -> getRow("SELECT FOUND_ROWS() AS `testimonials`");
	
	foreach ($data as $key => $value)
	{
		$data[$key]['Status'] = $lang[$data[$key]['Status']];
	}

	$reefless -> loadClass('Json');
	
	$output['total'] = $count['count'];
	$output['data'] = $data;

	echo $rlJson -> encode( $output );
}
else
{
	$reefless -> loadClass('Testimonials', null, 'testimonials');
	$rlXajax -> registerFunction(array('deleteTestimonial', $rlTestimonials, 'ajaxDelete'));
}
/* ext js action end */