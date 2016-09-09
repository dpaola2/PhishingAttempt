<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLCATEGORYTREE.CLASS.PHP
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

class rlCategoryTree extends reefless
{
	/**
	* re-assign category blocks controller
	*
	* @access hook - specialBlock
	*
	**/
	function blocks()
	{
		global $blocks, $rlListingTypes;
		
		foreach ($blocks as $key => &$block) {
			if ( ereg('^ltcb\_', $key) ) {
				$apply = true;
				
				preg_match('/types\="([^"].*)"/', $block['Content'], $matches);
				if ( $matches[1] ) {
					foreach (explode(',', $matches[1]) as $type) {
						if ( !$rlListingTypes -> types[$type]['Ctree_module'] ) {
							$apply = false;
							break;
						}
					}
					
					if ( $apply ) {
						$block['Content'] = '{include file=$smarty.const.RL_PLUGINS|cat:"categories_tree"|cat:$smarty.const.RL_DS|cat:"block.tpl" types="'. $matches[1] .'"}';
					}
				}
			}
		}
	}
	
	/**
	* open category tree level
	*
	* @package xAjax
	*
	* @param int $id - category ID
	* @param string $id - category ID
	*
	**/
	function ajaxOpen( $id = false, $type = false, $bread_crumbs = false )
	{
		global $_response, $rlValid, $rlSmarty, $rlListingTypes;
				
		/* validate data */
		$id = (int) $id;
		$type = $rlValid -> xSql($type);
		
		if ( !$id ) {
			return $_response;
		}
		
		$this -> loadClass('Categories');
		$rlSmarty -> assign('ctree_subcategories', $GLOBALS['rlCategories'] -> getCategories($id, $type));
		$rlSmarty -> assign('box_listing_type', $rlListingTypes -> types[$type]);
		
		$file = RL_PLUGINS .'categories_tree'. RL_DS .'level.tpl';
		$_response -> append('ctree-catid-'. $id, 'innerHTML', $rlSmarty -> fetch($file, null, null, false));
		
		$_response -> script("
			ctree_progress = false;
			$('#ctree-catid-{$id}').addClass('opened').addClass('loaded');
			$('#ctree-catid-{$id}').find('ul').fadeIn();
			$('#ctree-catid-{$id}').find('.tree_loader').fadeOut();
		");
		
		$_response -> call('ctreeOpen'.$type);
		
		/* open tree up to current category */
		if ( is_numeric($pos = array_search($id, $bread_crumbs)) ) {
			if ( $bread_crumbs[$pos+1] ) {
				$_response -> script("$('#ctree-catid-{$bread_crumbs[$pos+1]} > img.plus-icon:first').trigger('click');");
			}
		}

		return $_response;
	}
	
	/**
	* add cache data
	*
	* @access hook - specialBlock
	*
	**/
	function setCache()
	{
		global $rlCache, $config;
		
		$sql = "SELECT `T1`.`ID`, `T1`.`Count`, IF(`T2`.`ID`, 1, 0) AS `Sub_cat` ";
		$sql .= "FROM `". RL_DBPREFIX ."categories` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`ID` = `T2`.`Parent_ID` AND `T2`.`Status` = 'active' ";
		$sql .= "WHERE `T1`.`Level` > 0 AND `T1`.`Status` = 'active' ";
		$categories = $this -> getAll($sql, 'ID');
		
		reset($categories);
		if ( key($categories) <= 0 ) {
			foreach ($categories as $category) {
				$data[$category['ID']] = $category;
			}
			unset($categories);
		}
		
		// write cache to file
		if ( $data || $categories )
		{
			$this -> loadClass('Actions');
			
			$rlCache -> file('cache_ctree_data');
			$file = RL_CACHE . $config['cache_ctree_data'];

			$fh = fopen($file, 'w');
			fwrite($fh, serialize($data ? $data : $categories)); 
			fclose($fh);

			unset($data, $categories);
		}
	}
	
	/**
	* get cache data
	*
	* @access hook - specialBlock
	*
	**/
	function getCache()
	{
		global $rlCache, $rlSmarty;
		
		if ( !$GLOBALS['cache_ctree_data'] ) {
			$GLOBALS['cache_ctree_data'] = $rlCache -> get('cache_ctree_data');
		}
		
		$rlSmarty -> assign_by_ref('cache_ctree_data', $GLOBALS['cache_ctree_data']);
	}
}