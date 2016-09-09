<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: APPHPCATEGORIESBEFOREADD.PHP
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

global $reefless, $data;

$reefless->loadClass( 'CategoriesIcons', null, 'categories_icons' );

if ( !empty( $_FILES['icon']['tmp_name'] ) && $rlCategoriesIcons -> isImage( $_FILES['icon']['tmp_name'] ) )
{
    $reefless->loadClass('Actions');
    $reefless->loadClass( 'Resize');

    $reefless->loadClass('Crop');

    $file_ext = explode('.', $_FILES['icon']['name']);
    $file_ext = array_reverse($file_ext);
    $file_ext = '.' . $file_ext[0];

    $tmp_location = RL_UPLOAD."tmp_listing" . $id . '_' . mt_rand() . time() . $file_ext;

    if(move_uploaded_file($_FILES['icon']['tmp_name'], $tmp_location))
    {
        chmod($tmp_location, 0777);

        $icon_name = "category_icon_" . $id . '_' . mt_rand() . time() . $file_ext;

		$icon_original = str_replace("icon", "icon_original", $icon_name);
		copy($tmp_location, RL_FILES . $icon_original);

        $icon_file = RL_FILES . $icon_name;

		if($GLOBALS['config']['icon_crop_module'])
		{
			$rlCrop -> loadImage($tmp_location);
			$rlCrop -> cropBySize($GLOBALS['config']['categories_icons_width'], $GLOBALS['config']['categories_icons_height'], ccCENTER);
			$rlCrop -> saveImage($icon_file, $GLOBALS['config']['img_quality']);
			$rlCrop -> flushImages();

			$GLOBALS['rlResize'] -> resize($icon_file, $icon_file, 'C', array($GLOBALS['config']['categories_icons_width'], $GLOBALS['config']['categories_icons_height']));
		}
		else
		{
			$GLOBALS['rlResize']->resize($tmp_location, $icon_file, 'C', array($GLOBALS['config']['categories_icons_width'], $GLOBALS['config']['categories_icons_height']), null, false);   
		}

        unlink($tmp_location);

        if(is_readable($icon_file))
        {
            chmod($icon_file, 0644);

			$data['Icon'] = $icon_name;
        }
    }
}