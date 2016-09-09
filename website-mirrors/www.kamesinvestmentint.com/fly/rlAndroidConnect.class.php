<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLANDROIDCONNECT.CLASS.PHP
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

class rlAndroidConnect extends reefless
{
	/**
	* custom output flag
	**/
	var $custom_output = false;
	
	/**
	* @var listing types available for app
	**/
	var $types;
	
	/**
	* deflate response (using gzip)
	**/
	var $response_deflate = false;
	
	/**
	* price field key
	**/
	var $price_key = 'price';
	
	/**
	* listings grid limit
	**/
	var $grid_listings_limit = 10;
	
	/**
	* featured listings grid limit
	**/
	var $grid_featured_limit = 20;
	
	/**
	* featured listings grid limit | tablet mode
	**/
	var $grid_featured_limit_tablet = 40;
	
	/**
	* map capture zoom on home page
	**/
	var $home_map_host_zoom = 16; //0-21
	
	/**
	* zip code field numeric input type - if true then use numbers only else use numbers and letters
	**/
	var $zip_numeric_input_type = true;
	
	/**
	* listing transfer fields
	**/
	var $transfer_listings_grid_fields = array('ID', 'Main_photo', 'Listing_type', 'Photos_count', 'Featured');
	
	/**
	* account transfer fields
	**/
	var $transfer_account_grid_fields = array('ID', 'Photo', 'Full_name', 'Date', 'Listings_count');
	
	/**
	* sorting transfer fields
	**/
	var $transfer_sorting_fields = array('Key', 'Type', 'name');
	
	/**
	* main listing type key
	**/
	var $main_listing_type;
	
	/**
	* account types
	**/
	var $account_types;
	
	/**
	* youtube thumbnail url
	**/
	var $youtube_thumbnail_url = 'http://img.youtube.com/vi/{key}/mqdefault.jpg';
	//var $youtube_thumbnail_url = 'http://img.youtube.com/vi/{key}/hqdefault.jpg';
	
	/**
	* class constructor
	*
	**/
	function rlAndroidConnect()
	{
		global $response_deflate;
		
		if ( REALM != 'admin' ) {
			$this -> getListingTypes();
			
			reset($this -> types);
			$type = current($this -> types);
			$this -> main_listing_type = $type['Key'];
		}
		
		if ( $response_deflate )
		{
			$this -> response_deflate = true;
		}
	}
	
