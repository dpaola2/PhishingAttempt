<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.0
 *	LISENSE: http://www.flynax.com/license-agreement.html
 *	PRODUCT: General Classifieds
 *	
 *	FILE: LISTING_REMOVE.INC.PHP
 *
 *	This script is a commercial software and any kind of using it must be 
 *	coordinate with Flynax Owners Team and be agree to Flynax License Agreement
 *
 *	This block may not be removed from this file or any other files with out 
 *	permission of Flynax respective owners.
 *
 *	Copyrights Flynax Classifieds Software | 2012
 *	http://www.flynax.com/
 *
 ******************************************************************************/

$id = (int)$_GET['id'];
$hash = $rlValid -> xSql($_GET['hash']);
$md5hash = md5($hash);

if ( !isset($_GET['complete']) )
{
	if ( !$id || !$hash )
	{
		$sError = true;
	}
	else
	{
		$sql = "SELECT `T1`.`ID`, `T1`.`Last_step`, `T2`.`Type` AS `Listing_type` ";
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";	
		$sql .= "WHERE `T1`.`ID` = '{$id}' AND `T1`.`Loc_address` = '{$md5hash}' AND `T1`.`Status` = 'incomplete' LIMIT 1";
		$listing = $rlDb -> getRow($sql);
		
		if ( $listing )
		{
			if ( isset($_GET['confirm']) )
			{
				$rlListings -> deleteListingData($listing['ID']);
				$rlDb -> query("DELETE FROM `". RL_DBPREFIX ."listings` WHERE `ID` = '{$listing['ID']}' LIMIT 1");
				
				$reefless -> loadClass('Notice');
				$rlNotice -> saveNotice($lang['remote_delete_listing_removed']);
				
				$url = SEO_BASE;
				$url = $config['mod_rewrite'] ? $pages['listing_remove'] .'.html?complete' : '?page='. $pages['listing_remove']. '&complete';
				$reefless -> redirect(null, $url);
			}
			else
			{
				$rlSmarty -> assign_by_ref('listing', $listing);
				$rlSmarty -> assign('show_form', true);
				$rlSmarty -> assign_by_ref('listing_type', $rlListingTypes -> types[$listing['Listing_type']]);
			}
		}
		else
		{
			$pAlert = $lang['remote_delete_listing_alert'];
			$rlSmarty -> assign('pAlert', $pAlert);
		}
	}
}