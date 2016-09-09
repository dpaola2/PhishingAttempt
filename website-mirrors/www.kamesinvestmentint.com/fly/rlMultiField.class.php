<?php


/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLMULTIFIELD.CLASS.PHP
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

class rlMultiField extends reefless 
{	
	/**
	* getNext - get next level 
	*
	* @package ajax
	*
	* @param string $value - value of current level
	* @param string $name - field name 
 	* @param string $form_key - form key which contain the field (for search forms only) 
	* @param int $levels - current number of selectors on the page
	* @param string $type - listing or account - to define target fields 
	* 
	**/
	
	function ajaxGetNext( $value = false, $name = false, $form_key = false, $levels = false, $type = 'listing', $order_type = false )
	{
		global $_response;

		$GLOBALS['rlValid'] -> sql($value);
		$post_prefix = $type == 'account' && !defined('REALM') ? 'account' : 'f';

		$post_form_dom = $form_key ? 'form:has(input[value='. $form_key .'][name=post_form_key]) ' : '';

		preg_match('/'.$post_prefix.'\[(.*?)(_level([0-9]))?\]/i', $name, $match);

		$top_field = $match[1];
		$level = $match[3] ? $match[3] : 0;
		$next_field = $top_field ."_level". ($level + 1);

		$next_values = $this -> getMDF( $value, $order_type );

		$options = $empty_option = '<option value="0">'. $GLOBALS['lang']['any'] .'</option>';
		foreach( $next_values as $key => $option )
		{
			$options .='<option value="'. $option['Key'] .'">'. $option['name'] .'</option>';
		}

		for( $i=$level+2; $i<=(int)$levels; $i++ )
		{
			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $top_field ."_level". $i ."]\"]').attr('disabled', 'disabled').val('". $empty_option ."')");
		}

