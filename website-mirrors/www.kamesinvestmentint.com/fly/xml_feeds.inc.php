<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: XML_FEEDS.INC.PHP
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

/* ext js action */
if ($_GET['q'] == 'ext_feeds')
{
	/* system config */
	require_once( '../../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
	require_once( RL_LIBS . 'system.lib.php' );
	
	/* date update */
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );

		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br($_GET['value']) );
		$id = $rlValid -> xSql( $_GET['id'] );
		$key = $rlValid -> xSql( $_GET['key'] );

		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);
		
		$rlActions -> updateOne( $updateData, 'xml_feeds');
	}
	
	/* data read */
	$limit = (int)$_GET['limit'];
	$start = (int)$_GET['start'];
	$sort = $rlValid -> xSql( $_GET['sort'] );
	$sortDir = $rlValid -> xSql( $_GET['dir'] );
	$key = $rlValid -> xSql( $_GET['key'] );

	
	/* run filters */
	$filters = array(		
		'f_Account' => true,
		'f_format' => true
	);

	$rlHook -> load('apExtListingsFilters');

	foreach ($_GET as $filter => $val)
	{
		if ( array_key_exists($filter, $filters) )
		{
			$filter_field = explode('f_', $filter);

			switch ($filter_field[1]){
				case 'Account':
					$where .= "AND `T3`.`Username` = '".$_GET[$filter]."' ";
					break;
				default:
					$where .= "AND `T1`.`".$filter_field[1]."` = '".$_GET[$filter]."' ";
					break;
			}
		}
	}
	

	$sql = "SELECT SQL_CALC_FOUND_ROWS  `T1`.*, `T2`.`Value` as `name`, `T3`.`Username` as `account` ";
	$sql .="FROM `".RL_DBPREFIX."xml_feeds` AS `T1` ";
	$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON CONCAT('xml_feeds+name+',`T1`.`Key`) = `T2`.`Key` AND `T2`.`Code` = '". RL_LANG_CODE ."' ";
	$sql .="LEFT JOIN `".RL_DBPREFIX."accounts` AS `T3` ON `T3`.`ID` = `T1`.`Account_ID` ";
	$sql .="WHERE `T1`.`Status` <> 'trash' ";

	if( $where )
	{
		$sql .=$where;
	}
	
	if ( $sort )
	{
		$sortField = $sort == 'name' ? "`T2`.`Value`" : "`T1`.`{$sort}`";
		$sql .= "ORDER BY {$sortField} {$sortDir} ";
	}

	$sql .= "LIMIT {$start},{$limit}";

	$data = $rlDb -> getAll( $sql );	

	foreach ( $data as $key => $value )
	{
		$data[$key]['Status'] = $lang[$value['Status']];
		$data[$key]['Format'] = $lang[ 'xml_formats+name+'.$data[$key]['Format'] ];
	}

	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;
	
	echo $rlJson -> encode( $output );
}
elseif ($_GET['q'] == 'ext_formats')
{
	/* system config */
	require_once( '../../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
	require_once( RL_LIBS . 'system.lib.php' );

	/* date update */
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );

		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br($_GET['value']) );
		$id = $rlValid -> xSql( $_GET['id'] );
		$key = $rlValid -> xSql( $_GET['key'] );

		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);
			
		
		$rlActions -> updateOne( $updateData, 'xml_formats');
	}
	
	/* data read */
	$limit = (int)$_GET['limit'];
	$start = (int)$_GET['start'];
	$sort = $rlValid -> xSql( $_GET['sort'] );
	$sortDir = $rlValid -> xSql( $_GET['dir'] );

	$sql = "SELECT SQL_CALC_FOUND_ROWS  `T1`.*, `T2`.`Value` as `name` ";
	$sql .= "FROM `". RL_DBPREFIX ."xml_formats` AS `T1` ";
	$sql .= "LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON CONCAT('xml_formats+name+',`T1`.`Key`) = `T2`.`Key` AND `T2`.`Code` = '". RL_LANG_CODE ."' ";
	$sql .= "WHERE `T1`.`Status` <> 'trash' ";

	if ( $sort )
	{
		$sortField = $sort == 'name' ? "`T2`.`Value`" : "`T1`.`{$sort}`";
		$sql .= "ORDER BY {$sortField} {$sortDir} ";
	}

	$sql .= "LIMIT {$start},{$limit}";

	$data = $rlDb -> getAll( $sql );

	foreach ( $data as $key => $value )
	{
		$data[$key]['Status'] = $lang[ $value['Status'] ];		
	}
	
	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;

	echo $rlJson -> encode( $output );
}
elseif ($_GET['q'] == 'ext_mapping')
{
	/* system config */
	require_once( '../../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
	require_once( RL_LIBS . 'system.lib.php' );

	/* date update */
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );

		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br($_GET['value']) );
		$id = $rlValid -> xSql( $_GET['id'] );
		$key = $rlValid -> xSql( $_GET['key'] );

		if( $field == 'Local_field_name' )
		{
			$field = 'Data_local';
		}
		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);

		$rlActions -> updateOne( $updateData, 'xml_mapping');
	}
	
	if( $rlDb -> getOne("Key", "`Key`='multiField'", "plugins") )
	{
		$multi_formats_tmp = $rlDb -> fetch(array("Key"), null, null, null, "multi_formats");

		foreach( $multi_formats_tmp as $k => $v )
		{
			$mfs = $v['Key'].",";
		}

		if( $mfs )
		{
			$mfs = substr($mfs, 0, -1);
		}
	}

	/* data read */
	$limit = (int)$_GET['limit'];
	$start = (int)$_GET['start'];
	$sortField = $_GET['sort'] ? $rlValid -> xSql( $_GET['sort'] ) : "ID";
	$sortDir = $_GET['dir'] ? $rlValid -> xSql( $_GET['dir'] ) : "ASC";

	$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.*, `T2`.`Type` AS `Local_field_type`, `T3`.`Value` AS `Local_field_name`, `T1`.`Format` AS `Format`, ";
	$sql .="IF(FIND_IN_SET(`T2`.`Condition`, '".$mfs."'), 1, '') as `Mf` ";
	$sql .= "FROM `". RL_DBPREFIX ."xml_mapping` AS `T1` ";
	$sql .= "LEFT JOIN `".RL_DBPREFIX."listing_fields` AS `T2` ON `T1`.`Data_local` = `T2`.`Key` ";
	$sql .= "LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T3` ON `T3`.`Key` = CONCAT('listing_fields+name+', `T2`.`Key`) AND `T3`.`Code` = '".RL_LANG_CODE."' ";
	$sql .= "WHERE `T1`.`Status` <> 'trash' ";

	if( $_GET['format'] )
	{
		$sql .="AND `T1`.`Format` = '".$_GET['format']."' ";
	}

	$sql .="AND `T1`.`Parent_ID` = 0 ";
	$sql .= "ORDER BY `Status` ASC, {$sortField} {$sortDir} ";
	$sql .= "LIMIT {$start},{$limit}";

	$data = $rlDb -> getAll( $sql );

	foreach ( $data as $key => $value )
	{
		$data[$key]['Status'] = $lang[ $value['Status'] ];
		if( is_numeric(strpos($value['Data_local'], 'category_')) && strpos($value['Data_local'], 'category_') == 0 )
		{
			$i = substr($value['Data_local'], -1, 1);
			$data[$key]['Local_field_name']  = $GLOBALS['lang']['category']." Level ".$i;
			$data[$key]['Local_field_type'] = "select";			
		}
		elseif( $value['Data_local'] == 'pictures' )
		{			
			$data[$key]['Local_field_name']  = $GLOBALS['lang']['xf_pictures'];			
		}
		elseif( $value['Data_local'] == 'pictures2' )
		{			
			$data[$key]['Local_field_name']  = $GLOBALS['lang']['xf_pictures2'];
		}
		elseif( $value['Data_local'] == 'back_url' )
		{			
			$data[$key]['Local_field_name']  = $GLOBALS['lang']['xf_back_url'];
		}
		elseif( is_numeric(strpos($value['Data_local'], "_unit")) )
		{
			preg_match("/^(.*)_unit$/smi", $value['Data_local'], $matches);
			if( $matches[1] )
			{
				$data[$key]['Local_field_name'] = $GLOBALS['lang']['listing_fields+name+'.$matches[1]]." ".$lang['xf_unit'];
			}
		}
		elseif( $value['Data_local'] == 'Date' )
		{
			$data[$key]['Local_field_name'] = $GLOBALS['lang']['date'];			
		}

		$data[$key]['Cdata'] = $data[$key]['Cdata'] ? $lang['yes'] : $lang['no'];
	}

	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;

	echo $rlJson -> encode( $output );
}
elseif ($_GET['q'] == 'ext_item_mapping')
{
	/* system config */
	require_once( '../../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
	require_once( RL_LIBS . 'system.lib.php' );

	/* date update */
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );

		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br($_GET['value']) );
		$id = $rlValid -> xSql( $_GET['id'] );
		$key = $rlValid -> xSql( $_GET['key'] );

		if( $field == 'Local_field_name' )
		{
			$field = 'Data_local';
		}

		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);

		$rlActions -> updateOne( $updateData, 'xml_mapping');
	}

	/* data read */
	$limit = (int)$_GET['limit'];
	$start = (int)$_GET['start'];
	$sort = $rlValid -> xSql( $_GET['sort'] );
	$sortDir = $rlValid -> xSql( $_GET['dir'] );

	if( trim($_GET['field']) == 'category' )
	{
		$field = $rlDb -> getOne("Data_remote", "`Data_local` = 'category_0' AND `Format` = '{$_GET['format']}'", "xml_mapping");		
	}
	elseif( is_numeric(strpos($_GET['field'], 'mf|')) )
	{
		$local = trim(str_replace('mf|','', $_GET['field'] ));
		$field = $rlDb -> getOne("Data_remote", "`Data_local` = '".$local."' AND `Format` = '{$_GET['format']}'", "xml_mapping");
	}
	else
	{
		$field = trim($_GET['field']);
	}

	if( $_GET['parent'] )
	{
		$parent_id = $_GET['parent'];
	}
	elseif( $field )
	{
		$sql = "SELECT `ID` FROM `".RL_DBPREFIX."xml_mapping` WHERE `Format` = '{$_GET['format']}' AND `Data_remote` = '{$field}'";
		$parent_id = $rlDb -> getRow( $sql );
		
		$parent_id = $parent_id['ID'];
	}

	if( $parent_id )
	{
	$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE `Parent_ID` = '{$parent_id}'";

	if ( $sort )
	{
		$sql .= "ORDER BY {$sortField} {$sortDir} ";
	}

	$sql .= "LIMIT {$start},{$limit}";

	$data = $rlDb -> getAll( $sql );
	}

	foreach ( $data as $key => $value )
	{
		$data[$key]['Status'] = $lang[ $value['Status'] ];

		if( $data[$key]['Data_local'] )
		{
			if( trim($_GET['field']) == 'category' )
			{
				$data[$key]['Data_local'] = $lang[ "categories+name+".$value['Data_local'] ];				
			}
			else
			{
				if( $lang[ "data_formats+name+".$value['Data_local'] ] )
				{
					$data[$key]['Data_local'] = $lang[ "data_formats+name+".$value['Data_local'] ];
				}
				elseif( $lang[ "listing_fields+name+".$value['Data_local'] ] )
				{
					$data[$key]['Data_local'] = $lang[ "listing_fields+name+".$value['Data_local'] ];
				}
				else
				{
					$data[$key]['Data_local'] = $rlDb -> getOne("Value", "`Key` = 'data_formats+name+".$value['Data_local']."'", "lang_keys");
				}				
			}
		}
	}

	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );
	
	$reefless -> loadClass( 'Json' );
	
	$output['total'] = $count['count'];
	$output['data'] = $data;

	echo $rlJson -> encode( $output );
}
/* ext js action end */

