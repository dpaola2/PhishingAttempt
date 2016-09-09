<?php


/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLRECENTLYVIEWED.CLASS.PHP
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

class rlRecentlyViewed extends reefless
{
	/**
	* add viewed listing id to database
	*
	* @param array $listing_data - listing info
	*
	**/
	function addRvListing( $listing_data = false ) {
		global $config, $account_info, $rlDb, $rlActions;

		if ( !$listing_data || !defined('IS_LOGIN') )
			return false;

		$GLOBALS['reefless'] -> loadClass('Actions');

		if ( $rv_listings =  $rlDb -> getOne('rv_listings', "`ID` = '{$account_info['ID']}'", 'accounts') ) {
			$rv_listings = explode(',', $rv_listings);

			foreach ($rv_listings as $id => $listing) {
				if ( $listing != $listing_data['ID'] )
					$new_rv_listings[] = $listing;
			}

			array_unshift($new_rv_listings, $listing_data['ID']);

			if ( count($new_rv_listings) > $config['rv_total_count'] )
				$new_rv_listings = array_slice($new_rv_listings, 0, $config['rv_total_count']);

			$new_rv_listings = implode(',', $new_rv_listings);

			$update_rv_listings = array(
				'fields' => array(
					'rv_listings' => $new_rv_listings
				),
				'where' => array(
					'ID' => $account_info['ID']
				)
			);

			$rlActions -> updateOne($update_rv_listings, 'accounts');
		} else {
			$update_rv_listings = array(
				'fields' => array(
					'rv_listings' => $listing_data['ID']
				),
				'where' => array(
					'ID' => $account_info['ID']
				)
			);

			$rlActions -> updateOne($update_rv_listings, 'accounts');
		}
	}

	/**
	* get viewed listings from database
	*
	* @param array $rv_listings_ids - ids of viewed listings
	*
	* @param int $start - page number
	*
	* @param bool $all - get details of all listings
	*
	* @return array - array of listings
	**/
	function getRvListings( $rv_listings_ids = false, $start = false, $all = false ) {
		global $config, $rlDb, $rlListings, $rlCommon;

		if ( !$rv_listings_ids )
			return false;

		/* define start position */
		if ( !$all ) {
			$limit = $config['rv_count_per_page'];
			$start = $start > 1 ? ($start - 1) * $limit : 0;

			$rv_listings_ids = explode(',', $rv_listings_ids);
			$rv_listings_ids = array_slice($rv_listings_ids, $start, $limit);
			$rv_listings_ids = implode(',', $rv_listings_ids);
		}

		$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.*, `T4`.`Path`, `T4`.`Type` AS `Listing_type`, DATE(`T1`.`Date`) AS `Post_date`, ";
		$sql .= "IF(TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T5`.`Listing_period` * 24 OR `T5`.`Listing_period` = 0, '1', '0') `Featured`, ";
		$sql .= "`T4`.`Parent_ID`, `T4`.`Key` AS `Cat_key`, `T4`.`Key`, ";
		$sql .= "`T1`.`Status` AS `Listing_status`, `T4`.`Status` AS `Category_status`, `T7`.`Status` AS `Owner_status` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T5` ON `T1`.`Featured_ID` = `T5`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";

		$sql .= "WHERE FIND_IN_SET(`T1`.`ID`, '{$rv_listings_ids}') ";
		$sql .= "ORDER BY FIND_IN_SET(`T1`.`ID`, '{$rv_listings_ids}')";

		$rv_listings = $this -> getAll( $sql );
		$rv_listings = $GLOBALS['rlLang'] -> replaceLangKeys( $rv_listings, 'categories', 'name' );
		
		if ( empty($rv_listings) )
			return false;

		$calc = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		$rlListings -> calc = $calc['calc'];

		foreach ( $rv_listings as $key => $value ) {
			/* populate fields */
			$fields = $rlListings -> getFormFields( $value['Category_ID'], 'short_forms', $value['Listing_type'] );
			
			foreach ( $fields as $fKey => $fValue )	{
				if ( $first )
					$fields[$fKey]['value'] = $rlCommon -> adaptValue( $fValue, $value[$fKey], 'listing', $value['ID'] );
				else {
					if ( $field['Condition'] == 'isUrl' || $field['Condition'] == 'isEmail' )
						$fields[$fKey]['value'] = $rv_listings[$key][$item];
					else
						$fields[$fKey]['value'] = $rlCommon -> adaptValue( $fValue, $value[$fKey], 'listing', $value['ID'] );
				}
				$first++;
			}
			
			$rv_listings[$key]['fields'] = $fields;
			$rv_listings[$key]['listing_title'] = $rlListings -> getListingTitle( $value['Category_ID'], $value, $value['Listing_type'] );
		}

		return $rv_listings;
	}

