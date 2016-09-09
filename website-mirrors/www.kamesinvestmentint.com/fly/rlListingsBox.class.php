<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLLISTINGSBOX.CLASS.PHP
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

class rlListingsBox extends reefless
{
	/**
	* get listings
	*
	* @param string $info - array info
	* @param int $field - fields for update in grid
	*
	* @return array - listings information
	**/
	function checkContentBlock( $info = false, $field = false)
	{
		if ( is_array($field) )
		{
			$data = $this -> fetch( array('Type','Box_type', 'Count', 'Unique', 'Display_mode', 'Box_columns'), array( 'ID' => $field[2] ), null, null, 'listing_box', 'row' );

			if( $field[0] == 'Type' )
			{
				$type = $field[1];
				$box_type = $data['Box_type'];
				$limit = $data['Count'];
			}
			elseif( $field[0] == 'Box_type' )
			{
				$type = $data['Type'];
				$box_type = $field[1];
				$limit = $data['Count'];
			}
			elseif( $field[0] == 'Count' )
			{
				$type = $data['Type'];
				$box_type = $data['Box_type'];
				$limit = $field[1];
			}
			$unique = $data['unique'];
			$box_option['display_mode'] =  $data['Display_mode'];
			$box_option['box_columns'] =  $data['Box_columns'];
		}
		else
		{
			$type = $info['type'];
			$box_type = $info['box_type'];
			$limit = $info['count'];
			$unique = $info['unique'];
			$box_option['display_mode'] =  $info['display_mode'];
			$box_option['box_columns'] =  $info['columns'];
		}

		$content = '
				global $reefless;
				global $rlSmarty;
				$reefless -> loadClass("ListingsBox", null, "listings_box");
				global $rlListingsBox;
				$listings_box = $rlListingsBox -> getListings( "' . $type . '", "' . $box_type . '", "' . $limit . '", "' . $unique . '" );
				$rlSmarty -> assign_by_ref( "listings_box", $listings_box );
				$rlSmarty -> assign( "type", "' . $type . '" );';
				foreach ($box_option as $key => $val)
				{
		$content .= '$box_option['.$key.'] = "' . $val . '";';
				}
		$content .= '$rlSmarty -> assign( "box_option", $box_option );
				$rlSmarty -> display( RL_PLUGINS . "listings_box" . RL_DS . "listings_box.block.tpl" );
			';
		return $content;
	}

