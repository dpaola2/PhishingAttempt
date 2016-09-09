<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: {version}
 *	LICENSE: RETAIL - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: xxxxxxxxxxxx.com
 *	FILE: REQUEST.AJAX.PHP
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

require_once('..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'config.inc.php');
require_once('controllers'. RL_DS .'ext_header.inc.php');

$item = $_REQUEST['item'];

switch ($item){
	/* get category path by ID */
	case 'getCategoryPathByID':
		$category_id = (int)$_REQUEST['id'];

		if ( $category_id )
		{
			$out = $rlDb -> getOne('Path', "`ID` = '{$category_id}'", 'categories');
		}
		
		break;
		
	/* get category titles by listing type */
	case 'getCategoriesByType':
		$type_key = $rlValid -> xSql($_REQUEST['type']);
		
		$reefless -> loadClass('Categories');
		$out = array_values($rlCategories -> getCatTitles($type_key));
		break;	
	case 'refreshImages':
		$reefless -> loadClass('Resize');
		$reefless -> loadClass('Crop');
		$reefless -> loadClass('Listings');

		$limit = $_REQUEST['limit'];
		$start = $_REQUEST['start'] ? $_REQUEST['start'] : 0;
	
		$photos = $rlDb -> fetch("*", null, null, array($start, $limit), "listing_photos" );

		if( !$photos )
		{
			$out = 'end';
		}else
		{
			foreach( $photos as $key => $photo )
			{
				$mt = time() . mt_rand();
				$source = $photo['Original'] ? $photo['Original'] : $photo['Photo'];
				$rlResize -> refreshImage( $photo['Thumbnail'], 'thumbnail', $mt, $source );
				$rlResize -> refreshImage( $photo['Photo'], 'large', $mt, $source );

				$rlListings -> updatePhotoData( $photo['Listing_ID'] );
			}
			$out = true;
		}
	break;
}

$rlHook -> load('apAjaxRequest');

if ( !empty($out) )
{
	$reefless -> loadClass('Json');
	echo $rlJson -> encode($out);
}
else
{
	echo null;
}