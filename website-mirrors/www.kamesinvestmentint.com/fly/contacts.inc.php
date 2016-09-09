<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.0
 *	LISENSE: http://www.flynax.com/license-agreement.html
 *	PRODUCT: General Classifieds
 *	
 *	FILE: CONTACTS.INC.PHP
 *
 *	This script is a commercial software and any kind of using it must be 
 *	coordinate with Flynax Owners Team and be agree to Flynax License Agreement
 *
 *	This block may not be removed from this file or any other files with out 
 *	permission of Flynax respective owners.
 *
 *	Copyrights Flynax Classifieds Software | 2012
 *	http://www.flynax.com/
 *
 ******************************************************************************/

/* ext js action */
if ($_GET['q'] == 'ext')
{
	/* system config */
	require_once( '../../includes/config.inc.php' );
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
				'ID' => $id
			)
		);
		
		$rlHook -> load('apExtContactsUpdate');
		
		$rlActions -> updateOne( $updateData, 'contacts');
		exit;
	}
	
	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );
	$status = $rlValid -> xSql( $_GET['status'] );
	$sort = $rlValid -> xSql( $_GET['sort'] );
	$sortDir = $rlValid -> xSql( $_GET['dir'] );
	
	if( in_array($status, array('reviewed', 'new')) )
	{	
		$status = "`Status` = '" . $status . "'";
	}
	else
	{
		$status = "`Status` <> 'trash'";
	}

	$rlHook -> load('apExtContactsSql');
	
	$rlDb -> setTable( 'contacts' );
	$data = $rlDb -> fetch( '*', null, "WHERE {$status} ORDER BY `{$sort}` {$sortDir}", array( $start, $limit ) );
	$data = $rlLang -> replaceLangKeys( $data, 'contacts', array( 'name' ), RL_LANG_CODE, 'admin' );
	$rlDb -> resetTable();

	foreach ( $data as $key => $value )
	{
		$data[$key]['Status'] = $lang[$data[$key]['Status']];
		
		$rlHook -> load('apExtContactsData');
	}
	
	$count = $rlDb -> getRow( "SELECT COUNT(`ID`) AS `count` FROM `" . RL_DBPREFIX . "contacts` WHERE {$status}" );
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;

	echo $rlJson -> encode( $output );
}
/* ext js action end */

else
{
	$rlHook -> load('apPhpContactsTop');
	
	/* additional bread crumb step */
	if ($_GET['action'])
	{
		$bcAStep = $lang['view_contact'] ;
	}
	
	if ( $_GET['action'] == 'view' )
	{
		$id = (int)$_GET['id'];

		// get current contact info
		$contact = $rlDb -> fetch( '*', array( 'ID' => $id ), "AND `Status` <> 'trash'", 1, 'contacts', 'row' );
		$rlSmarty -> assign_by_ref( 'contact', $contact );
		
		$update = array(
			'fields' => array(
				'Status' => 'reviewed'
			),
			'where' => array(
				'ID' => $id
			)
		);
		$rlActions -> updateOne( $update, 'contacts' );
		
		if ( !$_POST['fromPost'] )
		{
			function flDddReply(&$str)
			{
				$str = '>>'. $str;
			}
			
			$mess = explode(PHP_EOL, $contact['Message']);
			array_walk($mess, 'flDddReply');
			
			$_POST['message'] = implode('<br />', $mess). '<br />';
		}
		
		if ( $_POST['submit'] )
		{
			if ( empty($_POST['subject']) )
			{
				$errors[] = str_replace( '{field}', "<b>".$lang['subject']."</b>", $lang['notice_field_empty']);
				$error_fields[] = 'subject';
			}
			
			if ( empty($_POST['message']) )
			{
				$errors[] = str_replace( '{field}', "<b>".$lang['message']."</b>", $lang['notice_field_empty']);
				$error_fields[] = 'message';
			}
			
			if( !empty($errors) )
			{
				$rlSmarty -> assign_by_ref( 'errors', $errors );
			}
			else 
			{
				$update = array(
					'fields' => array(
						'Status' => 'replied'
					),
					'where' => array(
						'ID' => $contact['ID']
					)
				);
				
				$rlActions -> updateOne($update, 'contacts');
				
				$reefless -> loadClass('Mail');
				$rlMail -> send(array('subject' => $_POST['subject'], 'body' => $_POST['message']), $contact['Email']);
				
				$aUrl = array( "controller" => $controller );
				
				$reefless -> loadClass( 'Notice' );
				$rlNotice -> saveNotice( $lang['notice_message_sent'] );
				$reefless -> redirect( $aUrl );
			}
		}
	}
	
	$rlHook -> load('apPhpContactsBottom');
	
	/* register ajax methods */
	$rlXajax -> registerFunction( array( 'deleteContact', $rlAdmin, 'ajaxDeleteContact' ) );
}