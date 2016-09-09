<?php


/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLXMLIMPORT.CLASS.PHP
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

class rlXmlImport extends reefless
{
	var $mapping = array();
	var $mapping_rev = array();
	var $print_progress = false;


	/**
	* getDfMap - get data formats mapping
	*
	* @param array $format_keys - data entries keys to get mapping
	* @param string $type - import or export - to define output
	*
	**/

	function getDfMap( $format_keys = false, $type = 'import' )
	{
		global $rlCache, $config, $rlLang;

		if ( !$format_keys )
			return;

		if( !is_array($format_keys) )
		{
			$format_keys[] = $format_keys;
		}

		$out_df = array();

		foreach( $format_keys as $index => $key )
		{
			/* get data from cache */
/*			if ( $config['cache'] )
			{
				$df = $rlCache -> get('cache_data_formats', $key);

				if ( $df )
				{
					$df = $rlLang -> replaceLangKeys( $df, 'data_formats', array( 'name' ) );
				}
			}else
			{*/
				$format_id = $this -> getOne('ID', "`Key` = '{$key}'", 'data_formats');

//				$df = $this -> fetch( array('ID', 'Parent_ID', 'Key'), array('Status' => 'active', 'Parent_ID' => $format_id), 'ORDER BY `ID`, `Key`', null, 'data_formats' );
//				$df = $rlLang -> replaceLangKeys( $df, 'data_formats', array( 'name' ) );

				if( $format_id )
				{
					$sql ="SELECT `T1`.`ID`, `T1`.`Parent_ID`, `T1`.`Key`, `T2`.`Value` as `name` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
					$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('data_formats+name+', `T1`.`Key`) ";
					$sql .="WHERE `T1`.`Status` = 'active' AND `T1`.`Parent_ID` = ".$format_id." ";
					$sql .="GROUP BY `T1`.`Key` ";
					$sql .="ORDER BY `ID`, `Key` ";


					$df = $this -> getAll($sql);
				}
//			}

			foreach($df as $dk => $dv)
			{
				if( $type == 'export' ) 
				{
					$out_df[$key][ strtolower( $dv['Key'] ) ] = $dv['name'];
				}else
				{
				$out_df[$key][ strtolower($dv['name']) ] = $dv['Key'];
			}
		}
		}

		return $out_df;
	}


	/**
	* getCategoriesMap - get categories mapping
	*
	* @param int $parent_id - parent_id 
	* @param string $type - type
	*
	**/

	function getCategoriesMap( $parent_id = 0, $type )
	{
		global $rlCategories;
	
		$categories = $rlCategories -> getCategories( $parent_id, $type );

		foreach( $categories as $key => $category )
		{
			$out[ strtolower( $category['name'] ) ]['id'] = $category['ID'];

			if( $subs = $this -> getCategoriesMap( $category['ID'], $type ) )
			{
				$out[ strtolower( $category['name'] ) ]['subs'] = $subs;
			}
		}

		if( $parent_id == 0 && $mparent_id = $this -> getOne("ID", "`Data_local` = 'category_0' AND `Format` = '".$GLOBALS['feed']['Format']."'", "xml_mapping"))
		{
			$mp_out = $this -> getCategoriesMapRelations( $mparent_id );

			if( $mp_out )
			{
				$out = $this -> catArrayMergeRecursive($out, $mp_out);//add 2nd to first.
			}
		}

		return $out;
	}


	function catArrayMergeRecursive( $arr1 = array(), $arr2 = array() )
	{
		foreach( $arr1 as $key => $value )
		{
			if( $arr2[$key]['subs'] )
			{
				foreach( $arr2[$key]['subs'] as $k => $v )
				{
					$arr1[$key]['subs'][$k] = $v;
					if( $v['subs'] )
					{
						$arr1[$key]['subs'][$k] = $this -> catArrayMergeRecursive( $arr1[$key]['subs'][$k], $v );
					}
				}
			}
		}

		return $arr1;
	}

	/**
	* getCategoriesMapRelations - get categories mapping relations
	*
	* @param int $parent_id - parent_id 	
	*
	**/

