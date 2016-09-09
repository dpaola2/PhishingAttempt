<?php


/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLSIMILARLISTINGS.CLASS.PHP
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

class rlSimilarListings extends reefless
{

	/**
	* get listings
	*
	* @param string $listing_id - listing id (optional), excluded from results listing id
	*
	* @return array - listings information
	**/

	function getListings( $listing_id = false )
	{
		global $sql, $config, $rlListings, $rlValid;

		$listing_id = $listing_id ? $listing_id : $_GET['listing_id'];

		$sql ="SELECT `T1`.*, `T2`.`Type` as `Listing_type`, `T2`.`Parent_ID` FROM `".RL_DBPREFIX."listings` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."categories` AS `T2` ON `T2`.`ID` = `T1`.`Category_ID` ";
		$sql .="WHERE `T1`.`ID` = {$listing_id} ";

		$listing_info = $this -> getRow( $sql );

		if( !$listing_info )
			return;

		if( $config['cache'] )
		{
			$config['cache'] = 0;
			$restore_cache = true;
		}

		$similar_form_fields = $GLOBALS['rlListings'] -> getFormFields( $listing_info['Category_ID'], 'similar_listings_form', $listing_info['Listing_type'] );
		
		if( $restore_cache )
		{
			$config['cache'] = 1;
		}

		/*if there is not form field, add category field*/		
		if( !$similar_form_fields )
		{
			$similar_form_fields['Category_ID'] = array(			
				    "Key" => "Category_ID",
				    "Type" => "select"				    
				);
		}

		$sql  = "SELECT DISTINCT {hook} ";
		$sql .= "`T1`.*, `T3`.`Path` AS `Path`, `T3`.`Key` AS `Key`, `T3`.`Type` AS `Listing_type`, ";

		$GLOBALS['rlHook'] -> load('listingsModifyField');

		$sql .= "IF(UNIX_TIMESTAMP(DATE_ADD(`T1`.`Featured_date`, INTERVAL `T4`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T4`.`Listing_period` = 0, '1', '0') `Featured` ";
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T4` ON `T1`.`Featured_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";

		$sql .= "LEFT JOIN `". RL_DBPREFIX ."accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";

		$GLOBALS['rlHook'] -> load('listingsModifyJoin');

		$sql .= "WHERE ";	
		$sql .= " ( TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T2`.`Listing_period` * 24 OR `T2`.`Listing_period` = 0 )";
		$sql .= "AND `T1`.`Status` = 'active' AND `T7`.`Status` = 'active' ";

		$sql .="AND `T1`.`ID` != '{$listing_id}' ";
		
		if( $config['sl_relevance_mode'] )
		{
			$hook = "( ";
		}

		foreach( $similar_form_fields as $key => $field )
		{
			if( $listing_info[$field['Key']] )
			{
				switch( $field['Type'] )
				{
					case "select":
						if( $field['Key'] == 'Category_ID' )
						{
							if( $config['sl_relevance_mode'] )
							{
								$hook .= "IF(`T1`.`Category_ID` = '{$listing_info[$field['Key']]}', 3, 0) + ";
								$hook .= "IF( FIND_IN_SET('{$listing_info['Category_ID']}', `T1`.`Crossed`) > 0 AND `T2`.`Cross` > 0, 2, 0 ) + ";
								$hook .= "IF( FIND_IN_SET('{$listing_info['Category_ID']}', `T3`.`Parent_IDs`) > 0, 1, 0 ) + ";
								$hook .="IF( FIND_IN_SET('{$listing_info['Parent_ID']}', `T3`.`Parent_IDs`) > 0, 1, 0 ) + ";
							}

							if( $config['sl_category_exact_match'] || !$config['sl_relevance_mode'] )
							{
								$sql .= "AND (`T1`.`Category_ID` = '{$listing_info['Category_ID']}' ";

								if( !$config['sl_category_exact_match'] || $config['sl_relevance_mode'] )
								{
									$sql .= "OR (FIND_IN_SET('{$listing_info['Category_ID']}', `T1`.`Crossed`) > 0 AND `T2`.`Cross` > 0 ) ";
									$sql .= "OR FIND_IN_SET('{$listing_info['Category_ID']}', `T3`.`Parent_IDs`) > 0 ";
									$sql .="OR FIND_IN_SET('{$listing_info['Parent_ID']}', `T3`.`Parent_IDs`) > 0 ";
								}

								$sql .=" ) ";
							}
							break;							
						}
						
						
					case "text":
						if( $config['sl_relevance_mode'] )
						{
							$keywords = preg_split("/[\s,]+/", $listing_info[$field['Key']]);
							if( $keywords )
							{
								foreach( $keywords as $kwKey => $keyword )
								{
									$hook .="IF(`T1`.`{$field['Key']}` LIKE '%".$rlValid -> xSql($keyword)."%', 1, 0) + ";
								}
							}
							break;
						}						
					default:
						if( $config['sl_relevance_mode'] )
						{
							$hook .="IF(`T1`.`{$field['Key']}` = '".$rlValid -> xSql($listing_info[$field['Key']])."', 1, 0) + ";
						}else
						{
							$sql .=" AND `T1`.`{$field['Key']}` = '".$rlValid -> xSql($listing_info[$field['Key']])."' ";
						}
						break;
				}
			}
		}

		if( $config['sl_relevance_mode'] )
		{			
			$hook = substr($hook, 0, -3);
			$hook .=" ) as `relevance`, ";
		}

		$plugin_name = "similar_listings";
		$GLOBALS['rlHook'] -> load('listingsModifyWhere', $sql, $plugin_name);
		$GLOBALS['rlHook'] -> load('listingsModifyGroup');

		$sql .= "GROUP BY `T1`.`ID` ";

		if( $config['sl_relevance_mode'] )
		{
			$sql .= "ORDER BY `relevance` DESC ";
		}
		else
		{
			$sql .= "ORDER BY RAND() ";
		}

		if( $config['sl_listings_in_box'] )
		{
			$sql .= "LIMIT ".intval($config['sl_listings_in_box']);
		}

		$sql = str_replace('{hook}', $hook, $sql);
		
		$listings = $this -> getAll($sql);
		$listings = $GLOBALS['rlLang'] -> replaceLangKeys($listings, 'categories', 'name');

		if ( empty($listings) )
		{
			return false;
		}

		foreach ( $listings as $key => $value )
		{
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

		return $listings;
	}
}	
