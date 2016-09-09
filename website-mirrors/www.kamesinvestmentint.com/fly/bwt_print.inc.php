<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: BWT_PRINT.INC.PHP
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

$languages = $rlLang -> getLanguagesList();
$rlLang -> modifyLanguagesList( $languages );

$reefless -> loadClass( 'Common' );
$reefless -> loadClass( 'Listings' );

if(!empty($_GET['txn_id']))
{
	/* get transaction info	*/
	$sql = "SELECT `T1`.* FROM `".RL_DBPREFIX."bwt_transactions` AS `T1` WHERE `T1`.`ID` = '{$_GET['txn_id']}' LIMIT 1";
	$txn_info = $rlDb->getRow($sql);

	if($txn_info['Account_ID'] == $_SESSION['id'])
   	{                                   
		$rlSmarty -> assign_by_ref( 'txn_info', $txn_info );
		$rlSmarty -> assign( 'txn_id', $txn_info['Txn_ID'] );
		$rlSmarty -> assign( 'bwt_type', $txn_info['Type'] );

		/* get listing info */
		$listing = $rlListings -> getShortDetails( $txn_info['Item_ID'], $plan_info = true );
		$rlSmarty -> assign_by_ref( 'listing', $listing );                              
		           
		/* get payments details */
		if($txn_info['Type'] == 'by_check')
		{
			$sql = "SELECT * FROM `".RL_DBPREFIX."bwt_payment_details` ";
			$payment_details = $rlDb->getAll($sql);
			
			foreach($payment_details as $key => $val)
			{
				$payment_details[$key]['name'] = $lang['payment_details+name+'.$val['Key']];
				$payment_details[$key]['description'] = $lang['payment_details+des+'.$val['Key']];
			}

			$rlSmarty -> assign_by_ref( 'payment_details', $payment_details );
		}
		$rlSmarty->display(RL_PLUGINS . 'bankWireTransfer' . RL_DS . 'bwt_print.tpl');
	}
}

exit;
?>