	function getCategoriesMapRelations( $parent_id )
	{
		$sql ="SELECT `T2`.`ID`, `T1`.`Data_remote`, `T1`.`ID` AS `Mapping_ID` FROM `".RL_DBPREFIX."xml_mapping` AS `T1` ";
		$sql .="JOIN `".RL_DBPREFIX."categories` AS `T2` ON `T2`.`Key` = `T1`.`Data_local` ";
		$sql .="WHERE `T1`.`Parent_ID` = ".$parent_id;

		$categories = $this -> getAll( $sql );

		foreach( $categories as $key => $category )
		{
			$out[ strtolower( $category['Data_remote'] ) ]['id'] = $category['ID'];

			if( $subs = $this -> getCategoriesMapRelations( $category['Mapping_ID'] ) )
			{
				$out[ strtolower( $category['Data_remote'] ) ]['subs'] = $subs;
			}
		}

		return $out;
	}


	/**
	* getFieldsMap - get fields mapping
	*
	* @param array $field_keys - field keys to get mapping for
	* @param string $type - import or export
	*
	**/

	function getFieldsMap( $field_keys, $type = 'import'  )
	{
		global $rlDb;

		foreach( $field_keys as $index => $field_key )
		{
			$sql = "SELECT `Key`, `Value` as `name` FROM `".RL_DBPREFIX."lang_keys` ";
			$sql .="WHERE `Key` LIKE 'listing_fields+name+{$field_key}_%'";

			$data = $rlDb -> getAll( $sql );

			foreach($data as $key => $value)
			{
				preg_match('/.*([0-9]+)$/', $value['Key'], $match);
				if( $match[1] )
				{
					if( $type == 'import' )
					{
						$out[$field_key][ strtolower($value['name']) ] = $match[1];
					}else
					{
						$out[$field_key][ $match[1] ] = strtolower($value['name']);
					}
				}
			}
		}

		return $out;
	}


	/**
	* saveStatistics
	*
	* @param array $stats - stats array
	* @param string $feed - feed info array
	*
	**/

	function saveStatistics( $stats, $feed )
	{
		$insert['Account_ID'] = $feed['Account_ID'];
		$insert['Feed'] = $feed['Feed'];
		$insert['Date'] = 'NOW()';
		$insert['Listings_inserted'] = trim($stats['inserted'], ",");
		$insert['Listings_updated'] = trim($stats['updated'], ",");		
		$insert['Listings_deleted'] = $stats['deleted'];

		$GLOBALS['rlActions'] -> insertOne($insert, 'xml_statistics');
	}

	/**
	* function copy pictures 
	*
	* @param pictures - string which contains pictures through comma 
	* @param - listing id
	* @param - mode
	*
	*/

