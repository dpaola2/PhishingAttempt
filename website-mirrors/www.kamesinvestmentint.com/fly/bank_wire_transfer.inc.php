<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: BANK_WIRE_TRANSFER.INC.PHP
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

$reefless -> loadClass( 'Actions' );
$reefless -> loadClass( 'Mail' );
$reefless -> loadClass( 'Notice' );
$reefless -> loadClass( 'BankWireTransfer', null, 'bankWireTransfer' );

/* if enabled Shopping Cart plugin */
if( $GLOBALS['config']['shc_method'] == 'multi')
{
	$reefless -> loadClass( 'ShoppingCart', false, 'shoppingCart' );
}

/* payment info */
if ( $_SESSION['complete_payment'] )
{
	$payment = $_SESSION['complete_payment'];

	$gateway = 'bankWireTransfer';
	$price = $payment['plan_info']['Price'];
	$plan_id = $payment['plan_info']['ID'];
	$item_id = $payment['item_id'];
	$item_name = $payment['item_name'];
	$callback_class = $payment['callback']['class'];
	$callback_method = $payment['callback']['method'];
	$callback_plugin = $payment['callback']['plugin'];
	$cancel_url = $payment['callback']['cancel_url'];
	$success_url = $payment['callback']['success_url'];
}

$bwt_type = $_SESSION['bwt_type'];

$rlSmarty -> assign_by_ref( 'bwt_type', $_SESSION['bwt_type'] );

