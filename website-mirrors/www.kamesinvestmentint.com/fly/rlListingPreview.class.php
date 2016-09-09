<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLLISTINGPREVIEW.CLASS.PHP
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

class rlListingPreview extends reefless
{
	/**
	* prepare data for listing preview stap
	**/
	function step()
	{
		global $rlDb, $listing_id, $rlSmarty, $rlHook, $rlXajax, $lang, $config, $rlLang, $reefless, $l_youtube_thumbnail, 
		$rlListings, $rlAccount, $rlListingTypes, $page_info, $listing_data, $next_step, $category, $tpl_settings;

		if ( $_POST['step'] == 'preview' )
		{
			$redirect = SEO_BASE;
			$redirect .= $config['mod_rewrite'] ? $page_info['Path'] .'/'. $category['Path'] .'/'. $next_step['path'] .'.html' : '?page='. $page_info['Path'] .'&id='. $category['ID'] .'&step=' .$next_step['path'];
			$reefless -> redirect( null, $redirect );
			exit;
		}

		$page_info['name'] = $lang['listingPreview_preview'];
		
		/* get current listing details */
		$sql = "SELECT `T1`.*, `T2`.`Path`, `T2`.`Type` AS `Listing_type`, `T2`.`Key` AS `Cat_key`, `T2`.`Type` AS `Cat_type`, ";
		$sql .= "`T3`.`Image`, `T3`.`Image_unlim`, `T3`.`Video`, `T3`.`Video_unlim`, CONCAT('categories+name+', `T2`.`Key`) AS `Category_pName` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T5` ON `T1`.`Account_ID` = `T5`.`ID` ";
		$sql .= "WHERE `T1`.`ID` = '{$listing_id}' AND `T5`.`Status` = 'active' LIMIT 1";
		
		$listing_data = $rlDb -> getRow($sql);
		$rlSmarty -> assign_by_ref('listing_data', $listing_data);
		
		/* define listing type */
		$listing_type = $rlListingTypes -> types[$listing_data['Listing_type']];
		$rlSmarty -> assign_by_ref('listing_type', $listing_type);
		
		$rlHook -> load('listingDetailsTop');
	
		/* build listing structure */
		$category_id = $listing_data['Category_ID'];
		$listing = $rlListings -> getListingDetails( $category_id, $listing_data, $listing_type );	
		$rlSmarty -> assign( 'listing', $listing );
	
		/* get seller information */
		$seller_info = $rlAccount -> getProfile((int)$listing_data['Account_ID']);
		$rlSmarty -> assign_by_ref('seller_info', $seller_info);
		
		/* build location fields */
		if ( $config['address_on_map'] && $listing_data['account_address_on_map'] )
		{
			/* get location data from user account */
			$location = $rlAccount -> mapLocation;
		}
		else
		{
			/* get location data from listing */
			$fields_list = $rlListings -> fieldsList;
		
			$location = false;
			foreach ( $fields_list as $key => $value )
			{
				if ( $fields_list[$key]['Map'] && !empty($listing_data[$fields_list[$key]['Key']]) )
				{
					$mValue = str_replace( "'", "\'", $value['value'] );
					$location['search'] .= $mValue .', ';
					$location['show'] .= $lang[$value['pName']].': <b>'. $mValue .'<\/b><br />';
					unset($mValue);
				}
			}
			if ( !empty($location) )
			{
				$location['search'] = substr($location['search'], 0, -2);
			}
			
			if ( $listing_data['Loc_latitude'] && $listing_data['Loc_longitude'] )
			{
				$location['direct'] = $listing_data['Loc_latitude'] .','. $listing_data['Loc_longitude'];
			}
		}
		$rlSmarty -> assign_by_ref( 'location', $location );
		
		/* get listing title */
		$listing_title = $rlListings -> getListingTitle( $category_id, $listing_data, $listing_type['Key'] );
		$rlSmarty -> assign_by_ref('listing_title', $listing_title);
		
		/* get listing photos */
		$photos = $rlDb -> fetch( '*', array( 'Listing_ID' => $listing_id, 'Status' => 'active' ), "AND `Thumbnail` <> '' AND `Photo` <> '' ORDER BY `Position`", $listing_data['Image'], 'listing_photos' );
		$rlSmarty -> assign_by_ref( 'photos', $photos );
		
		/* get amenties */
		if ( $config['map_amenities'] )
		{
			$rlDb -> setTable('map_amenities');
			$amenities = $rlDb -> fetch(array('Key', 'Default'), array('Status' => 'active'), "ORDER BY `Position`");
			$amenities = $rlLang -> replaceLangKeys( $amenities, 'map_amenities', array('name') );
			$rlSmarty -> assign_by_ref('amenities', $amenities);
		}
		
		/* get listing video */
		$rlDb -> setTable('listing_video');
		$videos = $rlDb -> fetch(array('ID', 'Type', 'Video', 'Preview'), array( 'Listing_ID' => $listing_id ), "ORDER BY `Position`");
		$rlSmarty -> assign_by_ref( 'videos', $videos );
		
		/* populate tabs */
		$tabs = array(
			'listing' => array(
				'key' => 'listing',
				'name' => $lang['listing']
			),
			'seller' => array(
				'key' => 'seller',
				'name' => $lang['seller_info']
			),
			'video' => array(
				'key' => 'video',
				'name' => $lang['video']
			),
			'map' => array(
				'key' => 'map',
				'name' => $lang['map']
			),
			'tell_friend' => array(
				'key' => 'tell_friend',
				'name' => $lang['tell_friend']
			)
		);

		if ( $tpl_settings['type'] == 'responsive_42' ) {
			/* remove unnecessary steps for responsive template */
			unset($tabs['seller'], $tabs['map'], $tabs['video']);

			/* merge photos and videos */
			foreach ($videos as &$video) {
				if ( $video['Type'] == 'youtube' ) {
					$video['Thumbnail'] = str_replace('{key}', $video['Preview'], $l_youtube_thumbnail);
					$video['Photo'] = '//www.youtube.com/watch?v='.$video['Preview'];
				}
				else {
					$video['Thumbnail'] = RL_FILES_URL . $video['Preview'];
					$video['Photo'] = RL_FILES_URL . $video['Video'];
				}
			}

			foreach ($photos as &$photo) {
				$photo['Photo'] = RL_FILES_URL . $photo['Photo'];
				$photo['Thumbnail'] = RL_FILES_URL . $photo['Thumbnail'];
			}

			$photos = array_merge($photos, $videos);
		}
		
		if ( empty($videos) || !$listing_type['Video'] || ($listing_data['Video'] == 0 && !$listing_data['Video_unlim']) )
		{
			unset($tabs['video']);
		}
		if ( !$config['map_module'] || !$location )
		{
			unset($tabs['map']);
		}
		
		$rlSmarty -> assign_by_ref('tabs', $tabs);
		
		/* register ajax methods */
		$rlXajax -> registerFunction( array( 'tellFriend', $rlListings, 'ajaxTellFriend' ) );
		$rlXajax -> registerFunction( array( 'contactOwner', $rlMessage, 'ajaxContactOwner' ) );
	
		$rlHook -> load('listingDetailsBottom');
	}
}