	/**
	* remove viewed listing from list
	*
	* @param int $rv_listing_id - id of viewed listing
	*
	* @param array $rv_storage_ids - viewed listings from storage
	*
	* @return
	**/
	function ajaxRemoveRvListing( $rv_listing_id = false, $rv_storage_ids = false )
	{
		global $_response, $account_info, $config, $rlDb, $lang, $tpl_settings;

		$rv_listing_id = (int)$rv_listing_id;

		if ( !$rv_listing_id )
			return $_response;

		if ( defined('IS_LOGIN') ) {
			if ( $rv_listings =  $rlDb -> getOne('rv_listings', "`ID` = '{$account_info['ID']}'", 'accounts') ) {
				$rv_listings = explode(',', $rv_listings);

				foreach ($rv_listings as $id => $listing) {
					if ( $listing != $rv_listing_id )
						$new_rv_listings[] = $listing;
				}

				$new_rv_listings = implode(',', $new_rv_listings);

				$update_rv_listings = array(
					'fields' => array(
						'rv_listings' => $new_rv_listings
					),
					'where' => array(
						'ID' => $account_info['ID']
					)
				);

				$GLOBALS['reefless'] -> loadClass('Actions');
				$GLOBALS['rlActions'] -> updateOne($update_rv_listings, 'accounts');
			}

			$rv_listings_ids =  $rlDb -> getOne('rv_listings', "`ID` = '{$account_info['ID']}'", 'accounts');
		} else {
			$rv_listings_ids = $rv_storage_ids;
			$rv_listings_ids = explode(',', $rv_listings_ids);

			foreach ($rv_listings_ids as $id => $listing) {
				if ( $listing != $rv_listing_id )
					$new_rv_listings[] = $listing;
			}

			$rv_listings_ids = implode(',', $new_rv_listings);
		}

		$pInfo['current'] = (int)$_GET['pg'];
		$pInfo['calc'] = count(explode(',', $rv_listings_ids));
		$GLOBALS['rlSmarty'] -> assign_by_ref( 'pInfo', $pInfo );

		$rv_listings = $this -> getRvListings( $rv_listings_ids, $pInfo['current'] );
		$GLOBALS['rlSmarty'] -> assign_by_ref('listings', $rv_listings);

		$tpl = RL_PLUGINS .'recentlyViewed'. RL_DS . 'rv_listings.tpl';
		$_response -> assign( 'controller_area', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );

		$_response -> script("
			printMessage('notice', '{$lang['rv_del_listing_success']}');
			rvRemoveListing({$rv_listing_id});
			addTriggerToIcons();
		");

		if ( !$rv_listings )
			$_response -> script("$(document).ready(function(){ $('#controller_area .info').text('{$lang['rv_no_listings']}'); });");

		if ( $tpl_settings['type'] == 'responsive_42' )
			$_response -> script("$('#listings div.picture:not(.no-picture) img').hisrc()");

		return $_response;
	}

	/**
	* remove all viewed listings from list
	**/
	function ajaxRemoveAllRvListings()
	{
		global $_response, $account_info, $rlDb, $lang;

		if ( defined('IS_LOGIN') )
			$rlDb -> query("UPDATE `". RL_DBPREFIX ."accounts` SET `rv_listings` = '' WHERE `ID` = '{$account_info['ID']}' LIMIT 1");

		$pInfo['current'] = 1;
		$pInfo['calc'] = 0;
		$GLOBALS['rlSmarty'] -> assign_by_ref( 'pInfo', $pInfo );

		$rv_listings = '';
		$GLOBALS['rlSmarty'] -> assign_by_ref( 'listings', $rv_listings );

		$tpl = RL_PLUGINS .'recentlyViewed'. RL_DS . 'rv_listings.tpl';

		if ( RL_MOBILE === true )
			$_response -> script('$(".content_container").html("<div class=\\"padding\\">' . $lang['rv_no_listings'] . '</div>");');
		else
			$_response -> assign( 'controller_area', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );

		$_response -> script("
			setTimeout(function(){printMessage('notice', '{$lang['rv_del_listings_success']}')}, 1000);
			addTriggerToIcons();
			rvRemoveListings();
		");

		$_response -> script("$(document).ready(function(){ $('#controller_area .info').text('{$lang['rv_no_listings']}'); });");

		return $_response;
	}

	/**
	* load viewed listings from storage
	*
	* @param array $rv_listings_ids - ids of viewed listings
	*
	* @return
	**/
	function ajaxLoadRvListings( $rv_listings_ids = false )
	{
		global $_response, $tpl_settings, $lang, $pages;

		if ( $rv_listings_ids ) {
			$pInfo['current'] = (int)$_GET['pg'];

			$tmp_rv_listings = $this -> getRvListings( $rv_listings_ids, $pInfo['current'] );

			foreach ($tmp_rv_listings as $key => $value) {
				if ( $value['Listing_status'] == 'active' && $value['Category_status'] == 'active' && $value['Owner_status'] == 'active' ) {
					$rv_listings[] = $value;
				} else {
					$inactive_listings = true;
				}
			}
		}

		if ( $rv_listings ) {
			$GLOBALS['rlSmarty'] -> assign_by_ref('listings', $rv_listings);

			if ( $inactive_listings ) {
				$tmp_rv_listings = $this -> getRvListings( $rv_listings_ids, false, true );
				$rv_listings = '';

				foreach ($tmp_rv_listings as $key => $value) {
					if ( $value['Listing_status'] == 'active' && $value['Category_status'] == 'active' && $value['Owner_status'] == 'active' ) {
						$rv_listings[] = $value;
						$rv_ids = $rv_ids ? $rv_ids . "," . $value['ID'] : $value['ID'];
					}
				}

				$st_listings = "[";

				foreach ($rv_listings as $key => $listing) {
					$st_listings .= "['" . $listing['ID'] . "','" . $listing['Main_photo'] . "','" . $pages['lt_' . $listing['Listing_type']] . "','" . $listing['Path'] . "','" . addslashes( $listing['listing_title'] ) . "']";
					$st_listings .= $key != count($rv_listings) - 1 ? ',' : '';
				}

				$st_listings .= "]";

				$storage_postfix = str_replace( array('http://', 'https://', 'www.'), array('', '', ''), RL_URL_HOME );
				$storage_postfix = str_replace( array('.', '/'), array('_', '_'), $storage_postfix );

				$_response -> script("localStorage.setItem('rv_listings_{$storage_postfix}', JSON.stringify({$st_listings}));");
			}

			$pInfo['calc'] = count(explode(',', $inactive_listings ? $rv_ids : $rv_listings_ids));
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'pInfo', $pInfo );

			$tpl = RL_PLUGINS .'recentlyViewed'. RL_DS . 'rv_listings.tpl';

			if ( RL_MOBILE === true ) {
				$_response -> script('$(".content_container").attr("id", "content_container");');
				$_response -> assign( 'content_container', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );
			} else {
				$_response -> assign( 'controller_area', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );
			}

			$_response -> script("addTriggerToIcons();");

			if ( $tpl_settings['type'] == 'responsive_42' )
				$_response -> script("$('#listings div.picture:not(.no-picture) img').hisrc();");
		} else {
			if ( RL_MOBILE === true )
				$_response -> script('$(".content_container").html("<div class=\\"padding\\">' . $lang['rv_no_listings'] . '</div>");');
			else
				$_response -> script("$(document).ready(function(){ $('#controller_area .info').text('{$lang['rv_no_listings']}'); });");
		}

		return $_response;
	}

	/**
	* synchronization local viewed and saved in database listings
	*
	* @param array $rv_storage_ids - ids of viewed listings from storage
	*
	* @return bool true or false
	*
	**/
	function ajaxSyncRvListings( $rv_storage_ids = false )
	{
		global $_response, $account_info, $rlDb, $pages, $page_info;

		if ( !defined('IS_LOGIN') )
			return false;

		$rv_listings_ids =  $rlDb -> getOne('rv_listings', "`ID` = '{$account_info['ID']}'", 'accounts');

		if ( substr($rv_listings_ids, -1, 1) == ',' )
			$rv_listings_ids = substr_replace( $rv_listings_ids, '', strrpos($rv_listings_ids, ',') );

		$rv_db_listings = explode(",", $rv_listings_ids);
		$rv_st_listings = explode(",", $rv_storage_ids);

		if ( count($rv_db_listings) > count($rv_st_listings) ) {
			/* add missing listings to storage from DB */
			for ($i = count( $rv_db_listings ); $i >= 0; $i--) {
				if ( !in_array($rv_db_listings[$i], $rv_st_listings) && (int)$rv_db_listings[$i] )
					array_unshift($rv_st_listings, $rv_db_listings[$i]);
			}

			$rv_ids = implode(",", $rv_st_listings);
		} else {
			/* add missing listings to DB from storage */
			for ($i = count( $rv_st_listings ); $i >= 0; $i--) {
				if ( !in_array($rv_st_listings[$i], $rv_db_listings) && (int)$rv_st_listings[$i] )
					array_unshift($rv_db_listings, $rv_st_listings[$i]);
			}

			$rv_ids = implode(",", $rv_db_listings);
			$rlDb -> query("UPDATE `". RL_DBPREFIX ."accounts` SET `rv_listings` = '{$rv_ids}' WHERE `ID` = '{$account_info['ID']}' LIMIT 1");
		}

		$tmp_rv_listings = $this -> getRvListings( $rv_ids, false, true );

		if ( $tmp_rv_listings ) {
			$rv_ids = '';

			/* removing inactive listings from storage and DB */
			foreach ($tmp_rv_listings as $key => $value) {
				if ( $value['Listing_status'] == 'active' && $value['Category_status'] == 'active' && $value['Owner_status'] == 'active' ) {
					$rv_listings[] = $value;
					$rv_ids = $rv_ids ? $rv_ids . "," . $value['ID'] : $value['ID'];
				}
			}

			$rlDb -> query("UPDATE `". RL_DBPREFIX ."accounts` SET `rv_listings` = '{$rv_ids}' WHERE `ID` = '{$account_info['ID']}' LIMIT 1");

			$st_listings = "[";

			foreach ($rv_listings as $key => $listing) {
				$st_listings .= "['" . $listing['ID'] . "','" . $listing['Main_photo'] . "','" . $pages['lt_' . $listing['Listing_type']] . "','" . $listing['Path'] . "','" . addslashes( $listing['listing_title'] ) . "']";
				$st_listings .= $key != count($rv_listings) - 1 ? ',' : '';
			}

			$st_listings .= "]";

			$storage_postfix = str_replace( array('http://', 'https://', 'www.'), array('', '', ''), RL_URL_HOME );
			$storage_postfix = str_replace( array('.', '/'), array('_', '_'), $storage_postfix );

			$_response -> script("localStorage.setItem('rv_listings_{$storage_postfix}', JSON.stringify({$st_listings})); loadRvListingsToBlock();");

			if ( $page_info['Key'] == 'rv_listings' ) {
				$this -> ajaxLoadRvListings( $rv_ids );
			}
		}

		$_response -> script("addTriggerToIcons();");

		$_SESSION['sync_rv_complete'] = 1;

		return $_response;
	}
}