else 
{
	$rlListingTypes -> get(true);

	/* help section */
	if( !$_GET['action'] )
	{
		$format = $rlDb -> fetch( array('Key'), array( 'Status' => 'active' ) , "ORDER BY `Key`", null, 'xml_formats', "row" );

		if( !$format )
		{
			$info[] = $lang['xf_help_no_format'];
			$formats_mode = true;
		}
		elseif( !$rlDb -> getOne( "ID", "`Format` = '".$format['Key']."'", 'xml_mapping' ) )
		{
			$info[] = $lang['xf_help_no_mapping'];
		}
				
		if( $formats_mode && $_GET['mode'] != 'formats' )
		{
			$aUrl = array( "controller" => $controller, 'mode' => 'formats' );
			$reefless -> redirect($aUrl);
		}
		$rlSmarty -> assign('formats_mode', $formats_mode);
	}
	elseif( $_GET['action'] == 'export' )
	{
		$info[] = $lang['xf_help_export'];		
	}
	elseif( $_GET['action'] == 'mapping' && $_GET['field'] )
	{
		$info[] = $lang['xf_help_select_field_mapping'];

		if( $_GET['field'] == 'category' )
	{
		$info[] = $lang['xf_help_category_mapping'];
	}
	}	

	$rlSmarty -> assign("info", $info);

	/* additional bread crumb step */
	switch ($_GET['action']){
		case 'add_feed':
			$bcAStep = $lang['xf_add_feed'];
			break;
		case 'add_format':
			$bcAStep = $lang['xf_add_format'];
			break;
		case 'add_user':
			$bcAStep = $lang['xf_add_user'];
			break;
		case 'edit_feed':
			$bcAStep = $lang['xf_edit_feed'];
			break;
		case 'edit_format':
			$bcAStep = $lang['xf_edit_format'];
			break;
		case 'edit_user':
			$bcAStep = $lang['xf_edit_user'];
			break;
		case 'import_file':
			$bcAStep = $lang['xf_import_file'];
			break;
		case 'export':
			$bcAStep = $lang['xf_export'];
			break;
		case 'mapping':
			$bread_crumbs[0]['Controller'] = 'xml_feeds';
			$bread_crumbs[0]['Vars'] = 'mode=formats';
			$bread_crumbs[0]['name'] = $lang['xf_manage_formats'];

			$bread_crumbs[1]['name'] = str_replace( '{format}', $lang['xml_formats+name+'.$_GET['format']], $lang['xf_mapping_of_format'] );

			if( $_GET['field'] )
			{
				$bread_crumbs[1]['Controller'] = 'xml_feeds';
				$bread_crumbs[1]['Vars'] = 'mode=formats&amp;action=mapping&amp;format='.$_GET['format'];

				if( $_GET['parent'] )
				{
					$bread_crumbs[1]['Vars'] .="&field=".$_GET['field'];
				}

				if( is_numeric(strpos($_GET['field'], 'mf|')) )
				{
					$field_name = $lang['listing_fields+name+'.str_replace('mf|','', $_GET['field'])];
				}else
				{
					$field_name = ucwords($_GET['field']);
				}
				
				$bread_crumbs[2]['name'] = str_replace( '{field}', $field_name, $lang['xf_mapping_of_field'] );

				if( $_GET['parent'] )
				{
					$bread_crumbs[2]['Controller'] = 'xml_feeds';
					$bread_crumbs[2]['Vars'] = 'mode=formats&amp;action=mapping&amp;format='.$_GET['format'].'&field='.$_GET['field'];

					$parent_item_info = $rlDb -> fetch("*", array('ID' => $_GET['parent']), null, null, "xml_mapping", "row" );				
					$bread_crumbs[3]['name'] = $parent_item_info['Data_remote'];
				}
			}

			$bcAStep = $bread_crumbs;
			break;
	}

	if( !$_GET['action'] )
	{
		$mode = $_GET['mode'] ? $_GET['mode'] : 'feeds';
	
		switch ( $mode ){
			case 'feeds':
				$bcAStep = $lang['xf_manage_feeds'];
			break;
			case 'formats':
				$bcAStep = $lang['xf_manage_formats'];
			break;
			case 'users':
				$bcAStep = $lang['xf_manage_users'];
			break;
		}
	}

	$reefless -> loadClass('XmlFeeds', null, 'xmlFeeds');

	if ( $_GET['action'] == 'add_feed' || $_GET['action'] == 'edit_feed' )
	{
		/* get all languages */
		$allLangs = $GLOBALS['languages'];
		$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );
		
		$formats = $rlDb -> fetch( array('Key'), array( 'Status' => 'active' ), "AND FIND_IN_SET('import', `Format_for`) ORDER BY `Key`", null, 'xml_formats' );
		$formats = $rlLang -> replaceLangKeys( $formats, 'xml_formats', 'name', RL_LANG_CODE, 'admin' );
		$rlSmarty -> assign_by_ref('formats', $formats);

		$reefless -> loadClass( "Account" );
		$account_types = $rlAccount -> getAccountTypes();
		$rlSmarty -> assign( "account_types", $account_types );		
		
		/* get accounts */
		$accounts_list = $rlDb -> fetch(array('ID', 'Username'), array('Status' => 'active'), null, null, 'accounts');
		$rlSmarty -> assign_by_ref('accounts', $accounts_list);

		$plans = $rlDb -> fetch( array('ID', 'Key'), array( 'Status' => 'active' ) , null, null, 'listing_plans' );
		$plans = $rlLang -> replaceLangKeys( $plans, 'listing_plans', 'name', RL_LANG_CODE, 'admin' );
		$rlSmarty -> assign_by_ref('plans', $plans);

		if ( $_GET['action'] == 'edit_feed' && !$_POST['fromPost'] )
		{
			$f_key = $rlValid -> xSql( $_GET['feed'] );
			$item_info = $rlDb -> fetch( "*", array( 'Key' => $f_key ), "AND `Status` <> 'trash'", null, 'xml_feeds', 'row' );
		}

		$listing_type = $item_info['Listing_type'] ? $rlListingTypes -> types[ $item_info['Listing_type'] ] : current($rlListingTypes -> types);
		$listing_type = $listing_type['Key'];		

		$rlSmarty -> assign('listing_types', $rlListingTypes -> types);
		$rlSmarty -> assign('listing_type', $listing_type);

		$categories = $rlCategories -> getCategories(0, $listing_type);		
		$rlSmarty -> assign( 'categories', $categories );
		
		$rlXajax -> registerFunction( array( 'loadCategories', $rlXmlFeeds, 'ajaxLoadCategories' ) );
		$rlXajax -> registerFunction( array( 'buildCategories', $rlXmlFeeds, 'ajaxBuildCategories' ) );		

		if ( $_GET['action'] == 'edit_feed' && !$_POST['fromPost'] )
		{			
			$_POST['key'] = $item_info['Key'];
			$_POST['url'] = $item_info['Url'];
			$_POST['format'] = $item_info['Format'];
			$_POST['plan_id'] = $item_info['Plan_ID'];
			$_POST['feed_type'] = $item_info['Feed_type'];
			$_POST['account_type'] = $item_info['Feed_account_type'];
			$_POST['listings_status'] = $item_info['Listings_status'];
			$_POST['listing_type'] = $item_info['Listing_type'];
			$_POST['account_id'] = $item_info['Account_ID'];
			$_POST['default_category'] = $item_info['Default_category'];
			$_POST['status'] = $item_info['Status'];

			$names = $rlDb -> fetch( array( 'Code', 'Value' ), array( 'Key' => 'xml_feeds+name+'.$f_key ), "AND `Status` <> 'trash'", null, 'lang_keys' );
			foreach ($names as $nKey => $nVal)
			{
				$_POST['name'][$names[$nKey]['Code']] = $names[$nKey]['Value'];
			}
		}
		
		if ( isset($_POST['submit']) )
		{
			$errors = array();
			
			/* load the utf8 lib */
			loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

			if ( $_GET['action'] == 'add_feed' || $_GET['action'] == 'edit_feed')
			{
				$f_key = $_POST['name'][$config['lang']];				

				if ( !utf8_is_ascii( $f_key ) )
				{
					$f_key = utf8_to_ascii( $f_key );
				}
				$f_key = $rlValid -> str2key( $f_key );
				$f_xpath = trim( $_POST['xpath'], "/" );

				$f_url = $_POST['url'];
				$f_format = $_POST['format'];

				if ( !$rlValid -> isUrl( $f_url ) )
				{
					$errors[] = $lang['xf_notice_url_incorrect'];
					$error_fields[] = 'url';
				}

				if( empty($f_format) )
				{	
					$errors[] = $lang['xf_notice_format_empty'];
					$error_fields[] = 'format';					
				}

				if( empty($_POST['plan_id']) )
				{
					$errors[] = str_replace('{field}', '<b>'.$lang['xf_plan'].'</b>', $lang['notice_field_empty']);
					$error_fields[] = 'plan_id';
				}

				if( empty($_POST['default_category']) )
				{
					$errors[] = str_replace('{field}', '<b>'.$lang['xf_default_category'].'</b>', $lang['notice_field_empty']);
					$error_fields[] = 'default_category';
				}

				if( empty($_POST['listing_type']) && count($rlListingTypes -> types) > 1 )
				{
					$errors[] = str_replace('{field}', '<b>'.$lang['xf_listing_type'].'</b>', $lang['notice_field_empty']);
					$error_fields[] = 'listing_type';
				}
				
				if( $_GET['action'] == 'add_feed' )
				{
					/* check key exist (in add mode only) */
					if ( strlen( $f_key ) < 2 )
					{
						$errors[] = $lang['incorrect_phrase_key'];
						$error_fields[] = 'key';
					}
						
					$exist_key = $rlDb -> fetch( array('Key'), array( 'Key' => $f_key ), null, null, 'xml_feeds' );
					if ( !empty($exist_key) )
					{
						$errors[] = str_replace( '{key}', "<b>\"".$f_key."\"</b>", $lang['notice_key_exist']);
						$error_fields[] = 'key';
					}

					$exist_url = $rlDb -> fetch( array('ID'), array( 'Url' => $f_url ), null, null, 'xml_feeds' );
					if ( !empty($exist_key) )
					{
						$errors[] = $lang['xf_notice_url_exist'];
						$error_fields[] = 'url';
					}
				}

				/* check names */
				$f_name = $_POST['name'];
					
				foreach( $allLangs as $lkey => $lval )
				{
					if ( empty( $f_name[$allLangs[$lkey]['Code']] ) )
					{
						$errors[] = str_replace( '{field}', "<b>".$lang['name']."({$allLangs[$lkey]['name']})</b>", $lang['notice_field_empty']);
						$error_fields[] = "name[{$lval['Code']}]";
					}
				}
			}

			if( !empty($errors) )
			{
				$rlSmarty -> assign_by_ref( 'errors', $errors );
			}
			else 
			{
				/* add/edit action */
				if ( $_GET['action'] == 'add_feed' )
				{
					$data = array(
						'Key' => $f_key,
						'Url' => $f_url,						
						'Plan_ID' => $_POST['plan_id'],
						'Default_category' => $_POST['default_category'],
						'Format' => $f_format,
						'Feed_type' => $_POST['feed_type'],
						'Feed_account_type' => $_POST['account_type'],
						'Listings_status' => $_POST['listings_status'],
						'Listing_type' => $_POST['listing_type'],
						'Status' => $_POST['status'],
						'Account_ID' => $_POST['account_id']
					);

					if ( $action = $rlActions -> insertOne( $data, 'xml_feeds' ) )
					{
						foreach ($allLangs as $key => $value)
						{
							$lang_keys[] = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Plugin' => 'xmlFeeds',
								'Key' => 'xml_feeds+name+' . $f_key,
								'Value' => $f_name[$allLangs[$key]['Code']],
							);
						}

						$rlActions -> insert( $lang_keys, 'lang_keys' );

						$message = $lang['notice_item_added'];
						$aUrl = array( "controller" => $controller, 'mode' => 'feeds' );
					}else
					{
						trigger_error( "Can't add new data format (MYSQL problems)", E_WARNING );
						$rlDebug -> logger("Can't add new data format (MYSQL problems)");
					}
				}
				elseif ( $_GET['action'] == 'edit_feed' )
				{
					$f_key = $_GET['feed'];					

					$update_data = array('fields' => array(
							'Status' => $_POST['status'],
							'Format' => $f_format,
							'Feed_type' => $_POST['feed_type'],
							'Feed_account_type' => $_POST['account_type'],
							'Listings_status' => $_POST['listings_status'],
							'Default_category' => $_POST['default_category'],
							'Listing_type' => $_POST['listing_type'],
							'Url' => $f_url,
							'Plan_ID' => $_POST['plan_id'],
							'Default_category' => $_POST['default_category'],
							'Account_ID' => $_POST['account_id']
						),
						'where' => array( 'Key' => $f_key )
					);

					if( $action = $GLOBALS['rlActions'] -> updateOne( $update_data, 'xml_feeds' ) )
					{						
						foreach( $allLangs as $key => $value )
						{
							if ( $rlDb -> getOne('ID', "`Key` = 'xml_feeds+name+{$f_key}' AND `Code` = '{$allLangs[$key]['Code']}'", 'lang_keys') )
							{
								// edit name's values
								$update_names = array(
									'fields' => array(
										'Value' => $_POST['name'][$allLangs[$key]['Code']]
									),
									'where' => array(
										'Code' => $allLangs[$key]['Code'],
										'Key' => 'xml_feeds+name+' . $f_key
									)
								);
								
								// update
								$rlActions -> updateOne( $update_names, 'lang_keys' );
							}
							else
							{
								// insert names
								$insert_names = array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Key' => 'xml_feeds+name+' . $f_key,
									'Value' => $_POST['name'][$allLangs[$key]['Code']]
								);
								
								// insert
								$rlActions -> insertOne( $insert_names, 'lang_keys' );
							}											
						}

						$message = $lang['notice_item_edited'];
						$aUrl = array( "controller" => $controller );
					}
				}
				
				if ( $action )
				{
					$reefless -> loadClass( 'Notice' );
					$rlNotice -> saveNotice( $message );
					$reefless -> redirect( $aUrl );
				}
				else 
				{
					trigger_error( "Can't edit datafomats (MYSQL problems)", E_WARNING );
					$rlDebug -> logger("Can't edit datafomats (MYSQL problems)");
				}
			}
		}
	}
	elseif ( $_GET['action'] == 'add_format' || $_GET['action'] == 'edit_format' )
	{
		/* get all languages */
		$allLangs = $GLOBALS['languages'];
		$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );
		
		if ( $_GET['action'] == 'edit_format' && !$_POST['fromPost'] )
		{
			$f_key = $rlValid -> xSql( $_GET['format'] );
			
			$item_info = $rlDb -> fetch( "*", array( 'Key' => $f_key ), "AND `Status` <> 'trash'", null, 'xml_formats', 'row' );

			$_POST['key'] = $item_info['Key'];			
			$_POST['status'] = $item_info['Status'];
			$_POST['xpath'] = $item_info['Xpath'];
			$format_for = explode(",", $item_info['Format_for']);
			$_POST['format_for'] = $format_for;

			$names = $rlDb -> fetch( array( 'Code', 'Value' ), array( 'Key' => 'xml_formats+name+'.$f_key ), "AND `Status` <> 'trash'", null, 'lang_keys' );
			foreach ($names as $nKey => $nVal)
			{
				$_POST['name'][$names[$nKey]['Code']] = $names[$nKey]['Value'];
			}
		}
		
		if ( isset($_POST['submit']) )
		{
			$errors = array();
			
			/* load the utf8 lib */
			loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

			if ( $_GET['action'] == 'add_format' )
			{
				$f_key = $_POST['name'][$config['lang']];

				if ( !utf8_is_ascii( $f_key ) )
				{
					$f_key = utf8_to_ascii( $f_key );
				}
				$f_key = $rlValid -> str2key( $f_key );
				
				/* check key exist (in add mode only) */
				if ( strlen( $f_key ) < 2 )
				{
					$errors[] = $lang['incorrect_phrase_key'];
					$error_fields[] = 'key';
				}
					
				$exist_key = $rlDb -> fetch( array('Key'), array( 'Key' => $f_key ), null, null, 'xml_formats' );
				if ( !empty($exist_key) )
				{
					$errors[] = str_replace( '{key}', "<b>\"".$f_key."\"</b>", $lang['notice_key_exist']);
					$error_fields[] = 'key';
				}

				/* check names */
				$f_name = $_POST['name'];
				
				foreach( $allLangs as $lkey => $lval )
				{
					if ( empty( $f_name[$allLangs[$lkey]['Code']] ) )
					{
						$errors[] = str_replace( '{field}', "<b>".$lang['name']."({$allLangs[$lkey]['name']})</b>", $lang['notice_field_empty']);
						$error_fields[] = "name[{$lval['Code']}]";
					}
				}
			}

			if( !$_POST['format_for'] )
			{
				$errors[] = str_replace('{field}', '<b>'.$lang['xf_format_for'].'</b>', $lang['notice_field_empty']);
				$error_fields[] = 'format';
			}
			else
			{				
				$format_for = implode(",", array_values($_POST['format_for']));
			}

			$f_xpath = trim($_POST['xpath'], "/");
			if( empty($f_xpath) )
			{
				$errors[] = str_replace('{field}', '<b>'.$lang['xf_xpath'].'</b>', $lang['notice_field_empty']);
				$error_fields[] = 'xpath';
			}			

			if( !empty($errors) )
			{
				$rlSmarty -> assign_by_ref( 'errors', $errors );
			}
			else 
			{	
				/* add/edit action */
				if ( $_GET['action'] == 'add_format' )
				{
					$data = array(
						'Key' => $f_key,
						'Status' => $_POST['status'],
						'Format_for' => $format_for,
						'Xpath' => $f_xpath						
					);

					if ( $action = $rlActions -> insertOne( $data, 'xml_formats' ) )
					{
						foreach ($allLangs as $key => $value)
						{
							$lang_keys[] = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',								
								'Status' => 'active',
								'Plugin' => 'xmlFeeds',
								'Key' => 'xml_formats+name+' . $f_key,
								'Value' => $f_name[$allLangs[$key]['Code']],
							);
						}

						$rlActions -> insert( $lang_keys, 'lang_keys' );
					
						$href = "index.php?controller=xml_feeds&action=mapping&format=".$f_key;
						$link = '<a href="'. $href .'">$1</a>';
						$message = preg_replace( '/\[(.+)\]/', $link, $lang['xf_added_need_build'] );
						
						$aUrl = array( "controller" => $controller, 'mode' => 'formats' );
					}else
					{
						trigger_error( "Can't add new data format (MYSQL problems)", E_WARNING );
						$rlDebug -> logger("Can't add new data format (MYSQL problems)");
					}
				}
				elseif ( $_GET['action'] == 'edit_format' )
				{
					$f_key = $_GET['format'];
							
					$update_data = array('fields' => array(
							'Status' => $_POST['status'],
							'Format_for' => $format_for,
							'Xpath' => $f_xpath
						),
						'where' => array('Key' => $f_key)
					);
					
					if( $action = $GLOBALS['rlActions'] -> updateOne( $update_data, 'xml_formats' ) )
					{
						foreach( $allLangs as $key => $value )
						{
							if ( $rlDb -> getOne('ID', "`Key` = 'xml_formats+name+{$f_key}' AND `Code` = '{$allLangs[$key]['Code']}'", 'lang_keys') )
							{
								// edit name's values
								$update_names = array(
									'fields' => array(
										'Value' => $_POST['name'][$allLangs[$key]['Code']]
									),
									'where' => array(
										'Code' => $allLangs[$key]['Code'],
										'Key' => 'xml_formats+name+' . $f_key
									)
								);
								
								// update
								$rlActions -> updateOne( $update_names, 'lang_keys' );
							}
							else
							{
								// insert names
								$insert_names = array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Plugin' => 'xmlFeeds',
									'Key' => 'xml_formats+name+' . $f_key,
									'Value' => $_POST['name'][$allLangs[$key]['Code']]
								);
								
								// insert
								$rlActions -> insertOne( $insert_names, 'lang_keys' );
							}							
						}
						$message = $lang['notice_item_edited'];
						$aUrl = array( "controller" => $controller, 'mode' => 'formats' );
					}
				}

				if ( $action )
				{
					$reefless -> loadClass( 'Notice' );
					$rlNotice -> saveNotice( $message );
					$reefless -> redirect( $aUrl );
				}
				else 
				{
					trigger_error( "Can't edit datafomats (MYSQL problems)", E_WARNING );
					$rlDebug -> logger("Can't edit datafomats (MYSQL problems)");
				}
			}
		}
	}
	elseif ( $_GET['action'] == 'statistics' )
	{
		if( !$_POST['xjxfun'] )
		{
			unset($_SESSION['xmlFeedsImport']);			
		}
		
		$feed = $_GET['feed'];

		$feed_info = $rlDb -> fetch("*", array("Key" => $feed), null, null, "xml_feeds", "row");

		$feed_info['Format_name'] = $lang['xml_formats+name+'.$feed_info['Format']];
		
		$plan_key = $rlDb -> getOne("Key", "`ID` = '".$feed_info['Plan_ID']."'", "listing_plans");
		$feed_info['Plan_name'] = $lang['listing_plans+name+'.$plan_key];

		$feed_info['Username'] = $rlDb -> getOne("Username", "`ID` = '".$feed_info['Account_ID']."'", "accounts");
		
		$category_key = $rlDb -> getOne("Key", "`ID` = '".$feed_info['Default_category']."'", "categories");
		$feed_info['Category_name'] = $lang['categories+name+'.$category_key];
		$rlSmarty -> assign('feed_info', $feed_info);

		$sql = "SELECT *, `T1`.`Key` as `Key` FROM `".RL_DBPREFIX."xml_formats` AS `T1` ";
		$sql .="JOIN `".RL_DBPREFIX."xml_feeds` AS `T2` ON `T2`.`Format` = `T1`.`Key` ";
		$sql .="WHERE `T2`.`Key` = '".$feed."'";

		$format_info = $rlDb -> getRow($sql);
		$rlSmarty -> assign('format_info', $format_info);

		
		$sql = "SELECT `T1`.*, `T2`.`Username`, `T3`.`Value` as `Feed_name` FROM `".RL_DBPREFIX."xml_statistics` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."accounts` AS `T2` ON `T2`.`ID` = `T1`.`Account_ID` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T3` ON `T3`.`Key` = CONCAT('xml_feeds+name+', `T1`.`Feed`) AND `T3`.`Code` = '". RL_LANG_CODE ."'";
		$sql .= "WHERE `T1`.`Feed` = '{$feed}'";

		if($_GET['account_id'])
		{
			$sql .= "AND `T1`.`Account_ID` = '{$_GET['account_id']}'";
			
			$account_username = $rlDb -> getOne( 'Username', '`ID` = '.$_GET['account_id'], 'accounts' );
			$rlSmarty -> assign( 'account_username', $account_username );
		}
		$data = $rlDb -> getAll( $sql );

		$bcAStep = str_replace( '[feed]', "<b>". $lang['xml_feeds+name+'.$_GET['feed']] ."</b>", $lang['xf_stats_bc'] );
		
		if( $_GET['account_id'] )
		{
			$bcAStep .=str_replace( '[username]', "<b>". $account_username ."</b>", $lang['xf_stats_bc_account'] );;
		}

		foreach( $data as $key => $value )
		{
			if( $value['Listings_updated'] )
			{
				$updated = explode(",", $value['Listings_updated']);
				foreach($updated as $lk => $listing)
				{
					$link = RL_URL_HOME . ADMIN ."/index.php?controller=listings&action=view&id=".$listing;
					$updated[$lk] = '<a href="'. $link .'">'. $listing .'</a>';
				}
				$data[$key]['Listings_updated'] = $updated;
				$data[$key]['Count_updated'] = count($updated);
			}

			if( $value['Listings_inserted'] )
			{
				$inserted = explode(",", $value['Listings_inserted']);
				foreach($inserted as $lk => $listing)
				{
					$link = RL_URL_HOME . ADMIN ."/index.php?controller=listings&action=view&id=".$listing;
					$inserted[$lk] = '<a href="'. $link .'">'. $listing .'</a>';
				}
				$data[$key]['Listings_inserted'] = $inserted;
				$data[$key]['Count_inserted'] = count($inserted);
			}
		}

		$rlSmarty -> assign('statistics', $data);

		$rlXajax -> registerFunction( array( 'clearStatistics', $rlXmlFeeds, 'ajaxClearStatistics' ) );
		$rlXajax -> registerFunction( array( 'runFeed', $rlXmlFeeds, 'ajaxRunFeed' ) );
		$rlXajax -> registerFunction( array( 'performImport', $rlXmlFeeds, 'ajaxPerformImport' ) );
		
	}
	elseif( $_GET['action'] == 'manual_import' && ($_GET['feed'] || $_GET['file']) )
	{		
		set_time_limit(0);
		echo '<link href="'.RL_PLUGINS_URL.'xmlFeeds/static/import_progress.css" type="text/css" rel="stylesheet" />';
		
		$reefless -> loadClass('XmlImport', null, 'xmlFeeds');

		include(RL_PLUGINS."xmlFeeds".RL_DS."import.php");
		exit;
	}
	elseif( $_GET['action'] == 'mapping' && $_GET['format'] )
	{
		if( $_GET['field'] )
		{
			$sql ="SELECT `T1`.`Data_local`, `T2`.* FROM `".RL_DBPREFIX."xml_mapping` AS `T1` ";
			$sql .="LEFT JOIN `".RL_DBPREFIX."listing_fields` AS `T2` ON `T2`.`Key` = `T1`.`Data_local` ";
			$sql .="WHERE ";

			if( $_GET['field'] == 'category' )
			{
				$sql .="`T1`.`Data_local` = 'category_0' ";
			}
			elseif( is_numeric(strpos($_GET['field'], 'mf|')) )
			{
				$sql .="`T1`.`Data_local` = '".str_replace('mf|','', $_GET['field'] )."' ";
				$rlSmarty -> assign('mf_field', true);
				$mf_field = true;
			}
			else
			{
				$sql .="`T1`.`Data_remote` = '".$_GET['field']."' ";
			}
			$local_field_info =  $rlDb -> getRow( $sql );

			$rlSmarty -> assign('local_field_info', $local_field_info);

			preg_match('#category_(\d)#',$local_field_info['Data_local'], $match);

			if( $match )
			{
				if( $_GET['parent'] )
				{
					$sql ="SELECT `T2`.`ID`, `T2`.`Type` FROM `".RL_DBPREFIX."xml_mapping` AS `T1` ";
					$sql .="JOIN `".RL_DBPREFIX."categories` AS `T2` ON `T2`.`Key` = `T1`.`Data_local` ";
					$sql .="WHERE `T1`.`ID` = ".$_GET['parent'];

					$cat_info = $rlDb -> getRow($sql);
				}
				else
				{
					$sql ="SELECT `T3`.`ID`, `T3`.`Type` FROM `".RL_DBPREFIX."xml_mapping` AS `T1` ";
					$sql .="JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Value` = `T1`.`Default` ";
					$sql .="JOIN `".RL_DBPREFIX."categories` AS `T3` ON `T2`.`Key` = CONCAT('categories+name+', `T3`.`Key` ) ";
					$sql .="WHERE `T1`.`Data_local` = 'category_".$match[1]."' AND `T1`.`Default` != '' ";

					$cat_info = $rlDb -> getRow($sql);					
				}

				$parent_id = $cat_info['ID'] ? $cat_info['ID'] : 0;

				$cats_tree = $rlCategories -> getCatTree( $parent_id );

				foreach( $cats_tree as $key => $value )
				{
					$local_values[$key]['Key'] = $value['Key'];
					$local_values[$key]['name'] = $value['name'];
					$local_values[$key]['Level'] = $value['Level'];
				}

				$rlSmarty -> assign('local_values', $local_values);
			}
			elseif( $mf_field )
			{
				$mfield_info = $rlDb -> getRow($sql);

				if( $_GET['parent'] )
				{
					$sql ="SELECT `T2`.`Key` FROM `".RL_DBPREFIX."xml_mapping` AS `T1` ";
					$sql .="JOIN `".RL_DBPREFIX."data_formats` AS `T2` ON `T2`.`Key` = `T1`.`Data_local` ";
					$sql .="WHERE `T1`.`ID` = ".$_GET['parent'];

					$item_info = $rlDb -> getRow($sql);
				}
				
				$reefless -> loadClass('MultiField', null, 'multiField');
				$data = $rlMultiField -> getMDF( $item_info ? $item_info['Key'] : $local_field_info['Condition'] );			

				foreach( $data as $key => $value )
				{
					$local_values[$key]['Key'] = $value['Key'];
					$local_values[$key]['name'] = $value['name'];					
				}

				$rlSmarty -> assign('local_values', $local_values);
			}
			else
			{
				$local_values_tmp = $rlCommon -> fieldValuesAdaptation( array(0 => $local_field_info), "listing_fields" );

				foreach( $local_values_tmp[0]['Values'] as $key => $value )
				{
					if( !$local_field_info['Condition'] && $local_field_info['Type'] == 'select' )
					{
						$local_values[$key]['Key'] = str_replace($local_field_info['Key']."_", '', $value['Key']);
					}
					else
					{
					$local_values[$key]['Key'] = $value['Key'];
					}
					$local_values[$key]['name'] = $value['name'] ? $value['name'] : $lang[ $value['pName'] ];
				}
				$rlSmarty -> assign('local_values', $local_values);
			}
			//$rlXajax -> registerFunction( array( 'addFieldMappingItem', $rlXmlFeeds, 'ajaxAddFieldMappingItem' ) );
			$rlXajax -> registerFunction( array( 'copyMappingItem', $rlXmlFeeds, 'ajaxCopyMappingItem' ) );
			$rlXajax -> registerFunction( array( 'deleteMappingItem', $rlXmlFeeds, 'ajaxDeleteMappingItem' ) );
		}
		else
		{
			$fields = $rlDb -> fetch("*", array("Status" => "active"), "AND `Key` != 'Category_ID' AND `Key` != 'text_search' AND `Key` != 'xml_ref'", null, 'listing_fields');
			$fields = $rlLang -> replaceLangKeys( $fields, 'listing_fields', 'name', RL_LANG_CODE );

			foreach( $fields as $key => $field )
			{
				$out[$key]['Key'] = $field['Key'];
	//			$out[$key]['Type'] = $field['Type'];
				$out[$key]['Type_name'] = $lang['type_'.$field['Type']];
				$out[$key]['name'] = $field['name'];

				if( $field['Type'] == 'mixed' )
				{
					$measurement_fields[] = $field;
				}
			}

			$rlSmarty -> assign( 'listing_fields', $out );

			$max_level = $rlDb -> getOne("Level", "`Status` = 'active' ORDER BY `Level` DESC", 'categories');
			
			$key = 0;
			for( $i = 0; $i <= $max_level; $i++ )
			{
				$system_out[$key]['Key'] = 'category_'.$i;
				$system_out[$key]['name'] = $lang['category']." Level ".$i;
				$system_out[$key]['Type_name'] = $lang['category'];
				$key++;
			}

			$system_out[$key]['Key'] = 'currency';
			$system_out[$key]['name'] = $lang['currency'];
			$system_out[$key]['Type_name'] = $lang['currency'];			

			foreach( $measurement_fields as $mfk => $mfv )
			{
				$key++;
				$system_out[$key]['Key'] = $mfv['Key']."_unit";
				$system_out[$key]['name'] = $mfv['name']. " ".$lang['xf_unit'];
				$system_out[$key]['Type_name'] = $lang['data_formats+name+'.$mfv['Condition']];
			}

			$key++;
			$system_out[$key]['Key'] = 'pictures';
			$system_out[$key]['name'] = $lang['xf_pictures'];
			$system_out[$key]['Type_name'] = $lang['xf_pictures_ftype'];

			$key++;
			$system_out[$key]['Key'] = 'pictures2';
			$system_out[$key]['name'] = $lang['xf_pictures2'];
			$system_out[$key]['Type_name'] = $lang['xf_pictures_ftype2'];

			$key++;
			$system_out[$key]['Key'] = 'xml_ref';
			$system_out[$key]['name'] = $lang['xf_ref_field'];
			$system_out[$key]['Type_name'] = $lang['listing_fields+name+xml_ref'];

			$key++;
			$system_out[$key]['Key'] = 'Date';
			$system_out[$key]['name'] = $lang['date'];
			$system_out[$key]['Type_name'] = $lang['date'];

			$key++;
			$system_out[$key]['Key'] = 'back_url';
			$system_out[$key]['name'] = $lang['xf_back_url'];
			$system_out[$key]['Type_name'] = 'back link';

			$key++;
			$system_out[$key]['Key'] = 'Loc_latitude';
			$system_out[$key]['name'] = $lang['xf_latitude'];
			$system_out[$key]['Type_name'] = '';

			$key++;
			$system_out[$key]['Key'] = 'Loc_longitude';
			$system_out[$key]['name'] = $lang['xf_longitude'];
			$system_out[$key]['Type_name'] = '';

			$rlSmarty -> assign( 'system_fields', $system_out );

			$data = $rlDb -> fetch("*", array('Format' => $_GET['format']), null, null, 'xml_mapping', 'row');

			$fields = unserialize($data['Data']);
			$rlSmarty -> assign('map_fields', $fields);			
		}

		$rlXajax -> registerFunction( array( 'addMappingItem', $rlXmlFeeds, 'ajaxAddMappingItem' ) );
		$rlXajax -> registerFunction( array( 'deleteMappingItem', $rlXmlFeeds, 'ajaxDeleteMappingItem' ) );
		$rlXajax -> registerFunction( array( 'clearMapping', $rlXmlFeeds, 'ajaxClearMapping' ) );		

		if( isset($_POST['submit']) )
		{
			$pdata = $_POST['xf'];

			foreach( $pdata as $key => $value )
			{
				if( !$value['xml'] || !$value['fl'])
				{
					unset( $pdata[$key] );
				}
			}

			$update['fields'] = array(
				'Format' => $_GET['format'],
				'Xpath' => $_POST['xpath'],
				'Data' => serialize($pdata)
			);
			$update['where'] = array( 'Format' => $_GET['format'] );
			$action = $rlActions -> updateOne($update, 'xml_mapping');

			$message = $lang['notice_item_edited'];
			$aUrl = array( "controller" => $controller, 'mode' => 'formats' );
			
			if ( $action )
			{
				$reefless -> loadClass( 'Notice' );
				$rlNotice -> saveNotice( $message );
				$reefless -> redirect( $aUrl );
			}
		}
	}
	elseif( $_GET['action'] == 'export')
	{				
		/* get available formats */		
		$formats = $rlDb -> fetch( array('Key'), array( 'Status' => 'active' ) , "AND FIND_IN_SET('export', `Format_for`) ORDER BY `Key`", null, 'xml_formats' );
		$formats = $rlLang -> replaceLangKeys( $formats, 'xml_formats', 'name', RL_LANG_CODE, 'admin' );
		$rlSmarty -> assign_by_ref('formats', $formats);

		$listing_type = current($rlListingTypes -> types);
		$listing_type = $listing_type['Key'];

		$rlSmarty -> assign('listing_types', $rlListingTypes -> types);
		$rlSmarty -> assign('listing_type', $listing_type);
		
		$htaccess = RL_ROOT.".htaccess";
		$htaccess_cont = file_get_contents($htaccess);
		
		preg_match('/RewriteRule \^([^$]*)\$ plugins\/xmlFeeds\/export.php\?format=\$1\&\$2 \[QSA\,L\]/smi', $htaccess_cont, $match);
		
		if( $match )
		{
			$rewrite_cond = $match[1];
			$rewrite_cond = str_replace('([^-]*)', '[format]', $rewrite_cond);
			$rewrite_cond = str_replace('(.*)', '[params]', $rewrite_cond);
			
			$rlSmarty -> assign('rewrite', $rewrite_cond);
		}else
		{
			$default_rewrite = '[format]-feed.xml[params]';
			$rlSmarty -> assign('default_rewrite', $default_rewrite);			
		}
		$rlXajax -> registerFunction( array( 'applyRule', $rlXmlFeeds, 'ajaxApplyRule' ) );
	}

	$filter_formats = $rlDb -> fetch( array('Key'), array( 'Status' => 'active' ), "AND FIND_IN_SET('import', `Format_for`) ORDER BY `Key`", null, 'xml_formats' );
	$filter_formats = $rlLang -> replaceLangKeys( $filter_formats, 'xml_formats', 'name', RL_LANG_CODE, 'admin' );
	$rlSmarty -> assign_by_ref('filter_formats', $filter_formats);

	$rlXajax -> registerFunction( array( 'deleteFeed', $rlXmlFeeds, 'ajaxDeleteFeed' ) );
	$rlXajax -> registerFunction( array( 'deleteFormat', $rlXmlFeeds, 'ajaxDeleteFormat' ) );
	$rlXajax -> registerFunction( array( 'deleteUser', $rlXmlFeeds, 'ajaxDeleteUser' ) );
}
