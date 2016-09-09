<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLTAGCLOUD.CLASS.PHP
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

class rlTagCloud extends reefless
{

	/**
	* box template
	**/
	var $box_tpl = '
		global $rlSmarty;
		if( $GLOBALS["config"]["tc_order"] == "randomly" )
		{
			$GLOBALS["reefless"] -> loadClass("TagCloud", null, "tag_cloud");
			$tag_cloud = $GLOBALS["rlTagCloud"] -> getTagCloud();
		}else
		{
			$tag_cloud = array({tag_cloud});
		}
		$rlSmarty -> assign_by_ref("tag_cloud", $tag_cloud);
		$rlSmarty -> display(RL_PLUGINS . "tag_cloud" . RL_DS . "tag_cloud.tpl");
		';

	/**
	* update box
	**/
	function updateBox()
	{
		if( $GLOBALS["config"]["tc_order"] == "randomly" )
		{
			return false;
		}

		$tag_cloud = $this -> getTagCloud();

		foreach( $tag_cloud as $key => $tag )
		{
			$code .= "'{$key}' => array('Tag' => '{$tag['Tag']}', 'Path' => '{$tag['Path']}', 'Count' => '{$tag['Count']}', 'Size' => '{$tag['Size']}' ),";
		}
		$code = rtrim($code, ',');

		$update = array(
			'fields' => array(
				'Content' => str_replace('{tag_cloud}', $code, $this -> box_tpl)
			),
			'where' => array(
				'Key' => 'tag_cloud'
			)
		);

		$GLOBALS['reefless'] -> loadClass('Actions');
		$GLOBALS['rlActions'] -> rlAllowHTML = true;
		$GLOBALS['rlActions'] -> updateOne($update, 'blocks');
	}
	
	function searchAddTag( $search_query )
	{
		if( $_SESSION['keyword_search'] == $search_query )
			return;
	
		if( $config['tc_query_explode'] )
		{
			$tags = explode( ' ', trim($search_query) );
		}else
		{
			$tags[0] = $search_query;
		}

		$tags = $GLOBALS['rlValid'] -> xHtml( $tags );

		/* load the utf8 lib */
		loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

		foreach( $tags as $tag )
		{
			if ( strlen($tag) >= $config['tc_tag_min_symbols'] && (stripos( $config['tc_exwords'].",", $tag."," ) === false) )
			{
				$check = $this -> getOne('Tag', "CONVERT(`Tag` USING `utf8`) = '".$tag."'", 'tag_cloud');

				if ( $check )
				{
					$sql= "UPDATE `".RL_DBPREFIX."tag_cloud` SET `Count`=`Count`+1 WHERE `Tag` ='{$tag}'";
					$this -> query( $sql );
				}
				else
				{
					$insert['Tag'] = $tag;
					$insert['Date'] = 'NOW()';
					$insert['Modified'] = 'NOW()';
					$f_key = $tag;

					if ( !utf8_is_ascii( $f_key ) )
					{
						$f_key = utf8_to_ascii( $f_key );
					}

					$insert['Key'] = $GLOBALS['rlValid'] -> str2key( $f_key );
					$insert['Path'] = $GLOBALS['rlValid'] -> str2path( $tag );

					$GLOBALS['reefless'] -> loadClass('Actions');
					$GLOBALS['rlActions'] -> insertOne( $insert, 'tag_cloud' );
				}
			}
		}
		$this -> updateBox();
	}

	function ajaxDeleteTag( $key = false )
	{
		global $_response;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$_response -> script("printMessage('notice', '{$GLOBALS['lang']['item_deleted']}');");	
		$_response -> script( "tagsGrid.reload()" );

		$lang_keys = array(
			array('Key' => 'tag_cloud+title+'. $key),
			array('Key' => 'tag_cloud+des+'. $key),
			array('Key' => 'tag_cloud+meta_description+'. $key),
			array('Key' => 'tag_cloud+meta_keywords+'. $key)
		);

		$GLOBALS['rlActions'] -> delete( array( 'Key' => $key ), array('tag_cloud'), null, 1, $key, $lang_keys );
		$del_mode = $GLOBALS['rlActions'] -> action;

		$this -> updateBox();
		return $_response;	
	}

