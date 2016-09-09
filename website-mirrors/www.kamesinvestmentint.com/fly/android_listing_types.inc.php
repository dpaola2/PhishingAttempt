<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: ANDROID_LISTING_TYPES.INC.PHP
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

		$field = $field == 'Status' ? 'Android_status' : $field;
		
		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);
		
		$rlActions -> updateOne($updateData, 'listing_types');
		
		exit;
	}
	
	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );
	$sort = $rlValid -> xSql( $_GET['sort'] );
	$sortDir = $rlValid -> xSql( $_GET['dir'] );
	
	$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT `T1`.*, `T2`.`Value` AS `name` ";
	$sql .= "FROM `". RL_DBPREFIX ."listing_types` AS `T1` ";
	$sql .= "LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON CONCAT('listing_types+name+',`T1`.`Key`) = `T2`.`Key` AND `T2`.`Code` = '". RL_LANG_CODE ."' ";
	$sql .= "WHERE `T1`.`Status` <> 'trash' ";
	if ( $sort )
	{
		$sortField = $sort == 'name' ? "`T2`.`Value`" : "`T1`.`{$sort}`";
		$sql .= "ORDER BY {$sortField} {$sortDir} ";
	}
	$sql .= "LIMIT {$start}, {$limit}";
	
	$data = $rlDb -> getAll($sql);
	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );

	foreach ( $data as $key => $value )
	{
		$data[$key]['Android_status'] = $GLOBALS['lang'][$value['Android_status']];
	}
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;

	echo $rlJson -> encode( $output );
}
/* ext js action end */

else
{
	$icons = array(
    	'default',
    	'auto',
    	'auto_parts',
    	'job',
    	'services',
    	'listings',
    	'pets',
    	'boat_staff',
    	'med_service',
    	'boats',
    	'property',
    	'guys',
    	'girls'
	);
	$rlSmarty -> assign_by_ref('icons', $icons);
	
	if ( $_GET['action'] == 'edit' ) {
		$key = $rlValid -> xSql($_GET['key']);
		
		$rlSmarty -> assign('cpTitle', $lang['edit_type']);
		
		$bcAStep = $lang['edit_type'];
		
		// get current listing type info
		$type_info = $rlDb -> fetch(array('ID', 'Key', 'Android_position', 'Android_icon', 'Android_status'), array( 'Key' => $key ), null, null, 'listing_types', 'row');
		$type_info['name'] = $lang['listing_types+name+'.$key];
		$rlSmarty -> assign_by_ref('type_info', $type_info);

		if ($_GET['action'] == 'edit' && !$_POST['fromPost'])
		{
			$_POST['status'] = $type_info['Android_status'];
			$_POST['icon'] = $type_info['Android_icon'] ? $type_info['Android_icon'] : 'default';
		}
		
		$set_icon = $_POST['icon'] == 'default' ? '' : $_POST['icon'];
		
		if ( isset($_POST['submit']) ) {
			$update = array(
				'fields' => array(
					'Android_icon' => $set_icon,
					'Android_status' => $_POST['status']
				),
				'where' => array(
					'ID' => $type_info['ID']
				)
			);
			
			$rlActions -> updateOne($update, 'listing_types');
			
			$message = $lang['listing_type_edited'];
			$aUrl = array('controller' => $controller);
			
			/* redirect */
			$reefless -> loadClass('Notice');
			$rlNotice -> saveNotice($message);
			$reefless -> redirect($aUrl);
		}
	}
}