<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: USER_FEEDS.INC.PHP
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

$formats = $rlDb -> fetch( array('Key'), array( 'Status' => 'active' ) , "AND FIND_IN_SET('import', `Format_for`) ORDER BY `Key`", null, 'xml_formats' );

if( !$formats )
{
	$errors[] = $lang['xf_configure_formats'];
}
else
{
	$formats = $rlLang -> replaceLangKeys( $formats, 'xml_formats', 'name', RL_LANG_CODE );
	$rlSmarty -> assign_by_ref('formats', $formats);

	$reefless -> loadClass('XmlImport', null, 'xmlFeeds');

	if( $_POST['submit'] )
	{
		$feed_url = $_POST['feed_url'];
		$format = $_POST['xml_format'];
		$name = $_POST['feed_name'];
		$feed_key = $rlValid -> str2key( $name )."_".$account_info['ID']."_".rand();

		if( !trim($feed_url) )
		{			
			$errors[] = str_replace('{field}', '<span class="field_error">'. $lang['xf_feed_url'] .'</span>', $lang['notice_field_empty']);
			$error_fields .= 'feed_url,';
		}
		elseif( !$rlValid -> isUrl($feed_url) )
		{
			$errors[] = str_replace('{field}', '<span class="field_error">'. $lang['xf_feed_url'] .'</span>', $lang['notice_field_incorrect']);
			$error_fields .= 'feed_url,';
		}
		elseif( !$format || $format == "0" )
		{
			$errors[] = str_replace('{field}', '<span class="field_error">'. $lang['xf_feed_url'] .'</span>', $lang['notice_field_empty']);
			$error_fields .= 'xml_format,';
		}
/*		elseif( !$rlXmlImport -> checkFeed( $feed_url, $format ) )
		{
			//$errors[] = str_replace('{field}', '<span class="field_error">'. $lang['xf_feed_url'] .'</span>', $lang['notice_field_incorrect']);
			$errors[] = 'Feed you entered is not a $format feed';
			$error_fields .= 'xml_feeds,';
		}*/


		if( !$errors )
		{
			$insert['Key'] = $feed_key;
			$insert['Url'] = $feed_url;
			$insert['Account_ID'] = $account_info['ID'];
			$insert['Format'] = $format;
			$insert['Plan_ID'] = $rlDb -> getOne("ID", "`Status` = 'active' AND `Price` = 0", "listing_plans");
			$insert['Feed_type'] ='one';			
			$insert['Feed_account_type'] = '';
			$insert['Default_category'] = '';
			$insert['Listings_status'] = $config['xml_users_feeds_status'] == 'active' ? 'active' : 'approval';
			$insert['Status'] = 'active';

			$reefless -> loadClass('Actions');

			if( $rlActions -> insertOne($insert, "xml_feeds") )
			{
				foreach( $languages as $key => $value )
				{
					$lang_insert[$key]['Code'] = $value['Code'];
					$lang_insert[$key]['Module'] = 'common';
					$lang_insert[$key]['Key'] = 'xml_feeds+name+'.$feed_key;
					$lang_insert[$key]['Value'] = $name;
					$lang_insert[$key]['Status'] = 'active';
				}

				$rlActions -> insert($lang_insert, "lang_keys");

				$reefless -> loadClass( 'Notice' );
				$rlNotice -> saveNotice( $lang['xf_feed_submitted'] );
				$reefless -> refresh();
			}
		}
	}

	$feeds = $rlDb -> fetch("*", array("Account_ID" => $account_info['ID']), null, null, "xml_feeds");	
	$feeds = $rlLang -> replaceLangKeys( $feeds, 'xml_feeds', 'name', RL_LANG_CODE );

	$rlSmarty -> assign("feeds", $feeds);

	$reefless -> loadClass('XmlFeeds', null, "xmlFeeds");
	$rlXajax -> registerFunction( array( 'deleteXmlFeed', $rlXmlFeeds, 'ajaxDeleteXmlFeed' ) );
}