	function copyPictures( $pictures, $listing_id, $mode = 'insert' )
	{
		global $rlDb, $reefless, $rlResize, $rlCrop, $config, $rlActions;

		set_time_limit(0);

        $main = true;
		if( !$listing_id || !$pictures)
			return false;

		if( is_array($pictures) )
		{
		}else
		{
			$pictures = explode( ',', $pictures );
		}

		if( $mode == 'update' )
		{
			$sql = "SELECT * FROM `".RL_DBPREFIX."listing_photos` WHERE `Listing_ID` = ".$listing_id;
			$listing_photos = $rlDb -> getRow( $sql );

			if( $listing_photos && count($listing_photos) != count($pictures) )//if count the same we may suppose that pictures were not changed
			{
				foreach( $listing_photos as $lk => $lphoto )
				{
					unlink( RL_FILES.$lphoto['Original'] );
					unlink( RL_FILES.$lphoto['Photo'] );
					unlink( RL_FILES.$lphoto['Thumbnail'] );
				}
				$sql = "DELETE FROM `".RL_DBPREFIX."listing_photos` WHERE `Listing_ID` = ".$listing_id;
				$rlDb -> query( $sql );
			}elseif( $listing_photos )
			{
				return false; //puctures the same 
			}
		}

		$image_versions['large']['max_width'] = $config['pg_upload_large_width'];
		$image_versions['large']['max_height'] = $config['pg_upload_large_height'];
		$image_versions['large']['watermark'] = true;

		$image_versions['thumb']['max_width'] = $config['pg_upload_thumbnail_width'];
		$image_versions['thumb']['max_height'] = $config['pg_upload_thumbnail_height'];
		$image_versions['thumb']['watermark'] = false;

		$max_position = $rlDb -> getOne("Position", "`Listing_ID` = ".$listing_id." ORDER BY `Position` DESC", "listing_photos" );

		$cur_photo = $rlDb -> getOne('Photo', "`Listing_id` = '{$listing_id}'", 'listing_photos');

		if ( $cur_photo )
		{
			$exp_dir = explode('/', $cur_photo);
			if ( count($exp_dir) > 1 )
			{
				array_pop($exp_dir);
				$dir = RL_FILES . implode(RL_DS, $exp_dir) . RL_DS;
				$dir_name = implode('/', $exp_dir) .'/';
			}
		}

		if ( !$dir )
		{
			$dir = RL_FILES . date('m-Y') . RL_DS .'ad'. $listing_id . RL_DS;
			$dir_name = date('m-Y') .'/ad'. $listing_id .'/';
		}

		$reefless -> deleteDirectory( $dir );
		$url = RL_FILES_URL . $dir_name;
		$reefless -> rlMkdir( $dir );

		foreach ( $pictures as $key => $image )
		{
			if( $image )
			{
				preg_match('/.*\.(jpeg|tif|tiff|jpg|gif|png)(.*)?$/i', $image, $match);
				$ext = $match[1] ? $match[1] : 'jpg';
				$ext = strtolower($ext);

				$original_name = 'orig_'. time() . mt_rand(). '.' .$ext;

				copy($image, $dir.$original_name);


				foreach( $image_versions as $prefix => $options )
				{
					$new_file_name = $prefix ."_". time() . mt_rand(). '.' .$ext;

					$file_path = $dir . $original_name;
					$new_file_path = $dir . $new_file_name;

	/*				$rlCrop -> loadImage($file_path);
					$rlCrop -> cropBySize($options['max_width'], $options['max_height'], ccCENTER);
					$rlCrop -> saveImage($new_file_path, $config['img_quality']);
					$rlCrop -> flushImages();*/

	//				$rlResize -> resize( $new_file_path, $new_file_path, 'C', array($options['max_width'], $options['max_height']), null, $options['watermark'] );

	//				if( $prefix == 'thumb' )
	//				{
						$rlResize -> resize( $file_path, $new_file_path, 'C', array($options['max_width'], $options['max_height']), null, $options['watermark'] );
	/*				}else
					{
						copy( $file_path, $new_file_path );
					}*/

					$filenames[$prefix] = $dir_name . $new_file_name;
				}

				if ( is_readable( RL_FILES . $filenames['large'] ) && is_readable( RL_FILES . $filenames['thumb'] ) )
				{
					chmod( $photo_file, 0644 );
					chmod( $thumbnail_file, 0644 );

					$insert_info = array(
							'Listing_ID' => $listing_id,
							'Position' => $max_position,
							'Photo' => $filenames['large'],
							'Thumbnail' => $filenames['thumb'],
							'Original' => $dir_name . $original_name,
							'Description' => '',
							'Type' => $main ? 'main' : 'photo',
							'Status' => 'active'
					);
					$main = false;
					$max_position++;					
					$rlActions -> insertOne($insert_info, 'listing_photos');
				}
			}
		}
		$this -> updatePhotoData( $listing_id );

		return true;
	}


	/**
	* function adaptFeatures
	*	
	* @param field_key - field key
	* @param features - features string 
	* @param delimiter - delimiter sign
	* @param type - import or export
	* @param mapping - mapping
	*
	*/

	function adaptFeatures( $field_key, $features, $delimiter, $type = 'import', $mapping )
	{
		global $listing_fields_map;

		$data = explode( $delimiter, $features );

		if( $type == 'import' )
		{
		$out = '';
		foreach( $data as $key => $item )
		{
			if( $listing_fields_map[$field_key][$item] )
			{
				$out .= $listing_fields_map[$field_key][$item].",";
			}
		}
		}else
		{
			$out = '';
			foreach( $data as $key => $item )
			{
				$out .= $mapping[ $item ].$delimiter;
			}
		}

		return trim($out, $delimiter);
	}


	/**
	* function updatePhotoData
	*	
	* @param id - listing id
	*
	*/