	/**
	* get listing types
	*
	* @return array
	**/
	function getListingTypes()
	{
		global $rlSmarty, $rlLang;
		
		$sql = "SELECT `T1`.*, IF(`T2`.`Status` = 'active', 1, 0) AS `Advanced_search_availability` ";
		$sql .= "FROM `". RL_DBPREFIX."listing_types` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX."search_forms` AS `T2` ON `T1`.`Key` = `T2`.`Type` AND `T2`.`Mode` = 'advanced' ";
		$sql .= "WHERE `T1`.`Android_status` = 'active' ";
		$sql .= "ORDER BY `Android_position` ";
		$types = $this -> getAll($sql);

		$types = $rlLang -> replaceLangKeys( $types, 'listing_types', array( 'name' ) );
		
		foreach ($types as $type)
		{
			$type['Type'] = $type['Key'];
			$type['Page_key'] = 'lt_'. $type['Type'];
			$type['My_key'] = 'my_'. $type['Type'];
			$type_out[$type['Key']] = $type;
		}
		unset($types);
		
		$this -> types = $type_out;
		
		if ( is_object($rlSmarty) )
		{
			$rlSmarty -> assign_by_ref('listing_types', $this -> types);
		}
		unset($type_out);
	}
	
	/**
	* send response
	*
	* @param array $response - response array
	* @param string $type - response type: xml or json
	* @param string $item - requested resource item
	*
	**/
	function send( $response = false, $type = 'xml', $item = false )
	{
		global $response_dely;
		
		if ( !$type )
		{
			$GLOBALS['rlDebug'] -> logger("ANDROID: No response type defined in request by {$item} request");
			exit;
		}
		
		if ( !$this -> custom_output )
		{
			$print = '<?xml version="1.0" encoding="UTF-8"?>'
					. '<items>';
			
			switch ( $type ){
				case 'xml':
					foreach ( $response as $item )
					{
						$node_name = $item['node_name'] ? $item['node_name'] : 'item';
						unset($item['node_name']);
						
						$print .= '<'. $node_name .'>';
						if ( $item['child_nodes'] ) {
							foreach ($item['child_nodes'] as $child_node) {
								foreach ($child_node as $child_node_key => $child_node_value) {
									if (strtolower($child_node_key) == 'name') {
										$text_content = $child_node_value;
									}
									else {
										$attrs .= ' '. strtolower($child_node_key) .'="'. $child_node_value .'"';
									}
								}
								$print .= '<item'. $attrs;
								$print .= $text_content ? '><![CDATA['. $text_content .']]></item>' : ' />';
								
								unset($text_content, $attrs);
							}
						}
						else {
							foreach ( $item as $key => $value )
							{
								$print .= '<'. strtolower($key) .'><![CDATA['. $value .']]></'. strtolower($key) .'>';
							}
						}
						$print .= '</'. $node_name .'>';
					}
					break;
					
				case 'json':
					// go ahead with this case programming (the first who need it)
					break;
					
				break;
					die('Unsupported response type occured');
					break;
			}
			
			$print .= '</items>';
		}
		else
		{
			$print = $response;
		}
		
		$print = str_replace("&amp;", "AMPREPLACE", $print);
		$print = str_replace("&", "&amp;", $print);
		$print = str_replace("AMPREPLACE", "&amp;", $print);
		
		header('Content-Type: text/xml; charset=utf-8');
		
		if ( function_exists('gzencode') && $this -> response_deflate )
		{
			header("Content-Type: application/x-gzip");
			header("Content-Encoding: gzip");
			
			$print = gzencode($print);
		}
		
		if ( $response_dely )
			usleep(700000);//sleep 0.7 second
		
		echo $print;
	}
	
	/**
	* get language phrases by language iso code
	*
	* @param string $code - requested language code
	*
	* @return array - requested phrases list
	*
	**/
	function getLangPhrases( $code = false )
	{
		global $languages, $config;
		
		if ( !$code )
		{
			$GLOBALS['rlDebug'] -> logger("ANDROID: Unable to fetch lang phrases, no language code specified");
			return false;
		}
		
		$system_code = $code;
		if ( !$languages[$code] )
		{
			$system_code = $config['lang'];
		}

		// get system languages
		$where = "WHERE `Status` = 'active' AND `Code` = '{$system_code}' AND ";
		$where .= "(`Key` LIKE 'listing_types+name+%' OR `Key` LIKE 'account_types+name+%') ";
		
		$this -> setTable('lang_keys');
		$system_phrases = $this -> fetch(array('Key', 'Value'), null, $where);
		
		// get app phrases
		$this -> setTable('android_phrases');
		$app_phrases = $this -> fetch(array('Key', 'Value'), array('Code' => $code));
		
		foreach (array_merge($system_phrases, $app_phrases) as $phrase)
		{
			$phrases[$phrase['Key']] = $phrase['Value'];
		}
		unset($system_phrases, $app_phrases);
		
		return $phrases;
	}
	
	/**
	* get configs related to android app
	*
	* @return array - configs list
	*
	**/
	function getConfigs( $countDate = false )
	{
		global $config;
		
		/* get system configs */
		$from_system = array(
			'android_lang' => 'system_lang',
			'system_currency_position' => 'currency_position',
			'site_main_email' => 'feedback_email');
		
		foreach ($from_system as $key => $sys_config)
		{
			if ( is_numeric($key) )
			{
				$response[$sys_config] = $config[$sys_config];
			}
			else
			{
				$response[$sys_config] = $config[$key];
			}
		}
		
		/* get android configs */
		$this -> setTable('config');
		$app_configs = $this -> fetch(array('Key', 'Default'), array('Plugin' => 'androidConnect'), "AND `Type` <> 'divider'");
		
		foreach ($app_configs as $app_config)
		{
			$response[$app_config['Key']] = str_replace('android_', '', $app_config['Default']);
		}
		unset($app_configs);
		
		/* get custom configs */
		$response['site_name'] = $this -> getOne('Value', "`Key` = 'pages+title+home' AND `Code` = '{$config['lang']}'", 'lang_keys');
		$response['site_url'] = '<a href="'. RL_URL_HOME .'">'. RL_URL_HOME .'</a>';
		$response['site_email'] = '<a href="mailto:'.$config['site_main_email'].'">'.$config['site_main_email'].'</a>';
		$response['home_map_host_zoom'] = $this -> home_map_host_zoom;
		
		// add custom configs
		$comment_plugin = $this -> getOne('ID', "`Key` = 'comment' AND `Status` = 'active'", 'plugins') ? 1 : 0;
		$response['comment_plugin'] = $comment_plugin;
		
		// zip field input type, numeric or mixed
		$response['zip_numeric_input'] = $this -> zip_numeric_input_type;
		
		// count new listings handler
		$response['countNewListingsData'] = 'empty';
		if ( $config['android_count_recently_added'] )
		{
			if ( $countDate )
			{
				$response['countNewListingsData'] = $this -> countNewListings($countDate);
			}
		}
		
		// set main listing type
		$response['mainListingType'] = $this -> main_listing_type;
		
		return $response;
	}
	
	/**
	* build main cache
	*
	* @return int $countDate - timestamp date, the latest date the use run application
	*
	**/
	function getCache( $countDate = false, $tablet = false )
	{
		global $config, $rlLang;
		
		if ( $tablet ) {
			$this -> grid_featured_limit = $this -> grid_featured_limit_tablet;
		}
		
		$this -> custom_output = true;
		
		// cache start
		$response = '<?xml version="1.0" encoding="UTF-8"?><cache>';
		
		// add configs
		$aConfigs = $this -> getConfigs($countDate);
		$response .= '<configs>';
		
		foreach ($aConfigs as $config_key => $config_value)
		{
			$response .= '<config key="'. $config_key .'"><![CDATA['. $config_value .']]></config>';
		}
		
		$response .= '</configs>';
		// add configs END
		
		$this -> setTable('android_languages');
		$app_languages = $this -> fetch(array('Code', 'Direction', 'Key', 'Date_format'));
		
		// add languages and language phrases
		$response .= '<langs>';
		
		foreach ($app_languages as &$language)
		{
			$phrases = $this -> getLangPhrases($language['Code']);
			$response .= '<lang code="'. $language['Code'] .'" name="'. $phrases['android_'.$language['Key']] .'">';
			
			foreach ($phrases as $phrase_key => $phrase_value)
			{
				$response .= '<phrase key="'. $phrase_key .'"><![CDATA['. $phrase_value .']]></phrase>';
			}
				
			$response .= '</lang>';
		}
		
		$response .= '</langs>';
		// add languages and language phrases END
		
		// add listing types
		if ( $this -> types )
		{
			$response .= '<listing_types>';
			foreach ($this -> types as $listing_type)
			{
				$response .= '<type key="'. $listing_type['Key'] .'" photo="'. $listing_type['Photo'] .'" video="'. $listing_type['Video'] .'" page="'. $listing_type['Page'] .'" search="'. $listing_type['Search'] .'" icon="'. $listing_type['Android_icon'] .'"></type>';
			}
			$response .= '</listing_types>';
		}
		// add listing types END

		// add account types
		$this -> getAccountTypes($response);
		
		// add featured listings steck for home page
		$this -> getFeatured($response);
		
		// add listing search forms
		$this -> getSearchForms($response);
		
		// add account search forms
		$this -> getAccountSearchForms($response);
		
		// cache end
		$response .= '</cache>';
		
		return $response;
	}
	
	/**
	* get recently added listings by listing type
	*
	* @param string $type - listing type key
	* @param int $start - stack position
	*
	* @return array - recently added listings
	*
	**/
	function getRecentlyAdded( $type = false, $start = 0 )
	{
		$this -> loadClass('Listings');
		$listings = $GLOBALS['rlListings'] -> getRecentlyAdded($start, $this -> grid_listings_limit, $type);
		
		if ( !$listings )
			return false;
		
		$listings_total = $GLOBALS['rlListings'] -> calc;
		
		array_push($this -> transfer_listings_grid_fields, 'Date_diff');
		
		foreach ($listings as $index => $listing)
		{
			$date_diff = $listings[$index]['Date_diff'];
			unset($listings[$index]['Date_diff']); //unset date_diff to move it to the end of the item array
			
			$listings[$index]['Main_photo'] = $listing['Main_photo'] ? RL_FILES_URL . $listing['Main_photo'] : '';
			$fields = $listing['fields'];
			
			foreach ( $listing as $key => $value )
			{
				if ( !in_array($key, $this -> transfer_listings_grid_fields) )
				{
					unset($listings[$index][$key]);
				}
			}
			
			$listings[$index]['price'] = '';
			$listings[$index]['title'] = '';
			$listings[$index]['middle_field'] = '';
			$listings[$index]['Date_diff'] = $date_diff; // set saved data_diff

			if ( !$fields )
				continue;
			
			// set price
			if ( array_key_exists($this -> price_key, $fields) )
			{
				$listings[$index]['price'] = $fields[$this -> price_key]['value'];
				unset($fields[$this -> price_key]);
			}
			
			$iteration = 1;
			foreach ($fields as $field_key => $field_value)
			{
				// set title
				if ( $iteration == 1 )
				{
					$listings[$index]['title'] = $field_value['value'];
				}
				
				// set middle field
				if ( $iteration == 2 )
				{
					$listings[$index]['middle_field'] = $field_value['value'];
				}
				
				$iteration++;
			}
		}
		$listings[$index+1] = array(
			'total' => $listings_total,
			'node_name' => 'statistic'
		);

		return $listings;
	}
	
	/**
	* get featured listings for home page
	*
	* @param string $response - xml response
	*
	* @return array - recently added listings
	*
	**/
	function getFeatured( &$response )
	{
		global $config;

		$this -> loadClass('Listings');
		$this -> loadClass('Resize');
		$this -> loadClass('Crop');
		
		$listings = $GLOBALS['rlListings'] -> getFeatured($this -> main_listing_type, $this -> grid_featured_limit);
		unset($this -> transfer_listings_grid_fields[array_search('Photos_count', $this -> transfer_listings_grid_fields)]);
		
		/* get latest listings in case if there are not featured listings */
		if ( !$listings ) {
			$listings = $GLOBALS['rlListings'] -> getRecentlyAdded(1, $this -> grid_featured_limit, $this -> main_listing_type);
		}
		
		$response .= '<featured>';
		
		foreach ($listings as $index => $listing)
		{
			$response .= '<listing>';
			
			// set id
			$response .= '<id><![CDATA[';
			$response .= (int)$listing['ID'];
			$response .= ']]></id>';
			
			// set main photo
			if ( $listing['Android_photo'] )
			{
				$listing['Main_photo'] = $listing['Android_photo'];
			}
			
			$response .= '<main_photo><![CDATA[';
			$response .= $listing['Main_photo'] ? RL_FILES_URL . $listing['Main_photo'] : '';
			$response .= ']]></main_photo>';
			
			$fields = $listing['fields'];

			if ( $fields ) {
				// set price
				$response .= '<price><![CDATA[';
				if ( array_key_exists($this -> price_key, $fields) )
				{
					$response .= $fields[$this -> price_key]['value'];
					unset($fields[$this -> price_key]);
				}
				$response .= ']]></price>';
	
				$iteration = 1;
				
				foreach ($fields as $field_key => $field_value)
				{
					// set title
					if ( $iteration == 1 )
					{
						$response .= '<title><![CDATA[';
						$response .= $field_value['value'];
						$response .= ']]></title>';
					}
					
					$iteration++;
				}
			}
			else {
				$response .= '<price><![CDATA[]]></price>';
				$response .= '<title><![CDATA[]]></title>';
			}
			
			$response .= '</listing>';
		}
		
		$response .= '</featured>';
	}
	
	/**
	* get listings by requsted IDs
	*
	* @param string $IDs - string or listing ids separated by comma
	* @param string $start - stack position
	*
	* @return array - listings
	*
	**/
	function getListingByIDs( $IDs = false, $start = 1 )
	{
		$exp_IDs = explode(",", $IDs);
		
		if ( !count($exp_IDs) )
			return;
		
		$this -> loadClass('Listings');
		
		/* define start position */
		$limit = $this -> grid_listings_limit;
		$start = $start > 1 ? ($start - 1) * $limit : 0;
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS ";
		$sql .= "`T1`.*, `T4`.`Path`, `T4`.`Type` AS `Listing_type`, ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyFieldByPeriod');

		$sql .= "IF(TIMESTAMPDIFF(HOUR, `T1`.`Featured_date`, NOW()) <= `T5`.`Listing_period` * 24 OR `T5`.`Listing_period` = 0, '1', '0') `Featured`, ";
		$sql .= "`T4`.`Parent_ID`, `T4`.`Key` AS `Cat_key`, `T4`.`Key` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T5` ON `T1`.`Featured_ID` = `T5`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T4` ON `T1`.`Category_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyJoinByPeriod');
		
		$sql .= "WHERE (";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T2`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T2`.`Listing_period` = 0 ";
		$sql .= ") ";
		
		$sql .= "AND `T1`.`Status` = 'active' AND `T4`.`Status` = 'active' AND `T7`.`Status` = 'active' ";
		$sql .= "AND (`T1`.`ID` = '". implode("' OR `T1`.`ID` = '", $exp_IDs) ."')";
		
		$GLOBALS['rlHook'] -> load('listingsModifyWhereByPeriod');
		$GLOBALS['rlHook'] -> load('listingsModifyGroupByPeriod');

		$sql .= "ORDER BY FIND_IN_SET(`T1`.`ID`, '{$IDs}') DESC ";
		$sql .= "LIMIT {$start}, {$limit}";

		$listings = $this -> getAll( $sql );
		
		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		
		foreach ( $listings as $key => $value )
		{
			/* populate fields */
			$fields = $GLOBALS['rlListings'] -> getFormFields( $value['Category_ID'], 'short_forms', $value['Listing_type'] );
			
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
		
		return $this -> prepareListings($listings, $calc['calc']);
	}
	
	/**
	* get certain listing details
	*
	* @param int $id - listing id
	*
	* @return array - listing details
	*
	**/
	function getListingDetails( $id = false )
	{
		global $config;

		$this -> custom_output = true;
		$price = false;
		
		/* get listing plain data */
		$sql = "SELECT `T1`.*, `T2`.`Path`, `T2`.`Type` AS `Listing_type`, `T2`.`Key` AS `Cat_key`, `T2`.`Type` AS `Cat_type`, ";
		$sql .= "`T3`.`Image`, `T3`.`Image_unlim`, `T3`.`Video`, `T3`.`Video_unlim`, CONCAT('categories+name+', `T2`.`Key`) AS `Category_pName`, ";
		$sql .= "`T2`.`Path` as `Category_path`, ";
		$sql .= "IF ( UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T3`.`Listing_period` DAY)) <= UNIX_TIMESTAMP(NOW()) AND `T3`.`Listing_period` > 0, 1, 0) AS `Listing_expired` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T5` ON `T1`.`Account_ID` = `T5`.`ID` ";
		$sql .= "WHERE `T1`.`ID` = '{$id}' AND `T5`.`Status` = 'active' ";
		
		//$rlHook -> load('listingDetailsSql', $sql);
		
		$sql .= "LIMIT 1";
		
		$listing_data = $this -> getRow( $sql );
		$listing_type = $this -> types[$listing_data['Listing_type']];
		
		$this -> loadClass('Listings');
		$listings = $GLOBALS['rlListings'] -> getListingDetails($listing_data['Category_ID'], $listing_data, $listing_type);
		
		if ( !$listings )
			return false;
			
		$response = '<?xml version="1.0" encoding="UTF-8"?><listing>';
		
		/* get listing title */
		$listing_title = $GLOBALS['rlListings'] -> getListingTitle( $listing_data['Category_ID'], $listing_data, $listing_type['Key'] );
		$response .= '<title><![CDATA['. $listing_title .']]></title>';

		/* get listing type  */
		$response .= '<listing_type><![CDATA['. $listing_type['Key'] .']]></listing_type>';
		
		/* get listing url */
		$type_page_path = $this -> getOne('Path', "`Key` = '{$listing_type['Page_key']}'", 'pages');
		$listing_url = RL_URL_HOME . $type_page_path .'/'. $listing_data['Category_path'] .'/'. $GLOBALS['rlValid'] -> str2path($listing_title) .'-'. $listing_data['ID'] .'.html';
		$response .= '<url><![CDATA['. $listing_url .']]></url>';
		
		/* get photos */
		$photos_limit = $listing_data['Image_unlim'] ? null : $listing_data['Image'];
		$photos = $this -> fetch( '*', array( 'Listing_ID' => $id, 'Status' => 'active' ), "AND `Thumbnail` <> '' AND `Photo` <> '' ORDER BY `Position`", $photos_limit, 'listing_photos' );

		/* populate photos stack */
		$response .= '<photos>';
		foreach ($photos as $photo)
		{
			$response .= '<photo large="'. RL_FILES_URL . $photo['Photo'] .'" thumbnail="'. RL_FILES_URL . $photo['Thumbnail'] .'"><![CDATA['. $photo['Description'] .']]></photo>';	
		}
		$response .= '</photos>';
		
		/* populate details stack */
		$response .= '<details>';
		foreach ($listings as $group)
		{
			if ( empty($group['Fields']) )
				continue;
				
			$response .= '<group key="'. $group['Key'] .'" name="'. strip_tags($group['name']) .'">';
			
			foreach ($group['Fields'] as $field)
			{
				if ( !$price && is_numeric(strpos($field['Key'], 'price')) )
				{
					$price = $field['value'];
				}

				if ( $field['value'] == "" || !$field['Details_page'] )
					continue;

				$response .= '<field key="'. $field['Key'] .'" name="'. strip_tags($field['name']) .'" type="'. $field['Type'] .'"><![CDATA['. $this -> adaptValue($field) .']]></field>';
			}
			
			$response .= '</group>';
		}
		$response .= '</details>';
		
		/* get listing video */
		$this -> setTable('listing_video');
		$videos = $this -> fetch(array('Type', 'Video', 'Preview'), array( 'Listing_ID' => $id ), "ORDER BY `Position`");
		
		$response .= '<videos>';
		if ( $listing_type['Video'] && $videos )
		{
			foreach ($videos as $video)
			{
				if ( $video['Type'] == 'local' ) {
					$video['Preview'] = RL_FILES_URL . $video['Preview'];
					$video['Video'] = RL_FILES_URL . $video['Video'];
				}
				else {
					$video['Video'] = $video['Preview'];
					$video['Preview'] = str_replace('{key}', $video['Preview'], $this -> youtube_thumbnail_url);
				}

				$response .= '<video type="'. $video['Type'] .'" video="'. $video['Video'] .'" preview="'. $video['Preview'] .'" />';
			}
		}
		$response .= '</videos>';

		/* get comments */
		if ( $this -> getOne('ID', "`Key` = 'comment' AND `Status` = 'active'", 'plugins') )
		{
			$sql = "SELECT `T1`.`Author`, `T1`.`Title`, `T1`.`Description`, `T1`.`Rating`, UNIX_TIMESTAMP(`T1`.`Date`) AS `Date` ";
			$sql .= "FROM `".RL_DBPREFIX."comments` AS `T1` ";
			$sql .= "WHERE `T1`.`Listing_ID` = {$id} ";
			$sql .= "LIMIT 10";
	
			$comments = $this -> getAll($sql);

			if ( $comments )
			{
				$response .= '<comments>';
				foreach ($comments as &$comment )
				{
					$comment['Description'] = preg_replace_callback('#(\s|^)((?:https?://)?\w+(?:\.\w+)+(?<=\.(net|org|edu|com))(?:/[^\s]*|))(?=\s|\b)#is',
							create_function('$m', 'if (!preg_match("#^(https?://)#", $m[2]))
							return $m[1]."<a href=\"http://".$m[2]."\">".$m[2]."</a>"; else return $m[1]."<a href=\"".$m[2]."\">".$m[2]."</a>";'),
							$comment['Description']);

					$comment['Date'] = date(str_replace(array('%', 'b'), array('', 'M'), RL_DATE_FORMAT), $comment['Date']);
					$comment['Rating'] = round((5 * $comment['Rating']) / $config['comments_stars_number']);// 5 because we use 5 stars policy in the app
					
					$response .= '<comment title="'. $comment['Title'] .'" author="'. $comment['Author'] .'" rating="'. $comment['Rating'] .'" date="'. $comment['Date'] .'"><![CDATA['. $comment['Description'] .']]></comment>';
				}
				$response .= '</comments>';
			}
		}
		
		/* get seller information */
		$this -> loadClass('Account');
		$seller_info = $GLOBALS['rlAccount'] -> getProfile((int)$listing_data['Account_ID']);

		/* populate seller stack */
		$response .= '<seller>';
		$response .= '<id><![CDATA['. $seller_info['ID'] .']]></id>';
		$response .= '<name><![CDATA['. $seller_info['Full_name'] .']]></name>';
		$response .= '<email><![CDATA['. $seller_info['Mail'] .']]></email>';
		$response .= '<listings_count><![CDATA['. $seller_info['Listings_count'] .']]></listings_count>';
		if ( $seller_info['Photo'] )
		{
			$response .= '<thumbnail><![CDATA['. RL_FILES_URL . $seller_info['Photo'] .']]></thumbnail>';
		}
		$response .= '<fields>';
		
		foreach ($seller_info['Fields'] as $field)
		{
			if ( $field['value'] == '' || !$field['Details_page'] )
				continue;
				
			$response .= '<field key="'. $field['Key'] .'" name="'. $field['name'] .'" type="'. $field['Type'] .'"><![CDATA['. $this -> adaptValue($field) .']]></field>';
		}
		
		$response .= '</fields>';
		$response .= '</seller>';
		
		/* set price */
		$response .= '<price><![CDATA['. $price .']]></price>';
		
		/* build location fields */
		if ( $config['address_on_map'] && $listing_data['account_address_on_map'] )
		{
			/* get location data from user account */
			$location = $GLOBALS['rlAccount'] -> mapLocation;
			
			if ( $seller_info['Loc_latitude'] && $seller_info['Loc_longitude'] )
			{
				$location['direct'] = $seller_info['Loc_latitude'] .','. $seller_info['Loc_longitude'];
			}
		}
		else
		{
			/* get location data from listing */
			$fields_list = $GLOBALS['rlListings'] -> fieldsList;
		
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
		
		/* set location */
		$response .= '<location direct="'. $location['direct'] .'"><![CDATA['. $location['search'] .']]></location>';
		
		$response .= '</listing>';
		
		return $response;
	}
	
	/**
	* get certain account details
	*
	* @param int $id - account id
	*
	* @return array - account details
	*
	**/
	function getAccountDetails( $id = false )
	{
		$this -> loadClass("Account");
		$this -> loadClass('Listings');
		
		$account = $GLOBALS['rlAccount'] -> getProfile($id);
		
		if ( !$account )
			return;
		
		$this -> custom_output = true;
		
		$response = '<?xml version="1.0" encoding="UTF-8"?><account>';
		$response .= '<email><![CDATA['. $account['Mail'] .']]></email>';
		
		/* set account fields */
		$response .= '<fields>';
		
		foreach ($account['Fields'] as $field)
		{
			if ( $field['value'] == '' || !$field['Details_page'] )
				continue;
				
			$response .= '<field key="'. $field['Key'] .'" name="'. $field['name'] .'" type="'. $field['Type'] .'"><![CDATA['. $this -> adaptValue($field) .']]></field>';
		}
		
		$response .= '</fields>';
		
		/* set location */
		$location = $GLOBALS['rlAccount'] -> mapLocation;
		$response .= '<location latitude="'. $account['Loc_latitude'] .'" longitude="'. $account['Loc_longitude'] .'"><![CDATA['. $location['search'] .']]></location>';
		
		/* get listings */
		$listings = $GLOBALS['rlListings'] -> getListingsByAccount($id, false, false, 1, $this -> grid_listings_limit);
		if ( $listings ) {
			$calc = $GLOBALS['rlListings'] -> calc;

			$response .= '<listings>';
			foreach ($this -> prepareListings($listings, $calc) as $listing) {
				$nod_name = $listing['node_name'] ? $listing['node_name'] : 'item';
				unset($listing['node_name']);
				
				$response .= '<'. $nod_name .'>';
				foreach ( $listing as $key => $value )
				{
					$response .= '<'. strtolower($key) .'><![CDATA['. $value .']]></'. strtolower($key) .'>';
				}
				$response .= '</'. $nod_name .'>';
			}
			$response .= '</listings>';
		}
		
		$response .= '</account>';
		
		return $response;
	}
	
	/**
	* get search forms for available listing types
	*
	* @param string $response - xml response
	*
	**/
	function getSearchForms( &$response )
	{
		global $lang, $config;
		
		$this -> loadClass('Search');
		$this -> loadClass('Categories');

		$response .= '<search_forms>';
		
		if( $this -> getRow("SELECT `Key` FROM `".RL_DBPREFIX."plugins` WHERE `Status` = 'active' AND `Key` = 'multiField'") )
		{
			$sql = "SELECT * FROM `".RL_DBPREFIX."multi_formats` AS `T1`";
			$sql .="JOIN `".RL_DBPREFIX."listing_fields` AS `T2` ON `T2`.`Condition` = `T1`.`Key` ";
			$sql .=" WHERE 1 ";
			
			$mf_tmp = $this -> getAll( $sql );

			foreach( $mf_tmp as $key => $item )
			{
				$multi_fields[ $item['Key'] ] = true;
			}
		}

		/* get search forms */
		foreach ($this -> types as $type_key => $listing_type)
		{
			if ( $listing_type['Search_page'] )
			{
				if ( $search_form = $GLOBALS['rlSearch'] -> buildSearch( $type_key .'_quick', $type_key ) )
				{
					$response .= '<form type="'. $type_key .'">';
					
					foreach ($search_form as $field)
					{
						switch ($field['Fields'][0]['Type']) {
							case 'price':
								$sql = "SELECT MIN(ROUND(`{$field['Fields'][0]['Key']}`)) AS `min`, MAX(ROUND(`{$field['Fields'][0]['Key']}`)) AS `max` ";
								$sql .= "FROM `". RL_DBPREFIX ."listings` ";
								$sql .= "WHERE `Status` = 'active'";
								$max =  $this -> getRow($sql);
								
								$max['max'] = $max['max'] > 10000000 ? 10000000 : round($max['max']);
								
								$data = $max['min'].'-'.$max['max'];
								break;
								
							case 'number':
								if ( is_numeric(strpos($field['Fields'][0]['Key'], 'zip')) ) {
									// TODO in case of zip code
								}
								else {
									$sql = "SELECT MIN(ROUND(`{$field['Fields'][0]['Key']}`)) AS `min`, MAX(ROUND(`{$field['Fields'][0]['Key']}`)) AS `max` ";
									$sql .= "FROM `". RL_DBPREFIX ."listings` ";
									$sql .= "WHERE `Status` = 'active'";
									$max =  $this -> getRow($sql);
									
									$data = $max['min'].'-'.$max['max'];
								}
								break;
							case 'select':
								if( $multi_fields[$field['Fields'][0]['Key']] )
								{									
									$data = 'multiField';
									if(is_numeric(strpos($field['Fields'][0]['Key'], '_level')))
									{
										unset($field['Fields'][0]['Values']);										
									}
								}								
								break;
						}
						
						/* re-define the field key because fields will be stored in single array and keys maybe be overwrited */
						if ( $field['Fields'][0]['Key'] == 'Category_ID' ) {
							$field['Fields'][0]['Key'] .= '|' .$type_key;
						}
						
						$response .= '<field name="'. strip_tags($lang[$field['Fields'][0]['pName']]) .'" type="'. $field['Fields'][0]['Type'] .'" key="'. $field['Fields'][0]['Key'] .'" data="'. $data .'">';

						/* collect possible field items */
						if ( is_array($field['Fields'][0]['Values']) )
						{
							foreach ($field['Fields'][0]['Values'] as $item)
							{
								if ( ereg('^Category_ID', $field['Fields'][0]['Key']) )
								{
									$item['margin'] = (int) $item['margin'] >= 5 ? ceil(($item['margin'] - 5) * 2) : $item['margin'];
									$item['margin'] = $item['margin'] ? $item['margin'] : 0;
									$set_name = $lang[$item['pName']];
									if ( $listing_type['Cat_listing_counter'] && $item['Count'] > 0 )
									{
										$set_name .= " ({$item['Count']})";
									}
									$response .= '<item name="'. $set_name .'" key="'. $item['ID'] .'" margin="'. $item['margin'] .'" />';
								}
								elseif ( $field['Fields'][0]['Key'] == 'built' )
								{
									$response .= '<item name="'. $item['Key'] .'" key="'. $item['Key'] .'" />';
								}
								elseif ( $field['Fields'][0]['Key'] == 'posted_by' )
								{
									$response .= '<item name="'. $lang[$item['pName']] .'" key="'. $item['ID'] .'" />';
								}
								else
								{
									switch ($field['Fields'][0]['Type']) {
										case 'checkbox':
										case 'radio':
											$set_key = str_replace($field['Fields'][0]['Key'] .'_', '', $item['Key']);
											$response .= '<item name="'. $lang[$item['pName']] .'" key="'. $set_key .'" />';
											break;
											
										default:
											$response .= '<item name="'. $lang[$item['pName']] .'" key="'. $item['Key'] .'" />';
											break;
									}
								}
							}
						}
						elseif ( $field['Fields'][0]['Type'] == 'price' )
						{
							foreach ($GLOBALS['rlCategories'] -> getDF('currency') as $currency_item)
							{
								$response .= '<item name="'. $currency_item['name'] .'" key="'. $currency_item['Key'] .'" />';
							}
						}
						elseif ( is_numeric(strpos($field['Fields'][0]['Key'], 'zip')) )
						{
							$response .= '<item name="'. $lang['sbd_distance'] .'" key="" />';
							
							$units = $config['sbd_default_units'] == 'kilometres' ? $lang['sbd_km'] : $lang['sbd_mi'];
							foreach (explode(',', $config['sbd_distance_items']) as $mile)
							{
								$response .= '<item name="'. $mile .' '. $units .'" key="'. $mile .'" />';
							}
						}
						$response .= '</field>';
						
						unset($data);
					}
					
					$response .= '</form>';
				}
			}
		}
		
		$response .= '</search_forms>';
	}
	
	/**
	* get search forms for available account types
	*
	* @param string $response - xml response
	*
	**/
	function getAccountSearchForms( &$response )
	{
		global $lang, $config;
		
		if( $this -> getRow("SELECT `Key` FROM `".RL_DBPREFIX."plugins` WHERE `Status` = 'active' AND `Key` = 'multiField'") )
		{
			$sql = "SELECT * FROM `".RL_DBPREFIX."multi_formats` WHERE 1 ";
			global $multi_formats;
			$mf_tmp = $this -> getAll( $sql );
			foreach( $mf_tmp as $key => $item )
			{
				$multi_formats[ $item['Key'] ] = $item;
			}
		}
		$this -> loadClass('Account');
		
		$response .= '<account_search_forms>';
		foreach ($this -> account_types as $type)
		{
			if ( !$type['Page'] )
				continue;
			
			if ( $fields = $GLOBALS['rlAccount'] -> buildSearch($type['ID']) )
			{
				$response .= '<form type="'. $type['Key'] .'">';
					
				foreach ($fields as $field)
				{
					switch ($field['Type']) {
						case 'price':
							$sql = "SELECT MAX(ROUND(`{$field['Key']}`)) AS `max` ";
							$sql .= "FROM `". RL_DBPREFIX ."accounts` ";
							$sql .= "WHERE `Status` = 'active'";
							$max =  $this -> getRow($sql);
							
							$data = $max['max'] > 1000000 ? 1000000 : round($max['max']);
							break;
							
						case 'number':
							if ( is_numeric(strpos($field['Key'], 'zip')) ) {
								// TODO in case of zip code
							}
							else {
								$sql = "SELECT MIN(ROUND(`{$field['Key']}`)) AS `min`, MAX(ROUND(`{$field['Key']}`)) AS `max` ";
								$sql .= "FROM `". RL_DBPREFIX ."accounts` ";
								$sql .= "WHERE `Status` = 'active'";
								$max =  $this -> getRow($sql);
								
								$data = $max['min'].'-'.$max['max'];
							}
							break;
							case 'select':
								if( $multi_formats[$field['Condition']] )
								{									
									$data = 'multi|'.$field['Condition'];
								}
								break;
					}
					
					$response .= '<field name="'. strip_tags($lang[$field['pName']]) .'" type="'. $field['Type'] .'" key="'. $field['Key'] .'" data="'. $data .'">';
					
					/* collect possible field items */
					if ( is_array($field['Values']) )
					{
						foreach ($field['Values'] as $item)
						{
							switch ($field['Type']) {
								case 'checkbox':
								case 'radio':
									$set_key = str_replace($field['Key'] .'_', '', $item['Key']);
									$response .= '<item name="'. $lang[$item['pName']] .'" key="'. $set_key .'" />';
									break;
									
								default:
									$response .= '<item name="'. $lang[$item['pName']] .'" key="'. $item['Key'] .'" />';
									break;
							}
						}
					}
					elseif ( $field['Type'] == 'price' )
					{
						foreach ($GLOBALS['rlCategories'] -> getDF('currency') as $currency_item)
						{
							$response .= '<item name="'. $currency_item['name'] .'" key="'. $currency_item['Key'] .'" />';
						}
					}
					elseif ( is_numeric(strpos($field['Key'], 'zip')) )
					{
						$response .= '<item name="'. $lang['sbd_distance'] .'" key="" />';
						
						$units = $config['sbd_default_units'] == 'kilometres' ? $lang['sbd_km'] : $lang['sbd_mi'];
						foreach (explode(',', $config['sbd_distance_items']) as $mile)
						{
							$response .= '<item name="'. $mile .' '. $units .'" key="'. $mile .'" />';
						}
					}
					$response .= '</field>';
					
					unset($data);
				}
				
				$response .= '</form>';
			}
		}
		$response .= '</account_search_forms>';
	}
	
	function searchResults( $data = false, $type = false, $start = 1, $sort = false )
	{
		global $sorting;
		
		if ( !$type || !$data )
			return false;

		$form_key = $type .'_quick';
		
		$this -> loadClass('Search');
		
		/* get sorting fields */
		$GLOBALS['rlSearch'] -> getFields($form_key, $type);
		$sorting = $GLOBALS['rlSearch'] -> fields;
		
		/* adapt sorting array */
		if ( $sorting ) {
			foreach ($sorting as &$field) {
				if ( !$field['Details_page'] ) {
					unset($field);
					continue;
				}
				
				foreach ($field as $item_key => $value) {
					if ( !in_array($item_key, $this -> transfer_sorting_fields) ) {
						unset($field[$item_key]);
					}
				}
			}
		}
			
		$this -> loadClass('Search');

		$GLOBALS['rlSearch'] -> getFields($form_key, $type);
		$fields = $GLOBALS['rlSearch'] -> fields;
		
		if ( !$fields )
			$GLOBALS['rlDebug'] -> logger("ANDROID: searchResults, no fields by form found");
			
		foreach (explode(',', $data) as $form_item) {
			$params = explode('=', $form_item);
			
			/* remove |listing_type_key from the field key */
			if ( ereg('^Category_ID', $params[0]) ) {
				$category_id = explode('|', $params[0]);
				$params[0] = $category_id[0];
			}
			
			//$params[0] - field key
			//$params[1] - field value
			
			switch ($fields[$params[0]]['Type']) {
				case 'checkbox':
					$exp_items = explode(';', $params[1]);
					array_unshift($exp_items, 0);
					$form_data[$params[0]] = $exp_items;
					break;
					
				case 'number':
					$value = explode('-', $params[1]);
					if ( is_numeric(strpos($params[0], 'zip')) ) {
						$form_data[$params[0]]['distance'] = $value[0];
						$form_data[$params[0]]['zip'] = $value[1];
					}
					else {
						$form_data[$params[0]]['from'] = $value[0];
						$form_data[$params[0]]['to'] = $value[1];
					}
					break;
					
				case 'price':
					$value = explode('-', $params[1]);
					$form_data[$params[0]]['from'] = $value[0];
					$form_data[$params[0]]['to'] = $value[1];
					
					break;
				
				default:
					$form_data[$params[0]] = $params[1];
					break;
			}
		}
		
		if ( $sort ) {
			$sort = explode('|/|', $sort);
			
			if ( $sorting[$sort[0]] ) {
				$form_data['sort_by'] = $sort[0];
				$form_data['sort_type'] = $sort[1];
			}
		}
		
		$listings = $GLOBALS['rlSearch'] -> search($form_data, $type, $start, $this -> grid_listings_limit);
		
		return $this -> prepareListings($listings, $GLOBALS['rlSearch'] -> calc, $sorting);
	}
	
	function getListingsByAccount( $id = false, $start = 1 )
	{
		$this -> loadClass('Listings');
		return $this -> prepareListings($GLOBALS['rlListings'] -> getListingsByAccount($id, false, false, $start, $this -> grid_listings_limit), $GLOBALS['rlListings'] -> calc);
	}
	
	/**
	* prepare listings array for xml responce
	*
	* @param array $listings - referent to original listings array
	* @param int $count - total listings count from CALC
	* @param array $sorting - sorting fields array
	*
	* @return array $listings - response type: xml or json
	*
	**/
	function prepareListings( &$listings, $count = false, $sorting = false )
	{
		$listings = $GLOBALS['rlLang'] -> replaceLangKeys( $listings, 'categories', 'name' );
		
		if ( empty($listings) )
			return false;
		
		foreach ($listings as $index => $listing)
		{
			$listings[$index]['Main_photo'] = $listing['Main_photo'] ? RL_FILES_URL . $listing['Main_photo'] : '';
			$fields = $listing['fields'];
			
			foreach ( $listing as $key => $value )
			{
				if ( !in_array($key, $this -> transfer_listings_grid_fields) )
				{
					unset($listings[$index][$key]);
				}
			}
			
			$listings[$index]['price'] = '';
			$listings[$index]['title'] = '';
			$listings[$index]['middle_field'] = '';

			if ( !$fields )
				continue;
			
			// set price
			if ( array_key_exists($this -> price_key, $fields) )
			{
				$listings[$index]['price'] = $fields[$this -> price_key]['value'];
				unset($fields[$this -> price_key]);
			}
			
			$iteration = 1;
			foreach ($fields as $field_key => $field_value)
			{
				// set title
				if ( $iteration == 1 )
				{
					$listings[$index]['title'] = $field_value['value'];
				}
				
				// set middle field
				if ( $iteration == 2 )
				{
					$listings[$index]['middle_field'] = $field_value['value'];
				}
				
				$iteration++;
			}
		}
		
		$listings[$index+1] = array(
			'total' => $count,
			'node_name' => 'statistic'
		);
		
		if ( $sorting ) {
			$listings[$index+2] = array(
				'child_nodes' => $sorting,
				'node_name' => 'sorting'
			);
		}
		
		return $listings;
	}
	
	/**
	* prepare accounts array for xml responce
	*
	* @param array $account - referent to original accounts array
	* @param int $count - total account count from CALC
	*
	* @return array $accounts - response type: xml or json
	*
	**/
	function prepareAccounts( &$accounts, $count = false )
	{
		if ( !$accounts )
			return false;
		
		foreach ($accounts as $index => $account)
		{
			$accounts[$index]['Photo'] = $account['Photo'] ? RL_FILES_URL . $account['Photo'] : '';
			$fields = $account['fields'];
			
			$accounts[$index]['Date'] = date(str_replace(array('%', 'b'), array('', 'M'), RL_DATE_FORMAT), $account['Date']);
			
			foreach ( $account as $key => $value )
			{
				if ( !in_array($key, $this -> transfer_account_grid_fields) )
				{
					unset($accounts[$index][$key]);
				}
			}
			
			$iteration = 1;
			$middle_field = '';
			foreach ($fields as $field)
			{
				if ( $iteration > 2 )
					break;
				
				if ( $field['value'] != '' ) {
					$middle_field .= $field['value'].', ';
					$iteration++;
				}
			}
			
			$middle_field = substr($middle_field, 0, -2);
			$accounts[$index]['middle_field'] = $middle_field;
		}
		$accounts[$index+1] = array(
			'total' => $count,
			'node_name' => 'statistic'
		);
		
		return $accounts;
	}
	
	function countNewListings( $date = false )
	{
		if ( !$date )
			return "empty";
			
		$sql = "SELECT COUNT(`T1`.`ID`) AS `Count` ";
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "WHERE (";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T2`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T2`.`Listing_period` = 0 ";
		$sql .= ") ";
		$sql .= "AND `T1`.`Status` = 'active' ";
		$sql .= "AND UNIX_TIMESTAMP(`T1`.`Date`) >= {$date} ";
		$sql .= "LIMIT 100";
		
		$total = $this -> getRow($sql);
		
		return $total['Count'];
	}
	
	function resizePhotos( $stack = 1 )
	{
		global $rlHook;
		
		/* remove pictures before resize */
		if ( $stack == 1 ) {
			$this -> removePictures();
			//$this -> query("UPDATE `". RL_DBPREFIX ."listing_photos` SET `Android310` = ''");
			$this -> query("UPDATE `". RL_DBPREFIX ."listings` SET `Android_photo` = ''");
		}
		
		$limit = 5;
		$start = $stack > 0 ? ($stack - 1) * $limit : 0;
		
		$sql = "SELECT `T1`.`ID`, `T2`.`ID` AS `Photo_ID`, `T2`.`Original`, `T2`.`Photo` ";
		
		$rlHook -> load('androidResizeGetPhotosSqlSelect', $sql);
		
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_photos` AS `T2` ON `T1`.`Main_photo` = `T2`.`Thumbnail` AND `T1`.`ID` = `T2`.`Listing_ID` ";
		
		$rlHook -> load('androidResizeGetPhotosSqlJoin', $sql);
		
		$sql .= "WHERE `T1`.`Main_photo` <> '' ";
		
		$rlHook -> load('androidResizeGetPhotosSqlWhere', $sql);
		
		$sql .= "ORDER BY `T1`.`ID` LIMIT {$start},{$limit}";
		
		$photos = $this -> getAll($sql);
		
		if ( $photos )
		{
			foreach ($photos as $photo)
			{
				$photo['Photo'] = $photo['Original'] ? $photo['Original'] : $photo['Photo'];
				$this -> resize($photo['Photo'], $photo['ID']);
			}
			echo 1;
		}
		else
		{
			echo 0;
		}
	}
	
	function resizePhoto( &$sql, &$listing_data, &$id )
	{
		$data = $this -> fetch(array('Original', 'Photo'), array('Thumbnail' => $listing_data['Main_photo'], 'Listing_ID' => $id), null, 1, 'listing_photos', 'row');
		$photo = $data['Original'] ? $data['Original'] : $data['Photo'];
		
		$android_img = $this -> getOne('Android_photo', "`ID` = '{$id}'", 'listings');
		if ( $android_img ) {
			unlink(RL_FILES . $android_img);
		}
		
		$this -> resize($photo, $id);
	}
	
	function resize( &$photo, &$listing_id )
	{
		global $config, $rlHook;
		
		$this -> loadClass('Resize');
		$this -> loadClass('Crop');
		
		$exp_dir = explode('/', $photo);
		if ( $exp_dir[1] )
		{
			array_pop($exp_dir);
			$dir = RL_FILES . implode(RL_DS, $exp_dir) . RL_DS;
			$dir_name = implode('/', $exp_dir) .'/';
		}
		else
		{
			$dir = RL_FILES;
			$dir_name = '';
		}
		
		$file_path = RL_FILES . str_replace('/', RL_DS, $photo);
		$file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
		$file_name = 'android310_'. time() . mt_rand(). '.' .$file_ext;
		$new_file_path = $dir . $file_name;
		$set_name = $dir_name . $file_name;

		$GLOBALS['rlCrop'] -> loadImage($file_path);
		$GLOBALS['rlCrop'] -> cropBySize(310, 310, ccCENTER);
		$GLOBALS['rlCrop'] -> saveImage($new_file_path, $config['img_quality']);
		$GLOBALS['rlCrop'] -> flushImages();

		$GLOBALS['rlResize'] -> resize($new_file_path, $new_file_path, 'C', array(310, 310), false, false);

		//$this -> query("UPDATE `". RL_DBPREFIX ."listing_photos` SET `Android310` = '{$set_name}' WHERE `ID` = '{$photo['Photo_ID']}'");
		$this -> query("UPDATE `". RL_DBPREFIX ."listings` SET `Android_photo` = '{$set_name}' WHERE `ID` = '{$listing_id}'");
		
		$rlHook -> load('androidResizePhotosLoopEnd', $listing_id, $new_file_path, $file_name);
	}
	
	function getCategories( $type = false, $parent = false )
	{
		if ( !$type || !is_numeric($parent) )
			return;

		$this -> loadClass('Categories');
		
		foreach ($GLOBALS['rlCategories'] -> getCategories($parent, $type) as $category)
		{
			$categories[] = array(
				'id' => $category['ID'],
				'name' => $category['name'],
				'count' => $category['Count'],
				'sub_categories' => $category['sub_categories'] ? 1 : 0
			);
		}
		
		return $categories;
	}
	
	/**
	* get listings by category id
	*
	* @param int $id - category ID
	* @param int $start - start stack
	* @param string $listing_type - listing type key
	**/
	function getListingsByCategory( $id = false, $start = 1, $listing_type = false, $sort = false )
	{
		global $sorting;
		
		if ( !$id )
			return;

		$sort_field = false;
		$sort_type = 'ASC';
		
		$this -> loadClass('Listings');
		
		/* get sorting fields */
		$sorting = $GLOBALS['rlListings'] -> getFormFields($id, 'short_forms', $listing_type);
		
		if ( $sort ) {
			$sort = explode('|/|', $sort);
			
			if ( $sorting[$sort[0]] ) {
				$sort_field = $sort[0];
				$sort_type = strtoupper($sort[1]);
			}
		}
		
		$listings = $GLOBALS['rlListings'] -> getListings($id, $sort_field, $sort_type, $start);
		
		/* adapt sorting array */
		if ( $sorting ) {
			foreach ($sorting as &$field) {
				if ( !$field['Details_page'] ) {
					unset($field);
					continue;
				}
				
				foreach ($field as $item_key => $value) {
					if ( !in_array($item_key, $this -> transfer_sorting_fields) ) {
						unset($field[$item_key]);
					}
				}
			}
		}
		
		return $this -> prepareListings($listings, $GLOBALS['rlListings'] -> calc, $sorting);
	}
	
	/**
	* get listings by LatLng
	*
	* @param int $id - category ID
	* @param int $start - start stack
	**/
	function getListingsByLatLng( $type = false, $start = 1, $coordinates )
	{
		if ( !$type )
			return;
			
		$this -> loadClass('Listings');
			
		$sql = "SELECT `T1`.*, `T3`.`Key` AS `Key`, `T3`.`Type` AS `Listing_type`, ";
		
		$GLOBALS['rlHook'] -> load('listingsModifyField');
		
		$sql .= "ROUND(3956 * 2 * ASIN(SQRT(
			POWER(SIN(({$coordinates['centerLat']} - `T1`.`Loc_latitude`) * 0.0174532925 / 2), 2) +
			COS({$coordinates['centerLat']} * 0.0174532925) *
			COS(`T1`.`Loc_latitude` * 0.0174532925) *
			POWER(SIN(({$coordinates['centerLng']} - `T1`.`Loc_longitude`) * 0.0174532925 / 2), 2)
		)), 3) AS `Android_distance` ";
		
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";

		$GLOBALS['rlHook'] -> load('listingsModifyJoin');
		
		$sql .= "WHERE (";
		$sql .= " TIMESTAMPDIFF(HOUR, `T1`.`Pay_date`, NOW()) <= `T2`.`Listing_period` * 24 "; //round to hour
		$sql .= " OR `T2`.`Listing_period` = 0 ";
		$sql .= ") ";
		
		$sql .= "AND (`T1`.`Loc_latitude` BETWEEN {$coordinates['southWestLat']} AND {$coordinates['northEastLat']}) ";
		$sql .= "AND (`T1`.`Loc_longitude` BETWEEN {$coordinates['southWestLng']} AND {$coordinates['northEastLng']}) ";
		
		$sql .= "AND `T3`.`Type` = '{$type}' ";
		
		$sql .= "AND `T1`.`Status` = 'active' AND `T7`.`Status` = 'active' ";

		$GLOBALS['rlHook'] -> load('listingsModifyWhere');
		$GLOBALS['rlHook'] -> load('listingsModifyGroup');
		
		if ( false === strpos($sql, 'GROUP BY') )
		{
			$sql .= " GROUP BY `T1`.`ID` ";
		}
		
		$sql .= "ORDER BY `ID` DESC ";
		$sql .= "LIMIT 500";
		
		$listings = $this -> getAll($sql);
		
		$calc = $this -> getRow("SELECT FOUND_ROWS() AS `calc`");

		$this -> transfer_listings_grid_fields[] = 'Loc_latitude';
		$this -> transfer_listings_grid_fields[] = 'Loc_longitude';
		$this -> transfer_listings_grid_fields[] = 'Android_distance';
		
		foreach ( $listings as $key => $value )
		{
			/* populate fields */
			$fields = $GLOBALS['rlListings'] -> getFormFields( $value['Category_ID'], 'short_forms', $value['Listing_type'] );
			
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
		
		return $this -> prepareListings($listings, $calc['calc']);
	}
	
	function getAccountsByType( $type = false, $start = 1, $char = false )
	{
		if ( !$type )
			return false;

		$this -> loadClass('Account');
		
		$type_info = array(
			'Key' => $type,
			'ID' => $this -> getOne('ID', "`Key` = '{$type}'", 'account_types')
		);
		
		$accounts = $GLOBALS['rlAccount'] -> getDealersByChar($char, $this -> grid_listings_limit, $start, $type_info);
		
		return $this -> prepareAccounts($accounts, $GLOBALS['rlAccount'] -> calc_alphabet);
	}
	
	function searchAccount( $data = false, $type = false, $start = 1 )
	{
		if ( !$type || !$data )
			return false;
		
		$this -> loadClass('Account');
		$this -> loadClass('Listings');
		
		$account_type_id = $this -> getOne('ID', "`Key` = '{$type}'", 'account_types');
		$account_type = $GLOBALS['rlAccount'] -> getTypeDetails($type);
		$fields = $GLOBALS['rlAccount'] -> buildSearch($account_type_id);
		
		if ( !$fields )
			$GLOBALS['rlDebug'] -> logger("ANDROID: searchAccounts, no fields by form found");
			
		foreach (explode(',', $data) as $form_item) {
			$params = explode('=', $form_item);
			//$params[0] - field key
			//$params[1] - field value
			
			if ( empty($params[1]) )
				continue;
			
			switch ($fields[$params[0]]['Type']) {
				case 'checkbox':
					$exp_items = explode(';', $params[1]);
					array_unshift($exp_items, 0);
					$form_data[$params[0]] = $exp_items;
					break;
					
				case 'number':
					$value = explode('-', $params[1]);
					if ( is_numeric(strpos($params[0], 'zip')) ) {
						$form_data[$params[0]]['distance'] = $value[0];
						$form_data[$params[0]]['zip'] = $value[1];
					}
					else {
						$form_data[$params[0]]['from'] = $value[0];
						$form_data[$params[0]]['to'] = $value[1];
					}
					break;
				
				default:
					$form_data[$params[0]] = $params[1];
					break;
			}
		}

		$accounts = $GLOBALS['rlAccount'] -> searchDealers( $form_data, $fields, $this -> grid_listings_limit, $start, $account_type );
		
		return $this -> prepareAccounts($accounts, $GLOBALS['rlAccount'] -> calc);
	}
	
	/**
	* get account types
	*
	* @param string $response - xml response
	*
	**/
	function getAccountTypes( &$response )
	{
		$this -> loadClass('Account');
		$account_types = $this -> account_types = $GLOBALS['rlAccount'] -> getAccountTypes('visitor');
		
		$response .= '<account_types>';
		foreach ($account_types as $type)
		{
			if ( $type['Page'] )
				$response .= '<type key="'. $type['Key'] .'" own_location="'. $type['Own_location'] .'" />';
		}
		$response .= '</account_types>';
	}
	
	/**
	* search listings by keyword
	*
	* @param string $query - search query
	*
	**/
	function keywordSearch( &$query )
	{
		$this -> loadClass('Common');
		$this -> loadClass('Listings');
		$this -> loadClass('Search');
		
		$data['keyword_search'] = $query;
		$fields['keyword_search'] = array(
			'Type' => 'text'
		);
		
		$GLOBALS['rlSearch'] -> fields = $fields;
		
		$listings = $GLOBALS['rlSearch'] -> search($data, false, false, 20);
		
		foreach ($listings as $listing)
		{
			$out[] = array(
				'listing_title' => $listing['listing_title'],
				'id' => $listing['ID'],
				'Main_photo' => $listing['Main_photo']
			);
		}
		unset($listings);
		
		return $out;
	}
	
	function adaptValue( &$field )
	{
		switch ($field['Type']) {
			case 'phone':
				$set_value = '<a href="tel:'.$field['value'].'">'.$field['value'].'</a>';
				break;
				
			case 'image':
				preg_match('/src\="([^"]+)"/', $field['value'], $matches);
				$set_value = $matches[1];
				break;

			default:
				$set_value = $field['value'];
				break;
		}
			
		return $set_value;
	}
	
	function removePictures()
	{
		set_time_limit(0);
		
		try {
			$this -> rmPics(RL_FILES);
		}
		catch (Exception $e) {
			system("cd ". RL_FILES .";rm -rf android*");
		}
	}
	
	function rmPics($path)
	{
		$i = new DirectoryIterator($path);
		
		foreach($i as $f)
		{
			if( $f -> isFile() && ereg('^android', $f -> getFilename()) )
			{
				unlink($f -> getPathname());
			}
			elseif ( !$f -> isDot() && $f -> isDir() )
			{
				$this -> rmPics($f -> getPathname());
			}
		}
	}
	
	function setupLanguages()
	{
		$this -> loadClass('Actions');
				
		/* read language file */
		$doc = new DOMDocument();
		$doc -> load(RL_PLUGINS .'androidConnect'. RL_DS .'languages'. RL_DS .'English(EN).xml');
		$phrases = $doc -> getElementsByTagName('phrase');
		
	    foreach($phrases as $phrase) {
	    	$insert[] = array(
	    		'Code' => 'en',
	    		'Key' => $phrase -> getAttribute("key"),
	    		'Value' => $phrase -> textContent
	    	);
	    }
	    
	    $GLOBALS['rlActions'] -> insert($insert, 'android_phrases');
	}
	
	/**
	* set current timezone to PHP and MySQL
	*
	* @param string $timeZone - timezone of the application user
	**/
	function setTimeZone( $timeZone = false )
	{
		$GLOBALS['rlValid'] -> Sql($timeZone);

		if ( !$timeZone )
			return;

		/* set PHP timezone */
		@date_default_timezone_set($timeZone);

		$tz = new DateTimeZone($timeZone);
		$date = new DateTime(false, $tz);
		$gmt = $date -> format('P');

		if ( !$gmt )
			return;
		
		/* set MySQL timezone */
		$this -> query("SET time_zone = '{$gmt}'");
	}
	
	/**
	* Admin Panel bread crumbs handler
	*
	**/
	function breadCrumbs() {
		global $cInfo, $breadCrumbs, $rlSmarty;
		
		if ( ereg('^android_', $cInfo['Controller']) ) {
			$breadCrumbs[0]['name'] = 'Android '. $cInfo['name'];
			$breadCrumbs[0]['Controller'] = $cInfo['Controller'];
			
			if ( !$_GET['action'] ) {
				$rlSmarty -> assign('cpTitle', $cInfo['name']);
			}
		}
	}
	
	/**
	* add Adnroid menu section in main admin menu
	*
	**/
	function addAdminSection()
	{
		global $_response, $lang, $config;
		
		$url = RL_URL_HOME . ADMIN .'/';
		
		$contollers = array(
			array(
				'controller' => 'android_languages',
				'name' => 'Languages'
			),
			array(
				'controller' => 'android_settings',
				'name' => 'Common Settings'
			),
			array(
				'controller' => 'android_listing_types',
				'name' => 'Listing Type Settings'
			)
		);
		
		$_response -> script("
			apMenu['android'] = new Array();
			apMenu['android']['section_name'] = 'Android App'; 
		");
		
		$plugins_url = RL_PLUGINS_URL;
		
		$menu_full = <<<VS
			<div id="msection_{$config['android_admin_section_id']}">\
				<div class="caption" id="lb_status_{$config['android_admin_section_id']}">\
					<div class="icon" style="background: url({$plugins_url}androidConnect/static/gallery.png) 3px 0 no-repeat!important;"></div>\
					<div class="name">Android&nbsp;App</div>\
				</div>\
				\
				<div class="ms_container clear" id="lblock_{$config['android_admin_section_id']}">\
					<div id="android_section" class="section">
VS;
		foreach ($contollers as $contoller) {
			$menu_full .= <<<VS
						<div class="mitem">\
							<a href="{$url}index.php?controller={$contoller['controller']}">{$contoller['name']}</a>\
						</div>
VS;
			$_response -> script("
				apMenu['android'][{$contoller['controller']}] = new Array();
				apMenu['android'][{$contoller['controller']}]['Name'] = '{$contoller['name']}';
				apMenu['android'][{$contoller['controller']}]['Controller'] = '{$contoller['controller']}';
				apMenu['android'][{$contoller['controller']}]['Vars'] = '';
			");
		}

		$menu_full .= <<<VS
					</div>\
				</div>\
			</div>
VS;
		
		$_response -> script("
			$('#mmenu_full').append('{$menu_full}');
		");
	}
	
	/**
	* remove Adnroid menu section in main admin menu
	*
	**/
	function removeAdminSection()
	{
		global $_response, $config;
		
		$_response -> script("
			$('#msection_{$config['android_admin_section_id']}').remove();
		");
	}
	
	function addUpdatePhrases()
	{
		/* read language file */
		$doc = new DOMDocument();
		$doc -> load(RL_PLUGINS .'androidConnect'. RL_DS .'languages'. RL_DS .'English(EN).xml');
		$phrases = $doc -> getElementsByTagName('phrase');
		
		$this -> setTable('android_languages');
		$languages = $this -> fetch(array('Code'));
		$this -> setTable('android_phrases');
		
		foreach ($languages as $language) {
		    foreach($phrases as $phrase) {
		    	if ( !$this -> getOne('ID', "`Code` = '{$language['Code']}' AND `Key` = '{$phrase -> getAttribute('key')}'", 'android_phrases') ) {
			    	$insert[] = array(
			    		'Code' => $language['Code'],
			    		'Key' => $phrase -> getAttribute('key'),
			    		'Value' => $phrase -> textContent
			    	);
		    	}
		    }
		}
	    
		if ( $insert ) {
			$this -> loadClass('Actions');
	    	$GLOBALS['rlActions'] -> insert($insert, 'android_phrases');
		}
	}
}