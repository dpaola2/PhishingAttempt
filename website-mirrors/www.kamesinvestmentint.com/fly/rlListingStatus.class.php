<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLLISTINGSTATUS.CLASS.PHP
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

class rlListingStatus extends reefless
{
	/**
	* @var language class object
	**/
	var $rlLang;
	
	/**
	* @var validator class object
	**/
	var $rlValid;

	var $lbGlobalBoxes = array();

	/**
	* class constructor
	**/
	function rlListingStatus()
	{
		global $rlLang, $rlValid;
		
		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
	}
	
	/*
	*
	* install first status(sold)
	*
	*/
	function installWatermark()
	{
		$allLangs = $GLOBALS['languages'];
		
		$data = array(
			'Key' => 'sold',
			'Type' => 'listings',
			'Days' => '3',
			'Count' => '6',
			'Delete' => 'disabled',
			'Used_block' => '1',
			'Status' => 'active'
		);
		
		$folder_name = RL_FILES . "watermark";
		$this -> rlMkdir($folder_name);
		
		if ( $GLOBALS['rlActions'] -> insertOne( $data, 'listing_status' ) )
		{
			$file =  RL_PLUGINS . 'listing_status' . RL_DS . 'sold.png';
			$fileLarge =  RL_PLUGINS . 'listing_status' . RL_DS . 'sold_large.png';
			
			$field = '';
			$fieldL = '';
			foreach ($allLangs as $lkey => $lval )
			{
				$newname = 'sold_' . $lkey . '.png';
				$newnameLarge = 'sold_large_' . $lkey . '.png';
				$newfile = $folder_name . RL_DS . $newname;			
				$newfileLarge = $folder_name . RL_DS . $newnameLarge;			
				
				if (copy($file, $newfile) && copy($fileLarge, $newfileLarge)) 
				{
					$field = 'watermark_'. $lkey;
					$fieldL = 'watermarkLarge_'. $lkey;
					$this -> query( "ALTER TABLE `".RL_DBPREFIX."listing_status` ADD `{$field}` VARCHAR( 50 ) NOT NULL AFTER `Key`, ADD `{$fieldL}` VARCHAR( 50 ) NOT NULL AFTER `Key`" );
					$updat_watermark['fields'][$field] = $newname;
					$updat_watermark['fields'][$fieldL] = $newnameLarge;
				}
			}
			
			$updat_watermark['where']['ID'] = '1';
				
			$GLOBALS['rlActions'] -> updateOne( $updat_watermark, 'listing_status' );
		}
		return $listings;		
	}
	
	
	
	/*
	*
	* delete all watermark in folder
	**/
	function ajaxDeleteWatermark( $key = false, $code = false, $name = false , $large = false )
	{
		global $_response;
		
		$field = $large==1 ? 'watermarkLarge_' . $code : 'watermark_' . $code;

		/*update watermark*/
		$this -> query("UPDATE `".RL_DBPREFIX."listing_status` SET `{$field}` = '' WHERE `Key` = '{$key}'" );
		
		/*delete watermark*/
		$file = RL_FILES . "watermark" . RL_DS . $name;
		unlink($file);
		
		$GLOBALS['rlSmarty'] -> assign_by_ref( 'code', $code );
		if($large)
		{
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'large', $large );
		}
		