	/**
	* get listings
	*
	* @param string $category - category ID
	* @param string $order - field name for order
	* @param string $order_type - order type
	* @param int $start - start DB position
	* @param int $limit - listing number per request
	* @param int $Unique - Unique listings in box
	*
	* @return array - listings information
	**/
	function getListings( $type = false, $order = false , $limit = false, $unique = false )
	{
		global $sql, $config, $rlListings;

		if ( version_compare($config['rl_version'], '4.1.0') < 0 )
		{
			$sql  = "SELECT DISTINCT {hook} SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `T6`.`Thumbnail` ORDER BY `T6`.`Type` DESC, `T6`.`ID` ASC), ',', 1) AS `Main_photo`, ";
			$sql .= "`T1`.*, `T1`.`Shows`, `T3`.`Path` AS `Path`, `T3`.`Key` AS `Key`, `T3`.`Type` AS `Listing_type`, ";
			$sql .= $config['grid_photos_count'] ? "COUNT(`T6`.`Thumbnail`) AS `Photos_count`, " : "";
		}
		else
		{
			$sql  = "SELECT DISTINCT {hook} ";
			$sql .= "`T1`.*, `T3`.`Path` AS `Path`, `T3`.`Key` AS `Key`, `T3`.`Type` AS `Listing_type`, ";
		}

		$GLOBALS['rlHook'] -> load('listingsModifyField');

		$sql .= "IF(UNIX_TIMESTAMP(DATE_ADD(`T1`.`Featured_date`, INTERVAL `T4`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T4`.`Listing_period` = 0, '1', '0') `Featured` ";
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T4` ON `T1`.`Featured_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";

		if ( version_compare($config['rl_version'], '4.1.0') < 0 )
		{
			$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_photos` AS `T6` ON `T1`.`ID` = `T6`.`Listing_ID` ";
		}

		$sql .= "LEFT JOIN `". RL_DBPREFIX ."accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";

		$GLOBALS['rlHook'] -> load('listingsModifyJoin');

		$sql .= "WHERE ";	
		$sql .= " ( TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T2`.`Listing_period` * 24 OR `T2`.`Listing_period` = 0 )";
		if( $order == 'featured' )
		{
			$sql .= "AND ( TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T4`.`Listing_period` * 24 OR `T4`.`Listing_period` = 0 ) AND `T4`.`Status` = 'active'";
		}
		
		$sql .= "AND `T1`.`Status` = 'active' AND `T7`.`Status` = 'active' AND `T3`.`Status` = 'active'";

		if ( $type )
		{
			$GLOBALS['rlValid'] -> sql($type);
			$sql .= "AND (`T3`.`Type` = '{$type}' OR FIND_IN_SET( `T3`.`Type` , '{$type}') > 0 ) ";
		}

		if ( $unique && $rlListings -> selectedIDs )
		{
			$sql .= "AND FIND_IN_SET(`T1`.`ID`, '". implode(',', $rlListings -> selectedIDs) ."') = 0 ";
		}

		$plugin_name = "listings_box";
		$GLOBALS['rlHook'] -> load('listingsModifyWhere', $sql, $plugin_name); // > 4.1.0
		$GLOBALS['rlHook'] -> load('listingsModifyGroup');

		$sql .= "GROUP BY `T1`.`ID` ";

		switch ($order)
		{
			case 'popular':
				$sql .= "ORDER BY `T1`.`Shows` DESC ";
				break;
			case 'top_rating':
				$sql .= "ORDER BY `T1`.`lr_rating_votes` DESC ";
				break;
			case 'random':
				$sql .= "ORDER BY RAND() ";
				break;
			case 'featured':
				$sql .= "ORDER BY `T1`.`Last_show` ASC, RAND() ";
				break;
			case 'recently_added':
				$sql .= "ORDER BY `T1`.`Date` DESC ";
				break;

			default:
				$sql .= "ORDER BY `ID` DESC ";
				break;
		}

		$sql .= "LIMIT ". intval($limit);
		$sql = str_replace('{hook}', $hook, $sql);

		$listings = $this -> getAll($sql);
		$listings = $GLOBALS['rlLang'] -> replaceLangKeys($listings, 'categories', 'name');

		if ( empty($listings) )
		{
			return false;
		}

		foreach ( $listings as $key => $value )
		{
			if ( $unique)
			{
				/* get listing IDs */
				$rlListings -> selectedIDs[] =  $value['ID'];
			}
			$IDs[] = $value['ID'];
			/* populate fields */
			$fields = $GLOBALS['rlListings'] -> getFormFields( $value['Category_ID'], 'featured_form', $value['Listing_type'] );

			foreach ( $fields as $fKey => $fValue )
			{
				if ( $first )
				{
					$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $value[$fKey], 'listing', $value['ID'] );
				}
				else
				{
					if ( $field['Condition'] == 'isUrl' || $field['Condition'] == 'isEmail' )
					{
						$fields[$fKey]['value'] = $listings[$key][$item];
					}
					else
					{
						$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $value[$fKey], 'listing', $value['ID'] );
					}
				}
				$first++;
			}

			$listings[$key]['fields'] = $fields;
			$listings[$key]['listing_title'] = $GLOBALS['rlListings'] -> getListingTitle( $value['Category_ID'], $value, $value['Listing_type'] );
		}
		/* save show date */
		if ( $IDs && $order == 'featured')
		{
			$this -> query("UPDATE `" . RL_DBPREFIX . "listings` SET `Last_show` = NOW() WHERE `ID` = ". implode(" OR `ID` = ", $IDs));
		}

		return $listings;
	}

	/**
	* delete Rss
	*
	* @package xAjax
	*
	* @param int $id -  id
	*
	**/
	function ajaxDeleteBoxBlock( $id = false )
	{
		global $_response;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$id = (int)$id;
		if ( !$id )
		{
			return $_response;
		}

		$key = 'listing_box_' . $id;

		// delete rss feed
		$this -> query("DELETE FROM `". RL_DBPREFIX ."listing_box` WHERE `ID` = '{$id}' LIMIT 1");
		$this -> query("DELETE FROM `". RL_DBPREFIX ."blocks` WHERE `Key` = '{$key}' LIMIT 1");
		$this -> query("DELETE FROM `". RL_DBPREFIX ."lang_keys` WHERE `Key` = 'blocks+name+{$key}'");

		$_response -> script("
			listingsBox.reload();
			printMessage('notice', '{$GLOBALS['lang']['block_deleted']}')
		");

		return $_response;
	}
}