	function getTagCloud()
	{
		global $config;

		$limit = $config['tc_limit'];

		switch( $config['tc_order'] )
		{
			case 'randomly':
				$order = "ORDER BY RAND()";
			break;
			case 'clicks':
				$order = "ORDER BY `Count` DESC";
			break;
			case 'date':
				$order = "ORDER BY `Date` DESC";
			break;
		}

		$result = $this -> fetch('*', array( "Status" => "active" ), $order, $limit, 'tag_cloud');

		if( $config['tc_tags_display_type'] != 'as_is' )
		{
			foreach( $result as $key => $val )
			{
				switch( $config['tc_tags_display_type'] )
				{
					case 'ucwords':
						$result[$key]['Tag'] = ucwords( $result[$key]['Tag'] );
					break;
					case 'ucfirst':
						$result[$key]['Tag'] = ucfirst( $result[$key]['Tag'] );
					break;
					case 'uppercase':
						$result[$key]['Tag'] = strtoupper( $result[$key]['Tag'] );
					break;
					case 'lowercase':
						$result[$key]['Tag'] = strtolower( $result[$key]['Tag'] );
					break;
				}
			}
		}

		if( $config['tc_box_type'] != 'gradient' )
		{
			foreach( $result as $key => $tag )
			{ 
				$minmax['max'] = $tag['Count'] > $minmax['max'] ? $tag['Count'] : $minmax['max'];
				$minmax['min'] = $tag['Count'] < $minmax['min'] ? $tag['Count'] : $minmax['min'];
			}

			$spread = $minmax['max'] - $minmax['min'];
			$step = ($config['tc_maxsize'] - $config['tc_minsize'])/($spread);

			foreach( $result as $key => $tag )
			{
				$size = round( $config['tc_minsize'] + (($tag['Count']-$minmax['min']) * $step ) );
				$result[$key]['Size'] = $size > $config['tc_maxsize'] ? $config['tc_maxsize'] : $size;
			}
		}
	
		if( $config['tc_order'] == 'clicks' )
		{
			$tmp = $result;
			unset($result);

			foreach( $tmp as $k => $v )
			{
				$result[ $v['ID'] ] = $v;
			}
			ksort( $result );
		}elseif( $config['tc_order'] == 'randomly' )
		{
			shuffle($result);
		}

		return $result;
	}


	function ajaxImportTags( $tags )
	{
		global $_response, $rlDb;
		
		$tags = explode(",", $tags);
		
		/* load the utf8 lib */
		loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

		foreach( $tags as $key => $tag )
		{
			$tag_key = $tag;
			if ( !utf8_is_ascii( $tag_key ) )
			{
				$tag_key = utf8_to_ascii( $tag_key );
			}

			$tag_key = $GLOBALS['rlValid'] -> str2key( $tag_key );
			$tag_path = $GLOBALS['rlValid'] -> str2path( $tag );

			if( !$rlDb -> getOne("Key", "`Key` = '{$tag_key}'", "tag_cloud") && !$rlDb -> getOne("Key", "`Path` = '{$tag_path}'", "tag_cloud") )
			{
				$insert['Tag'] = trim($tag);
				$insert['Date'] = 'NOW()';
				$insert['Modified'] = 'NOW()';
				$insert['Key'] = $tag_key;
				$insert['Path'] = $tag_path;
				$GLOBALS['rlActions'] -> insertOne( $insert, 'tag_cloud' );
			}
		}

		if( $insert )
		{
			$_response -> script("printMessage('notice', '{$GLOBALS['lang']['tc_tags_imported']}');");	
			$_response -> script( "tagsGrid.reload()" );
			$_response -> script( "$('#action_blocks div#import').slideUp();" );			
		}

		$this -> updateBox();

		return $_response;	
	}

	//for sitemap
	function getTagsForSitemap( $start = false, $limit = false, $languages_count = false )
	{
		global $pages;

		$languages_count = (int)$languages_count;

		$sql  = "SELECT SQL_CALC_FOUND_ROWS `T1`.`ID`, `T1`.`Path`, `T1`.`Key`, `T1`.`Tag` ";
		$sql .= "FROM `".RL_DBPREFIX."tag_cloud` AS `T1` ";
		$sql .= "WHERE `T1`.`Status` = 'active' ";
		$sql .= "ORDER BY `T1`.`Date` DESC ";

		if ( $this -> languages_count > 1 )
		{
			$limit = ceil( $limit / $languages_count );

			if ( $start > 0 )
			{
				$start = ceil( $start / $languages_count );
			}
		} 

		$sql .= "LIMIT {$start},{$limit}";
		$tags = $this -> getAll( $sql );

		$languages_count > 1 ? $lang = '[lang]' .'/' : $lang = ''; 

		foreach( $tags as $key => $val )
		{
			$tags[$key]['url'] = RL_URL_HOME . $lang . ($GLOBALS['config']['mod_rewrite'] ? $pages['tags'] . '/' . $val['Path']  . ( $GLOBALS['config']['tc_urls_postfix'] ? '.html' : '/' ) : '?page=' . $pages['tags'] . '&amp;tag=' .$val['Path']);
		}

		return $tags;
	}
}
