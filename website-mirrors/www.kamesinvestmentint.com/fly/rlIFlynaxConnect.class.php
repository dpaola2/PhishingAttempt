<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLIFLYNAXCONNECT.CLASS.PHP
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

class rlIFlynaxConnect extends reefless {

	/**
	* Class constructor
	**/
	function rlIFlynaxConnect()
	{

	}

	/**
	* Get featured listings
	**/
	function getFeatured()
	{
		global $config, $rlListings, $rlCommon;

		$sql = "SELECT DISTINCT SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `T6`.`Thumbnail` ORDER BY `T6`.`Type` DESC, `T6`.`ID` ASC), ',', 1) AS `Main_photo`, ";
		$sql .= "`T1`.*, `T4`.`Path`, `T4`.`Parent_ID`, `T4`.`Type` AS `Listing_type` ";
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T2` ON `T1`.`Featured_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_photos` AS `T6` ON `T1`.`ID` = `T6`.`Listing_ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
		$sql .= "WHERE (UNIX_TIMESTAMP(DATE_ADD(`T1`.`Featured_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) ";
		$sql .= " OR `T2`.`Listing_period` = 0) AND (UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T3`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW())";
		$sql .= " OR `T3`.`Listing_period` = 0) ";
		$sql .= "AND `T1`.`Status` = 'active' AND `T4`.`Status` = 'active' AND `T7`.`Status` = 'active' ";
		$sql .= "GROUP BY `T1`.`ID` ORDER BY `Last_show` ASC, RAND() ";
		$sql .= "LIMIT {$config['iFlynaxConnect_featured_per_page']}";
		$listings = $this -> getAll( $sql );

		if ( empty($listings) )
		{
			return false;
		}

		$IDs = array();
		foreach( $listings as $key => $value )
		{
			// get listing IDs
			array_push($IDs, $value['ID']);

			// populate fields
			$fields = $rlListings -> getFormFields($value['Category_ID'], 'featured_form', $value['Listing_type']);
			foreach( $fields as $fKey => $fValue )
			{
				if ( $first )
				{
					$fields[$fKey]['value'] = $rlCommon -> adaptValue($fValue, $value[$fKey], 'listing', $value['ID']);
				}
				else
				{
					if ( $field['Condition'] == 'isUrl' || $field['Condition'] == 'isEmail' )
					{
						$fields[$fKey]['value'] = $listings[$key][$item];
					}
					else
					{
						$fields[$fKey]['value'] = $rlCommon -> adaptValue($fValue, $value[$fKey], 'listing', $value['ID']);
					}
				}
				$first++;
			}
			$listings[$key]['fields'] = $fields;
			$listings[$key]['listing_title'] = $rlListings -> getListingTitle($value['Category_ID'], $value, $value['Listing_type']);
		}

		// save show date */
		if ( !empty($IDs) )
		{
			$this -> query("UPDATE `" . RL_DBPREFIX . "listings` SET `Last_show` = NOW() WHERE FIND_IN_SET(`ID`, '". implode(',', $IDs) ."') > 0");
		}
		return $listings;
	}

	/**
	* Delete listing
	*
	* @param int $id - listing id
	* @return bool - true/false
	**/
	function deleteListing( $id = false )
	{
		if ( defined( 'IS_LOGIN' ) && $id !== false )
		{
			$accountId = (int)$_SESSION['id'];

			$sql  = "SELECT `T1`.`ID`, `T1`.`Kind_ID`, `T2`.`Type`, `T1`.`Crossed` ";
			$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Kind_ID` = `T2`.`ID` ";
			$sql .= "WHERE `T1`.`ID` = '{$id}' AND `T1`.`Account_ID` = '{$accountId}' AND `T1`.`Status` <> 'trash'";
			$info = $this -> getRow( $sql );

			if ( !empty( $info ) )
			{
				$GLOBALS['rlActions'] -> delete( array( 'ID' => $info['ID'] ), 'listings', $info['ID'], 1 );

				if ( !$GLOBALS['config']['trash'] )
				{
					$GLOBALS['rlListings'] -> deleteListingData( $info['ID'] );
				}

				// decrease category listing
				$GLOBALS['rlCategories'] -> listingsDecrease( $info['Kind_ID'] );

				// crossed listings count control
				if ( !empty( $info['Crossed'] ) )
				{
					$crossed_cats = explode( ',', trim( $info['Crossed'], ',' ) );
					foreach ( $crossed_cats as $crossed_cat_id )
					{
						$GLOBALS['rlCategories'] -> listingsDecrease( $crossed_cat_id );
					}
				}

				return true;
			}

			return false;
		}

		return false;
	}

	/**
	* Delete listing photo
	*
	* @param int $id - listing photo id
	* @return bool - true/false
	**/
	function deleteListingPhoto( $id = false ) {

		if ( $id === false ) return false;

		// get listing photos
		$photo = $this -> fetch( array( 'Photo', 'Thumbnail', 'Original' ), array( 'ID' => $id ), null, null, 'listing_photos', 'row' );
		$sql = "DELETE FROM `". RL_DBPREFIX ."listing_photos` WHERE `ID` = '{$id}' LIMIT 1";

		if ( $this -> query( $sql ) )
		{
			if ( !empty( $photo ) )
			{
				@unlink( RL_FILES . $photo['Photo'] );
				@unlink( RL_FILES . $photo['Thumbnail'] );
				@unlink( RL_FILES . $photo['Original'] );
			}

			return true;
		}

		return false;
	}
}