		$tpl = RL_PLUGINS . 'listing_status' . RL_DS . 'admin' . RL_DS . 'watermark.tpl';
		$_response -> assign( $field, 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));		
		return $_response;
	}
	
	/**
	* get listings
	*
	* @param string $type - type list
	* @param string $days - days keep in active
	* @param int $limit - listing number per request
	* @param int $label - key label
	* @param int $field - fields for update in grid
	*
	* @return array - listings information
	**/
	function checkContentBlock( $type = false, $days = false, $limit = false, $label = false, $order = false, $field = false)
	{
		$days = (int)$days;
		$limit = (int)$limit;
		if( is_array($field) )
		{			
			$data = $this -> fetch( array('Type','Days', 'Count', 'Order', 'Key'), array( 'ID' => $field[2] ), null, null, 'listing_status', 'row' );
			$type = $data['Type'];
			$label = $data['Key'];
			$order = $data['Order'];
			if( $field[0] == 'Days' )
			{
				$days = $field[1];
				$limit = $data['Count'];
			}
			elseif( $field[0] == 'Count' )
			{
				$days = $data['Days'];
				$limit = $field[1];
			}
		}
		
		$content = '
				global $reefless, $rlSmarty;
				$reefless -> loadClass("ListingStatus", null, "listing_status");
				global $rlListingStatus;
				$ls_listings = $rlListingStatus -> getRecentlySoldListings( "' . $type . '", "' . $days . '", "' . $limit . '", "' . $label . '", "' . $order . '" );
				$key_s = ' . $label .';
				$rlSmarty -> assign_by_ref( "ls_key",  $key_s);
				$rlSmarty -> assign_by_ref( "ls_listings", $ls_listings );
				$rlSmarty -> display( RL_PLUGINS . "listing_status" . RL_DS . "recently_sold.block.tpl" );
			';

		return preg_replace("'(\r|\n|\t)'", "", $content);
	}

	/**
	* Prepare sold boxes
	*/
	function prepareSoldBoxes()
	{
		global $block_keys, $blocks, $rlHook, $rlMemcache;

		// search labels in boxes
		foreach ($block_keys as $key => $value)
		{
			if ( (bool)preg_match('/^lb_/', $key) )
			{
				// get box params
				// $ls_listings = $rlListingStatus -> getRecentlySoldListings( "listings", "10", "4", "sold", "latest" );
				// preg_match('/getRecentlySoldListings\(\s?"([a-z0-9_]+)",\s?"([0-9]+)",\s?"([0-9]+)",\s?"([a-z0-9_]+)",\s?"(latest|random)"/i', $blocks[$key]['Content'], $matches);
				preg_match('/getRecentlySoldListings\(\s?"([a-z0-9_,]+)",\s?"([0-9]+)",\s?"([0-9]+)",\s?"([a-z0-9_]+)",\s?"(latest|random)"/i', $blocks[$key]['Content'], $matches);
				if ( count($matches) === 6 )
				{
					list($subject, $type, $days, $limit, $label, $order) = $matches;
					$tmp = $this -> getGlobalRecentlySoldListings($type, $days, $limit, $label, $order);

					if ( !empty($tmp) )
					{
						$lbgKey = md5($type . $days . $limit . $label . $order);
						$this -> lbGlobalBoxes[$lbgKey] = $tmp;
						unset($tmp);
					}
				}
			}
		}
	}

	/**
	* ajax Change Status
	*
	* @param string $listing_id - id listing
	* @param int $status - status listing
	*
	* @return array - listings information
	**/
	
	function ajaxChangeStatus( $listing_id, $status = 'visible', $admin = false )
	{
		global $_response, $config, $tpl_settings;

		if(empty($listing_id))
		{
			return $_response;
		}
		
		$allLangs = $GLOBALS['languages'];
		
		
		$GLOBALS['reefless'] -> loadClass('Categories');
		$GLOBALS['reefless'] -> loadClass('Resize');
		$GLOBALS['reefless'] -> loadClass('Crop');

		$listing_info = $this -> fetch(array('Sub_status', 'Category_ID'), array('ID' => $listing_id), NULL, NULL, 'listings', 'row');
		
		if( $status != 'visible' && $status != 'invisible' )
		{
			$sql ="UPDATE `".RL_DBPREFIX."listings` SET `Sold_date` = NOW(),`Sub_status` = '{$status}' WHERE `ID` =".$listing_id;
			$this->query($sql);
			
			if ($config['rl_version'] == '4.0.1')
			{
				$sql = "SELECT SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `T6`.`Thumbnail` ORDER BY `T6`.`Type` DESC, `T6`.`ID` ASC), ',', 1) AS `Main_photo` FROM `".RL_DBPREFIX."listings` AS `T1` ";
				$sql .="LEFT JOIN `".RL_DBPREFIX."listing_photos` AS `T6` ON `T1`.`ID` = `T6`.`Listing_ID` ";		
				$sql .="WHERE `T1`.`ID` =".$listing_id."  LIMIT 1";
				$main = $this->getRow($sql);
			}
			else
			{
				$main['Main_photo'] = $this->getOne('Main_photo',"`ID` = {$listing_id}",'listings');
			}
			
			if($main['Main_photo'])
			{
				$sql ="SELECT * FROM `".RL_DBPREFIX."listing_photos` WHERE `Listing_ID` =".$listing_id." ORDER BY `Position`";
				$photos = $this->getAll($sql);
				
				
				$GLOBALS['config']['watermark_using'] = 1;
				$GLOBALS['config']['watermark_type'] = 'image';
				
				$status_info = $this -> fetch( '*', array( 'Status' => 'active', 'Key'=>$status ), null, null, 'listing_status','row' );
				
				
				foreach($allLangs as $kLang => $code)
				{
					foreach($photos as $key => $photo)
					{
						$stat_field = 'watermark_'. $code['Code'];
						$GLOBALS['config']['watermark_image_url'] = RL_FILES . 'watermark' . RL_DS . $status_info[$stat_field];
						
						$original_file = RL_FILES. $photo['Original'];
						$thumbnail_file = RL_FILES. $photo['Thumbnail'];
						$photo_file = RL_FILES. $photo['Photo'];
						
						$thum_name = explode('.',$photo['Thumbnail']);
						$thum_ext = $thum_name[1];
						$thum_pname = $thum_name[0];

						$old_thumb = RL_FILES . str_replace('/', RL_DS,$thum_pname) .'_' . $listing_info['Sub_status'] . '_' . $code['Code'] . '.' . $thum_ext;
						
						if( file_exists($old_thumb) )
						{
							unlink($old_thumb);
						}
						
						$new_thumb = $thum_pname .'_' . $status . '_' . $code['Code'] . '.' . $thum_ext;
						$new_thumb_file = RL_FILES . $new_thumb;
						
						chmod( $new_thumb_file, 0777 );
						
						if($photo['Original'])
						{
							$GLOBALS['rlCrop'] -> loadImage($original_file);
						}
						else
						{
							$GLOBALS['rlCrop'] -> loadImage($photo_file);
						}
						$GLOBALS['rlCrop'] -> cropBySize($GLOBALS['config']['pg_upload_thumbnail_width'], $GLOBALS['config']['pg_upload_thumbnail_height'], ccCENTER);
						$GLOBALS['rlCrop'] -> saveImage($new_thumb_file, $GLOBALS['config']['img_quality']);
						$GLOBALS['rlCrop'] -> flushImages();
						$GLOBALS['rlResize'] -> resize( $new_thumb_file, $new_thumb_file, 'C', array($GLOBALS['config']['pg_upload_thumbnail_width'], $GLOBALS['config']['pg_upload_thumbnail_height']), null, true );
					
						if($key == 0)
						{
							$stat_field = 'watermarkLarge_'. $code['Code'];
							$GLOBALS['config']['watermark_image_url'] = RL_FILES . 'watermark' . RL_DS . $status_info[$stat_field];
							
							$photo_name = explode('.',$photo['Photo']);
							$old_photo = RL_FILES . str_replace('/', RL_DS,$photo_name[0]) .'_' . $listing_info['Sub_status'] . '_' . $code['Code'] . '.' . $photo_name[1];						
							if( file_exists($old_photo) )
							{
								unlink($old_photo);
							}
							$new_photo = $photo_name[0] .'_' . $status . '_' . $code['Code'] . '.' . $photo_name[1];
							$new_photo_file = RL_FILES . $new_photo;
							chmod( $new_photo_file, 0777 );
							if($photo['Original'])
							{
								$GLOBALS['rlCrop'] -> loadImage($original_file);
							}
							else
							{
								$GLOBALS['rlCrop'] -> loadImage($photo_file);
							}
							$GLOBALS['rlCrop'] -> cropBySize($GLOBALS['config']['pg_upload_large_width'], $GLOBALS['config']['pg_upload_large_height'], ccCENTER);
							$GLOBALS['rlCrop'] -> saveImage($new_photo_file, $GLOBALS['config']['img_quality']);
							$GLOBALS['rlCrop'] -> flushImages();
							$GLOBALS['rlResize'] -> resize( $new_photo_file, $new_photo_file, 'C', array($GLOBALS['config']['pg_upload_large_width'], $GLOBALS['config']['pg_upload_large_height']), true, true );
							
						}
					}
				}
				
				$main_name = explode('.',$main['Main_photo']);
				$main_ext = $main_name[1];
				$main_pname = $main_name[0];
				$main = $main_pname .'_' . $status . '_' . RL_LANG_CODE . '.' . $main_ext;
				
				if(!$admin)
				{
					if ( $tpl_settings['type'] == 'responsive_42' ){
						$photo_img = RL_URL_HOME.'files/'.$main;
						$_response -> script( "$('#listing_".$listing_id."').find('a:eq(0) div>img').css({'background-image': 'url(".$photo_img.")'})");
					}
					else {
						$img = '<img src="'.RL_URL_HOME.'files/'.$main.'"/>';
						$_response -> script( "$('#listing_".$listing_id."').find('tr:eq(0) td.photo div a:eq(0)').html('".$img."');" );
					}
				}
			}
			if( $listing_info['Sub_status'] == 'invisible' )
			{
				//increase category count
				$GLOBALS['rlCategories'] -> listingsIncrease( $listing_info['Category_ID'] );
			}
		}
		else
		{
			if( $status == 'invisible' )
			{
				//decrease category count
				$GLOBALS['rlCategories'] -> listingsDecrease( $listing_info['Category_ID'] );
			}
			elseif( $status == 'visible' && $listing_info['Sub_status'] == 'invisible')
			{
				//increase category count
				$GLOBALS['rlCategories'] -> listingsIncrease( $listing_info['Category_ID'] );
			}
			$sql ="UPDATE `".RL_DBPREFIX."listings` SET `Sold_date` = '0000-00-00 00:00:00',`Sub_status` = '{$status}' WHERE `ID` =".$listing_id;
			$this->query($sql);
			
			if( $listing_info['Sub_status'] != 'visible' && $listing_info['Sub_status'] != 'invisible' )
			{
				$sql = "SELECT SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `T6`.`Thumbnail` ORDER BY `T6`.`Type` DESC, `T6`.`ID` ASC), ',', 1) AS `Main_photo` FROM `".RL_DBPREFIX."listings` AS `T1` ";
				$sql .="LEFT JOIN `".RL_DBPREFIX."listing_photos` AS `T6` ON `T1`.`ID` = `T6`.`Listing_ID` ";		
				$sql .="WHERE `T1`.`ID` =".$listing_id." LIMIT 1";
			
				$main = $this->getRow($sql);
				if($main['Main_photo'])
				{
					$sql ="SELECT `Thumbnail`, `Photo` FROM `".RL_DBPREFIX."listing_photos` WHERE `Listing_ID` =".$listing_id." ORDER BY `Position" ;
					
					$photos = $this->getAll($sql);
					
					foreach($photos as $key => $photo)
					{
						foreach($allLangs as $kLang => $code)
						{
							$thum_name = explode('.',$photo['Thumbnail']);
							$thum_ext = $thum_name[1];
							$thum_pname = $thum_name[0];
							$old_thumb = RL_FILES . str_replace('/', RL_DS, $thum_pname) .'_' . $listing_info['Sub_status'] . '_' . $code['Code'] . '.' . $thum_ext;
							
							if( file_exists($old_thumb) )
							{
								unlink($old_thumb);
							}
							
							if($key == 0)
							{
								$thum_name = explode('.',$photo['Photo']);
								$thum_ext = $thum_name[1];
								$thum_pname = $thum_name[0];
								$old_photo = RL_FILES . str_replace('/', RL_DS, $thum_pname) .'_' . $listing_info['Sub_status'] . '_' . $code['Code'] . '.' . $thum_ext;
								if( file_exists($old_photo) )
								{
									unlink($old_photo);
								}
							}
							
						}
					}
					if(!$admin)
					{
						if ( $tpl_settings['type'] == 'responsive_42' ){
							$photo_img = RL_URL_HOME.'files/'.$main['Main_photo'];
							$_response -> script( "$('#listing_".$listing_id."').find('a:eq(0) div>img').css({'background-image': 'url(".$photo_img.")'})");
						}
						else {
							$img = '<img src="'.RL_URL_HOME.'files/'. $main['Main_photo'].'"/>';
							$_response -> script( "$('#listing_".$listing_id."').find('tr:eq(0) td.photo div a:eq(0)').html('".$img."');" );
						}
					}
				}
			}
		}

		$mess = $GLOBALS['lang']['ls_notice_'.$status];
		$_response -> script("printMessage('notice', '".$mess."');");

		return $_response;
	}

	/**
	* delete
	*
	* @package xAjax
	*
	* @param int $id -  id
	*
	**/
	function ajaxDeleteStatusBlock( $id = false )
	{
		global $_response;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$_response -> redirect( RL_URL_HOME . ADMIN . '/index.php?action=session_expired' );
			return $_response;
		}

		$id = (int)$id;
		if ( !$id )
		{
			return $_response;
		}
		$key = $this -> getOne('Key', "`ID` = '{$id}'", 'listing_status');
		
		// delete 
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "listing_status` WHERE `ID` = '{$id}' LIMIT 1");
		
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "blocks` WHERE `Key` = 'lb_{$key}' LIMIT 1");

		$this -> query("DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'lsl_{$key}' || `Key` = 'blocks+name+lb_{$key}'");		
		
		$GLOBALS['rlActions'] -> enumRemove( 'listings', 'Sub_status', $key );
		
			$_response -> script("
				ListingStatus.reload();
				printMessage('notice', '{$GLOBALS['lang']['block_deleted']}')
			");
		
		return $_response;
	}
	
	
	/**
	*
	* get status
	*
	**/
	function getStatus()
	{
		global $sql, $config, $lang;
		
		$status[] = Array('Key'=>'visible', 'Type'=>'all', 'name'=> $lang['ls_visible']);
		$status[] = Array('Key'=>'invisible','Type'=>'all', 'name'=> $lang['ls_invisible']);
		$data = $this -> fetch( array('Key','Type'), array( 'Status' => 'active' ), null, null, 'listing_status' );
		
		foreach( $data as $key => $val)
		{
			$status[] = Array('Key'=>$data[$key]['Key'],'Type'=> explode(',',$data[$key]['Type']), 'name'=> $lang['lsl_'.$data[$key]['Key']]);
		}
		return $status;
	}

	/**
	* getGlobalRecentlySoldListings
	*/
	function getGlobalRecentlySoldListings($listings_type, $days, $limit, $s_status, $order)
	{
		global $sql, $config;

		if ( version_compare($config['rl_version'], '4.0.1', '<') )
		{
			$sql = "SELECT DISTINCT {hook} SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `T6`.`Thumbnail` ORDER BY `T6`.`Type` DESC, `T6`.`ID` ASC), ',', 1) AS `Main_photo`, ";
		}
		else
		{
			$sql = "SELECT DISTINCT {hook}  ";
		}
		$sql .= "`T1`.*, `T1`.`Shows`, `T2`.`Image`, `T2`.`Image_unlim`, `T3`.`Path` AS `Path`, `T3`.`Key` AS `Key`, `T3`.`Type` AS `Listing_type`, ";
		$sql .= $config['grid_photos_count'] ? "COUNT(`T6`.`Thumbnail`) AS `Photos_count`, " : "";
		
		$GLOBALS['rlHook'] -> load('listingsModifyField');
		
		$sql .= "IF(UNIX_TIMESTAMP(DATE_ADD(`T1`.`Featured_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0, '1', '0') `Featured` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_status` AS `T4` ON `T1`.`Sub_status` = `T4`.`Key` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_photos` AS `T6` ON `T1`.`ID` = `T6`.`Listing_ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";

		$GLOBALS['rlHook'] -> load('listingsModifyJoin');
		
		
		$sql .= "WHERE if( `T4`.`Delete` != 'simple' , UNIX_TIMESTAMP(DATE_ADD(`T1`.`Sold_date`, INTERVAL {$days} DAY)) > UNIX_TIMESTAMP(NOW()), 1) AND ";
		
		$sql .= "(`T3`.`Type` = '{$listings_type}' OR FIND_IN_SET( `T3`.`Type` , '{$listings_type}') > 0 ) AND ";
		
		$sql .= "`T1`.`Status` = 'active' AND `T3`.`Status` = 'active' AND `T7`.`Status` = 'active' AND ";
		$sql .= "`T1`.`Sub_status` = '{$s_status}' ";
		
		$sql .= "GROUP BY `ID` ";
		
		switch ($order){
			case 'latest':
				$sql .= "ORDER BY `T1`.`Sold_date` DESC ";
				break;
			case 'random':
				$sql .= "ORDER BY RAND() ";
				break;
			default:
				$sql .= "ORDER BY `ID` DESC ";
				break;
		}
		
		$sql .= "LIMIT {$limit} ";
		/* replace hook */
		$sql = str_replace('{hook}', $hook, $sql);
		
		$listings = $this -> getAll( $sql );
		$listings = $this -> rlLang -> replaceLangKeys( $listings, 'categories', 'name' );
		
		if ( empty($listings) )
		{
			return false;
		}

		if ( !$config['cache'] )
		{
			$fields = $GLOBALS['rlListings'] -> getFormFields( $listings[0]['Category_ID'], 'featured_form', $listings[0]['Listing_type'] );
		}
		
		foreach ( $listings as $key => $value )
		{
			/* populate fields */
			if ( $config['cache'] )
			{
				$fields = $GLOBALS['rlListings'] -> getFormFields( $value['Category_ID'], 'featured_form', $value['Listing_type'] );
			}
			
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

	/**
	* get status listings 
	*
	* @param string $listings_type - listing types
	* @param int $days - listing will be shown
	* @param int $limit -  limit
	* @param int $order -  order type
	*
	**/
	function getRecentlySoldListings( $listings_type, $days, $limit, $s_status, $order )
	{
		global $rlSmarty;

		$lbgKey = md5($listings_type . $days . $limit . $s_status . $order);
		return $this -> lbGlobalBoxes[$lbgKey];
	}
	
	/**
	* delete listing photos
	* array info = listing info
	**/
	function deleteListingPhotos( $info = false )
	{
		global $rlDb;
		
		$allLangs = $GLOBALS['languages'];
		$sub_status = $this -> getOne('Sub_status', "`ID` = '{$info['ID']}'", 'listings');
		if( $sub_status != 'visible' && $sub_status != 'invisible' )
		{
			$data_photos = $this -> fetch( array('Thumbnail'), array( 'Listing_ID' => $info['ID'] ), null, null, 'listing_photos');
			foreach ($data_photos as $key => $val )
			{
				$thum = explode('.', str_replace('/', RL_DS, $val['Thumbnail']));
				foreach ($allLangs as $lkey => $lval )
				{
					$thum_label = RL_FILES . $thum[0] . '_' . $sub_status . '_' . $lval['Code'] . '.' . $thum[1];
					unlink( $thum_label );
				}
			}
		}
	}
	
	
	/**
	* delete listing with lable
	*
	**/	
	function deleteAllListingPhotosWithLable( )
	{
		$data = $this -> getAll("SELECT `Key` FROM `".RL_DBPREFIX."listing_status`  ");
		foreach($data as $key => $val)
		{
			$this -> foundPhotoLable( RL_FILES ,  $val['Key']);
		}
	}
	
	/**
	* delete All Listing Photos With lable
	*
	* @param  $dir - folder
	* @param  $label - label
	*
	**/	
	function foundPhotoLable( $dir = RL_ROOT, $label = false )
	{
		
		$files = $this -> scanDir($dir, false, true);
		
		if ( $files )
		{
			foreach ($files as $file)
			{
				if ( $file['type'] == 'dir' )
				{
					$this -> foundPhotoLable( $dir . $file['name']. RL_DS, $label);
				}
				elseif ( $file['type'] == 'file' )
				{	
					$test =  strpos($file['name'], $label);
					
					if ( preg_match( '/_'.$label.'_/',$file['name'] ) )
					{
						$file_path = rtrim($dir) . $file['name'];						
						unlink( $file_path );
					}
				}
			}
		}
	}
	/**
	* replace photos
	*
	* @param  $photos - listing photos
	* @param  $listing_data - listing info
	*
	**/	
	function replacePhotos( $photos, $listing_info )
	{
		foreach ($photos as $key => $photo)
		{
			if($key == 0)
			{
				$photos[$key]['Photo'] = preg_replace('/(\\.[^\\.]+)$/', '_'.$listing_info['Sub_status'].'_'.RL_LANG_CODE.'$1', $photo['Photo']);
			}
			$photos[$key]['Thumbnail'] = preg_replace('/(\\.[^\\.]+)$/', '_'.$listing_info['Sub_status'].'_'.RL_LANG_CODE.'$1', $photo['Thumbnail']);
		}		
		return $photos;
	}
	
	//upadte status
	function updateStatus( )
	{
		global $rlDb;
		if( !$rlDb -> getRow("SHOW FIELDS FROM `".RL_DBPREFIX."listing_status` WHERE `Field` LIKE 'watermarkLarge_%'"))
		{
			$data = $this -> fetch( '*', array( 'Status' => 'active' ), null, null, 'listing_status' );		
			foreach($GLOBALS['languages'] as $key => $val)
			{
				$lable = "watermark_" .$val['Code'];
				$large_lable = "watermarkLarge_" .$val['Code'];
				$rlDb -> query( "ALTER TABLE `".RL_DBPREFIX."listing_status` ADD `".$large_lable."` VARCHAR( 50 ) NOT NULL AFTER `".$lable."`" );
				
				foreach($data as $sKey => $sVal)
				{
					$file_ext = array_reverse( explode( '.', $sVal[$lable] ) );
					$photo_name = $sVal['Key'] . '_large_' . $val['Code']  . '.' . $file_ext[0];
					
					
					if( copy ( RL_FILES . 'watermark' . RL_DS . $sVal[$lable] , RL_FILES . 'watermark' . RL_DS . $photo_name ))
					{
						$rlDb -> query("UPDATE `".RL_DBPREFIX."listing_status` SET `{$large_lable}` = '{$photo_name}' WHERE `Key` = '{$sVal['Key']}'" );				
					}
				}
			}
		}
		return true;
	}
	
	/**
	*
	* array data = insert photo info
	*
	**/
	
	function ajaxChangeStatusNewPhotos( $data, $status )
	{
		global $rlDb, $reefless, $rlLang;
		
		$reefless -> loadClass('Resize');
		$reefless -> loadClass('Crop');
		$languages = $rlLang -> getLanguagesList();
		
		$GLOBALS['config']['watermark_using'] = 1;
		$GLOBALS['config']['watermark_type'] = 'image';
		
		$status_info = $rlDb -> fetch( '*', array( 'Status' => 'active', 'Key'=>$status ), null, null, 'listing_status','row' );
		
		foreach($languages as $kLang => $code)
		{
			$stat_field = 'watermark_'. $code['Code'];
			$GLOBALS['config']['watermark_image_url'] = RL_FILES . 'watermark' . RL_DS . $status_info[$stat_field];
			
			$new_thumb = preg_replace('/(\\.[^\\.]+)$/', '_'.$status.'_'.$code['Code'].'$1', $data['Thumbnail']);
			$new_thumb_file = RL_FILES . $new_thumb;
			
			chmod( $new_thumb_file, 0777 );
			
			$GLOBALS['rlCrop'] -> loadImage( RL_FILES . $data['Photo'] );
			$GLOBALS['rlCrop'] -> cropBySize($GLOBALS['config']['pg_upload_thumbnail_width'], $GLOBALS['config']['pg_upload_thumbnail_height'], ccCENTER);
			$GLOBALS['rlCrop'] -> saveImage($new_thumb_file, $GLOBALS['config']['img_quality']);
			$GLOBALS['rlCrop'] -> flushImages();
			$GLOBALS['rlResize'] -> resize( $new_thumb_file, $new_thumb_file, 'C', array($GLOBALS['config']['pg_upload_thumbnail_width'], $GLOBALS['config']['pg_upload_thumbnail_height']), null, true );
		}
		
		return true;
	}
	
	/**
	* rebuild labels
	*
	* @package xAjax
	*
	**/
	function ajaxRebuildLabels($self)
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false && !$direct )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$this -> rebuild();

		$_response -> script( "printMessage('notice', '{$lang['ls_rebuild_label']}')" );
		$_response -> script( "$('{$self}').val('{$lang['recount']}');" );

		return $_response;
	}
	
	
	function rebuild()
	{
		global $rlLang, $rlDb;
		
		$languages = $rlLang -> getLanguagesList();
		$data = $this -> fetch( '*', array( 'Status' => 'active' ), null, 1, 'listing_status', 'row' );
		
		$fields = '';
		foreach($languages as $key => $val)
		{
			$watermark =  'watermark_'. $val['Code'];
			$large =  'watermarkLarge_'. $val['Code'];
			if(!isset($data[$watermark]))
			{
				$fields .= ' ADD `' . $watermark . '` VARCHAR( 50 ) NOT NULL AFTER `Key`,';
			}
			if(!isset($data[$large]))
			{
				$fields .= ' ADD `' . $large . '` VARCHAR( 50 ) NOT NULL AFTER `Key`,';
			}
		}
		if($fields)
		{
			$fields = substr($fields, 0, -1);
			$rlDb -> query( "ALTER TABLE `".RL_DBPREFIX."listing_status` " . $fields );
		}
	}
	
	/*checked is image or not*/
	
	function isImage( $image = false )
	{
		if ( !$image )
		{
			return false;
		}
		
		$allowed_types = array('image/gif', 'image/jpeg', 'image/jpg', 'image/png' );
		$img_details = getimagesize( $image );
		if ( in_array( $img_details['mime'], $allowed_types ) )
		{
			return true;
		}

		return false;
	}
}