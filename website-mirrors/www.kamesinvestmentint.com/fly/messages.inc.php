<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.0
 *	LISENSE: http://www.flynax.com/license-agreement.html
 *	PRODUCT: General Classifieds
 *	
 *	FILE: MESSAGES.INC.PHP
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

$reefless -> loadClass('Message');
$reefless -> loadClass('Account');

$id = (int)$_GET['id'];

$account_info = array(
	'ID' => $_SESSION['sessAdmin']['user_id'],
	'Mail' => $_SESSION['sessAdmin']['mail'],
	'Full_name' => $_SESSION['sessAdmin']['name'] ? $_SESSION['sessAdmin']['name'] : 'Administrator'
);
$rlSmarty -> assign_by_ref('account_info', $account_info);

if ( $id )
{	
	/* get contact information */
	if ( isset($_GET['administrator']) )
	{
		$contact = $rlDb -> fetch(array('ID', 'Name', 'Email'), array('ID' => $id), null, 1, 'admins', 'row');
		$contact['Full_name'] = $contact['Name'] ? $contact['Name'] : $lang['administrator'];
		$contact['Admin'] = 1;
	}
	else
	{
		$contact = $rlAccount -> getProfile((int)$id);
	}
	$rlSmarty -> assign_by_ref('contact', $contact);
	
	/* get contact messages */
	$messages = $rlMessage -> getMessages( $id );
	
	if( empty($messages) )
	{
		$sError = true;
	}
	else
	{
		$rlSmarty -> assign_by_ref( 'messages', $messages);
		
		/* redefine bread crumbs */
		$bread_crumbs[] = array(
			'name' => $lang['chat_with'] . ' ' . $contact['Full_name']
		);
		$page_info['name'] = $lang['chat_with'] . ' ' . $contact['Full_name'];
	}

	/* check new messages one more time */
	$message_info = $rlCommon -> checkMessages();
	if ( !empty($message_info) )
	{
		$rlSmarty -> assign_by_ref('new_messages', $message_info);
	}
	
	$rlHook -> load('messagesBottom');
	
	/* register ajax methods */
	$rlXajax -> registerFunction( array( 'sendMessage', $rlMessage, 'ajaxSendMessage' ) );
	$rlXajax -> registerFunction( array( 'refreshMessagesArea', $rlMessage, 'ajaxRefreshMessagesArea' ) );
	$rlXajax -> registerFunction( array( 'removeMsg', $rlMessage, 'ajaxRemoveMsg' ) );
}
else
{
	$contacts = $rlMessage -> getContacts();
	$rlSmarty -> assign_by_ref( 'contacts', $contacts );
	
	$rlXajax -> registerFunction( array( 'removeContacts', $rlMessage, 'ajaxRemoveContacts' ) );
}

$rlHook -> load('apPhpMessagesBottom');