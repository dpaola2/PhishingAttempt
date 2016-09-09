<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLREPORTBROKENLISTING.CLASS.PHP
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

class rlReportBrokenListing extends reefless
{
	/**
	* add report broken listing
	*
	* @param int $listing_id - listing id
	* @param string $message - message
	* 
	**/
	function ajaxRreportBrokenListing($listing_id = false, $message = false )
	{		
		global $_response, $page_info, $lang, $account_info, $rlValid;
		
		$listing_id = (int)$listing_id;
		
		if ( !$listing_id )
			return $_response;
		
		if ( empty($message) )
		{
			$_response -> script("printMessage('error', '{$lang['reportbroken_you_should_add_comment']}')");
		}
		else
		{
			$this -> loadClass('Actions');
			
			$insert = array(
				'ID' => '',
				'Listing_ID' => $listing_id,
				'Account_ID' => defined('IS_LOGIN') ? $account_info['ID'] : '',
				'Message' => $message,
				'Date' => 'NOW()'
			);
		
			// insert
			$GLOBALS['rlActions'] -> insertOne($insert, 'report_broken_listing');
			
			$_response -> script("
				printMessage('notice', '{$lang['reportbroken_listing_has_been_added']}');
				$('#modal_block>div.inner>div.close').trigger('click');
				reportBrokenLisitngIcon({$listing_id});
			");
		}
	
		return $_response;
	}
	
	/**
	* cancel report from the listing
	*
	* @param int $listing_id - listing id
	*
	**/
	function ajaxRemoveReportBrokenListing( $listing_id = false )
	{
		global $_response, $page_info, $lang;
		
		$listing_id = (int)$listing_id;
		
		if ( !$listing_id )
			return $_response;
		
		$this -> query("DELETE FROM `".RL_DBPREFIX."report_broken_listing` WHERE `Listing_ID` = '{$listing_id}'");
		
		$_response -> script("
			printMessage('notice', '{$lang['reportbroken_listing_has_been_removed']}');
			reportBrokenLisitngIcon({$listing_id});
		");
		
		return $_response;
	}
	
	/**
	*
	* delete report broken listing from Admin Panel
	*
	* @param int $id - entry id
	*
	**/
	function ajaxDeletereportBrokenListing ( $id = false )
	{
		global $_response, $lang;
		
		$id = (int)$id;
		
		if ( !$id )
			return $_response;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$_response -> redirect( RL_URL_HOME . ADMIN . '/index.php?action=session_expired' );
			return $_response;
		}
		
		// delete rss feed
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "report_broken_listing` WHERE `ID` = '{$id}' LIMIT 1");
		
		$_response -> script("
			reportGrid.reload();
			printMessage('notice', '{$lang['item_deleted']}');
		");
		
		return $_response;
	}
	
	/**
	*
	* delete listing
	*
	* @param int $id - entry ID
	*
	**/
	function ajaxDeleteListing( $id = false )
	{		
		global $_response, $config, $lang;
	
		$id = (int)$id;
		
		if ( !$id )
			return $_response;
		
		$listing_id = $this -> getOne('Listing_ID', "`ID` = '{$id}'", 'report_broken_listing');
		$category_id = $this -> getOne('Category_ID', "`ID` = '{$listing_id}'", 'listings');

		/* decrease listing count in category */
		$this -> loadClass('Categories');
		$GLOBALS['rlCategories'] -> listingsDecrease($category_id);
		
		$GLOBALS['rlActions'] -> delete( array('ID' => $listing_id), 'listings', $id, 1 );
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "report_broken_listing` WHERE `ID` = '{$id}' LIMIT 1");
	
		if ( !$config['trash'] )
		{
			$this -> loadClass('Listings', 'admin');
			$GLOBALS['rlListings'] -> deleteListingData($listing_id);
		}
		
		$del_action = $GLOBALS['rlActions'] -> action;
		
		$_response -> script("
			reportGrid.reload();
			printMessage('notice', '{$GLOBALS['lang']['mass_listings_'.$del_action]}');
		");
		
		return $_response;
	}
	
	/**
	* delete listing's data
	*
	* @param int $id - listing id
	*
	**/
	function deleteListingData( $id )
	{
		/* get Report Broken listing */
		$broken = $this -> query("DELETE FROM `" . RL_DBPREFIX . "report_broken_listing` WHERE `Listing_ID` = '{$id}' LIMIT 1");
		
		/* get listing photos */
		$photos = $this -> fetch( array('Photo', 'Thumbnail'), array( 'Listing_ID' => $id ), null, null, 'listing_photos' );
		
		/* get listing video */
		$video = $this -> fetch( array('Video', 'Preview'), array( 'Listing_ID' => $id ), null, 1, 'listing_video', 'row' );

		/* delete photos */
		foreach ($photos as $pKey => $pValue)
		{
			unlink( RL_FILES . $photos[$pKey]['Photo'] );
			unlink( RL_FILES . $photos[$pKey]['Thumbnail'] );
		}
		
		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "listing_photos` WHERE `Listing_ID` = '{$id}'" );
		
		/* delete video */
		unlink( RL_FILES . $photos['Video'] );
		unlink( RL_FILES . $photos['Preview'] );
		
		$this -> query( "DELETE FROM `" . RL_DBPREFIX . "listing_video` WHERE `Listing_ID` = '{$id}'" );
	}
}