		$options = $GLOBALS['rlValid'] -> xSql( $options );
		$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $next_field ."]\"]').html('". $options ."').removeAttr('disabled').removeClass('disabled')");

		return $_response;
	}


	/**
	* build - build related fields
	*
	* @package ajax
	*
	* @param string $last_value - value of last selected field
	* @param string $last_field - key of last selected field
 	* @param string $form_key - form key which contain the field (for search forms only) 
	* @param int $levels - current number of selectors on the page
	* @param string $type - listing or account - to define target fields 
	* 
	**/

	function ajaxBuild( $last_value = false, $last_field = false, $form_key = false, $levels = false, $type = 'listing', $order_type = false )
	{
		global $_response;

		if ( $GLOBALS['geo_filter_data']['geo_url'] && !$last_value )
		{
			if ( $type == 'listing' )
			{
				$last_geo = array_slice( $GLOBALS['geo_filter_data']['lfields'], -1, 1);
			}
			else
			{
				$last_geo = array_slice( $GLOBALS['geo_filter_data']['afields'], -1, 1);
			}
			$last_field = current( array_keys($last_geo) );
			$last_value = current( array_values($last_geo) );
		}

		$post_form_dom = $form_key ? 'form:has(input[value='. $form_key .'][name=post_form_key]) ' : '';
		$post_prefix = $type == 'account' && !defined('REALM') ? 'account' : 'f';

		$tmp = explode('level', $last_field);

		$level = $tmp[1];
		$top_field = trim($tmp[0], '_');

		$parents[] = $last_value;
		$parents = $this -> getParents( $last_value, $parents );

		if( $parents && $last_value )
		{
			$top_value = $parents[count($parents)-1];
			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $top_field ."]\"] option[value=". $top_value ."]').attr('selected', 'selected').removeAttr('disabled').removeClass('disabled')");
			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $top_field ."]\"]').removeAttr('disabled').removeClass('disabled');");
		}else
		{
			$top_value = 0;
			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $top_field ."]\"] option[value=". $top_value ."]').attr('selected', 'selected');");
			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $top_field ."]\"]').removeAttr('disabled').removeClass('disabled')");
			
		}

		$empty_option = '<option value="0">'. $GLOBALS['lang']['any'] .'</option>';
		$lev_add = $last_value ? 2 : 1;

		for( $i=$level+$lev_add; $i<=(int)$levels; $i++ )
		{
			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $top_field ."_level". $i ."]\"]').attr('disabled', 'disabled').val('". $empty_option ."')");
		}
		
		$level++;
		foreach( $parents as $key => $parent )
		{
			$values = $this -> getMDF( $parent, $order_type );

			$options = $empty_option;
			foreach($values as $opt_key => $option)
			{
				$sel = $option['Key'] == $parents[$key-1] ? 'selected="selected"' : '';

				$options .='<option '. $sel .' value="'. $option['Key'] .'">'. $GLOBALS['rlValid'] -> xSql( $option['name'] ) .'</option>';
			}

			$target = $level == 0 ? $top_field : $top_field. "_level". ($level);

			$_response -> script("$('". $post_form_dom ."select[name=\"". $post_prefix ."[". $target ."]\"]').html('". $options ."').removeAttr('disabled').removeClass('disabled')");
			$level--;
		}

		return $_response;
	}


	/**
	* get parents - get all parents of item 
	*
	* @param string $key - key
	* @param array $parents - parents
	* 
	* @return array 
	*
	**/

	function getParents( $key = false, $parents = false )
	{
		if ( !$key )
			return false;

		$GLOBALS['rlValid'] -> sql($key);

		$sql  = "SELECT `T2`.`Key`, `T2`.`Parent_ID` FROM `". RL_DBPREFIX ."data_formats` AS `T1` ";
		$sql .= "JOIN `". RL_DBPREFIX ."data_formats` AS `T2` ON `T1`.`Parent_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T1`.`Key` = '{$key}' LIMIT 1";
		$parent = $this -> getRow($sql);

		if ( $parent['Parent_ID'] == 0 && $parent['Key'])
		{
			return $parents;
		}
		else
		{
			$parents[] = $parent['Key'];
			return $this -> getParents($parent['Key'], $parents);
		}

		return $parent;
	}

	/**
	* get all data format
	*
	* @param string $key - format key
	* @param string $order - order type (alphabetic/position)
	*
	* @return array - data formats list
	**/

	function getMDF( $key = false, $order = false, $get_path = false, $path = false, $include_childs = false )
	{
		global $rlCache, $rlLang, $config;

		if ( !$key && !$path )
			return false;

		$GLOBALS['rlValid'] -> sql($key);
		$GLOBALS['rlValid'] -> sql($path);

		/* get cache in case of multileveled representation */
		if ( (( $config['cache'] && $include_childs ) || $GLOBALS['geo_format'] == $key ) && !defined('RL_MF_NOCACHE') && $config['mf_cache_system'] && $config['cache'] )
		{
			$cache_key = $include_childs ? 'multi_leveled' : 'top_level';
			$df = $this -> getCache( $cache_key );

			if ( !$df )
			{
				$this -> cache();
				$df = $this -> getCache( $cache_key );
			}

			if ( $df && count($GLOBALS['languages']) > 1 )
			{
				$df = $GLOBALS['rlLang'] -> replaceLangKeys( $df, 'data_formats', array( 'name' ) );
			}

			return $df;
		}

		if ( $key && !is_array($key) )
		{
			$parent_id = $this -> getOne('ID', "`Key` = '{$key}'", 'data_formats');
		}
		elseif( $path )
		{
			$parent_id = $this -> getOne('ID', "`Path` = '".trim($path, "/")."'", 'data_formats');
		}
		elseif( $key['Key'] )
		{
			$parent_id = $this -> getOne('ID', "`Key` = '".$key['Key']."'", 'data_formats');
		}

		if ( !$parent_id )
			return false;

		$sql = "SELECT `T1`.`Position`, `T1`.`ID`, `T1`.`Parent_ID`, `T1`.`Key`, `T2`.`Value` AS `name`, ";
		if ( $get_path )
		{
			$sql .="CONCAT( `Path`, '/' ) as `Path`, ";
		}
		$sql .="CONCAT('data_formats+name+', `T1`.`Key`) as `pName` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('data_formats+name+', `T1`.`Key`) AND `T2`.`Code` = '".RL_LANG_CODE."' ";
		$sql .="WHERE `Parent_ID` = {$parent_id} AND `T1`.`Status` = 'active' GROUP BY `T1`.`ID` ";

		if ( $order == 'position' )
		{
			$sql .= "ORDER BY `Position` ";
		}
		elseif ( $order == 'alphabetic' )
		{
			$sql .= "ORDER BY `T2`.`Value` ";
		}
		else
		{
			$sql .= "ORDER BY `T1`.`ID`, `T1`.`Key` ";
		}

		$data = $this -> getAll( $sql );

		foreach( $data as $dkey => &$value )
		{
			$GLOBALS['lang'][$value['pName']] = $value['name'];

			if ( $include_childs )
			{
				$value['childs'] = $this -> getMDF( $value['Key'], $order, $get_path, true, $include_childs-1 );
				if ( $value['childs'][0]['childs'][0]['ID'] )
				{
					$value['subchilds'] = true;
				}
			}
		}

		/*if ( $order == 'alphabetic' )
		{
			$this -> rlArraySort($data, 'name');
		}*/

		return $data;
	}

	function cache( $area = 'detect', $clear = false )
	{
		global $rlConfig, $config;

		if( !$config['mf_cache_system'] || !$config['cache'] )
		{
			return false;
		}

		if( !$area || $area == 'detect' )
		{
			$area = $config['mf_geo_multileveled'] && $config['mf_geo_block_list'] ? 'multi_leveled' : 'top_level';
		}

		$geo_format = $this -> getOne("Key", "`Geo_filter` = '1'", "multi_formats");
		if( !$geo_format )
			return false;

		if( $area == 'multi_leveled' )
		{
			$include_childs = $config['mf_geo_block_list'] && $config['mf_geo_multileveled'] ? $config['mf_geo_levels_toshow'] - 1: false;

			if(!$include_childs)
				return false;
		}
		
		$clear = true;

		if( $clear )
		{
			$cache_files = scandir(RL_CACHE);
			foreach( $cache_files as $ck => $cfile )
			{
				if( strlen($cfile) > 10 )
				{
					preg_match('/^mf_cache_data_formats.*$/', $cfile, $cmatch );
					if( $cmatch )
					{
						unlink(RL_CACHE.$cfile);
					}
				}
			}
		}
		define('RL_MF_NOCACHE', true);
		$GLOBALS['reefless'] -> loadClass('Cache');
		$GLOBALS['reefless'] -> loadClass('Actions');
		
		$GLOBALS['rlCache'] -> file( 'mf_cache_data_formats_'.$area );

		$file = RL_CACHE . $config['mf_cache_data_formats_'.$area];

		$order_type = $GLOBALS['geo_filter_data']['order_type'] ? $GLOBALS['geo_filter_data']['order_type'] : $this -> getOne("Order_type", "`Key` = '".$geo_format."'", "data_formats");

		$out = $this -> getMDF( $geo_format, $order_type, true, false, $include_childs );
		
		$fh = fopen($file, 'w');
		fwrite($fh, serialize($out));
		fclose($fh);
		unset($out);
	}

	/**
	* get cache item
	*
	* @param string $key - cache item key
	* @param id $id - item id
	* @param string $type - listing type
	*
	* @return array
	*
	**/
	function getCache( $area )
	{	
		$key = 'mf_cache_data_formats_'.$area;

		$file = RL_CACHE . $GLOBALS['config'][$key];
		
		if ( empty($GLOBALS['config'][$key]) || !is_readable($file) )
		{
			return false;
		}
		
		$fh = fopen($file, 'r');
		$content = fread($fh, filesize($file));
		fclose($fh);
		
		$content = unserialize($content);

		return $content;
	}

	/**
	* add format item
	*
	* @package ajax
	*
	* @param string $key - key
	* @param array $names - names
	* @param string $status - status
	* @param string $format - parent format
	*
	**/

	function ajaxAddItem($key, $names, $status, $format, $path )
	{
		global $_response, $lang, $insert, $rlValid;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

		$key = utf8_is_ascii( $key ) ? $key : utf8_to_ascii($key);
		$item_key = $rlValid -> str2key($key);
		$rlValid -> sql($format);

		/* check key */
		if ( strlen($item_key) < 2 )
		{
			$errors[] = $lang['incorrect_phrase_key'];
		}

		$item_key = $format . '_' . $item_key;

		if ( !utf8_is_ascii( $item_key ) )
		{
			$errors[] = $lang['key_incorrect_charset'];
		}

		$key_exist = $this -> getOne('ID', "`Key` = '{$item_key}'", 'data_formats');
		if ( !empty($key_exist) )
		{
			$errors[] = str_replace( '{key}', "<b>{$item_key}</b>", $lang['notice_item_key_exist'] );
		}

		$parent_id = $this -> getOne('ID', "`Key` = '{$format}'", 'data_formats');

		/*check path*/
		$head = $this -> getHead( $parent_id );
		$geo_filter = $this -> getOne("Geo_filter", "`Key` = '{$head}'", 'multi_formats');

		if ( $geo_filter )
		{
			$path = $rlValid -> str2path( $path );
			$path = $path ? $path : $key;

			if ( strlen($path) < 3 )
			{
				$errors[] = $lang['mf_path_short'];
			}
			else
			{
				$parent_path = $this -> getOne('Path', "`Key` = '{$format}'", 'data_formats');
				$item_path = $parent_path ? $parent_path ."/". $path : $path;
	
				$path_exist = $this -> getOne('ID', "`Path` = '{$item_path}'", 'data_formats');
				if ( !empty($path_exist) )
				{
					$errors[] = $lang['mf_path_exists'];
				}
			}
		}

		/* check names */
		$languages = $GLOBALS['languages'];
		foreach ($languages as $key => $value)
		{
			if( empty($names[$languages[$key]['Code']]) )
			{
				$names[$languages[$key]['Code']] = $names[$GLOBALS['config']['lang']];
			}
			if( empty($names[$languages[$key]['Code']]) )
			{
				$errors[] = str_replace( '{field}', "'<b>{$lang['value']} ({$languages[$key]['name']})</b>'", $lang['notice_field_empty']);
			}
		}

		if ( $errors )
		{
			$out = '<ul>';

			/* print errors */
			foreach ($errors as $error)
			{
				$out .= '<li>'. $error .'</li>';
			}
			$out .= '</ul>';
			$_response -> script("printMessage('error', '{$out}');");
		}
		else
		{
			$level = $this -> getLevel( $parent_id );
			$module = $level >= 1 ? 'formats' : 'common';

			$max_position = $this -> getOne("Position", "`Parent_ID` = {$parent_id} ORDER BY `Position` DESC", "data_formats");
			
			$insert = array(
				'Parent_ID' => $parent_id,
				'Key' => $item_key,
				'Status' => $status,
				'Position' => $max_position+1,
				'Plugin' => $level ? 'multiField' : ''
			);

			if ( $item_path )
			{
				$insert['Path'] = $item_path;
			}

			/* insert new item */
			if ( $GLOBALS['rlActions'] -> insertOne($insert, 'data_formats') )
			{
				if( $level )
				{
					$listing_fields = $this -> createLevelField( $parent_id, 'listing' );
					$account_fields = $this -> createLevelField( $parent_id, 'account' );
				}

				if( $listing_fields || $account_fields )
				{
					$notice_out = '<ul>';
					$notice_out .="<li>".$lang['item_added']."</li>";

					foreach( $listing_fields as $k => $field )
					{
						$href = "index.php?controller=listing_fields&action=edit&field=".$field;
						$link = '<a target="_blank" href="'. $href .'">$1</a>';
						$row = preg_replace( '/\[(.+)\]/', $link, $lang['mf_lf_created'] );

						$notice_out .="<li>". $row ."</li>";
					}

					foreach( $account_fields as $k => $field )
					{
						$href = "index.php?controller=account_fields&action=edit&field=".$field;
						$link = '<a target="_blank" href="'. $href .'">$1</a>';
						$row = preg_replace( '/\[(.+)\]/', $link, $lang['mf_af_created'] );
						$notice_out .="<li>". $row ."</li>";
					}
					$notice_out .= '</ul>';
				}

				/* save new item  name */
				foreach ($languages as $key => $value)
				{
					$lang_keys[] = array(
						'Code' => $languages[$key]['Code'],
						'Module' => $module,
						'Key' => 'data_formats+name+'.$item_key,
						'Value' => $names[$languages[$key]['Code']],
						'Plugin' => $level ? 'multiField' : ''
					);
				}

				if ($GLOBALS['rlActions'] -> insert($lang_keys, 'lang_keys'))
				{
					$mess = $notice_out ? $notice_out : $lang['item_added'];

					$_response -> script("printMessage('notice', '{$mess}')");

					$_response -> script( "itemsGrid.reload();" );
					$_response -> script( "$('#new_item').slideUp('normal')" );
				}

				if ( $GLOBALS['config']['cache'] )
				{
					$GLOBALS['rlCache'] -> updateDataFormats();
					$this -> cache();
				}
			}
		}

		$_response -> script( "$('input[name=item_submit]').val('{$lang['add']}');" );

		return $_response;
	}

	/**
	* create field
	*
	* check related fields and add listing fields  
	* if there are no field yet for this level
	*
	* @param int $parent_id 
	* @param string $type - listing or account
	*
	**/

	function createLevelField( $parent_id, $type = 'listing' )
	{
		global $languages;
		
		$out = array();
		$parent_id = (int)$parent_id;
		$multi_format = $this -> getHead( $parent_id );

		if( !$multi_format )
		{
			return false;
		}

		$format_id = $this -> getOne("ID", "`Key` = '{$multi_format}'", 'data_formats');
		$this -> getLevels( $format_id ); //update levels count in db

		$sql = "SELECT * FROM `". RL_DBPREFIX ."{$type}_fields` WHERE `Condition` = '{$multi_format}' AND `Key` NOT REGEXP 'level[0-9]'";
		$related_fields = $this -> getAll($sql);

		if ( !$related_fields )
		{
			return false;
		}
	
		$level = $this -> getLevel( $parent_id );
		$level = $level ? $level : 1;
		
		foreach( $related_fields as $rlk => $field )
		{
			$field_key = $field['Key'] ."_level". $level;
			$prev_fk = $level == 1 ? $field['Key'] : $field['Key'] ."_level". ($level-1);

			$sql ="SHOW FIELDS FROM `". RL_DBPREFIX."{$type}s` WHERE `Field` = '{$field_key}'";
			$field_exists = $this -> getRow( $sql );

			if ( !$field_exists )
			{
				$sql = "ALTER TABLE `". RL_DBPREFIX."{$type}s` ADD `{$field_key}` VARCHAR(255) NOT NULL AFTER `{$prev_fk}`";
				$this -> query($sql);

				$sql = "SELECT `Key` FROM `".RL_DBPREFIX."{$type}_fields` WHERE `Key` = '{$field_key}'";
				$field_exists = $this -> getRow( $sql );
			}

			if ( !$field_exists )
			{
				$field_info = array(
					'Key' => $field_key,
					'Condition' => $multi_format,
					'Type' => 'select',
					'Status' => 'active'
				);

				if( $type == 'listing' )
				{
					$field_info['Add_page'] = '1';
					$field_info['Details_page'] = '1';
				}
				$field_info['Readonly'] = '1';

				preg_match('/country|location|state|region|province|address|city/i', $field_key, $match);
				if( $match )
				{
					$field_info['Map'] = '1';
				}

				if ( $GLOBALS['rlActions'] -> insertOne( $field_info, $type."_fields" ) )
				{
					$field_id = mysql_insert_id();

					if ( $type == 'listing' )//add entry after the 'parent' field to the search and submit forms
					{
						$prev_field_id = $this -> getOne("ID", "`Key` = '{$prev_fk}'", 'listing_fields');

						$sql ="UPDATE `".RL_DBPREFIX."listing_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$prev_field_id},', ',{$prev_field_id},{$field_id},'))) WHERE FIND_IN_SET('{$prev_field_id}', `Fields`) ";
						$this -> query( $sql );

						$sql ="UPDATE `".RL_DBPREFIX."search_forms_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$prev_field_id},', ',{$prev_field_id},{$field_id},'))) WHERE FIND_IN_SET('{$prev_field_id}', `Fields`) ";
						$this -> query( $sql );
					}
					elseif ( $type == 'account' )
					{
						$prev_field_id = $this -> getOne("ID", "`Key` = '".$prev_fk."'", 'account_fields');

						$sql ="SELECT `Category_ID`, `Position`, `Group_ID` FROM `".RL_DBPREFIX."account_submit_form` WHERE `Field_ID` =".$prev_field_id;
						$afields = $this -> getAll( $sql );
						foreach( $afields as $afk => $afield )
						{
							$sql = "UPDATE `".RL_DBPREFIX."account_submit_form` SET `Position` = `Position`+1 ";
							$sql .="WHERE `Position` > ".$afield['Position']." AND `Category_ID` = ".$afield['Category_ID'];
							$this -> query($sql);

							$insert[$afk]['Position'] = $afield['Position']+1;
							$insert[$afk]['Category_ID'] = $afield['Category_ID'];
							$insert[$afk]['Group_ID'] = $afield['Group_ID'];
							$insert[$afk]['Field_ID'] = $field_id;
						}
						$GLOBALS['rlActions'] -> insert($insert, 'account_submit_form');
					}

					foreach ( $languages as $key => $value )
					{
						$lang_keys[] = array(
							'Code' => $languages[$key]['Code'],
							'Module' => 'common',
							'Key' => $type.'_fields+name+'.$field_key,
							'Value' => $GLOBALS['lang'][$type.'_fields+name+'.$field['Key']]." Level ".$level,
							'Plugin' => 'multiField'
						);
					}

					$GLOBALS['rlActions'] -> insert($lang_keys, 'lang_keys');
				}
				$out[] = $field_key;
			}
		}
		$GLOBALS['rlCache'] -> updateForms();
		return $out;
	}


	/**
	* deletes automatically added fields (listing fields and account fields) when you delete multi-format 
	*
	* @param string $format - multi_format key
	* @param string $type - listing or account
	*
	**/

	function deleteFormatChildFields($format, $type ='listing')
	{
		$sql ="SELECT `Key`, `ID` FROM `".RL_DBPREFIX."{$type}_fields` WHERE `Condition` = '{$format}' AND `Key` REGEXP 'level[0-9]'";
		$related_fields = $this -> getAll($sql);

		foreach( $related_fields as $rlk => $field )
		{
			$sql ="DELETE `T1`,`T2` FROM `".RL_DBPREFIX."{$type}_fields` AS `T1` ";
			$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON (`T2`.`Key` = CONCAT('{$type}_fields+name+', `T1`.`Key`) OR `T2`.`Key` = CONCAT('{$type}_fields+des+', `T1`.`Key`)) ";
			$sql .="WHERE `T1`.`Key` ='".$field['Key']."'";

			$this -> query( $sql );

			if( $type == 'listing' )
			{
				$sql ="UPDATE `".RL_DBPREFIX."listing_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$field['ID']},', ','))) WHERE FIND_IN_SET('{$field['ID']}', `Fields`) ";
				$this -> query( $sql );
			
				$sql ="UPDATE `".RL_DBPREFIX."search_forms_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$field['ID']},', ','))) WHERE FIND_IN_SET('{$field['ID']}', `Fields`) ";

				$this -> query( $sql );

				$sql = "DELETE FROM `".RL_DBPREFIX."short_forms` WHERE `Field_ID` = ".$field['ID'];
				$this -> query( $sql );
			}else
			{
				$sql = "DELETE FROM `" . RL_DBPREFIX . "account_search_relations` WHERE `Field_ID` = '{$field['ID']}'";
				$this -> query( $sql );
			
				$sql = "DELETE FROM `" . RL_DBPREFIX . "account_short_form` WHERE `Field_ID` = '{$field['ID']}'";
				$this -> query( $sql );
			
				$sql = "DELETE FROM `" . RL_DBPREFIX . "account_submit_form` WHERE `Field_ID` = '{$field['ID']}'";
				$this -> query( $sql );
			}

			$sql ="SHOW FIELDS FROM `".RL_DBPREFIX."{$type}s` WHERE `Field` = '".$field['Key']."'";
			$field_exists = $this -> getRow( $sql );
			if( $field_exists )
			{
				$sql ="ALTER TABLE `".RL_DBPREFIX."{$type}s` DROP `".$field['Key']."`";
				$this -> query( $sql );
			}
		}
	}


	/**
	* deletes automatically added fields (listing fields and account fields) when you delete field
	*
	* @param string $format - multi_format key
	* @param string $type - listing or account
	*
	**/

	function deleteFieldChildFields( $field_key, $type ='listing' )
	{
		if( !$field_key || !$type )
		{
			return false;
		}

		$sql ="SELECT `Key`, `ID` FROM `".RL_DBPREFIX."{$type}_fields` WHERE `Key` REGEXP '".$field_key."_level[0-9]'";
		$related_fields = $this -> getAll($sql);

		foreach( $related_fields as $rlk => $field )
		{
			$sql ="DELETE `T1`,`T2` FROM `".RL_DBPREFIX."{$type}_fields` AS `T1` ";
			$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON (`T2`.`Key` = CONCAT('{$type}_fields+name+', `T1`.`Key`) OR `T2`.`Key` = CONCAT('{$type}_fields+des+', `T1`.`Key`)) ";
			$sql .="WHERE `T1`.`Key` ='".$field['Key']."'";

			$this -> query( $sql );

			$sql ="SHOW FIELDS FROM `".RL_DBPREFIX."{$type}s` WHERE `Field` = '".$field['Key']."'";
			$field_exists = $this -> getRow( $sql );

			if( $field_exists )
			{
				$sql ="ALTER TABLE `".RL_DBPREFIX."{$type}s` DROP `".$field['Key']."`";
				$this -> query( $sql );
			}
		}
	}


	/**
	* preparing item editing
	*
	* @package ajax
	*
	* @param string $key - key
	*
	**/
	function ajaxPrepareEdit($key)
	{
		global $_response;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$GLOBALS['rlValid'] -> sql($key);

		/* get item info */
		$item = $this -> fetch(array('ID', 'Key', 'Status', 'Default', 'Path'), array('Key' => $key), null, 1, 'data_formats', 'row');
		$item['Path'] = current(array_slice( array_reverse(explode("/",$item['Path'])), 0, 1 ));

		$GLOBALS['rlSmarty'] -> assign_by_ref('item', $item);
		
		/* get item names */
		$tmp_names = $this -> fetch( array( 'Code', 'Value' ), array( 'Key' => 'data_formats+name+'.$key ), "AND `Status` <> 'trash'", null, 'lang_keys' );
		foreach ($tmp_names as $k => $v)
		{
			$names[$tmp_names[$k]['Code']] = $tmp_names[$k];
		}
		unset($tmp_names);

		$GLOBALS['rlSmarty'] -> assign_by_ref('names', $names);

		$tpl = RL_PLUGINS.'multiField' . RL_DS . 'admin' . RL_DS . 'edit_format_block.tpl';
		
		$_response -> assign("prepare_edit_area", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));
		$_response -> script("flynax.tabs();");
		
		return $_response;
	}


	/**
	* edit format item
	*
	* @package ajax
	*
	* @param string $key - key
	* @param array $names - names
	* @param string $status - status
	* @param string $format - parent format
	*
	**/
	function ajaxEditItem($key, $names, $status, $format, $path)
	{
		global $_response, $lang, $update, $rlValid;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$this -> setTable('data_formats');
		$item_key = $rlValid -> xSql( trim($key) );
		$rlValid -> sql($format);

		/*check path*/
		$head = $this -> getHead( $this -> getOne("ID", "`Key` = '{$format}'", 'data_formats') );
		$geo_filter = $this -> getOne("Geo_filter", "`Key` = '{$head}'", 'multi_formats');

		if ( $geo_filter )
		{
			loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

			$path = utf8_is_ascii( $path ) ? $path : utf8_to_ascii($path);
			$path = $rlValid -> str2path( $path );

			/* check key */
			if ( strlen($path) < 3 )
			{
				$errors[] = $lang['mf_path_short'];
			}
			else
			{
				$parent_path = $this -> getOne('Path', "`Key` = '{$format}'", 'data_formats');
				$item_path = $parent_path ? $parent_path ."/". $path : $path;

				$path_exist = $this -> getOne('Key', "`Path` = '{$item_path}'", 'data_formats');

				if ( !empty($path_exist) && $path_exist != $key)
				{
					$errors[] = $lang['mf_path_exists'];
				}
			}
		}

		/* check names */
		$languages = $GLOBALS['languages'];
		foreach ($languages as $key => $value)
		{
			if (empty($names[$languages[$key]['Code']]))
			{
				$errors[] = str_replace( '{field}', "'<b>{$lang['value']} ({$languages[$key]['name']})</b>'", $lang['notice_field_empty']);
			}
		}

		if( $errors )
		{
			$out = '<ul>';

			/* print errors */
			foreach ($errors as $error)
			{
				$out .= '<li>'. $error .'</li>';
			}
			$out .= '</ul>';
			$_response -> script("printMessage('error', '{$out}');");
		}
		else
		{
			$update = array(
				'fields' => array(
					'Status' => $status,
					'Path' => $item_path
				),
				'where'	=> array(
					'Key' => $item_key
				)
			);

			$old_path = $this -> getOne("Path", "`Key` ='{$item_key}'", "data_formats");

			if ( $item_path != $old_path )
			{
				$path_update  = "UPDATE `". RL_DBPREFIX ."data_formats` SET `Path` = REPLACE(`Path`, '{$old_path}/', '{$item_path}/') ";
				$path_update .= "WHERE `Key` LIKE '{$item_key}_%'";
				$this -> query( $path_update );
			}

			/* update item */
			if ( $GLOBALS['rlActions'] -> updateOne($update, 'data_formats') )
			{
				/* update item name */
				foreach ($languages as $key => $value)
				{
					if ( $this -> getOne('ID', "`Key` = 'data_formats+name+{$item_key}' AND `Code` = '{$languages[$key]['Code']}'", 'lang_keys') )
					{
						$lang_keys[] = array(
							'fields' => array(
								'Value' => $names[$languages[$key]['Code']]
							),
							'where'	=> array(
								'Code' => $languages[$key]['Code'],
								'Key' => 'data_formats+name+'.$item_key
							)
						);
					}
					else
					{
						$insert_phrase[] = array(
							'Module' => 'common',
							'Value' => $names[$languages[$key]['Code']],
							'Code' => $languages[$key]['Code'],
							'Key' => 'data_formats+name+'.$item_key
						);
					}
				}

				$action = false;

				if ( !empty($lang_keys) )
				{
					$action = $GLOBALS['rlActions'] -> update($lang_keys, 'lang_keys');
				}
				if ( !empty($insert_phrase) )
				{
					$action = $GLOBALS['rlActions'] -> insert($insert_phrase, 'lang_keys');
				}

				if ( $action )
				{
					if( $GLOBALS['config']['cache'] )
					{
						$GLOBALS['rlCache'] -> updateDataFormats();
						$this -> cache();
					}

					$_response -> script("printMessage('notice', '{$lang['item_edited']}')");

					$_response -> script( "itemsGrid.reload()" );
					$_response -> script( "$('#edit_item').slideUp('normal')" );
				}
				else
				{
					trigger_error( "Can't edit data_format item, MySQL problems.", E_USER_WARNING );
					$GLOBALS['rlDebug'] -> logger("Can't edit data_format item, MySQL problems.");
				}
			}
		}

		$_response -> script( "$('input[name=item_edit]').val('{$lang['edit']}')" );

		return $_response;
	}

	/**
	* add format item
	*
	* @package ajax
	*
	* @param string $key - item key
	*
	**/
	function ajaxDeleteItem( $key = '', $only_childs = false )
	{
		global $_response, $lang, $rlValid;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		if ( str_replace('_', '', $key) == '' )
		{
			return $_response;
		}

		$key = $rlValid -> xSql( strtolower(trim($key)) );

		$item = $this -> fetch( array('ID', 'Parent_ID'), array('Key' => $key), null, null, 'data_formats', 'row' );

		$sql ="DELETE `T1`, `T2` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('data_formats+name+', `T1`.`Key`) ";

		$sql .="WHERE `T1`.`Key` LIKE '{$key}";
		if ( $only_childs )
		{
			$sql .="_";
		}
		$sql .="%' ";

		$this -> query($sql);

		if ( $GLOBALS['config']['cache'] )
		{
			$GLOBALS['rlCache'] -> updateDataFormats();
			$this -> cache();
		}

		$GLOBALS['rlHook'] -> load('apPhpFormatsAjaxDeleteItem');

		$_response -> script("printMessage('notice', '{$lang['item_deleted']}')");
		$_response -> script( "$('#loading').fadeOut('normal');" );

		$_response -> script( "itemsGrid.reload()" );
		$_response -> script( "$('#edit_item').slideUp('normal');" );
		$_response -> script( "$('#new_item').slideUp('normal');" );

		return $_response;
	}

	/**
	* delete format 
	*
	* @package ajax
	*
	* @param string $key - key
	*
	**/

	function ajaxDeleteFormat( $key )
	{
		global $_response, $lang, $rlValid;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		if ( !$key )
			return $_response;

		$rlValid -> sql($key);

		/* delete item */
		$this -> query("DELETE FROM `". RL_DBPREFIX ."multi_formats` WHERE `Key` = '{$key}' LIMIT 1");
		$format_id = $this -> getOne("ID", "`Key` ='{$key}'", 'data_formats');

		if ( $format_id )
		{
			$sql = "SELECT `ID` FROM `". RL_DBPREFIX ."data_formats` WHERE `Parent_ID` = {$format_id}";
			$child_t = $this -> getAll( $sql );
			foreach( $child_t as $ck => $cv )
			{
				$child .= $cv['ID'].",";
			}

			$sql = "SELECT `ID` FROM `" .RL_DBPREFIX ."data_formats` WHERE FIND_IN_SET(`Parent_ID`, '".rtrim($child, ",")."')";
			$child_t = $this -> getAll( $sql );
			$child = '';
			foreach( $child_t as $ck => $cv )
			{
				$child .= $cv['ID'].",";
			}

			$this -> deleteChildItems(rtrim($child, ",")); //delete all child items except 1st level (which Data Entries can use)

			$this -> deleteFormatChildFields($key, 'listing');
			$this -> deleteFormatChildFields($key, 'account');

			$GLOBALS['rlCache'] -> updateDataFormats();
			$GLOBALS['rlCache'] -> updateForms();

			$GLOBALS['rlHook'] -> load('apPhpFormatsAjaxDeleteItem');
		}

		$_response -> script("printMessage('notice', '{$lang['item_deleted']}')");
		$_response -> script( "$('#loading').fadeOut('normal');" );

		$_response -> script( "multiFieldGrid.reload()" );
		$_response -> script( "$('#edit_item').slideUp('normal');" );
		$_response -> script( "$('#new_item').slideUp('normal');" );

		return $_response;
	}

	/**
	* delete child items | recursive method
	*
	* @param int $parent_ids -  parent_ids
	*
	* @return boolean
	**/
	function deleteChildItems( $ids )
	{
		if ( !$ids )
			return false;

		$GLOBALS['rlValid'] -> sql($ids);

		/*get childs for next recursion*/
		$sql = "SELECT `ID` FROM `".RL_DBPREFIX."data_formats` WHERE FIND_IN_SET(`Parent_ID`, '{$ids}')";
		$child_t = $this -> getAll( $sql );

		$child = '';
		foreach( $child_t as $ck => $cv )
		{
			$child .= $cv['ID'].",";
		}

		/*delete current level items and langs*/
		$sql = "DELETE `T1`, `T2` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
		$sql .= "LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('data_formats+name+', `T1`.`Key`) ";
		$sql .= "WHERE FIND_IN_SET(`T1`.`ID`, '{$ids}')";

		$this -> query( $sql );

		if ( $child )
		{
			return $this -> deleteChildItems(rtrim($child, ","));
		}
		else
		{
			return true;
		}
	}


	/**
	* get bread crumbs | recursive method
	*
	* @param int $parent_id -  parent_id
	*
	* @return array  
	**/
	function getBreadCrumbs( $parent_id = false, $bc = false )
	{
		$parent_id = (int)$parent_id;

		$sql = "SELECT `T1`.`ID`, `T1`.`Parent_ID`, `T1`.`Key`, `T2`.`Value` AS `name` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON CONVERT( `T2`.`Key` USING utf8) = CONCAT('data_formats+name+', `T1`.`Key`) AND `T2`.`Code` = '".RL_LANG_CODE."' ";
		$sql .="WHERE `T1`.`Status` = 'active' AND `T1`.`ID` = '".$parent_id."'";

		$info = $this -> getRow($sql);

		if ( !empty($info) )
		{
			$bc[]  = $info;
		}
		else
		{
			$bc = false;
		}

		if (!empty($info['Parent_ID']))
		{
			return $this -> getBreadCrumbs( $info['Parent_ID'], $bc );
		}
		else
		{
			return $bc;
		}
	}


	/**
	* 
	* get level of item
	*
	* @param int $id - id
	* @param int $level - level
	*
	* @return int
	*
	**/

	function getLevel( $id, $level )
	{
		$id = (int)$id;
		if ( !$id )
			return false;

		$parent = $this -> getOne("Parent_ID", "`ID` = {$id}", "data_formats");

		if ( $parent )
		{
			$level++;
			return $this -> getLevel( $parent, $level );
		}
		else
		{
			return $level;
		}
	}


	/**
	*
	* get total levels of the format
	*
	* @param int $id - id
	* @param int $levels - levels
	*
	* @return int
	*
	**/

	function getLevels( $id, $updatedb = true )
	{
		if( !$id )
			return false;
		
		$i = 2;

		$sql = "SELECT `T{$i}`.`ID` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
		$sql_join .= "LEFT JOIN `".RL_DBPREFIX."data_formats` AS `T2` ON `T2`.`Parent_ID` = `T1`.`ID` ";
		$sql2 = "WHERE `T1`.`Parent_ID` = ".$id." ";
		$sql3 = "ORDER BY `T2`.`ID` DESC LIMIT 1";

		$tmp = $this -> getRow( $sql . $sql_join . $sql2 . $sql3 );
		
		if( $tmp['ID'] )
		{
			$levels = 1;

			while( $tmp['ID'] )
			{
				$i++;
				$levels++;

				$sql = "SELECT `T{$i}`.`ID` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
				$sql_join .="LEFT JOIN `".RL_DBPREFIX."data_formats` AS `T{$i}` ON `T{$i}`.`Parent_ID` = `T".($i-1)."`.`ID`";
				$sql3 ="ORDER BY `T{$i}`.`ID` DESC LIMIT 1";

				$tmp = $this -> getRow( $sql . $sql_join . $sql2 . $sql3 );
			}

			if( $updatedb )
			{
				$sql ="UPDATE `".RL_DBPREFIX."multi_formats` SET `Levels` = ".$levels." WHERE `Key` = '".$this -> getHead( $id )."'";
				$this -> query($sql);
			}

			return $levels;
		}

		
		return 0;
	}


	/**
	* 
	* get top level element key of the data/multi format
	*
	* @param int $id - id
	* @param string $key - key
	*
 	* @return string
	*
	**/

	function getHead( $id, $key )
	{

		if ( !$id && !$key )
			return false;

		$id = (int)$id;
		$GLOBALS['rlValid'] -> sql($key);

		if ( $id )
		{
			$parent = $this -> getOne("Parent_ID", "`ID` = {$id}", "data_formats");
		}
		elseif( $key )
		{
			$parent = $this -> getOne("Parent_ID", "`Key`='{$key}'", "data_formats");
		}
		else
		{
			return false;
		}

		if ( $parent )
		{
			return $this -> getHead( $parent );
		}
		else
		{
			return $this -> getOne("Key", "`ID` = {$id}", "data_formats");
		}
	}

	/**
	* create sub fields
	*
	* @param array $field_info - field info
	* @param string $type - type
	*
	**/

	function createSubFields( $field_info, $type = 'listing' )
	{
		if( strpos($field_info['key'], 'level') || !$field_info['key'])
		{
			return false;
		}

		$format_id = $this -> getOne("ID", "`Key` = '".$field_info['data_format']."'", 'data_formats');

		$head_field_key = $field_info['key'];

		if( !$format_id )
			return false;

		$levels = $this -> getLevels( $format_id );

		if( $levels < 2 )
			return false;

		for( $level=1; $level < $levels; $level++ )
		{
			$field_key = $head_field_key."_level".$level;
			$prev_fk = $level == 1 ? $head_field_key : $head_field_key."_level".($level-1);

			$sql ="SHOW FIELDS FROM `".RL_DBPREFIX."{$type}s` WHERE `Field` = '".$field_key."'";
			$field_exists = $this -> getRow( $sql );

			if( !$field_exists )
			{
				$sql ="SELECT `Key` FROM `".RL_DBPREFIX."{$type}_fields` WHERE `Key` = '".$field_key."'";
				$field_exists = $this -> getRow( $sql );
			}

			if( !$field_exists )
			{
				$sql ="ALTER TABLE `".RL_DBPREFIX."{$type}s` ADD `".$field_key."` VARCHAR(255) NOT NULL AFTER `".$prev_fk."`";
				$this -> query($sql);

				$field_insert_info = array(
					'Key' => $field_key,
					'Condition' => $field_info['data_format'],
					'Type' => 'select',
					'Status' => 'active'
				);

				if( $type == 'listing' )
				{
					$field_insert_info['Add_page'] = 1;
					$field_insert_info['Details_page'] = 1;
					$field_insert_info['Readonly'] = 1;
				}
				
				preg_match('/country|location|state|region|province|address/i', $head_field_key, $match);
				if( $match )
				{						
					$field_insert_info['Map'] = 1;
				}

				if( $GLOBALS['rlActions'] -> insertOne( $field_insert_info, $type."_fields" ) )
				{
					$field_id = mysql_insert_id();

					if( $type == 'listing' )//add entry after the 'parent' field to the search and submit forms
					{
						$prev_field_id = $this -> getOne("ID", "`Key` = '".$prev_fk."'", 'listing_fields');

						$sql ="UPDATE `".RL_DBPREFIX."listing_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$prev_field_id},', ',{$prev_field_id},{$field_id},'))) WHERE FIND_IN_SET('{$prev_field_id}', `Fields`) ";
						$this -> query( $sql );

						$sql ="UPDATE `".RL_DBPREFIX."search_forms_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$prev_field_id},', ',{$prev_field_id},{$field_id},'))) WHERE FIND_IN_SET('{$prev_field_id}', `Fields`) ";
						$this -> query( $sql );
					}elseif ( $type == 'account' )
					{
						$prev_field_id = $this -> getOne("ID", "`Key` = '".$prev_fk."'", 'account_fields');
							
						$sql ="SELECT `Category_ID`, `Position`, `Group_ID` FROM `".RL_DBPREFIX."account_submit_form` WHERE `Field_ID` =".$prev_field_id;
						$afields = $this -> getAll( $sql );
						foreach( $afields as $afk => $afield )
						{
							$sql = "UPDATE `".RL_DBPREFIX."account_submit_form` SET `Position` = `Position`+1 ";
							$sql .="WHERE `Position` > ".$afield['Position']." AND `Category_ID` = ".$afield['Category_ID'];
							$this -> query($sql);

							$insert[$afk]['Position'] = $afield['Position']+1;
							$insert[$afk]['Category_ID'] = $afield['Category_ID'];
							$insert[$afk]['Group_ID'] = $afield['Group_ID'];
							$insert[$afk]['Field_ID'] = $field_id;
						}
						$GLOBALS['rlActions'] -> insert($insert, 'account_submit_form');
					}

					$head_field_lkey = $type.'_fields+name+'.$head_field_key;

					foreach ( $GLOBALS['languages'] as $key => $value )
					{
						$head_field_name = $this -> getOne("Value", "`Key` ='{$head_field_lkey}' AND `Code` = '".$GLOBALS['languages'][$key]['Code']."'", "lang_keys");
						$lang_keys[] = array(
							'Code' => $GLOBALS['languages'][$key]['Code'],
							'Module' => 'common',
							'Key' => $type.'_fields+name+'.$field_key,
							'Value' => $head_field_name." Level ".$level,
							'Plugin' => 'multiField'
						);
					}

					$GLOBALS['rlActions'] -> insert($lang_keys, 'lang_keys');
				}
			}
		}
		$GLOBALS['rlCache'] -> updateForms();
	}


	/**
	* delete sub fields
	*
 	* @param array $field_info - field info
	* @param string $type - type
	*
	**/

	function deleteSubFields( $field_info, $type = 'listing' )
	{
		if( strpos($field_info['key'], 'level') )
		{
			return false;
		}

		$field_key = $field_info['key'];

		if( !$field_key )
		{
			return false;
		}

		$old_format = $this -> getOne("Condition", "`Key` = '".$field_key."'", $type.'_fields');
		
		$sql ="SELECT * FROM `".RL_DBPREFIX."listing_fields` WHERE `Condition` = '".$old_format."' AND `Key` REGEXP '".$field_key."_level[0-9]'";
		$fields = $this -> getAll( $sql );

		if( !$fields )
		{
			$sql ="SHOW FIELDS FROM `".RL_DBPREFIX."{$type}s` WHERE `Field` REGEXP '".$field_key."_level[0-9]'";
			$fields_struct = $this -> getAll( $sql );

			foreach( $fields_struct as $key => $field )
			{
				$sql ="ALTER TABLE `".RL_DBPREFIX."{$type}s` DROP `".$field['Field']."`";
				$this -> query( $sql );
			}
		}

		foreach( $fields as $key => $field )
		{
			$sql ="ALTER TABLE `".RL_DBPREFIX."{$type}s` DROP `".$field['Key']."`";
			$this -> query( $sql );

			if( $type == 'listing' )
			{
				$sql ="UPDATE `".RL_DBPREFIX."listing_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$field['ID']},', ','))) WHERE FIND_IN_SET('{$field['ID']}', `Fields`) ";
				$this -> query( $sql );
			
				$sql ="UPDATE `".RL_DBPREFIX."search_forms_relations` SET `Fields` = TRIM(BOTH ',' FROM ( REPLACE( CONCAT(',',`Fields`,','), ',{$field['ID']},', ','))) WHERE FIND_IN_SET('{$field['ID']}', `Fields`) ";
				$this -> query( $sql );

				$sql = "DELETE FROM `".RL_DBPREFIX."short_forms` WHERE `Field_ID` = ".$field['ID'];
				$this -> query( $sql );
			}elseif( $type == 'account' )
			{
				$sql = "DELETE FROM `" . RL_DBPREFIX . "account_search_relations` WHERE `Field_ID` = '{$field['ID']}'";
				$this -> query( $sql );
			
				$sql = "DELETE FROM `" . RL_DBPREFIX . "account_short_form` WHERE `Field_ID` = '{$field['ID']}'";
				$this -> query( $sql );
			
				$sql = "DELETE FROM `" . RL_DBPREFIX . "account_submit_form` WHERE `Field_ID` = '{$field['ID']}'";
				$this -> query( $sql );
			}
		}

		$sql = "DELETE `T1`, `T2` FROM `".RL_DBPREFIX."{$type}_fields` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('{$type}_fields+name+', `T1`.`Key`) ";
		$sql .="WHERE `T1`.`Condition` = '".$old_format."' AND `T1`.`Key` REGEXP '".$field_key."_level[0-9]'";

		$this -> query($sql);

		$GLOBALS['rlCache'] -> updateForms();
	}


	/**
	* adapt form 
	* 
	* @param array $form - fields form
	*
 	* @return array
	*
	**/

	function adaptForm( $form )
	{
		foreach( $form as $fk => $group )
		{
			foreach( $group['Fields'] as $grk => $field )
			{
				if( $GLOBALS['multi_formats'][ $field['Condition'] ] && strpos($field['Key'], 'level') > 0)
				{
					preg_match('/(.*)_level([0-9])/i', $field['Key'], $match);

					if( $top_field = $match[1] )
					{
						$level = $match[2];
						$prev_field = $level > 1 ? $top_field.($level-1) : $top_field;

						if( $_POST['f'][$prev_field] )
						{
							$prev_value = $_POST['f'][$prev_field];
						}else
						{
							$prev_value = $this -> getOne('Default', "`Key` = '".$field['Condition']."'", 'multi_formats');
						}

						if( $prev_value )
						{
							$format_values = $this -> getMDF( $prev_value, 'alphabetic' );
						}

						$form[$fk]['Fields'][$grk]['Values'] = $format_values;
					}
				}
			}
		}
	
		return $form;
	}

	/**
	* rebuild multi fields - rebuild sub fields 
	*
	* @package ajax
	*
	* @param string $self - button id 
	* @param string $mode - can be false or delete_existing
	* 
	**/

	function ajaxRebuildMultiField($self, $mode = false, $no_ajax = false)
	{
		global $lang;
	
		if ( !$no_ajax )
		{
			global $_response;

			// check admin session expire
			if ( $this -> checkSessionExpire() === false && !$direct )
			{
				$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
				$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
				$_response -> redirect( $redirect_url );
			}
		}

		$sql = "SELECT * FROM `".RL_DBPREFIX."multi_formats` WHERE 1 ";
		$multi_formats = $this -> getAll( $sql );

		foreach( $multi_formats as $key => $format)
		{
			$sql  = "SELECT `Condition` as `data_format`, `Key` as `key` FROM `". RL_DBPREFIX ."listing_fields` ";
			$sql .= "WHERE `Condition` = '{$format['Key']}' AND `Key` NOT REGEXP 'level[0-9]'";
			$related_listing_fields = $this -> getAll($sql);

			foreach($related_listing_fields as $rfKey => $rfield )
			{
				if ( $mode == 'delete_existing' )
				{
					$this -> deleteSubFields($rfield, 'listing');
				}
				$this -> createSubFields( $rfield, 'listing' );
			}
		
			$sql  = "SELECT `Condition` as `data_format`, `Key` as `key` FROM `". RL_DBPREFIX ."account_fields` ";
			$sql .= "WHERE `Condition` = '{$format['Key']}' AND `Key` NOT REGEXP 'level[0-9]'";
			$related_account_fields = $this -> getAll($sql);

			foreach($related_account_fields as $rfKey => $rfield )
			{
				if ( $mode == 'delete_existing' )
				{
					$this -> deleteSubFields( $rfield, 'account' );
				}
				$this -> createSubFields( $rfield, 'account' );
			}
		}

		if ( !$no_ajax )
		{
			$_response -> script( "printMessage('notice', '{$lang['mf_fields_rebuilt']}')" );
			$_response -> script( "$('{$self}').val('{$lang['rebuild']}');" );

			return $_response;
		}
	}

	/**
	* 
	* getFData - get data from flynax source server 
	* 
	* @param array $params - params to get data
 	* @return json string
	*
	**/
	function getFData( $params )
	{
		set_time_limit(0);
		$this -> time_limit = 0;

		$vps = "http://205.234.232.103/~flsource/getdata.php?nv&domain={$GLOBALS['license_domain']}&license={$GLOBALS['license_number']}";  // vps4

		foreach( $params as $k => $p )
		{
			$vps .="&".$k."=".$p;
		}
		$content = $this -> getPageContent( $vps );

		$GLOBALS['reefless'] -> loadClass("Json");

		return $GLOBALS['rlJson'] -> decode( $content );
	}

	/**
	* ajaxListSources - lists available on server databases
	* 
	* @package ajax
	*
	**/

	function ajaxListSources()
	{
		global $_response;

		$data = $this -> getFData( array("listdata" => true) );
		$GLOBALS['rlSmarty'] -> assign( "data", $data );

		$tpl = RL_PLUGINS.'multiField' . RL_DS . 'admin' . RL_DS . 'flsource.tpl';
		$_response -> assign("flsource_container", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));
		$_response -> script("$('#flsource_container').removeClass('block_loading');");
		$_response -> script("$('#flsource_container').css('height', 'auto').fadeIn('normal')");
		

		return $_response;	
	}

	/**
	* ajaxExpandSource - lists available data items
	* 
	* @package ajax
	*
	**/
	function ajaxExpandSource( $table )
	{
		global $_response;

		$data = $this -> getFData( array("table" => $table) );

		$GLOBALS['rlSmarty'] -> assign('topdata', $data);
		$GLOBALS['rlSmarty'] -> assign('table', $table);

		$tpl = RL_PLUGINS.'multiField' . RL_DS . 'admin' . RL_DS . 'flsource.tpl';
		$_response -> assign("flsource_container", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));
		$_response -> script("$('#flsource_container').fadeIn('normal')");
		$_response -> script("$('html, body').animate({ scrollTop: $('#flsource_container').offset().top-25 }, 'slow');");
		$_response -> call('handleSourceActs');

		return $_response;
	}

	/**
	* ajaxImportSource - imports data from server
	* 
	* @package ajax
	*
	**/
	function ajaxImportSource( $parents = '', $table = false, $one_ignore = false, $resume = false )
	{
		global $_response;

		if ( !$resume )
		{
			if ( empty($parents) )
			{
				$data = $this -> getFData( array("table" => $table) );
				$parents = "";
				foreach($data as $val)
				{
					$parents .= $val -> Key . ",";
				}
			}

			$one_ignore = !empty($one_ignore) && $one_ignore != "false" ? 1 : 0;
			$parents = explode( ",", trim($parents, ",") );

			unset( $_SESSION['mf_parent_ids'] );
			$_SESSION['mf_import']['total'] = count($parents);
			$_SESSION['mf_import']['parents'] = $parents;
			$_SESSION['mf_import']['table'] = $table;
			$_SESSION['mf_import']['one_ignore'] = $one_ignore;
			$_SESSION['mf_import']['top_key'] = $_GET['parent'];
			$_SESSION['mf_import']['parent_id'] = $this -> getOne("ID", "`Key` = '".$_GET['parent']."'", "data_formats");
			$_SESSION['mf_import']['per_run'] = $GLOBALS['config']['mf_import_per_run'];
			$_SESSION['mf_import']['available_rows'] = count($parents);
		}

		$_response -> script("$('#load_cont').fadeOut();");
		if ( $parents )
		{
			$_response -> script("var item_width = width = percent = percent_value = sub_width = sub_item_width = sub_percent = sub_percent_value = sub_percent_to_show = percent_to_show = 0;");
			$_response -> script("$('body').animate({ scrollTop: $('#flsource_container').offset().top-90 }, 'slow', function() { importExport.start(); });");
		}
		else
		{
			$_response -> script("$('body').animate({ scrollTop: $('#flsource_container').offset().top-90 }, 'slow');");
			$_response -> script("printMessage('error', 'nothing selected')");
		}

		return $_response;
	}


	/**
	* ajax rebuildPath - paths rebuilding 
	* 
	* @package ajax
	*
	**/
	function ajaxRebuildPath( $self, $firstrun = false, $no_ajax = false )
	{
		global $_response;

		$firstrun = $firstrun == "false" ? false : true;

		if ( $_SESSION['mf_rbpath'] && !$firstrun )
		{
			$this -> updatePath( $_SESSION['mf_rbpath'][0] );

			if ( !$_SESSION['mf_rbpath_start'] )
			{
				unset($_SESSION['mf_rbpath'][0]);
				$_SESSION['mf_rbpath'] = array_values($_SESSION['mf_rbpath']);
			}

			if ( $_SESSION['mf_rbpath'][0] )
			{
				$message = $GLOBALS['lang']['mf_geo_path_processing'];
				$_response -> script("printMessage('notice', '{$message}')");
				$_response -> script("xajax_rebuildPath('{$self}', false);");
				return $_response;
			}
			else
			{
				$message = $GLOBALS['lang']['mf_geo_path_rebuilt'];

				if ( $GLOBALS['config']['cache'] )
				{
					$this -> cache(null, true);
				}

				$_response -> script("printMessage('notice', '{$message}')");
				$_response -> script( "$('{$self}').val('{$GLOBALS['lang']['mf_refresh']}');" );

				return $_response;
			}

			return $_response;
		}
		else
		{
			$geo_format = $this -> getOne("Key", "`Geo_filter` = '1'", "multi_formats");
			$format = $this -> fetch(array("ID", "Key"), array("Key" => $geo_format ), null, null, "data_formats", "row");

			$cnt = $this -> getRow("SELECT COUNT(`ID`) AS `cnt` FROM `".RL_DBPREFIX."data_formats` WHERE `Key` LIKE '{$geo_format}_%'");

			if ( $cnt['cnt'] < 20000 || $no_ajax )
			{
				if ( $format )
				{
					$this -> updatePathPlain( $format );
					$message = $GLOBALS['lang']['mf_geo_path_rebuilt'];
					if ( $GLOBALS['config']['cache'] )
					{
						$this -> cache(null, true);
					}
				}
				else
				{
					$message = $GLOBALS['lang']['mf_geo_path_nogeo'];
				}
				$_response -> script("printMessage('notice', '{$message}')");
				$_response -> script( "$('{$self}').val('{$GLOBALS['lang']['mf_refresh']}');" );

				return $_response;
			}

			if ( $format )
			{
				unset($_SESSION['mf_rbpath'], $_SESSION['mf_rbpath_start']);
				$this -> updatePath( $format, true ); // update top level items.

				$_response -> script("xajax_rebuildPath('{$self}', false);");
				return $_response;
			}
			else
			{
				$message = $GLOBALS['lang']['mf_geo_path_nogeo'];
				$_response -> script("printMessage('notice', '{$message}')");
				$_response -> script( "$('{$self}').val('{$GLOBALS['lang']['mf_refresh']}');" );

				return $response;
			}
		}
	 }

	/**
	* ajaxRebuildPath - paths rebuilding, recursive function
	* 
	*
	**/
	function updatePath( $parent, $top_level = false, $nolimit = false )
	{
		if( !$nolimit && !$top_level )
		{
			$limit =  5;
			$start = $_SESSION['mf_rbpath_start'] ? $_SESSION['mf_rbpath_start'] : 0;

			$add_limit = "{$start}, {$limit}";
		}

		$items = $this -> fetch( array("Key", "ID"), array("Parent_ID" => $parent['ID']), null, $add_limit, "data_formats" );

		if( count($items) == $limit && $limit )
		{
			$_SESSION['mf_rbpath_start'] = $start+$limit;
		}elseif( $limit )
		{
			unset($_SESSION['mf_rbpath_start']);
		}

		foreach( $items as $key => $item )
		{
			$path = $parent['Path'] ? $parent['Path']."/" : '';
			$path .= $GLOBALS['rlValid'] -> str2path( str_replace($parent['Key']."_", "", $item['Key']) );

			$sql ="UPDATE `".RL_DBPREFIX."data_formats` SET `Path` = '".$path."' WHERE `ID` = ".$item['ID'];
			$this -> query($sql);

			$item['Path'] = $path;
			
			if( $top_level == false )
			{
				$this -> updatePath( $item, false, true );
			}else
			{
				$_SESSION['mf_rbpath'][] = $item;
			}
		}
	}

	/**
	* ajaxRebuildPath - paths rebuilding, recursive function
	* 
	*
	**/
	function updatePathPlain( $parent )
	{
		$items = $this -> fetch( array("Key", "ID"), array("Parent_ID" => $parent['ID']), null, null, "data_formats" );

		foreach( $items as $key => $item )
		{
			$path = $parent['Path'] ? $parent['Path']."/" : '';
			$path .= $GLOBALS['rlValid'] -> str2path( str_replace($parent['Key']."_", "", $item['Key']) );

			$sql ="UPDATE `".RL_DBPREFIX."data_formats` SET `Path` = '".$path."' WHERE `ID` = ".$item['ID'];
			$this -> query($sql);

			$item['Path'] = $path;
			$this -> updatePathPlain( $item );
		}
	}

	/**
	* ajaxGeoGetNext - get items to next selector
	* 
	* @package ajax
	*
	**/
	function ajaxGeoGetNext( $path = false, $level = 0, $levels )
	{
		global $_response, $geo_format;

		$level = $level ? $level : 0;

		$next_values = $this -> getMDF( $key, $GLOBALS['geo_filter_data']['order_type'], true, $path );
		
		$options = $empty_option = '<option value="0">'. $GLOBALS['lang']['any'] .'</option>';
		foreach( $next_values as $key => $option )
		{
			$options .='<option value="'. $option['Path'] .'">'. $option['name'] .'</option>';
		}

		for( $i=$level+2; $i<=(int)$levels; $i++ )
		{
			$_response -> script("$('#geo_selector_level". $i ."').attr('disabled', 'disabled').val('". $empty_option ."')");
		}
		
		$options = $GLOBALS['rlValid'] -> xSql( $options );
		$target = "geo_selector_level".($level+1);

		$_response -> script("$('#".$target."').html('". $options ."').removeAttr('disabled').removeClass('disabled')");

		return $_response;
	}


	/**
	* build - build related fields
	*
	* @package ajax
	*
	* @param string $last_value - value of last selected field
	* @param string $last_field - key of last selected field
 	* @param string $form_key - form key which contain the field (for search forms only) 
	* @param int $levels - current number of selectors on the page
	* @param string $type - listing or account - to define target fields 
	* 
	**/

	function ajaxGeoBuild( $last_value = false, $last_field = false, $form_key = false )
	{
		global $_response, $geo_filter_data, $config;

		$target = "geo_selector";
		$path1 = $this -> getOne("Path", "`Key` = '".$geo_filter_data['location'][0]['Key']."'", 'data_formats');
		$_response -> script( "$('#geo_selector').val('".$path1."/')" );

		$empty_option = '<option value="0">'. $GLOBALS['lang']['any'] .'</option>';
		$level = 1;

		foreach( $geo_filter_data['location'] as $key => $item)
		{
			$values = $this -> getMDF( $item['Key'], $geo_filter_data['order_type'], true );

			$target = "geo_selector_level".$level;

			$options = $empty_option;
			foreach($values as $opt_key => $option)
			{
				$sel = $option['Key'] == $geo_filter_data['location'][$key+1]['Key'] ? 'selected="selected"' : '';
				$options .='<option '. $sel .' value="'. $option['Path'] .'">'. $option['name'] .'</option>';
			}

			$_response -> script("$('#". $target ."').html('". $options ."').removeAttr('disabled').removeClass('disabled')");

			/* reset link building */
			if( $config['mf_geo_subdomains'] )
			{
				if( $level == 1 )
				{
					$reset_path = str_replace($geo_filter_data['clean_url'], "[geo_url]","" );					
				}else
				{
					$path = explode("/", trim($item['prev_path'],"/"));
					$sub = array_splice($path, 0, 1 );

					$reset_path = str_replace("[geo_sub]", $sub[0], $geo_filter_data['clean_url_sub']);
					$reset_path = str_replace("[geo_url]", implode("/", $path), $reset_path);					
				}
			}else
			{
				$reset_path = str_replace("[geo_url]", $item['prev_path'], $geo_filter_data['clean_url']);
			}
			$reset_level = $level-1 > 0 ? $level-1 :'';
			$reset_target = "geo_selector_level".$reset_level;
			$_response -> script("$('#". $reset_target ."').next('a').attr('href', '".$reset_path."').removeClass('hide')");
			/* reset link building end */

			$level++;
		}

		return $_response;
	}

	/**
	* prepareGet - get variables preparation
	*
	* @return array
	*
	**/
	function prepareGet()
	{
		global $geo_format;

		if( $GLOBALS['config']['mf_geo_subdomains'] && $_POST['xjxfun'] && RL_URL_HOME != $_SERVER['HTTP_HOST'] )
		{			
			$domain = $GLOBALS['rlValid'] -> getDomain( RL_URL_HOME );			
			$domain = str_replace('www.', '', $domain);
			$subdomain = str_replace(".".$domain, "", $_SERVER['HTTP_HOST']);

			if( $subdomain && $subdomain != 'www' )
			{
				$arr = array_reverse(explode("/", $_GET['rlVareables']));
				
				$_GET['rlVareables'] = implode("/", array_reverse($arr));
				$_GET['page'] = $subdomain;
			}
		}

		if( !$_GET['page'] && !isset($_GET['reset_location']))
		{
			return false;
		}

		if( strlen($_GET['page']) == 2 )
		{
			$lang = $_GET['page'];

			$tmp = explode("/", $_GET['rlVareables']);
			$page = array_splice($tmp, 0, 1);

			if( $this -> getOne("Key", "`Path` = '".$page[0]."'", 'data_formats') )
			{
				$_GET['page'] = $page[0];
				$_GET['rlVareables'] = implode("/", $tmp);
			}else
			{
				return false;
			}
		}
		
		if( $_GET['page'] && $location[0] = $this -> getOne("Key", "`Path` = '".$_GET['page']."'", 'data_formats') )
		{
			$page_old = $_GET['page'];

			$_GET['rlVareables'] = substr($_GET['rlVareables'], -5, 5)  == ".html" ? substr($_GET['rlVareables'], 0, -5) : $_GET['rlVareables'];

			$tmp_vars = $tmp_vars_old = explode("/", trim( $_GET['rlVareables'], "/" ));

			$tmp_vars = $this -> prepareGetVars( $geo_format, $_GET['page'], $tmp_vars, 1, $location );

			$tmp_vars = array_values( $tmp_vars );

			if( $lang )
			{
				$_GET['page'] = $lang;
			}else
			{
				$_GET['page'] = $tmp_vars[0];
				unset($tmp_vars[0]);			
			}
			
			$loc_url = $page_old . "/". implode("/", array_diff( $tmp_vars_old, array_merge( array(0 => $_GET['page']), $tmp_vars ) ));

			$_GET['rlVareables'] = '';
			foreach( $tmp_vars as $key => $value )
			{
				$_GET['rlVareables'] .= $value."/";
			}
	
			$_GET['rlVareables'] = trim( $_GET['rlVareables'], "/");
		}elseif( $lang )
		{
			$_GET['page'] = $lang;
		}

		$lfields = $this -> fetch( array("Key"), array('Condition' => $geo_format, 'Status' => 'active'), "ORDER BY `Key`", null, 'listing_fields' );
		foreach( $lfields as $key => $field )
		{
			$out['lfields_list'][] = $field['Key'];

			preg_match('/(.*)(_level([0-9]))/si', $field['Key'], $match);
			
			if( !$match[3] && $location[0])
			{
				$out['lfields'][$field['Key']] = $location[0];
			}elseif( $location[$match[3]] )
		 	{
				$out['lfields'][$field['Key']] = $location[$match[3]];
			}
		}

		$afields = $this -> fetch( array("Key"), array('Condition' => $geo_format, 'Status' => 'active'), "ORDER BY `Key`", null, 'account_fields' );
		foreach( $afields as $key => $field )
		{
			preg_match('/(.*)(_level([0-9]))/si', $field['Key'], $match);

			if( !$match[3] && $location[0])
			{
				$out['afields'][$field['Key']] = $location[0];
			}elseif( $location[$match[3]] )
			{
				$out['afields'][$field['Key']] = $location[$match[3]];
			}
		}

		foreach( $this -> fetch(array("Path"), array("Geo_exclude" => '1'), null, null, 'pages') as $page )
		{
			$out['clean_pages'][] = $page['Path'];
		}

		$out['order_type'] = $this -> getOne("Order_type", "`Key` = '".$geo_format."'", "data_formats");
		$out['location'] = $location[0] ? $location : $_SESSION['geo_location'];
		$out['geo_url'] = trim($loc_url, "/");

		if( isset($_GET['reset_location']) )
		{
			unset($_SESSION['geo_url']);
			unset($_SESSION['geo_location']);
			$out['geo_url'] = '';
			$out['location'] = '';
		}
		elseif( !$out['geo_url'] && !isset($_GET['reset_location']))
		{			
			$out['geo_url'] = $_SESSION['geo_url'];
		}else
		{			
			$_SESSION['geo_url'] = $out['geo_url'];
			$_SESSION['geo_location'] = $out['location'];
		}

		$tmp_paths = explode("/", $out['geo_url']);
		unset($tmp);
		foreach( $out['location'] as $key => $item )
		{
			if( $item )
			{
				$tmp[$key]['Key'] = $item;
				//$tmp[$key]['name'] = $GLOBALS['lang']['data_formats+name+'.$item] ? $GLOBALS['lang']['data_formats+name+'.$item] : $this -> getOne("Value", "`Key` = 'data_formats+name+".$item."'/* AND `Code` = '".RL_LANG_CODE."'*/", "lang_keys");
				$tmp_path2 = array_slice($tmp_paths, 0, $key);
				$tmp[$key]['prev_path'] = $tmp_path2 ? implode("/", $tmp_path2)."/" : "";

			}
		}
		$out['location'] = $tmp;

		return $out;
	}

	/**
	* prepareGet - get variables preparation, recursive function
	*
	* @return array
	*
	**/
	function prepareGetVars( $geo_format, $page, $tmp_vars, $level = 1, &$location = false )
	{
		if( $location[$level] = $this -> getOne("Key", "`Path` = '".$page."/".$tmp_vars[0]."'", 'data_formats') )
		{
			$page = $page."/".$tmp_vars[0];
			unset( $tmp_vars[0] );

			$level++;

			return $this -> prepareGetVars( $geo_format, $page, array_values($tmp_vars), $level, $location );
		}else
		{
			unset($location[$level]);
		}

		return $tmp_vars;
	}

	/**
	* adaptCategories - recount categories depending of current location
	*
	* @param array $categories - categories 
	*
	* @return array
	*
	**/
	function adaptCategories( $categories )
	{
		global $geo_filter_data;

		if( !$geo_filter_data['geo_url'] || !$geo_filter_data['lfields'] )
			return $categories;

		foreach( $categories as $key => &$category )
		{
			$sql = "SELECT COUNT(`T1`.`ID`) AS `Count` FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
			$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
			$sql .= "WHERE (`T1`.`Category_ID` = '{$category['ID']}' OR FIND_IN_SET('{$category['ID']}', `Crossed`) > 0 ";

			if ( $GLOBALS['config']['lisitng_get_children'] )
			{
				$sql .= "OR FIND_IN_SET('{$category['ID']}', `T3`.`Parent_IDs`) > 0 ";
			}

			$sql .= ") AND `T1`.`Status` = 'active' ";
			$sql .= "AND (UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0) ";

			foreach( $geo_filter_data['lfields'] as $field => $value )
			{
				$sql .="AND `T1`.`{$field}` = '{$value}' ";
			}
			
			$cat_listings = $this -> getRow( $sql );

			$category['Count'] = $cat_listings['Count'];
		}

		return $categories;
	}

	/**
	* getGeoBlockData - gets data to geo filtering box
	*
	* @param string $geo_format - key of format used for geo filtering
	*
	* @return array
	*
	**/
	function getGeoBlockData( $geo_format )
	{
		global $config;

		$this -> detectLocation();

		foreach( $GLOBALS['geo_filter_data']['location'] as $key => $item )
		{
			if( $item )
			{
				$GLOBALS['geo_filter_data']['location'][$key]['name'] = $GLOBALS['lang']['data_formats+name+'.$item['Key']] ? $GLOBALS['lang']['data_formats+name+'.$item['Key']] : $this -> getOne("Value", "`Key` = 'data_formats+name+".$item['Key']."' AND `Code` = '".RL_LANG_CODE."'", "lang_keys");
			}
		}

		$include_childs = $config['mf_geo_block_list'] && $config['mf_geo_multileveled'] ? $config['mf_geo_levels_toshow'] - 1: false;

		$start = 0;		

		if( $config['mf_geo_block_list'] && !$config['mf_geo_multileveled'] && $GLOBALS['geo_filter_data']['location'][0] )
		{
			$parent = current(array_slice( $GLOBALS['geo_filter_data']['location'], -1, 1 ));	
		}else
		{
			$parent = $geo_format;
		}

		$pk = $parent['Key'] ? $parent['Key'] : $parent;
		$hashstring = md5($pk.$include_childs);

		if( $config['mf_cache_client'] )
		{
			if( isset($_GET['reset_location']) )
			{
				unset( $_SESSION['geo_location'] );
			}
			elseif( $_SESSION['geo_block_data'][$hashstring] && $GLOBALS['config']['cache'] )
			{
				return $_SESSION['geo_block_data'][$hashstring];
			}
		}

		$order_type = $GLOBALS['geo_filter_data']['order_type'] ? $GLOBALS['geo_filter_data']['order_type'] : $this -> getOne("Order_type", "`Key` = '".$geo_format."'", "data_formats");
		$data = $this -> getMDF( $parent, $order_type, true, false, $include_childs );

		if( $config['mf_cache_client'] )
		{
			if( $data && !$_SESSION['geo_block_data'][$hashstring] )
			{
				if( count($_SESSION['geo_block_data']) > 5 )
				{
					unset($_SESSION['geo_block_data']);
				}
				$_SESSION['geo_block_data'][$hashstring] = $data;
			}
		}

		return $data;
	}

	/**
	* 
	* seoBaseHook - hook code for geo filtering module
	* 
	**/

	function seoBaseHook()
	{
		global $geo_filter_data, $bPath, $config;

		if( !$GLOBALS['geo_format'] )
		{
			return false;
		}

		$_SERVER['REQUEST_URI'] = str_replace('?reset_location', '', $_SERVER['REQUEST_URI']);

		if( $config['mf_geo_subdomains'] && $geo_filter_data['geo_url'] && !$_GET['listing_id'] )
		{
			$patharr = explode("/", $geo_filter_data['geo_url']);
			$geo_filter_data['geo_sub'] = $patharr[0];unset($patharr[0]);
			$geo_filter_data['geo_url'] = implode("/", $patharr);
			$geo_filter_data['geo_sub_home'] = preg_replace('#http://(www.)?#', 'http://'.$geo_filter_data['geo_sub'].'.', RL_URL_HOME);

			/*redirect to subdomain if url contains geo url but without subdomain */
			if( !isset($_GET['wildcard']) && !in_array( $_GET['page'], $GLOBALS['geo_filter_data']['clean_pages']) && !$_POST )
			{				
				$tmp = trim( preg_replace( '/'.str_replace("/", "\/", $geo_filter_data['geo_sub']).'(\/)?/i', "", $_SERVER['REQUEST_URI']), "/" );
				
				if( RL_DIR != '' && defined('RL_DIR') )
				{	
					$tmp = preg_replace('/^'.str_replace('/', '\/', RL_DIR).'/i', '', $tmp);
				}
				setcookie( 'mf_geo_detected', true, time()+360, $GLOBALS['domain_info']['path'], $GLOBALS['domain_info']['domain'] );
				header ('HTTP/1.1 301 Moved Permanently');
				header ('Location: '.$geo_filter_data['geo_sub_home'].$tmp);
				exit;
			}
		}

		if( $geo_filter_data['geo_url'] )
		{			
			$tmp = trim( preg_replace( '/'.str_replace("/", "\/", $geo_filter_data['geo_url']).'(\/)?/i', "[geo_url]", $_SERVER['REQUEST_URI']), "/" );

			if( RL_DIR != '' && defined('RL_DIR') )
			{	
				$tmp = preg_replace('/^'.str_replace('/', '\/', RL_DIR).'/i', '', $tmp);
			}
		}else
		{
			if( $GLOBALS['config']['lang'] != RL_LANG_CODE )
			{
				$tmp = RL_LANG_CODE."/";
			}
			
			$tmp .= '[geo_url]';
			$tmp .= trim(str_replace(RL_LANG_CODE."/",'/', $_SERVER['REQUEST_URI']), "/");

			if( RL_DIR != '' && defined('RL_DIR') )
			{			
				$tmp = str_replace('[geo_url]'.rtrim(RL_DIR, '/'), '[geo_url]', $tmp);
				$tmp = str_replace('[geo_url]/', '[geo_url]', $tmp);
			}			
		}
		
		// if( $config['mf_geo_subdomains'] && !$config['mf_geo_block_list'])
		// {
		// 	$tmp = $tmp != '[geo_url]' ? $tmp.'/' : '[geo_url]';
		// }

		$geo_filter_data['clean_url'] = RL_URL_HOME . $tmp;		
		$geo_filter_data['clean_url_sub'] = preg_replace("#(https?://)(www.)?#", "$1[geo_sub].", $geo_filter_data['clean_url']);

		$geo_filter_data['bPath'] = $bPath;

		if( $geo_filter_data['geo_url'] )
		{
			$bPath .= $geo_filter_data['geo_url'] . "/";			
		}

		if( $config['mf_geo_subdomains'] )
		{
			if( $geo_filter_data['geo_sub'] )
			{
				$bPath = preg_replace('#http://(www.)?#', 'http://'.$geo_filter_data['geo_sub'].'.', $bPath);
			}			
		}		
	}

	/**
	* detectLocation 
	*
	**/
	function detectLocation()
	{
		global $rlValid, $geo_filter_data, $reefless, $config, $domain_info;

		if( $_GET['q'] == 'ext' || $_POST['xjxfun'] || !$config['mf_geo_autodetect'] || isset($_GET['reset_location']) || $_COOKIE['mf_geo_detected'] || $GLOBALS['rlMobile'] -> isMobile )
		{
			if(isset($_GET['reset_location']))
			{
				setcookie( 'mf_geo_detected', true, time()+360, $domain_info['path'], $domain_info['domain'] );
			}
			return false;
		}

		$exclude = $this -> getOne("Geo_exclude", "`Path` = '".$_GET['page']."'", 'pages');	

		if( !$_COOKIE['mf_geo_loc'] && $geo_filter_data['geo_url'] || ( $_COOKIE['mf_geo_loc'] && !$_COOKIE['PHPSESSID'] && $_COOKIE['mf_geo_loc'] != $geo_filter_data['geo_url'] ) )
		{
			//first time but location in url or cookie location different with url location. rewrite cookie
			$expire_time = time()+( 86400 * $GLOBALS['config']['mf_geo_cookie_lifetime'] );
			setcookie( 'mf_geo_loc', $geo_filter_data['geo_url'], $expire_time, $domain_info['path'], $domain_info['domain'] );
			setcookie( 'mf_geo_detected', true, time()+360, $domain_info['path'], $domain_info['domain'] );				
		}elseif( !$geo_filter_data['geo_url'] && $_COOKIE['mf_geo_loc'] )
		{
			//cookie exists but location not in url or session, redirect
			$_SERVER['REQUEST_URI'] = str_replace('?reset_location', '', $_SERVER['REQUEST_URI']);

			$tmp = '[geo_url]';
			$tmp .= trim($_SERVER['REQUEST_URI'], "/");

			if( !$this -> getOne("ID", "`Path` = '".$_COOKIE['mf_geo_loc']."'", "data_formats") )
			{
				return false;
			}

			if( $exclude )
			{
				$_SESSION['geo_url'] = $_COOKIE['mf_geo_loc'];
				$GLOBALS['reefless'] -> redirect();
			}
			else
			{
				$redirect_url = str_replace( '[geo_url]', $_COOKIE['mf_geo_loc'], RL_URL_HOME . $tmp );				
				$GLOBALS['reefless'] -> redirect( null, $redirect_url );
			}
		}
		elseif( !$_COOKIE['mf_geo_loc'] && !$geo_filter_data['geo_url'] && !$_COOKIE['mf_geo_detected'] )
		{
			$country_path = $rlValid -> str2path( $_SESSION['GEOLocationData'] -> Country_name );
			$region_path = $rlValid -> str2path( $_SESSION['GEOLocationData'] -> Region );
			$city_path = $rlValid -> str2path( $_SESSION['GEOLocationData'] -> City );
			
			if( !$country_path && !$region_path && !$city_path)
				return;
				
			$reg_path = '';
			$reg_path .= $country_path ? "/".$country_path : "";
			$reg_path .= $region_path ? "/".$region_path : "";
			$reg_path .= $city_path ? "/".$city_path : "";
			$reg_path= substr($reg_path, 1);
			
			$sql = "SELECT `Path` FROM `".RL_DBPREFIX."data_formats` WHERE `Path` REGEXP '{$reg_path}'";
			$ip_path = $this -> getRow($sql, 'Path');
						
			if( !$ip_path )
			{
				$levels = $this -> getOne("Levels", "`Key` = '".$GLOBALS['geo_format']."'", "multi_formats");
				if( !$levels )
				{
					$levels = $this -> getLevels($this -> getOne("ID", "`Key` = '".$GLOBALS['geo_format']."'", "data_formats"), true);
				}
				
				if( $levels == 1 )
				{
					$sql = "SELECT `Path` FROM `".RL_DBPREFIX."data_formats` WHERE `Path` = '{$country_path}' OR `Path` = '{$region_path}' OR `Path` = '{$city_path}' ORDER BY `Key` DESC";
					$ip_path = $this -> getRow($sql, 'Path');
				}
				elseif( $levels > 2 )
				{
					if( $country_path && $region_path && $city_path )
					{
						$sql = "SELECT `Path` FROM `".RL_DBPREFIX."data_formats` WHERE `Path` = '{$country_path}/{$region_path}'";
						$ip_path = $this -> getRow($sql, 'Path');						
					}elseif( !$region_path )
					{						
						$sql = "SELECT `Path` FROM `".RL_DBPREFIX."data_formats` WHERE `Path` REGEXP '{$country_path}\/[^\/]*\/{$city_path}'";
						$ip_path = $this -> getRow($sql, 'Path');						
					}
				}
				elseif( $levels == 2  )
				{
					$sql = "SELECT `Path` FROM `".RL_DBPREFIX."data_formats` WHERE `Path` = '{$region_path}/{$city_path}' OR `Path` = '{$region_path}' ORDER BY `Key` DESC";
					$ip_path = $this -> getRow($sql, 'Path');
				}

				if( !$ip_path )
				{
					return false;
				}
			}

			$expire_time = time()+( 86400 * $GLOBALS['config']['mf_geo_cookie_lifetime'] );
			setcookie( 'mf_geo_loc', $ip_path."/", $expire_time, $domain_info['path'], $domain_info['domain'] );
			setcookie( 'mf_geo_detected', true, $expire_time, $domain_info['path'], $domain_info['domain'] );

			if( $exclude )
			{
				$_SESSION['geo_url'] = $ip_path;
				$GLOBALS['reefless'] -> redirect();
			}else
			{
				if( $config['mf_geo_subdomains'] )
				{
					$paths = explode("/", $ip_path);
					$geo_sub = array_splice($paths, 0, 1);
					$ip_path = implode("/", $paths);

					$bpath = preg_replace("#(https?://)(www.)?#", "$1".$geo_sub[0].".", RL_URL_HOME);
					$ip_path = $ip_path ? $ip_path."/" : '';
					$GLOBALS['reefless'] -> redirect( null, $bpath . $ip_path );
				}else
				{
					$GLOBALS['reefless'] -> redirect( null, RL_URL_HOME . $ip_path );
				}
			}
		}
	}

	function adaptPageInfo()
	{
		global $geo_filter_data;

		$k=1;
		foreach( $geo_filter_data['location'] as $key => $litem )
		{
			$loc_all .= $litem['name']." / ";

			$pattern[] = '{location_level'.$k.'}';
			$replacement[] = $litem['name'];

			$k++;
		}
		$loc_all = trim($loc_all, " / ");
		$pattern[] = '{location}';
		$replacement[] = $loc_all;

		$areas = array('name', 'meta_description', 'meta_keywords', 'meta_title');

		foreach( $areas as $area )
		{
			if( $GLOBALS['page_info'][ $area ] )
			{
				$GLOBALS['page_info'][ $area ] = str_replace( $pattern, $replacement, $GLOBALS['page_info'][$area ] );
				$GLOBALS['page_info'][ $area ] = preg_replace('/\{if location\}(.*?)\{\/if\}/smi', $geo_filter_data['location'] ? '\\1' : '', $GLOBALS['page_info'][ $area ]);
			}
		}

		if( $GLOBALS['bread_crumbs'] )
		{
			$bc_areas = array('title', 'name');
			foreach( $GLOBALS['bread_crumbs'] as $bk => $bc_item )
			{
				foreach( $bc_areas as $area )
				{
					if( $GLOBALS['bread_crumbs'][$bk][ $area ] )
					{
						$GLOBALS['bread_crumbs'][$bk][ $area ] = str_replace( $pattern, $replacement, $GLOBALS['bread_crumbs'][$bk][ $area ] );
						$GLOBALS['bread_crumbs'][$bk][ $area ] = preg_replace('/\{if location\}(.*?)\{\/if\}/smi', $geo_filter_data['location'] ? '\\1' : '', $GLOBALS['bread_crumbs'][$bk][ $area ]);
					}
				}
			}
		}

/*		if( $GLOBALS['category'] )
		{
			$cat_areas = array('meta_description','title', 'name');
			foreach( $cat_areas as $area )
			{
				if( $GLOBALS['category'][ $area ] )
				{
					$GLOBALS['category'][ $area ] = str_replace( $pattern, $replacement, $GLOBALS['category'][ $area ] );
					$GLOBALS['category'][ $area ] = preg_replace('/\{if location\}(.*?)\{\/if\}/smi', $geo_filter_data['location'] ? '\\1' : '', $GLOBALS['category'][ $area ]);
				}
			}

			$GLOBALS['rlSmarty'] -> assign_by_ref('category', $GLOBALS['category']);
		}*/
	}

	function adaptPageTitle( $title )
	{
		$k=0;
		foreach( $GLOBALS['geo_filter_data']['location'] as $key => $litem )
		{
			$loc_all .= $litem['name']." / ";

			$pattern[] = '{location_level'.$k.'}';
			$replacement[] = $litem['name'];

			$k++;
		}
		$loc_all = trim($loc_all, " / ");
		$pattern[] = '{location}';
		$replacement[] = $loc_all;

		foreach( $title as $key => $item )
		{
			if( $title[$key] )
			{
				$title[ $key ] = str_replace( $pattern, $replacement, $title[$key] );
				$title[ $key ] = preg_replace('/\{if location\}(.*?)\{\/if\}/smi', $GLOBALS['geo_filter_data']['location'] ? '\\1' : '', $title[$key]);
			}
		}

		return $title;
	}

	function adaptOtherData( $data, $pname = false )
	{
		global $geo_filter_data;

		$k=1;
		foreach( $geo_filter_data['location'] as $key => $litem )
		{
			$loc_all .= $litem['name']." / ";

			$pattern[] = '{location_level'.$k.'}';
			$replacement[] = $litem['name'];

			$k++;
		}
		$loc_all = trim($loc_all, " / ");
		$pattern[] = '{location}';
		$replacement[] = $loc_all;

		$areas = array('name', 'title');

		foreach( $areas as $area )
		{
			foreach( $data as $dk => $data_item )
			{
				if( $data_item[ $area ] )
				{
					$data[$dk][ $area ] = str_replace( $pattern, $replacement, $data[$dk][$area ] );
					$data[$dk][ $area ] = preg_replace('/\{if location\}(.*?)\{\/if\}/smi', $geo_filter_data['location'] ? '\\1' : '', $data[$dk][ $area ]);
				}
				
				if( $pname )
				{

					if( $data_item['pName'] )
					{
						$GLOBALS['lang'][ $data_item['pName'] ] = str_replace( $pattern, $replacement, $GLOBALS['lang'][ $data_item['pName'] ] );
						$GLOBALS['lang'][ $data_item['pName'] ] = preg_replace('/\{if location\}(.*?)\{\/if\}/smi', $geo_filter_data['location'] ? '\\1' : '', $GLOBALS['lang'][ $data_item['pName'] ]);
					}
					if( $data_item['pTitle'] )
					{						
						$GLOBALS['lang'][ $data_item['pTitle'] ] = str_replace( $pattern, $replacement, $GLOBALS['lang'][ $data_item['pTitle'] ] );
						$GLOBALS['lang'][ $data_item['pTitle'] ] = preg_replace('/\{if location\}(.*?)\{\/if\}/smi', $geo_filter_data['location'] ? '\\1' : '', $GLOBALS['lang'][ $data_item['pTitle'] ]);
					}
				}
			}
		}

		return $data;
	}

	function smartyFetchHook( $param1 )
	{
		$details_page_path = $GLOBALS['pages']['view_details'];
		if ( !$details_page_path )
			$details_page_path = $this -> getOne('Path', "`Key`= 'view_details'", 'pages');

		$local_bpath = $GLOBALS['geo_filter_data']['bPath'];

		if( $GLOBALS['geo_filter_data']['geo_sub'] )
		{
			$local_bpath = preg_replace('#http://(www.)?#', 'http://'.$GLOBALS['geo_filter_data']['geo_sub'].'.', $local_bpath);			
		}		

		foreach( $GLOBALS['geo_filter_data']['clean_pages'] as $page)
		{
			if( $page == $details_page_path )
			{
				$ldetails = $page;
			}
			elseif( $page )
			{
				if( $GLOBALS['geo_filter_data']['geo_url'] )
				{
	            	$sfind[] = $local_bpath.$GLOBALS['geo_filter_data']['geo_url']."/".$page;
	            }else
	            {
	            	$sfind[] = $local_bpath.$page;
	            }
        	    $sreplace[] = $GLOBALS['geo_filter_data']['bPath'].$page;
			}
		}
		$param1 = str_replace($sfind, $sreplace, $param1);

		if( $ldetails )
		{
			if( $GLOBALS['geo_filter_data']['geo_sub'] )
			{
				$bpath = preg_replace("#(https?://)(www.)?#", "$1".$GLOBALS['geo_filter_data']['geo_sub'].".", $GLOBALS['geo_filter_data']['bPath']);
			}
			else
			{
				$bpath = $GLOBALS['geo_filter_data']['bPath'];
			}
			$reg_geo_base = str_replace("/", "\/", $bpath.$GLOBALS['geo_filter_data']['geo_url'] );
			
			$reg_find = '/'.$reg_geo_base.'\/(([^"]*)-[0-9]+\.html)/smi';
			$reg_replace = $GLOBALS['geo_filter_data']['bPath'].'$1';			
			$param1	= preg_replace( $reg_find, $reg_replace, $param1);
		}

		return $param1;
	}

	/**
	* rebuild cache
	*
	* @package ajax
	*
	* 
	**/

	function ajaxRebuildMultiCache( $self )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false && !$direct )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
			return $_response;
		}

		$this -> cache(null, true);

		$_response -> script( "printMessage('notice', '{$lang['mf_cache_rebuilt']}')" );
		$_response -> script( "$('{$self}').val('{$lang['update']}');" );

		return $_response;
	}

	/**
	* geoAutocomplete
	*
	* @param $str - input string
	* 
	**/

	function geoAutocomplete( $str = false )
	{
		global $config,$geo_format;

		if( !$config['lang'] )
		{
			$config['lang'] = $_GET['lang'] ? $_GET['lang'] : $this -> getOne("Default", "`Key` = 'lang'", "config");
		}

		$geo_format = $geo_format ? $geo_format : $this -> fetch(array("Key", 'Levels'), array("Geo_filter" => '1'), null, null, "multi_formats", 'row');
		$geo_format['Levels'] = $geo_format['Levels'] ? $geo_format['Levels'] : 1;

		$sql ="SELECT `Value`, `Key` FROM `".RL_DBPREFIX."lang_keys` WHERE `Value` LIKE '{$str}%' ";
		$sql .= "AND SUBSTRING(`Key`, 19, ".strlen($geo_format['Key']).") = '{$geo_format['Key']}' ";
		$sql .= "AND `Code` = '{$config['lang']}' ";
		$sql .="GROUP BY `Key` ";
		if( $config['mf_geo_autocomplete_limit'] = $this -> getOne("Default", "`Key` = 'mf_geo_autocomplete_limit'", "config") )
		{
			$sql .="LIMIT {$config['mf_geo_autocomplete_limit']}";
		}

		$output = $this -> getAll( $sql );
	
		if ( !empty($output) )
		{
			foreach ($output as $key => $value)
			{	
				$item_key = str_replace('data_formats+name+', '', $value['Key']);
				$item = $this -> fetch( array("Parent_ID", "Path"), array("Key" => $item_key), null, null, "data_formats", "row" );
				$echo[$key]['path'] = $item['Path'] . "/";
				
				if( $item['Parent_ID'] )
				{
					$paths = explode( "/", $item['Path'] );
					$max_key = count($paths) - 1;
					$cpath = '';
					foreach( $paths as $pk => $path )
					{
						if( $pk < $max_key )
						{
							$cpath .= "/".$path;

							$parent = $this -> fetch( array("Key"), array("Path" => trim($cpath,"/")), null, null, "data_formats", "row" );
							$parent_name = $this -> getOne("Value", "`Key` = 'data_formats+name+".$parent['Key']."' AND `Code` = '{$config['lang']}'", "lang_keys");
							$echo[$key]['name'] .= $parent_name.", ";
						}
					}
				}

				$echo[$key]['name'] .= $value['Value'];
			}	
		}

		return $echo;
	}
}
