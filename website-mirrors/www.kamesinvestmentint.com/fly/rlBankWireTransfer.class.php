<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLBANKWIRETRANSFER.CLASS.PHP
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

class rlBankWireTransfer extends reefless 
{
	function rlBankWireTransfer()
	{
	}

	function completeTransaction( $txn )
	{
		global $rlDb;

		if ( !empty( $txn ) )
		{
			$items = explode( '|', base64_decode(urldecode( $txn['Item_data'] ) ) );

			$plan_id = $items[0];
			$item_id = $items[1];
			$account_id = $items[2];
			$callback_class = $items[4];
			$callback_method = $items[5];
			$plugin = $items[7];

			$this -> loadClass( str_replace( 'rl', '', $callback_class ), null, $plugin );
			$GLOBALS[$callback_class] -> $callback_method( $item_id, $plan_id, $account_id, $txn['Txn_ID'], 'bankWireTransfer', $txn['Total'] );
		}
	}

	function ajaxDeleteTransaction( $id )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN . "/index.php";
			$redirect_url .= empty( $_SERVER['QUERY_STRING'] ) ? '?session_expired' : '?' . $_SERVER['QUERY_STRING'] . '&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		if ( false === (bool)strpos( $id, '|' ) )
		{
			$GLOBALS['rlActions'] -> delete( array( 'ID' => $id ), array( 'bwt_transactions' ), $id, null, $id, false );
		}
		else
		{
			$ids = explode( '|', $id );
			foreach ( $ids as $id )
			{
				$GLOBALS['rlActions'] -> delete( array( 'ID' => $id ), array( 'bwt_transactions' ), $id, null, $id, false );
			}
		}

		$del_mode = $GLOBALS['rlActions'] -> action;
		
		$_response -> script("
			bwtTransactionsGrid.reload();
			bwtTransactionsGrid.checkboxColumn.clearSelections();
			bwtTransactionsGrid.actionsDropDown.setVisible(false);
			bwtTransactionsGrid.actionButton.setVisible(false);
			printMessage('notice', '{$lang['transaction_' . $del_mode]}');
		");

		return $_response;
	}

	function ajaxDeleteItem( $id = false )
	{
		global $_response, $lang;

		$delete = "DELETE FROM `" . RL_DBPREFIX . "payment_details` WHERE `ID` = '{$id}' LIMIT 1";
		$this -> query( $delete );

		// print message, update grid
		$_response -> script("
			bwtPaymentDetails.reload();
			printMessage('notice', '{$lang['item_deleted']}');
			$('#delete_block').slideUp();
		");

		return $_response;
	}

	/* for Shopping Cart plugin */
	function getPaymentMethods( $output = false )
	{
		$list = array(	
				array( 'Key' => 'by_check', 'name' => $GLOBALS['lang']['by_check'] ),
				array( 'Key' => 'write_transfer', 'name' => $GLOBALS['lang']['write_transfer'] ),
				array( 'Key' => 'both', 'name' => $GLOBALS['lang']['both'] )
			);

		if ( $output )
		{
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'bwt_types', $list );
			return;
		}

		return $list;

		return $list;
	}
	
	function ajaxCompleteTransaction( $item_id = false )
	{
		global $_response, $account_info;

		$item_id = (int)$item_id;

		if(!$item_id)
		{	
			return $_response;
		}
		
		$sql = "SELECT `T1`.*  ";
		$sql .= "FROM `".RL_DBPREFIX."bwt_transactions` AS `T1` ";
		$sql .= "WHERE `T1`.`ID` = '{$item_id}' AND `T1`.`Status` = 'approval' AND `Dealer_ID` = '{$account_info['ID']}' LIMIT 1";

		$txn_info = $this -> getRow( $sql );

		if ( !empty( $txn_info ) )
		{
			$this -> completeTransaction( $txn_info );

			$sql = "UPDATE `".RL_DBPREFIX."bwt_transactions` SET `Status` = 'active' WHERE `ID` = '{$item_id}' LIMIT 1";
			$this -> query( $sql );

			$url = SEO_BASE;
			$url .= $GLOBALS['config']['mod_rewrite'] ? $pages['bwt_requests'] . '.html?item=' . $item_id : 'index.php?page=' . $pages['bwt_requests'] . '&item=' . $item_id;

			$html = '<a href="'.$url.'"><img src="'.RL_TPL_BASE.'img/blank.gif" alt="'.$GLOBALS['lang']['view_details'].'" title="'.$GLOBALS['lang']['view_details'].'" class="view_details" /></a>';
			$html_status = '<span class="item_paid">'.$GLOBALS['lang']['shc_paid'].'</span>';

			$_response -> script("
					$('#bwt_{$item_id}').html('{$html}');
					$('#txn_status_{$item_id}').html('{$html_status}');
					printMessage('notice', '{$GLOBALS['lang']['bwt_request_activated_successfully']}');
				");
		}
		else
		{
			$_response -> script("
					printMessage('notice', '{$GLOBALS['lang']['bwt_request_activated_failed']}');
				");
		}       

		return $_response;
	}
}