<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLXMLFEEDS.CLASS.PHP
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

class rlXmlFeeds extends reefless
{


	/**
	* ajax delete format 
	*
	* @package - ajax
	*
	* @param string key - format key
	* 
	*/

	function ajaxDeleteFormat( $key )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		if ( !$key )
			return $_response;

		$GLOBALS['rlValid'] -> sql($key);

		$sql = "DELETE `T1`,`T2`,`T3`,`T4` FROM `".RL_DBPREFIX."xml_formats` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('xml_formats+name+', `T1`.`Key`) ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."xml_feeds` AS `T3` ON `T3`.`Format` = `T1`.`Key` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T4` ON `T4`.`Key` = CONCAT('xml_feeds+name+', `T3`.`Key`) ";		
		$sql .="WHERE `T1`.`Key` = '{$key}'";

		$this -> query( $sql );

		$_response -> script("printMessage('notice', '{$lang['item_deleted']}')");
		$_response -> script( "xmlFormatsGrid.reload()" );

		return $_response;
	}


	/**
	* ajax delete feed
	*
	* @package - ajax
	*
	* @param string key - feed key
	* 
	*/
	function ajaxDeleteFeed( $key )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		if ( !$key )
			return $_response;

		$GLOBALS['rlValid'] -> sql($key);

		$sql = "DELETE `T1`, `T2` FROM `".RL_DBPREFIX."xml_feeds` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('xml_feeds+name+', `T1`.`Key`) ";		
		$sql .="WHERE `T1`.`Key` = '{$key}'";

		$this -> query( $sql );

		$_response -> script("printMessage('notice', '{$lang['item_deleted']}')");
		$_response -> script( "xmlFeedsGrid.reload()" );

		return $_response;
	}


	/**
	* ajax delete user 
	*
	* @package - ajax
	*
	* @param string id - user id (not account_id)
	* 
	*/
	function ajaxDeleteUser( $id )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$id = (int)$id;
		if ( !$id )
			return $_response;

		$_response -> script("printMessage('notice', '{$lang['item_deleted']}')");
		$_response -> script( "xmlUsersGrid.reload()" );

		return $_response;
	}

	
	/**
	* ajax clear statistics
	*
	* @package - ajax
	*
	* @param string feed_key - feed key
	* @param string account_id - account_id
	* 
	*/

	function ajaxClearStatistics( $feed_key, $account_id )
	{
		global $_response;

		if ( !$feed_key )
			return $_response;

		$account_id = (int)$account_id;
		$GLOBALS['rlValid'] -> sql($feed_key);

		$sql = "DELETE FROM `".RL_DBPREFIX."xml_statistics` WHERE `Feed` = '{$feed_key}' ";
		if ( $account_id )
		{
			$sql .= "AND `Account_ID` = {$account_id}";
		}
		$this -> query($sql);

		$_response -> script("printMessage('notice', '{$GLOBALS['lang']['xf_stats_cleared']}')");
		$_response -> script("$('#stats_table').fadeOut()");

		return $_response;
	}


	/**
	* ajax run feed
	*
	* @package - ajax
	*
	* @param string feed_key - feed key
	* @param string account_id - account_id
	* 
	*/

	function ajaxRunFeed( $feed_key, $account_id )
	{
		global $_response;

		/*uncomment to enable ajax mode*/
//		$_response -> script("xajax_performImport()");
//		return $_response;

		$account_id = (int)$account_id;
		$GLOBALS['rlValid'] -> sql($feed_key);
		$params['feed'] = $feed_key;

		if ( $account_id )
		{
			$params['account_id'] = $account_id;
		}

		$GLOBALS['rlSmarty'] -> assign('params', $params);

		$tpl = RL_PLUGINS ."xmlFeeds". RL_DS ."admin". RL_DS ."import_frame.tpl";
		$_response -> assign( 'manual_import_dom', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );
		$_response -> script("$('#manual_import_cont').slideDown()");

		return $_response;
	}


	function ajaxPerformImport( )
	{
		global $_response;

		define('AJAX_MODE', true);

		if( !$_SESSION['xmlFeedsImport'] )
		{
			define('AJAX_XML_START', true);
			$file = '<link href="'.RL_PLUGINS_URL.'xmlFeeds/static/import_progress.css" type="text/css" rel="stylesheet" />';
			$_response -> script("$('".$file."').appendTo($('head'))");

			$_response -> script( "$('#manual_import_dom').html('');$('#manual_import_cont').slideDown();$('#manual_import_dom').css('height', '350px')" );			
		}
		
		include( RL_PLUGINS."xmlFeeds".RL_DS."import.php" );

		if( !defined('AJAX_XML_END') )
		{
			$_response -> script("xajax_performImport()");
		}

		return $_response;
	}

	/**
	* ajax apply rewrite rule
	*
	* @package - ajax
	*
	* @param string rule - rewrite rule
	* 
	*/

	function ajaxApplyRule( $rule = '')
	{
		global $_response;

		if ( !is_numeric( strpos($rule, '[params]') ) || !is_numeric( strpos($rule, '[format]') ) )
		{
			$_response -> script("printMessage('error', '".$GLOBALS['lang']['incorrect']."');");
			return $_response;
		}

		$htaccess = RL_ROOT.".htaccess";
		$htaccess_cont = file_get_contents($htaccess);

		preg_match('/RewriteRule \^([^$]*)\$ plugins\/xmlFeeds\/export.php\?format=\$1\&\$2 \[QSA\,L\]/smi', $htaccess_cont, $match);

		if ( $match[0] )
		{
			$rewrite_cond = $match[1];
			$old_rule = $match[0];

			$new_cond = str_replace(array('[format]','[params]'), array('([^-]*)', '(.*)'), $rule);

			$htaccess_cont = str_replace( $rewrite_cond, $new_cond, $htaccess_cont );
			file_put_contents($htaccess, $htaccess_cont);
		}
		else
		{
			$pattern ="\r\n# define paging";

			$new_cond = str_replace(array('[format]', '[params]'), array('([^-]*)', '(.*)'), $rule);
			$rewrite_rule = "RewriteRule ^".$new_cond."$ plugins/xmlFeeds/export.php?format=$1&$2 [QSA,L]";

			$replacement = "\r\n".$rewrite_rule.$pattern;

			$htaccess_cont = str_replace( $pattern, $replacement, $htaccess_cont );
			file_put_contents($htaccess, $htaccess_cont);
		}

		$_response -> script("$('#apply_rule').val('".$GLOBALS['lang']['xf_htrule_edit']."');");
		$_response -> script("$('#rewrited').val(1);");
		
		$_response -> script("printMessage('notice', '{$lang['xf_rewrite_success']}' );$('#actual_rewrite').val( '". RL_URL_HOME . $rule ."' );");
		$_response -> script("buildUrl();");

		return $_response;
	}


	/**
	* add format item
	*
	* @package ajax
	*
	* @param mixed $data - data
	*
	**/

	function ajaxAddMappingItem( $local = false, $remote = false )
	{
		global $_response, $lang;

		if( trim($_GET['field']) == 'category' )
		{
			$parent_id = $this -> getOne("ID", "`Data_local` = 'category_0' AND `Format` = '".$_GET['format']."'", "xml_mapping");
		}
		elseif( is_numeric(strpos($_GET['field'], 'mf|')) && !$_GET['parent'] )
		{
			$parent_id = $this -> getOne("ID", "`Data_local` = '".str_replace('mf|','', $_GET['field'] )."'", "xml_mapping");			
		}
		elseif( $_GET['field'] && !$_GET['parent'] )
		{			
			$parent_id = $this -> getOne("ID", "`Format` = '".$_GET['format']."' AND `Data_remote` = '".$_GET['field']."'", "xml_mapping");
		}
		elseif( $_GET['parent'] )
		{
			$parent_id = $_GET['parent'];
		}
		else
		{
			$parent_id = 0;
		}

		$insert['Parent_ID'] = $parent_id;
		$insert['Format'] = $_GET['format'];

		$insert['Data_remote'] = $remote;

		$ex = $this -> fetch("*", $insert, null, null, "xml_mapping", "row");
		if( $ex )
		{
			$_response -> script("printMessage('error', '".str_replace("{key}", $local, $lang['notice_field_exist'])."')");
			$_response -> script( "$('input[name=item_submit]').val('{$lang['add']}');" );
			
			return $_response;
		}
		$insert['Data_local'] = $local;

		$GLOBALS['rlActions'] -> insertOne( $insert, "xml_mapping" );

		$_response -> script("$('#mapping_item_local').val();$('#mapping_item_remote').val()");
		$_response -> script("$('#add_mapping_item').slideUp('normal')");

		$_response -> script( $_GET['field'] ? "xmlItemMappingGrid.reload();" : "xmlMappingGrid.reload();" );
		$_response -> script( "$('input[name=item_submit]').val('{$lang['add']}');" );

		return $_response;
	}


	/**
	* add format item
	*
	* @package ajax
	*
	* @param mixed $data - data
	*
	**/

	function ajaxDeleteMappingItem( $data_remote = false )
	{
		global $_response, $lang, $key;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		if ( !$data_remote )
			return $_response;


		/* delete item */
		if( $_GET['field'] )
		{
			if( is_numeric(strpos($_GET['field'], 'mf|')) )
			{
				$mapping_parent = $_GET['parent'] ? $_GET['parent'] : $this -> getOne("ID", "`Data_local` = '".str_replace('mf|', '', $_GET['field'])."'", "xml_mapping");
				$item = $this -> fetch(array("ID"), array("Data_remote" => $data_remote, "Parent_ID" => $mapping_parent), null, null, "xml_mapping", "row");

				$this -> deleteMappingItemWithChilds( $item['ID'] );
			}
			elseif( is_numeric(strpos($_GET['field'], 'category')) )
			{
				$mapping_parent = $_GET['parent'] ? $_GET['parent'] : $this -> getOne("ID", "`Data_local` = 'category_0' AND `Format` = '{$_GET['format']}'", "xml_mapping");				
				$item = $this -> fetch(array("ID"), array("Data_remote" => $data_remote, "Parent_ID" => $mapping_parent), null, null, "xml_mapping", "row");
				
				$this -> deleteMappingItemWithChilds( $item['ID'] );
			}
			else
			{
			$parent_id = $this -> getOne("ID", "`Data_remote` = '{$_GET['field']}' AND `Format` = '{$_GET['format']}'", "xml_mapping");

			$sql = "DELETE FROM `". RL_DBPREFIX ."xml_mapping` ";
			$sql .="WHERE `Data_remote` = '{$data_remote}' AND `Format` = '{$_GET['format']}'";
			
			$this -> query( $sql );
			}
		}else
		{
			$sql = "DELETE `T1`, `T2` FROM `". RL_DBPREFIX ."xml_mapping` AS `T1` ";
			$sql .="LEFT JOIN `".RL_DBPREFIX."xml_mapping` AS `T2` ON `T2`.`Parent_ID` = `T1`.`ID` ";
			$sql .="WHERE `T1`.`Data_remote` = '{$data_remote}' AND `T1`.`Format` = '{$_GET['format']}' ";

			$this -> query( $sql );
		}

		$_response -> script("printMessage('notice', '{$lang['item_deleted']}')");

		if( $_GET['field'] )
		{
			$_response -> script( "xmlItemMappingGrid.reload()" );
		}
		else
		{
			$_response -> script( "xmlMappingGrid.reload()" );
		}

		return $_response;
	}


	/**
	* copy mapping item
	*
	* @package ajax
	*
	* @param mixed $data_remote - data
	*
	**/

	function ajaxCopyMappingItem( $data_remote = false )
	{
		global $_response, $rlActions, $rlValid, $lang;

		$parent = $this -> getOne("Data_local", "`Format` = '".$_GET['format']."' AND `Data_remote` = '".$_GET['field']."'", "xml_mapping");

		//preg_match('#category_(\d)#', $parent, $match);

		/* insert category */
		if( $match[0] )
		{
			$GLOBALS['reefless'] -> loadClass('XmlImport', null, 'xmlFeeds');			
			$GLOBALS['rlXmlImport'] -> createCategory($data_remote, $data['Category_ID']);
		}
		else
		{
			$field_info = $this -> fetch("*", array("Key" => $parent), null, null, "listing_fields", "row");

			/* insert value */
			if( $field_info['Condition'] )
			{
				$data_format_info = $this -> fetch("*", array("Key" => $field_info['Condition']), null, null, "data_formats", "row" );

				$item_insert['Parent_ID'] = $data_format_info['ID'];
				$item_insert['Key'] = $data_format_info['Key'] ."_". $rlValid -> str2key( $data_remote );
				$item_insert['Position'] = $this -> getOne("Position", "`Parent_ID` = ".$data_format_info['ID']." ORDER BY `Position` DESC", "data_formats") + 1;
				$item_insert['Status'] = 'active';

				if( $rlActions -> insertOne($item_insert, "data_formats") )
				{
					foreach( $GLOBALS['languages'] as $key => $lang_item )
					{
						$lang_keys[] = array(
							'Code' => $lang_item['Code'],
							'Module' => 'common',
							'Key' => 'data_formats+name+'.$item_insert['Key'],
							'Value' => $data_remote,
							'Status' => 'active'
						);					
					}
					$rlActions -> insert($lang_keys, "lang_keys");
				}

				$sql = "UPDATE `".RL_DBPREFIX."xml_mapping` SET `Data_local` = '{$item_insert['Key']}' ";
				$sql .="WHERE `Format` = '".$_GET['format']."' AND `Data_remote` = '".$data_remote."'";

				$this -> query( $sql );
			}
		}
		
		$_response -> script("printMessage('notice', '{$lang['item_added']}')");
		$_response -> script( "xmlItemMappingGrid.reload();" );

		return $_response;
	}


	/**
	* add format item
	*
	* @package ajax
	*
	* @param mixed $data - data
	*
	**/

	function ajaxDeleteXmlFeed( $feed_id = false )
	{
		global $_response, $account_info;

		if ( defined( 'IS_LOGIN' ) && $feed_id )
		{
			$id = (int)$id;
			$info = $this -> fetch( array('ID', 'Account_ID'), array( 'ID' => $feed_id ), null, 1, 'xml_feeds', 'row' );			

			if ( $info['Account_ID'] == $account_info['ID'] )
			{
				$sql ="DELETE `T1`,`T2` FROM `".RL_DBPREFIX."xml_feeds` AS `T1` ";
				$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('xml_feeds+name+', `T1`.`Key`) ";
				$sql .="WHERE `T1`.`ID` = ".$feed_id;

				$this -> query( $sql );

				$feeds = $this -> fetch( array('ID'), array( 'Account_ID' => $account_info['ID'] ), null, 1, 'xml_feeds', 'row' );
				if ( empty($feeds) )
				{
					$_response -> script( "$('#user_feeds').slideUp();$('#add_feed_cont').slideDown();" );
					
					$empty_mess = '<div class="info">'.$lang['no_saved_search'].'</div>';
					$_response -> assign( 'saved_search_obj', 'innerHTML', $empty_mess );
				}
				
				$_response -> script( "$('#item_{$feed_id}').fadeOut('slow');" );
				$_response -> script( "printMessage('notice', '{$lang['notice_item_deleted']}');" );
			}
		}
		
		return $_response;	
	}


	/**
	* clear mapping
	*
	* @package ajax
	*
	* @param mixed $format - format
	*
	**/

	function ajaxClearMapping( $format = false )
	{
		global $_response, $lang;

		if( !$format )
			return $_response;


		$sql = "DELETE `T1`, `T2` FROM `". RL_DBPREFIX ."xml_mapping` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."xml_mapping` AS `T2` ON `T2`.`Parent_ID` = `T1`.`ID` ";
		$sql .="WHERE `T1`.`Format` = '{$format}' ";

		$this -> query( $sql );

		$_response -> script("printMessage('notice', '{$lang['notice_items_deleted']}')");
		$_response -> script("xmlMappingGrid.reload()");

		return $_response;
	}


	/**
	* delete mapping with childs
	*
	* @package ajax
	*
	* @param int $id - id
	*
	**/

	function deleteMappingItemWithChilds( $id = false )
	{		
		$sql = "DELETE FROM `". RL_DBPREFIX ."xml_mapping` ";
		$sql .="WHERE `ID` = '{$id}'";
		
		$this -> query( $sql );

		$childs = $this -> fetch(array('ID'), array('Parent_ID' => $id), null, null, "xml_mapping");
		foreach( $childs as $k => $v )
		{
			$this -> deleteMappingItemWithChilds($v['ID']);			
		}		
	}

	/**
	* load categories
	*
	* @package AJAX
	*
	* @param string $listing_type - listing type
	* @param int $value - category id
	* @param int $level - level
	*
	* @return array - listings information
	**/

	function ajaxLoadCategories( $listing_type, $value = 0, $level = 0 ) 
	{
		global $_response;

		if( count( $GLOBALS['rlListingTypes'] -> types ) == 1 && !$listing_type )
		{
			reset( $GLOBALS['rlListingTypes'] -> types );
			$listing_type = current( $GLOBALS['rlListingTypes'] -> types );
			$listing_type = $listing_type['Key'];
		}

		if( $listing_type )
		{
			$categories = $GLOBALS['rlCategories'] -> getCategories( $value, $listing_type );

			$options = '<option value="0">'.$GLOBALS['lang']['any'].'</option>';
			foreach($categories as $key => $category)
			{
				$options .='<option value="'.$category['ID'].'">'.$category['name'].'</option>';
			}

			$target = 'category_level'.($level+1);

			$_response -> script("$('#{$target}').html('".$options."')");
			$_response -> script("$('#{$target}').removeAttr('disabled');");
		}elseif( count( $GLOBALS['rlListingTypes'] -> types ) > 1 )
		{
			$_response -> script("$('select.multicat').attr('disabled', true).val('0')");
		}

		return $_response;
	}

	/**
	* load previous levels of categories in multi categories mode
	*
	* @package xajax
	* 
	* @param string $value - selected category
	* @param string $dom_id - post value container input id
	*
	* @todo - add new category
	**/
	function ajaxBuildCategories( $type = false, $value = false )
	{
		global $_response;

		$levels = 2;

		$value = (int)$value;
		$cat_level = $this -> getOne( "Level", "`ID` = {$value}", 'categories' );

		if ( $cat_level < ($levels - 1) )
		{
			$categories = $GLOBALS['rlCategories'] -> getCategories( $value, $type );
		
			$options = '<option value="0">'.$GLOBALS['lang']['any'].'</option>';
			foreach( $categories as $key => $category )
			{
				$options .='<option value="'. $category['ID'] .'">'. $category['name'] .'</option>';
			}
			$target = 'category_level'.($cat_level+1);
			$_response -> script("$('#{$target}').html('".$options."').removeAttr('disabled')");
		}

		$id = $value;
		for( $i = $levels-1; $i >= 0; $i-- )
		{
			$cat_info = $this -> fetch( array("Level", "Parent_ID"), array('ID' => $id), null, null, 'categories', 'row' );
			if( $cat_info['Level'] < $i )
			{
				continue;
			}

			$parent = (int)$cat_info['Parent_ID'];
			$target = 'category_level'.$i;

			if ( $parent == 0 )
			{
				$_response -> script("$('#{$target}').val(".$id.")");
				return $_response;
			}
			else
			{
				$categories = $GLOBALS['rlCategories'] -> getCategories( $parent, $type );

				$options = '<option value="0">'.$GLOBALS['lang']['any'].'</option>';
				foreach( $categories as $key => $category )
				{
					$selected = $id == $category['ID'] ? 'selected="selected"' : '';
					$options .='<option '. $selected .' value="'. $category['ID'] .'">'. $category['name'] .'</option>';
				}

				$target = 'category_level'.$i;
				$_response -> script("$('#{$target}').html('".$options."')");
				$_response -> script( "$('#{$target}').removeAttr('disabled');" );
				$id = $parent;
			}
		}

		return $_response;
	}
}