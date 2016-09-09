<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: UNSUBSCRIBE.INC.PHP
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

/* get requested e-mail address from post */
$hash = $rlValid -> xSql( $_GET['hash'] );
$type = $hash[0];

$email = substr($hash, 1, 32);
$date = substr($hash, 33);

switch ($type){
	case 1:
		$table = 'accounts';
		$field = 'Subscribe';
		$where = 'Mail';
		$value = 0;
		
		break;
	case 2:
		$table = 'subscribers';
		$field = 'Status';
		$where = 'Mail';
		$value = 'approval';
	
		break;
	case 3:
		$table = 'contacts';
		$where = 'Email';
		$field = 'Subscribe';
		$value = 0;
	
		break;
}

if ( $table && $field && $where )
{
	$id = $rlDb -> getOne('ID', "MD5(`{$where}`) = '{$email}' AND MD5(`Date`) = '{$date}' AND `{$field}` <> '{$value}'", $table);
}

if ( $id )
{
	/* update status */
	$reefless -> loadClass('Actions');
	
	$update = array(
		'fields' => array(
			$field => $value
		),
		'where' => array(
			'ID' => $id
		)
	);
	$rlActions -> updateOne($update, $table);
	
	$reefless -> loadClass( 'Notice' );
	$rlNotice -> saveNotice( str_replace('{sitename}', $GLOBALS['lang']['pages+title+home'], $lang['massmailer_newsletter_person_unsubscibed']) );
}
else
{
	$errors[] = str_replace('{sitename}', $GLOBALS['lang']['pages+title+home'], $lang['massmailer_newsletter_incorrect_request']);
	$rlSmarty -> assign_by_ref( 'errors', $errors );
}
