<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLCATEGORIESICONS.CLASS.PHP
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

class rlCategoriesIcons extends reefless 
{
	/**
	* 
	*/
	function ajaxDeleteIcon($category_key = false)
	{
		global $_response, $rlCache, $rlActions;

		$GLOBALS['rlValid'] -> sql($category_key);
		$_response -> setCharacterEncoding('UTF-8');

		$icon = $this -> getOne('Icon', "`Key` = '{$category_key}'", 'categories');

		$update_info = array(
			'fields' => array('Icon' => ''),
			'where' => array('Key' => $category_key)
		);

		$this -> loadClass('Actions');
		$rlActions -> updateOne($update_info, 'categories');

		if ( !empty($icon) )
	    {
			@unlink(RL_FILES . $icon);
			@unlink(RL_FILES . str_replace('icon', 'icon_original', $icon));
	    }

		$GLOBALS['rlCache'] -> updateCategories();

		$_response -> script("$('#gallery').slideUp('normal');");
		$_response -> script("$('#fileupload').html(null);");
		$_response -> script("printMessage('notice','{$GLOBALS['lang']['category_icon_icon_deleted']}');");

	    return $_response;
	}

	/**
	* 
	*/
	function updateIcons($width = 0, $height = 0)
	{
		global $config;

		if ($width > 0 && $height > 0)
		{
        	$sql  = "SELECT `ID`, `Icon` FROM `". RL_DBPREFIX ."categories` ";
			$sql .= "WHERE `Icon` <> '' AND `Status` <> 'trash'";
	        $categories = $this -> getAll($sql);

			if ( !empty($categories) )
			{
				$this -> loadClass('Resize');
				$this -> loadClass('Crop');

				foreach($categories as $key => $category)
				{
					if ( !empty($category['Icon']) )
					{
						$original = RL_FILES . str_replace("icon", "icon_original", $category['Icon']);
						$icon_name = $category['Icon'];
						$icon_file = RL_FILES . $icon_name;

						if ( $config['icon_crop_module'] )
						{
							$GLOBALS['rlCrop'] -> loadImage($original);
							$GLOBALS['rlCrop'] -> cropBySize($width, $height, ccCENTER);
							$GLOBALS['rlCrop'] -> saveImage($icon_file, $config['img_quality']);
							$GLOBALS['rlCrop'] -> flushImages();

							$GLOBALS['rlResize'] -> resize($icon_file, $icon_file, 'C', array($width, $height));
						}
						else
						{
							$GLOBALS['rlResize'] -> resize($original, $icon_file, 'C', array($width, $height), null, false);
						}

						if ( is_readable($icon_file) )
						{
							chmod($icon_file, 0644);
						}
					}
				}
			}
			unset($categories);
		}
	}

	/**
	* 
	*/
	function isImage( $image = false )
	{
		if ( !$image )
		{
			return false;
		}

		$allowed_types = array(
			'image/gif',
			'image/jpeg',
			'image/jpg',
			'image/png'
		);

		$img_details = getimagesize( $image );
		/*$mime = image_type_to_mime_type( exif_imagetype( $image ) );*/

		if ( in_array( $img_details['mime'], $allowed_types ) )
		{
			return true;
		}
		return false;
	}
}