<?php

set_time_limit(0);

/* system config */
if( !$_GET['feed'] )
{
	require_once( '../../includes/config.inc.php' );
	require_once( 'control.inc.php' );

	$rlLang -> defineLanguage();
}

if( defined('AJAX_MODE') )
{	
	global $reefless, $rlDb, $rlXmlImport, $flMap;
}

$reefless -> loadClass('XmlImport', null, 'xmlFeeds');


$sql = "SELECT `T1`.*, `T1`.`Key` as `Feed`, `T2`.`Key` as `Format`, `T2`.`Xpath` ";
$sql .="FROM `".RL_DBPREFIX."xml_feeds` AS `T1` ";
$sql .="LEFT JOIN `".RL_DBPREFIX."xml_formats` AS `T2` ON `T2`.`Key` = `T1`.`Format` ";
$sql .="WHERE `T1`.`Status` = 'active' AND `T2`.`Status` = 'active' ";

if( $_GET['feed'] )
{
	$sql .="AND `T1`.`Key` ='".$_GET['feed']."' ";
}

$feeds = $rlDb -> getAll( $sql );

if( $rlDb -> getOne("Key", "`Key` = 'ref'", "plugins") )
{
	$reefless -> loadClass("Ref", null, "ref");	
}

foreach( $feeds as $feed_key => $feed )
{
	$rlXmlImport -> loadFormat( 'flMap' );

	$flMap -> xml_file = $feed['Url'];
	$flMap -> xpath = $feed['Xpath'];

	$rlXmlImport -> print_progress = $_GET['feed'] ? true : false;


	$listing_base_data = array('Date' => 'NOW()', 'Pay_date' => 'NOW()',
								'Plan_ID' => $feed['Plan_ID'], 
								'Category_ID' => $feed['Default_category'],
								'Account_ID' => $feed['Account_ID'],
								'Status' => $feed['Listings_status'] ? $feed['Listings_status'] : 'active',
								'xml_feed_key' => $feed['Feed'],
								'account_type' => $feed['Feed_account_type']
							);
	$plan_info = $rlDb -> fetch("*", array( "ID" => $feed['Plan_ID'] ), null, null, 'listing_plans', 'row');
	if( $plan_info['Featured'] )
	{
		$listing_base_data['Featured_ID'] = $feed['Plan_ID'];
		$listing_base_data['Featured_date'] = 'NOW()';
	}

	$flMap -> listing_base_data = $listing_base_data;
	
	$start_time = time();

	$flMap -> import( $feed );
	
	if( !defined('AJAX_MODE') )
	{
		if( $feed['Feed'] && $start_time && $config['xf_set_missed_listings_expired'] )
		{
			$sql ="UPDATE `".RL_DBPREFIX."listings` SET `Status` = 'expired' WHERE `feed_key_xf` = '".$feed['Feed']."' AND UNIX_TIMESTAMP(`Date`) < $start_time AND UNIX_TIMESTAMP(`Pay_date`) < $start_time ";
			$rlDb -> query( $sql );
		}
	
		$rlXmlImport -> saveStatistics( $flMap -> statistics, $feed );
		$flMap -> statistics = false;
	}
}