	function updatePhotoData( $id = false )
	{
		global $rlDb;

		if ( !$id )
			return false;

		$sql = "SELECT DISTINCT SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `T2`.`Thumbnail` ORDER BY `T2`.`Type` DESC, `T2`.`Position` ASC), ',', 1) AS `Main_photo`, ";
		$sql .= "COUNT(`T2`.`Thumbnail`) AS `Photos_count` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_photos` AS `T2` ON `T1`.`ID` = `T2`.`Listing_ID` ";
		$sql .= "WHERE `T1`.`ID` = {$id} LIMIT 1";

		if ( $listing = $rlDb -> getRow($sql) )
		{
			$update_sql = "UPDATE `". RL_DBPREFIX ."listings` SET `Main_photo` = '{$listing['Main_photo']}', `Photos_count` = '{$listing['Photos_count']}' ";
			$update_sql .= "WHERE `ID` = {$id} LIMIT 1";
			$rlDb -> query($update_sql);
		}
	}


	/**
	* requires php class file, creates global class object
	*
	* @param string $formatName  - loaded class name
	**/

	function loadFormat( $formatName, $fileName )
	{
		$fileName = $fileName ? $fileName : $formatName;
		 
		if ( !is_object( $formatName ) )
		{
			$fileSource = RL_PLUGINS . "xmlFeeds" . RL_DS . "modules" . RL_DS . $fileName . ".format.php";

			global $$formatName;

			if ( !is_object( $$formatName ) )
			{
				if ( file_exists( $fileSource ) )
				{
					require_once( $fileSource );
				}
				else 
				{
					die( "The '{$formatName}' class not found" );
				}

				$$formatName = &new $fileName;
				$GLOBALS[$formatName] = $$formatName;
			}
		}
	}


	/**
	* function getMapping
	*
	* @param string $format - format
	* @param type - import or export
	**/

	function getMapping( $format, $type = 'import')
	{
		global $rlDb;

		if( $this -> mapping[$format] )
		{
			return $this -> mapping[$format];
		}
		elseif( defined('AJAX_MODE') && $_SESSION['xmlFeedsImport']['mapping'][$format] )
		{
			$this -> mapping_rev = $_SESSION['xmlFeedsImport']['mapping_rev'];
			return $_SESSION['xmlFeedsImport']['mapping'][$format];
		}

		$data = $rlDb -> fetch( "*", array('Format' => $format), null, null, 'xml_mapping');

		foreach( $data as $key => $row )
		{
			if( $row['Data_local'] )
			{
				$fields[] = $row['Data_local'];
			}

			if( $row['Data_local'] == 'feed_user_id' )
			{
				$feed_user_id = strtoupper($row['Data_remote']);
				continue;
			}

			if( $type == 'export' )
			{
				$mapping[ $row['Data_local'] ] = $row['Data_remote'];
				$mapping_rev[$format][ $row['Data_remote'] ] = $row['Data_local'];
			}else
			{				
				$mapping[ $row['Data_remote'] ] = $row['Data_local'];
				$mapping_rev[$format][ $row['Data_local'] ] = $row['Data_remote'];
			}

			if( $row['Cdata'] )
			{
				$cdata_fields[] = $row['Data_remote'];
			}
		}
		$cdata_fields[] = 'picture_url';
		$this -> cdata_fields = $cdata_fields;

		$sql ="SELECT * FROM `".RL_DBPREFIX."listing_fields` WHERE ";

		foreach( $fields as $fk => $field )
		{
			$sql .="`Key` = '".$field."' OR ";
		}
		$sql = substr($sql, 0, -3);
		$fields = $rlDb -> getAll( $sql );

		foreach( $fields as $fk => $field )
		{
			$info[ $field['Key'] ] = $field;
		}

		$out['mapping'] = $mapping;
		$out['fields_info'] = $info;		
		$out['feed_user_id'] = $feed_user_id;
		
		$this -> mapping[$format] = $out;
		$this -> mapping_rev = $mapping_rev;

		if( defined('AJAX_MODE') )
		{
			$_SESSION['xmlFeedsImport']['mapping'][$format] = $out;
			$_SESSION['xmlFeedsImport']['mapping_rev'] = $mapping_rev;
		}

		return $out;
	}