if ( isset( $_GET['completed'] ) && $_GET['item_id'] )
{
	$_GET['item_id'] = $rlValid -> xSql( $_GET['item_id'] );

	/* get transaction info	*/
	$sql = "SELECT `T1`.* ";
	$sql .= "FROM `" . RL_DBPREFIX . "bwt_transactions` AS `T1` ";
	$sql .= "WHERE `T1`.`Txn_ID` = '{$_GET['item_id']}' ";
	$sql .= "LIMIT 1";

	$txn_info = $rlDb->getRow($sql);
	$rlSmarty -> assign_by_ref( 'txn_info', $txn_info );
	$rlSmarty -> assign( 'txn_id', $_GET['item_id'] );
	
	/* get payments details */
	if ( $txn_info['Type'] == 'by_check' )
	{
		if ( $txn_info['Dealer_ID'] )
		{
			$dealer_info = $rlDb -> fetch( '*', array( 'ID' =>  $txn_info['Dealer_ID'] ), null, 1, 'accounts', 'row' );
			$dealer_info['Full_name'] = $dealer_info['First_name'] && $dealer_info['Last_name'] ? $dealer_info['First_name'] . ' ' . $dealer_info['Last_name'] : $dealer_info['Username'];

			$payment_details[] = array(
					'name' => $dealer_info['Full_name'],
					'description' => $dealer_info['shc_bankWireTransfer_details']
				);
		}
		else
		{
			$sql = "SELECT * FROM `" . RL_DBPREFIX . "bwt_payment_details` ";
			$payment_details = $rlDb -> getAll( $sql );

			foreach( $payment_details as $key => $val )
			{
				$payment_details[$key]['name'] = $lang['payment_details+name+'.$val['Key']];
				$payment_details[$key]['description'] = $lang['payment_details+des+'.$val['Key']];
			}
		}

		$rlSmarty -> assign_by_ref( 'payment_details', $payment_details ); 
	}

	/* define item name */
	if ( $txn_info['Service'] == 'invoice' )
	{
		if ( !$txn_info['Item'] )
		{
			$sql_inv = "SELECT `ID`,`Txn_ID`,`Subject` FROM `" . RL_DBPREFIX . "invoices` WHERE `ID` = '{$txn_info['Item_ID']}' LIMIT 1";
			$invoice_info = $GLOBALS['rlDb'] -> getRow( $sql_inv );

			$txn_info['Item'] = $invoice_info['Subject'] . '(#' . $invoice_info['ID'] . ')';
			
			unset( $invoice_info );
		}
	}
	elseif( $txn_info['Service'] == 'banner' )
	{
		if ( !$txn_info['Item'] )
		{
			$sql = "SELECT `T1`.*, `T2`.`Key` AS `bpKey` ";
			$sql .= "FROM `" . RL_DBPREFIX . "banners` AS `T1` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "banner_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
			$sql .= "WHERE `T1`.`ID` = '{$val['Item_ID']}' LIMIT 1";

			$banner_info = $rlDb -> getRow( $sql );

			$txn_info['Item'] = $GLOBALS['lang']['banner_plans+name+'. $banner_info['bpKey']] . '(#' . $banner_info['ID'] . ')';

			unset($banner_info);
		} 
	}
	else
	{
		if ( !$txn_info['Item'] )
		{
			$listing = $rlListings -> getShortDetails( $txn_info['Item_ID'], $plan_info = true );
	    	$txn_info['Item'] = $listing['listing_title'];
		}
	}

	$navIcons[] = '<a title="'. $lang['print_page'] .'" ref="nofollow" class="print" href="'.SEO_BASE.'bwt-print.html?txn_id='.$_GET['txn_id'].'"> <span></span> </a>';
	$rlSmarty -> assign_by_ref( 'navIcons', $navIcons );

	$rlNotice -> saveNotice( $GLOBALS['lang']['bwt_complete_please_wait'] );

	unset($_SESSION['Txn_ID']);
}
else
{
	if( ( empty( $_POST['form'] ) || empty( $_POST['txn_id'] ) ) && !$_SESSION['Txn_ID'] )
	{
		$Txn_ID = $reefless -> generateHash( (int)$GLOBALS['config']['bwt_lenght_txn_id'], 'upper' );
	}
	else
	{
		$Txn_ID = $_POST['Txn_ID'] ? $_POST['Txn_ID'] : $_SESSION['Txn_ID'];
	}

	$rlSmarty -> assign( 'txn_id', $Txn_ID );

	/* if shoppingCart plugin */
	if ( $payment['service'] == 'shoppingCart' || $payment['service'] == 'auction' )
	{
		$service = $payment['service'];
		
		$sql = "SELECT * FROM `" . RL_DBPREFIX . "shc_orders` WHERE `ID` = '{$item_id}' LIMIT 1";	
		$order_info = $rlDb -> getRow( $sql );
	}

	/* if invoices plugin */
	if ( $payment['callback']['plugin'] == 'invoices' )
	{
		$service = 'invoice';
	}
		
	if ( $payment['callback']['plugin'] == 'banners' )
	{
		$service = 'banner';
	}

	if( !$service )
	{
		/* get category info */
		$sql = "SELECT * FROM `".RL_DBPREFIX."listing_plans` WHERE `ID` = '{$plan_id}' LIMIT 1";
		$plan_info = $rlDb->getRow( $sql );

		$service = $plan_info['Type'];
	} 

	$rlSmarty -> assign( 'service', $service );

	/* get listing info */
	if ( $service == 'listing' )
	{
		$listing = $rlListings -> getShortDetails( $item_id, $plan_info = true );
		$rlSmarty -> assign_by_ref( 'listing', $listing );
	}

	if ( $bwt_type == 'by_check' )
	{
		/* get payments details */
		$sql = "SELECT * FROM `" . RL_DBPREFIX . "bwt_payment_details` ";
		$payment_details = $rlDb -> getAll( $sql );
		
		$payment_details_mail = '';

		foreach( $payment_details as $key => $val )
		{
			$payment_details[$key]['name'] = $lang['payment_details+name+'.$val['Key']];
			$payment_details[$key]['description'] = $lang['payment_details+des+'.$val['Key']];

			if ( $payment_details[$key]['name'] && $payment_details[$key]['description'] )
			{
				$payment_details_mail .= "{$payment_details[$key]['name']}<br />{$payment_details[$key]['description']}<br /><br />________";
			}
		}

		$rlSmarty -> assign_by_ref( 'payment_details', $payment_details ); 

		/* get transaction info	*/
		$sql = "SELECT `T1`.* ";
		$sql .= "FROM `" . RL_DBPREFIX . "bwt_transactions` AS `T1` ";
		$sql .= "WHERE `T1`.`Item_ID` = '{$item_id}' AND `T1`.`Status` = 'approval' && `T1`.`Service` = '{$service}' ";
		$sql .= "LIMIT 1";

		$txn_ready = $txn_info = $rlDb->getRow( $sql );

		if ( !empty( $txn_ready ) )
		{  
			$errors[] = $lang['bwt_txn_exists'];
	 		$rlSmarty -> assign_by_ref( 'errors', $errors );

			$rlSmarty -> assign_by_ref( 'txn_info', $txn_ready );
		}
		else
	    {
			if ( empty( $_SESSION['complete_payment'] ) && empty( $txn_info['Txn_ID'] ) )
			{
				$errors[] = $lang['bwt_session_finished'];
			}

			if ( empty ( $errors ) )
			{

				$data = $plan_id . '|' . $item_id . '|' . (int)$account_info['ID'] . '|' . $price . '|' . $callback_class . '|' . $callback_method . '|' . RL_LANG_CODE . '|' . $callback_plugin;
				$data = base64_encode( $data );

				$insert_data = array(
						'IP' => $_SERVER['REMOTE_ADDR'],
						'Txn_ID' => $Txn_ID,
						'Type' => 'by_check',      
						'Item_ID' => $item_id,
						'Dealer_ID' => $GLOBALS['config']['shc_method'] == 'multi' ? $order_info['Dealer_ID']  : 0,
						'Account_ID' => (int)$account_info['ID'],
						'Plan_ID' => $plan_id,
						'Total' => $price,
						'Service' => $service,
						'Item_data' => $data,
						'Item' => $item_name,
						'Date' => "NOW()"
					);

				if ( $rlActions -> insertOne( $insert_data, 'bwt_transactions' ) )
				{
					$txn_id_tmp = mysql_insert_id();

					// success                                            
					$rlSmarty -> assign_by_ref( 'txn_info', $insert_data );

					$total = $GLOBALS['config']['system_currency_position'] == 'before' ? $GLOBALS['config']['system_currency'] . $_SESSION['complete_payment']['plan_info']['Price'] : $_SESSION['complete_payment']['plan_info']['Price'] . ' ' . $GLOBALS['config']['system_currency']; 
					$date = date( str_replace( array( 'b', '%' ), array( 'M', '' ), RL_DATE_FORMAT ) );

						$order_details = "
{$GLOBALS['lang']['bwt_item_id']}: {$_SESSION['complete_payment']['item_id']}<br />
{$GLOBALS['lang']['item']}: {$_SESSION['complete_payment']['item_name']}<br />
{$GLOBALS['lang']['bwt_txn_id']}: {$Txn_ID}<br />
{$GLOBALS['lang']['bwt_total']}: {$total}<br />
{$GLOBALS['lang']['date']}: {$date}<br />
{$GLOBALS['lang']['bwt_type']}: {$GLOBALS['lang']['by_check']}<br />
";

					/* send notification to user */
					$mail_tpl = $rlMail -> getEmailTemplate( 'bwt_create_new_transaction' );

					$m_find = array( '{username}', '{payment_details}', '{details}' );
					$m_replace = array( $account_info['Full_name'], $payment_details_mail, $order_details );
					
					$mail_tpl['body'] = str_replace( $m_find, $m_replace, $mail_tpl['body'] );
					$rlMail -> send( $mail_tpl, $account_info['Mail'] );
						
					/* send notification to admin */
					$order_details .= "{$GLOBALS['lang']['username']}: {$account_info['Full_name']}<br />";

					$mail_tpl = $rlMail -> getEmailTemplate( 'bwt_create_new_transaction_admin' );

					$m_find = array( '{details}' );
					$m_replace = array( $order_details );
					
					$mail_tpl['body'] = str_replace( $m_find, $m_replace, $mail_tpl['body'] );
					$rlMail -> send( $mail_tpl, $config['notifications_email'] );

					$_SESSION['Txn_ID'] = $Txn_ID;
					unset( $_SESSION['complete_payment'], $insert_data );

					/* redirect to completed */
					$redirect = SEO_BASE;
					$redirect .= $config['mod_rewrite'] ? $pages['bank_wire_transfer'] . '.html?completed&item_id=' . $Txn_ID : '?page=' . $pages['bank_wire_transfer'] . '&completed&item_id=' . $Txn_ID;
					$reefless -> redirect( null, $redirect );
				}  
			}
			else
			{
	 			$rlSmarty -> assign_by_ref( 'errors', $errors );
			}

			$navIcons[] = '<a title="' . $lang['print_page'] . '" class="print" ref="nofollow" href="' . SEO_BASE . 'bwt-print.html?txn_id=' . $txn_info['ID'] . '"> <span></span> </a>';
			$rlSmarty -> assign_by_ref( 'navIcons', $navIcons ); 
		}
	}
	else
	{
		/* get countries */
		$bwt_countries = $rlCategories -> getDF( 'countries' );
		$rlSmarty -> assign_by_ref( 'bwt_country', $bwt_countries );

		if ( $_POST['form'] == 'submit' )
		{
			if ( empty( $_POST['bwt']['bank_account_number'] ) )
			{
				$errors[] = str_replace( '{field}', "<b>" . $lang['bwt_account_number'] . "</b>", $lang['notice_field_empty'] );
			}
			elseif ( strlen( $_POST['bwt']['bank_account_number'] ) > 30 || strlen( $_POST['bwt']['bank_account_number'] ) < 8 )
			{
				$errors[] = str_replace( '{field}', "<b>" . $lang['bwt_bank_account_number'] . "</b>", $lang['notice_field_incorrect'] );
			}
			if ( empty( $_POST['bwt']['account_name'] ) )
			{
				$errors[] = str_replace( '{field}', "<b>" . $lang['bwt_account_name'] . "</b>", $lang['notice_field_empty'] );
			}

			if ( empty( $errors ) )
			{
				$data = $plan_id . '|' . $item_id . '|' . (int)$account_info['ID'] . '|' . $price . '|' . $callback_class . '|' . $callback_method . '|' . RL_LANG_CODE . '|' . $callback_plugin;
				$data = base64_encode( $data );

				$insert_data = $_POST['bwt'];
				$insert_data['IP'] = $_SERVER['REMOTE_ADDR'];
				$insert_data['Txn_ID'] = $Txn_ID;
				$insert_data['Item_ID'] = $item_id;
				$insert_data['Dealer_ID'] = $GLOBALS['config']['shc_method'] == 'multi' ? $order_info['Dealer_ID']  : 0;
				$insert_data['Account_ID'] = (int)$account_info['ID'];
				$insert_data['Plan_ID'] = $plan_id;
				$insert_data['Total'] = $price;
				$insert_data['Service'] = $service;
				$insert_data['Type'] = 'write_transfer';
				$insert_data['Item_data'] = $data;
				$insert_data['Item'] = $item_name;
				$insert_data['Date'] = 'NOW()';

				if ( $rlActions -> insertOne( $insert_data, 'bwt_transactions' ) )
				{
					$txn_id_tmp = mysql_insert_id();

					// success                                            
					$rlSmarty -> assign_by_ref( 'txn_info', $insert_data );

					$total = $GLOBALS['config']['system_currency_position'] == 'before' ? $GLOBALS['config']['system_currency'] . $_SESSION['complete_payment']['plan_info']['Price'] : $_SESSION['complete_payment']['plan_info']['Price'] . ' ' . $GLOBALS['config']['system_currency']; 
					$date = date( str_replace( array( 'b', '%' ), array( 'M', '' ), RL_DATE_FORMAT ) );

					$order_details = "
{$GLOBALS['lang']['bwt_item_id']}: {$item_id}<br />
{$GLOBALS['lang']['item']}: {$_SESSION['complete_payment']['item_name']}<br />
{$GLOBALS['lang']['bwt_txn_id']}: {$Txn_ID}<br />
{$GLOBALS['lang']['bwt_total']}: {$total}<br />
{$GLOBALS['lang']['date']}: {$date}<br />
{$GLOBALS['lang']['bwt_type']}: {$GLOBALS['lang']['write_transfer']}<br />
";

					/* send notification to user */
					$mail_tpl = $rlMail -> getEmailTemplate( 'bwt_create_new_transaction_admin' );

					$m_find = array( '{username}', '{payment_details}', '{details}' );
					$m_replace = array( $account_info['Full_name'], '', $order_details );
					
					$mail_tpl['body'] = str_replace( $m_find, $m_replace, $mail_tpl['body'] );
					$rlMail -> send( $mail_tpl, $account_info['Mail'] );
						
					/* send notification to admin */
					$order_details .= "{$GLOBALS['lang']['username']}: {$account_info['Full_name']}<br />";

					$mail_tpl = $rlMail -> getEmailTemplate( 'bwt_create_new_transaction_admin' );

					$m_find = array( '{details}' );
					$m_replace = array( $order_details );
					
					$mail_tpl['body'] = str_replace( $m_find, $m_replace, $mail_tpl['body'] );
					$rlMail -> send( $mail_tpl, $config['notifications_email'] );

					$_SESSION['Txn_ID'] = $Txn_ID;
					unset( $_SESSION['complete_payment'], $insert_data );

					/* redirect to completed */
					$redirect = SEO_BASE;
					$redirect .= $config['mod_rewrite'] ? $pages['bank_wire_transfer'] . '.html?completed&item_id=' . $Txn_ID : '?page=' . $pages['bank_wire_transfer'] . '&completed&item_id=' . $Txn_ID;
					$reefless -> redirect( null, $redirect );
				}
			}
			else
			{
	 			$rlSmarty -> assign_by_ref( 'errors', $errors );
			}
		}
	}
}
?>