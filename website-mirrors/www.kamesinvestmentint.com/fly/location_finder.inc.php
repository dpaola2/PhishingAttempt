<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: LOCATION_FINDER.INC.PHP
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

$rlatitude = $_POST['rlatitude'];
$rlongitude = $_POST['rlongitude'];
$distance = $_POST['distance'];
$limit = $_POST['limit'];
$data = array();

if ( !empty( $rlongitude ) && !empty( $rlongitude ) )
{
	$sql  = "SELECT DISTINCT SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `T3`.`Thumbnail` ORDER BY `T3`.`Type` DESC), ',', 1) AS `Main_photo`, `T1`.*, `T4`.`Type` AS `Listing_type`, ";
	$sql .= "3956 * 2 * ASIN(SQRT(
			POWER(SIN(({$rlatitude} - `T1`.`Loc_latitude`) * 0.0174532925 / 2), 2) +
			COS({$rlatitude} * 0.0174532925) * COS(`T1`.`Loc_latitude` * 0.0174532925) *
			POWER(SIN(({$rlongitude} - `T1`.`Loc_longitude`) * 0.0174532925 / 2), 2))) `distance` ";

	$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
	$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
	$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_photos` AS `T3` ON `T1`.`ID` = `T3`.`Listing_ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
	$sql .= "WHERE UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) ";
	$sql .= "AND `T1`.`Status` = 'active' AND `T1`.`Loc_latitude` <> '' AND `T1`.`Loc_longitude` <> '' ";
	$sql .= "GROUP BY `T1`.`ID` HAVING `distance` <= {$distance}";

	if ( $limit != 'all' )
	{
		$sql .= " LIMIT ". (int)$limit;
	}
	$listings = $rlDb -> getAll($sql);

	if ( !empty($listings) )
	{
		$reefless -> loadClass('Listings');
		foreach( $listings as $key => $listing )
		{
			$thumbnail = false;
			if ( $rlListingTypes -> types[$listing['Listing_type']]['Photo'] )
			{
				if ( empty($listing['Main_photo']) || !file_exists(RL_FILES . $listing['Main_photo']) )
				{
					$thumbnail = RL_TPL_BASE .'img/no-picture.jpg';
				}
				else
				{
					$thumbnail = RL_FILES_URL . $listing['Main_photo'];
				}
			}

			$listingTitle = $listing['title'];
			if ( empty($listingTitle) )
			{
				$listingTitle = $rlListings -> getListingTitle($listing['Category_ID'], $listing, $listing['Listing_type']);
			}
			$listingTitle = !empty($listingTitle) ? $listingTitle : 'Title';

			$index = count($data);
			$data[$index] = array(
				'id' => (int)$listing['ID'],
				'title' => $listingTitle,
				'subtitle' => str_replace('{distance}', round($listing['distance'], 1), $lang['iflynax_distance_to_ad']), //$listing['Loc_address'],
				'latitude' => $listing['Loc_latitude'],
				'longitude' => $listing['Loc_longitude']
			);

			if ( $thumbnail )
			{
				$data[$index]['thumbnail'] = $thumbnail;
			}
		}
	}
	unset($listings);
}

$iPhone -> printAsXml($data);