	/**
	* function getListings
	*
	* @param array $where - conditions array
	* @param string $order - order
	* @param int $start - start
	* @param int $limit - limit
	* @param type $type - listing type 
	*
	**/

	function getListings( $where = false, $order= false, $start =0, $limit = 100, $type = false )
	{
		$start = $start ? $start : 0;
		$limit = $limit ? $limit : 10;

		$sql = "SELECT `T1`.*, `T3`.`Path` AS `Path`, `T3`.`Type` AS `Listing_type`, `T3`.`Path` as `Category_path`, ";

		$sql .= "IF(TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T4`.`Listing_period` * 24 OR `T4`.`Listing_period` = 0, '1', '0') `Featured` ";

		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T4` ON `T1`.`Featured_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "account_types` AS `T8` ON `T7`.`Type` = `T8`.`Key` ";

		$sql .= "WHERE (";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T2`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T2`.`Listing_period` = 0 ";
		$sql .= ") ";

		foreach( $where as $key => $value )
		{
			if( $key == 'category' )
			{
				$sql .= "AND (`T1`.`Category_ID` = '{$value}' OR (FIND_IN_SET('{$value}', `T1`.`Crossed`) > 0 AND `T2`.`Cross` > 0 ) ";
				$sql .= "OR FIND_IN_SET('{$value}', `T3`.`Parent_IDs`) > 0 ) ";
			}
			else
			{
				$sql .="AND `T1`.`{$key}` = '{$GLOBALS['rlValid'] -> xSql($value)}' ";
			}
		}

		$sql .= "AND `T1`.`Status` = 'active' AND `T3`.`Status` = 'active' ";

		$GLOBALS['rlHook'] -> load('listingsModifyWhere');

		if( $type )
		{
			$sql .= "AND `T3`.`Type` = '{$type}' ";
		}

		if( $featured )
		{
			$sql .="AND UNIX_TIMESTAMP(DATE_ADD(`T1`.`Featured_date`, INTERVAL `T2`.`Days` DAY)) > UNIX_TIMESTAMP(NOW()) ";
		}

		$sql .= "GROUP BY `ID` ";
		
		if( $order )
		{
				$sql .= "ORDER BY `T1`.`{$order['field']}` {$order['type']}, ";
		}
		$sql .= "`ID` DESC ";

		$sql .= "LIMIT {$start}, {$limit} ";

		$listings = $this -> getAll( $sql );
		$listings = $GLOBALS['rlLang'] -> replaceLangKeys( $listings, 'categories', 'name' );

		foreach ( $listings as $key => $value )
		{
			$listings[$key]['listing_title'] = $GLOBALS['rlListings'] -> getListingTitle( $value['Category_ID'], $value, $value['Listing_type'] );
			$listings[$key]['Page_path'] = $lt_pages[ $value['Listing_type'] ];
		}

		return $listings;
	}


	/**
	* function getAccounts
	*
	* @param string $type - account type
	* @param int $account_id - false	
	* @param int $limit - limit
	*
	**/

	function getAccounts( $type = false, $account_id = false, $limit )
	{
		global $rlDb, $reefless;

		$start = 0;
		$limit = 10;

		$sql = "SELECT `T1`.*, `T2`.`ID` AS `Type_ID` FROM `". RL_DBPREFIX ."accounts` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."account_types` AS `T2` ON `T1`.`Type` = `T2`.`Key` ";
		$sql .= "WHERE `T1`.`Status` = 'active' AND `T2`.`Status` = 'active' ";
/*		$sql .= "AND (";
			$sql .= "SELECT COUNT(`T3`.`ID`) ";
			$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T3` ";
			$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T4` ON `T3`.`Plan_ID` = `T4`.`ID` ";
			$sql .= "WHERE `T3`.`Account_ID` = `T1`.`ID` AND (UNIX_TIMESTAMP(DATE_ADD(`T3`.`Pay_date`, INTERVAL `T4`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T4`.`Listing_period` = 0) AND `T3`.`Status` = 'active'";
		$sql .= " ) > 0 ";

		if( $type )
		{
			$sql .= "AND `T2`.`Key` = '{$type}' ";
		}
*/
		$sql .= "LIMIT {$start}, {$limit}";

		$accounts = $rlDb -> getAll( $sql );
		$reefless -> loadClass('Common');

		if( $type )
		{
			$fields = $GLOBALS['rlListings'] -> getFormFields( $accounts[0]['Type_ID'] );
		}

		foreach ( $accounts as $key => $account )
		{
			if( !$type )
			{
				$fields = $GLOBALS['rlListings'] -> getFormFields( $account['Type_ID'] );
			}

			foreach ( $fields as $fKey => $fValue )
			{
				if ( $field['Condition'] == 'isUrl' || $field['Condition'] == 'isEmail' )
				{
					$fields[$fKey]['value'] = $accounts[$key][$item];
				}
				else
				{
					$fields[$fKey]['value'] = $GLOBALS['rlCommon'] -> adaptValue( $fValue, $account[$fKey], 'account', $account['ID'] );
				}
			}

			$accounts[$key]['Full_name'] = $account['First_name'] || $account['Last_name'] ? trim($account['First_name'] .' '. $account['Last_name']) : $account['Username'];
			$accounts[$key]['fields'] = $fields;
		}

		return $this -> sanitize( $accounts );
	}


	/**
	* function toArray
	*
	* @param object $xmlObject - xml data
	* @param bool $skip_attributes - skip attributes 
	* @param bool $json_method - enables json method
	*
	**/

	function toArray( $xmlObject, $skip_attributes = false, $json_method = false)
	{
		if( $json_method )
			return json_decode(json_encode((array) $xmlObject), 1);

	    foreach ( (array) $xmlObject as $index => $node )
	    {
	    	if( $index == '@attributes' && $skip_attributes )
			{
			}else
			{
				if( is_object ( $node ) && !(string) $node || is_array($node) )
				//if( is_object ( $node ) )
				{
					$out[$index] =  $this -> toArray ( $node, $skip_attributes );
				}
				else
				{
					$out[$index] = (string) $node;
				}
			}
    	}

		return $out;
	}


	/**
	* function toXml
	*
	* @param array $array - data array	
	*
	**/

	function toXML( $array )
	{
		foreach( $array as $key => $value )
		{
			if( is_int($key) && is_array($value) )
			{
			}else
			{
				$out .='<'.$key;
				if( is_array($value['@attributes']) )
				{
					foreach( $value['@attributes'] as $ak => $av ) 
					{
						$out .=' '.$ak.'="'.$av.'"';
					}
					unset($value['@attributes']);
				}

				$out .='>';
			}

			if( is_array($value) )
			{
				$out .= $this -> toXml($value);
			}
			else
			{
				if( in_array($key, $this -> cdata_fields ) )
				{
					$out .= '<![CDATA['.$value.']]>';
				}
				else
				{
					$out .= $value;
				}
			}

			if( is_int($key) && is_array($value) )
			{
			}
			else
			{
				$out .='</'.$key.'>';
			}
		}

		return $out;
	}


	/**
	* function sanitize
	*
	* @param array $array - data array	
	*
	**/

	function sanitize( $data )
	{
		if( is_array($data) )
		{
			foreach( $data as $k => $v)
			{
				$data[$k] = $this -> sanitize( $v );
			}
		}else
		{
			if( !is_numeric($data) ) 
			{
				$data = str_replace('&', '&amp;', $data );
			}
		}

		return $data;
	}


	/**
	* function xmlLogger
	*
	* @param string $message - message string
	* @param string $type - type of message
	*
	**/

	function xmlLogger( $message, $type )
	{
		if( $type )	{
			$out .='<div class="progress_'.$type.'">';
		}

		$out .=$message;

		if( $type )	{
			$out .='</div>';
		}
		

		if( defined('AJAX_MODE') )
		{			
			$GLOBALS['_response'] -> script("$('#manual_import_dom').append('{$out}')");
			return;
		}

		if( $this -> print_progress )
		{		
			echo $out;
		}
		elseif( $type == 'error')
		{
			$GLOBALS['rlDebug'] -> logger($message);
		}
	}


	/**
	* function checkFeed
	*
	* @param string $feed_url - url of the feed to check
	* @param string $format - format
	*
	**/

	function checkFeed( $feed_url = false, $format = false )
	{
		$format_info = $this -> fetch("*", array("Key" => $format), null, null, "xml_formats", "row");

		$reader = new XMLReader();
		$reader -> open( $feed_url );

		$xpath = explode( "/", strtolower($format_info['Xpath']) );

		while( $reader -> read() )
		{
			foreach( $xpath as $pkey => $path )
			{					
				if( $pkey == count($xpath) - 1 )//last node
				{
					$last_paths = explode(",", $path);

					if( $reader -> nodeType == XMLReader::ELEMENT && in_array(strtolower($reader->localName), $last_paths[0]) )
					{
						return true;
					}
				}
			}
		}
		
		return false;
	}


	/**
	* function createCategory
	*
	* @param string $category_name - category name
	* @param string $parent_id - parent_id
	*
	**/

	function createCategory( $category_name, $parent_id )
	{
		global $rlValid, $rlActions, $languages;

		if( $parent_id )
		{
			$parent_info = $this -> fetch("*", array("ID" => $parent_id), null, null, "categories", "row" );
		}
		else
		{
			$parent_id = 0;
		}

		$cat_insert['Parent_ID'] = $parent_id;
		$cat_insert['Position'] = $this -> getOne("Position", "`Parent_ID` = ".$parent_id." ORDER BY `Position` DESC", "categories") + 1;
		$cat_insert['Path'] = $parent_info ? $parent_info['Path']."/".$rlValid -> str2path( $category_name ) : $rlValid -> str2path( $category_name );
		$cat_insert['Level'] = $parent_info['Level']+1;

		$cat_insert['Tree'] = $parent_info ? $parent_info['Tree'].".".$cat_insert['Position'] : $parent_info['Position'].".".$cat_insert['Position'];;
		$cat_insert['Parent_IDs'] = $parent_info['Parent_IDs'] ? $parent_info['Parent_IDs'].".".$parent_info['Parent_ID'] : $parent_info['Parent_ID'] ? $parent_info['Parent_ID'] : '';
		$cat_insert['Type'] = $parent_info['Type'] ? $parent_info['Type'] : "listings";

		$cat_key = $rlValid -> str2key( $category_name );
		if( $cat_key )
		{
			while( $ex = $this -> getOne("ID", "`Key` ='".$cat_key."'", "categories") )
			{
				$cat_key = $parent_info['Key'] ."_".$cat_key;
			}
		}

		$cat_insert['Key'] = $cat_key;
		$cat_insert['Count'] = 1;
		$cat_insert['Status'] = 'active';// $config['xml_import_categories_status'];

		if( $rlActions -> insertOne($cat_insert, "categories") )
		{
			$category_id = mysql_insert_id();

			foreach( $languages as $lkey => $lang_item )
			{
				$lang_insert[$lkey]['Key'] = 'categories+name+'.$cat_key;
				$lang_insert[$lkey]['Value'] = $category_name;
				$lang_insert[$lkey]['Code'] = $lang_item['Code'];
				$lang_insert[$lkey]['Module'] = 'common';
				$lang_insert[$lkey]['Status'] = 'active';
			}

			$rlActions -> insert($lang_insert, "lang_keys");

			return $category_id;
		}
	}


	/**
	* function recount categories
	*
	* @param int $start - start 
	*
	**/

	function recountCategories( $start )
	{
		global $rlHook, $rlCache;

		$start = (int)$start;
		$limit = 500;

		/* get all categories */
		$this -> setTable('categories');
		$categories = $this -> fetch( array('ID', 'Parent_ID'), array('Status' => 'active'), "ORDER BY `Parent_ID`", array($start, $limit) );
		$this -> resetTable();

		foreach ($categories as $key => $value)
		{
			$sql = "SELECT COUNT(`T1`.`ID`) AS `Count` FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
			$sql .= "WHERE (`T1`.`Category_ID` = '{$categories[$key]['ID']}' OR FIND_IN_SET('{$categories[$key]['ID']}', `Crossed`) > 0) AND `T1`.`Status` = 'active' ";
			$sql .= "AND (UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0) ";

			$rlHook -> load('apAjaxRecountListings', $sql);
			
			$cat_listings = $this -> getRow( $sql );

			$update = array(
				'fields' => array(
					'Count' => $cat_listings['Count']
				),
				'where' => array(
					'ID' => $categories[$key]['ID']
				)
			);

			$GLOBALS['rlActions'] -> updateOne($update, 'categories');

			if ( $categories[$key]['Parent_ID'] > 0 )
			{
				$this -> recountListings($categories[$key]['Parent_ID'], $cat_listings['Count']);
			}
		}

		// start recursion
		if ( count( $categories ) == $limit )
		{
			$start += $limit;
			$this -> recountCategories($start);
			unset( $categories );
		}

		$rlCache -> updateCategories();
		$rlCache -> updateListingStatistics();		

		return $_response;
	}


	/**
	* recount listings number for parent category | recursive method
	*
	* @param int $parent_id - parent category ID
	* @param int $current_count - current category listing number
	*
	**/

	function recountListings( $parent_id, $current_number )
	{
		$update = "UPDATE `" . RL_DBPREFIX . "categories` SET `Count` = `Count` + '{$current_number}' WHERE `ID` = '{$parent_id}'";
		$this -> query($update);

		$category = $this -> fetch(array('ID', 'Parent_ID'), array('ID' => $parent_id, 'Status' => 'active'), null, 1, 'categories', 'row');

		if ( $category['Parent_ID'] > 0 )
		{
			$this -> recountListings( $category['Parent_ID'], $current_number );
		}
	}


	/**
	* function extract sub nodes recursive
	*
	* @param array node - data
	* @param nodeKey - node key
	*
	**/

	function extractSubNodesRecursive( $node, $nodeKey )
	{
		if( is_array($node) )
		{
			foreach( $node as $nKey => $nVal )
			{
				if( is_array($nVal) )
				{
					if( $nodeKey )
					{
						$out[ $nodeKey."_".$nKey ] = $this -> extractSubNodes($nVal, $nKey);
					}else
					{
						$out[ $nKey ] = $this -> extractSubNodes($nVal, $nKey);
					}
				}
				else
				{
					$out[ $nodeKey."_".$nKey ] = $nVal;
				}
			}
		}
		else
		{
			 $out[$nodeKey] = $node;
		}

		return $out;
	}


	/**
	* function extract sub nodes
	*
	* @param array listing_array - data	
	*
	**/

	function extractSubNodes( $listing_array = array() )
	{
		$out = array();

		foreach( $listing_array as $aKey => $node )
		{			
			if( is_array($node) )
			{				
				foreach( $node as $nKey => $nVal )
				{
					reset($nVal);
					$first_key = key($nVal);

					if( is_array($nVal) && !is_numeric($first_key) /*&& !is_array($nVal[$first_key])*/ )
					{
						foreach( $nVal as $nvKey => $nvVal )
						{
							if( is_array($nvVal) )
							{
								foreach( $nvVal as $nvvKey => $nvvVal )
								{
									$v[ $aKey."_".$nKey."_".$nvKey."_".$nvvKey ] = $nvvVal;									
								}
							}else
							{
								$v[ $aKey."_".$nKey."_".$nvKey ] = $nvVal;
							}
						}
					}
					else
					{
						$v[ $aKey."_".$nKey ] = $nVal;
					}
				}
				if( $v )
				{
					$out = array_merge($out, $v);
				}
			 }else
			 {
 				$out[$aKey] = $node;
			 }
		}

		return $out;
	}


	/**
	* function extract pictures
	*
	* @param array pictures - input data
	* @param array out - result
	*
	**/

	function extractPictures( $pictures, $out )
	{
		global $rlValid;

		if( is_array($pictures) )
		{
			foreach( $pictures as $key => $value )
			{			
				if( $rlValid -> isUrl( $value ) )
				{				
					$out[] = $value;
				}
				elseif( $rlValid -> isUrl( $value['image'] ) )
				{				
					$out[] = $value['image'];
				}
				elseif( $rlValid -> isUrl( (string) $value -> image ) )
				{				
					$out[] = (string) $value -> image;				
				}
				else
				{
					return $this -> extractPictures($value, $out);
				}
			}
		}
		elseif( $rlValid -> isUrl($pictures) )
		{
			$out[] = $pictures;
		}
		elseif( $delim = is_numeric(strpos($pictures, ",")) ? "," : false || $delim = is_numeric(strpos($pictures, ";")) ? ";" : false )
		{
			$out = explode($delim, $pictures);			
		}
		
		return $out;
	}

}