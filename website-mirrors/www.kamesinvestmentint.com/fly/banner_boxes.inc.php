<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: BANNER_BOXES.INC.PHP
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

if ( isset( $_GET['action'] ) )
{
	$reefless -> loadClass('Categories');
	$reefless -> loadClass('Banners', null, 'banners');

	// additional bread crumb step
	$bcAStep[0] = array('name' => $lang['banners_listOfBoxes'], 'Controller' => 'banners', 'Vars' => 'module=banner_boxes');
	$bcAStep[1] = array('name' => $_GET['action'] == 'add' ? $lang['banners_addBox'] : $lang['banners_editBox']);

	if ( $_GET['action'] == 'add' || $_GET['action'] == 'edit' )
	{
		// add long_top block
		if ( $tpl_settings['long_top_block'] )
		{
			$l_block_sides['long_top'] = $lang['long_top'];
		}

		$allowBoxesBetweenCategories = in_array($tpl_settings['name'], array('general_simple', 'auto_main_blue', 'auto_main_red'));
		$rlSmarty -> assign('allowSelectBoxType', $allowBoxesBetweenCategories);

		// get categories/section
		$sections = $rlCategories -> getCatTree(0, false, true);
		$rlSmarty -> assign_by_ref('sections', $sections);

		// get pages list
		$pages = $rlDb -> fetch( array('ID', 'Key'), array('Tpl' => 1), "AND `Status` <> 'trash' ORDER BY `Key`", null, 'pages' );
		$pages = $rlLang -> replaceLangKeys( $pages, 'pages', array( 'name' ), RL_LANG_CODE, 'admin' );
		$rlSmarty -> assign_by_ref( 'pages', $pages );

		if ( isset( $_GET['box'] ) )
		{
			$b_key = $rlValid -> xSql( $_GET['box'] );

			// get current block info
			$block_info = $rlDb -> fetch('*', array( 'Key' => $b_key, 'Plugin' => 'banners' ), "AND `Status` <> 'trash'", null, 'blocks', 'row');
			$rlSmarty -> assign_by_ref('block', $block_info);
		}

		// clear cache
		if ( !$_POST['submit'] && !$_POST['xjxfun'] )
		{
			unset($_SESSION['categories']);
		}

		if ( $_GET['action'] == 'edit' && !$_POST['fromPost'] )
		{
			unset( $_SESSION['categories'] );

			$_POST['status'] = $block_info['Status'];
			$_POST['side'] = $block_info['Side'];
			$_POST['tpl'] = $block_info['Tpl'];
			$_POST['show_on_all'] = $block_info['Sticky'];
			$_POST['cat_sticky'] = $block_info['Cat_sticky'];
			$_POST['subcategories'] = $block_info['Subcategories'];
			$_POST['categories'] = explode(',', $block_info['Category_ID']);

			$m_pages = explode(',', $block_info['Page_ID']);
			foreach( $m_pages as $page_id )
			{
				$_POST['pages'][$page_id] = $page_id;
			}
			unset($m_pages);

			// get names
			$names = $rlDb -> fetch(array('Code', 'Value'), array('Key' => 'blocks+name+'. $b_key), "AND `Status` <> 'trash'", null, 'lang_keys');
			foreach( $names as $nKey => $nVal )
			{
				$_POST['name'][$names[$nKey]['Code']] = $names[$nKey]['Value'];
			}

			// banner info
			$bannerInfo = unserialize($block_info['Banners']);
			$box_size_prefix = $bannerInfo['box_type'] == 'def' ? '' : 'btc_';

			$_POST['slider'] = (int)$bannerInfo['slider'];
			$_POST['box_type'] = $bannerInfo['box_type'];
			$_POST['banners_limit'] = $bannerInfo['limit'];
			$_POST[$box_size_prefix .'banners_width'] = $bannerInfo['width'];
			$_POST[$box_size_prefix .'banners_height'] = $bannerInfo['height'];

			if ( $bannerInfo['box_type'] == 'btc' )
			{
				$_POST['box_after_category'] = $block_info['Content'];
				$send_alert = $lang['banners_changeBannersCategoryBoxAlert'];
			}
			else
			{
				$send_alert = $lang['banners_changeBannersBoxAlert'];
			}

			// send alert
			$rlSmarty -> assign('alerts', $send_alert);
		}

		// get parent points
		if ( $_POST['categories'] )
		{
			$rlCategories -> parentPoints($_POST['categories']);
		}

		if ( isset($_POST['submit']) )
		{
			$errors = $error_fields = array();
			$f_type = 'php';

			$_SESSION['categories'] = $_POST['categories'];
			$box_type = $_POST['box_type'];

			// check name
			$f_name = $_POST['name'];
			if ( empty($f_name[$config['lang']]) )
			{
				$langName = count($allLangs) > 1 ? "{$lang['name']}({$allLangs[$config['lang']]['name']})" : $lang['name'];
				array_push($errors, str_replace('{field}', "<b>{$langName}</b>", $lang['notice_field_empty']) );
				array_push($error_fields, "name[{$config['lang']}]" );
			}

			if ( $box_type == 'def' )
			{
				// check side
				$f_side = $_POST['side'];
				if ( empty( $f_side ) )
				{
					array_push( $errors, str_replace('{field}', "<b>\"{$lang['block_side']}\"</b>", $lang['notice_select_empty']) );
					array_push( $error_fields, 'side' );
				}

				// check banners limit
				$f_limit = (int)$_POST['banners_limit'];
				if ( $f_limit <= 0 )
				{
					array_push( $errors, str_replace('{field}', "<b>\"{$lang['banners_limit']}\"</b>", $lang['notice_field_empty']) );
					array_push( $error_fields, 'banners_limit' );
				}
			}
			else
			{
				$box_after_category = $_POST['box_after_category'];
				if ( empty($box_after_category) )
				{
					array_push( $errors, str_replace('{field}', "<b>\"{$lang['banners_betweenCategories_field']}\"</b>", $lang['notice_select_empty']) );
					array_push( $error_fields, 'box_after_category' );
				}
			}

			// set size prefix
			$box_size_prefix = $box_type == 'def' ? '' : 'btc_';

			// check banner width
			$f_width = (int)$_POST[$box_size_prefix .'banners_width'];
			if ( !$f_width )
			{
				array_push( $errors, str_replace('{field}', "<b>\"{$lang['banners_boxSettingsWidth']}\"</b>", $lang['notice_field_empty']) );
				array_push( $error_fields, $box_size_prefix .'banners_width' );
			}

			// check banner height
			$f_height = (int)$_POST[$box_size_prefix .'banners_height'];
			if ( !$f_height )
			{
				array_push( $errors, str_replace('{field}', "<b>\"{$lang['banners_boxSettingsHeight']}\"</b>", $lang['notice_field_empty']) );
				array_push( $error_fields, $box_size_prefix .'banners_height' );
			}

			if ( !empty( $errors ) )
			{
				$rlSmarty -> assign_by_ref('errors', $errors);
			}
			else
			{
				// additional banners settings for this box
				$bannersInfo['box_type'] = $box_type;
				$bannersInfo['limit'] = $box_type == 'def' ? $f_limit : 1;
				$bannersInfo['width'] = $f_width;
				$bannersInfo['height'] = $f_height;
				$bannersInfo['slider'] = (int)$_POST['slider'];

				// add/edit action
				if ( $_GET['action'] == 'add' )
				{
					$defName = !empty($f_name['en']) ? $f_name['en'] : $f_name[$config['lang']];
					$f_key = $rlBanners -> uniqKeyByName($defName, 'blocks', 'bb_');

					// get max position
					if ( $box_type == 'def' )
					{
						$position = $rlDb -> getRow("SELECT MAX(`Position`) AS `max` FROM `". RL_DBPREFIX ."blocks`");
					}

					// write main, block information
					$data = array(
						'Key' => $f_key,
						'Status' => $_POST['status'],
						'Position' => (int)$position['max'] + 1,
						'Type' => $f_type,
						'Side' => $box_type == 'def' ? $f_side : '',
						'Content' => $box_type == 'def' ? $rlBanners -> makeBoxContent($f_key, $f_limit, $bannersInfo) : $box_after_category,
						'Tpl' => $box_type == 'def' ? (int)$_POST['tpl'] : 0,
						'Page_ID' => $box_type == 'def' ? implode(',', $_POST['pages']) : '',
						'Category_ID' => $box_type == 'def' ? implode(',', $_POST['categories']) : '',
						'Subcategories' => $box_type == 'def' ? (empty($_POST['subcategories']) ? 0 : 1) : 0,
						'Sticky' => $box_type == 'def' ? (empty($_POST['show_on_all']) ? 0 : 1) : 0,
						'Cat_sticky' => $box_type == 'def' ? (empty($_POST['cat_sticky']) ? 0 : 1) : 0,
						'Banners' => serialize($bannersInfo),
						'Plugin' => 'banners',
						'Readonly' => 1
					);

					if ( $action = $rlActions -> insertOne($data, 'blocks') )
					{
						// fake category box
						if ( $box_type == 'btc' )
						{
							$rlActions -> insertOne(array(
									'Name' => 'tplBetweenCategories',
									'Code' => $rlBanners -> makeFakeCategoryBox($f_key, $box_after_category, $bannersInfo),
									'Status' => $_POST['status'],
									'Plugin' => 'banners_'. $f_key
								),
								'hooks'
							);
						}

						// write name's phrases
						$lang_keys = array();
						foreach($allLangs as $key => $value)
						{
							array_push($lang_keys, array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Status' => 'active',
									'Key' => 'blocks+name+'. $f_key,
									'Value' => !empty($f_name[$allLangs[$key]['Code']]) ? $f_name[$allLangs[$key]['Code']] : $f_name[$config['lang']],
									'Plugin' => 'banners'
								)
							);
						}
						$rlActions -> insert($lang_keys, 'lang_keys');

						$message = $lang['block_added'];
						$aUrl = array('controller' => $controller, 'module' => 'banner_boxes');
					}
					else
					{
						trigger_error("Can't add new banners box (MYSQL problems)", E_WARNING);
						$rlDebug -> logger("Can't add new banners box (MYSQL problems)");
					}
				}
				elseif ( $_GET['action'] == 'edit' )
				{
					$f_key = $rlValid -> xSql($_GET['box']);

					$bannersInfoBase = $rlDb -> fetch(array('Side', 'Banners'), array('Key' => $f_key), null, 1, 'blocks', 'row');
					$update = array(
						'fields' => array(
							'Side' => $box_type == 'def' ? $f_side : '',
							'Content' => $box_type == 'def' ? $rlBanners -> makeBoxContent($f_key, $f_limit, $bannersInfo) : $box_after_category,
							'Tpl' => $box_type == 'def' ? (int)$_POST['tpl'] : 0,
							'Page_ID' => $box_type == 'def' ? implode(',', $_POST['pages']) : '',
							'Category_ID' => $box_type == 'def' ? implode(',', $_POST['categories']) : '',
							'Subcategories' => $box_type == 'def' ? (empty($_POST['subcategories']) ? 0 : 1) : 0,
							'Sticky' => $box_type == 'def' ? (empty($_POST['show_on_all']) ? 0 : 1) : 0,
							'Cat_sticky' => $box_type == 'def' ? (empty($_POST['cat_sticky']) ? 0 : 1) : 0,
							'Banners' => serialize($bannersInfo),
							'Status' => $_POST['status']
						),
						'where' => array('Key' => $f_key)
					);

					if ( $action = $rlActions -> updateOne($update, 'blocks') )
					{
						//
						$rlActions -> updateOne(array(
								'fields' => array(
									'Code' => $rlBanners -> makeFakeCategoryBox($f_key, $box_after_category, $bannersInfo),
									'Status' => $_POST['status']
								),
								'where' => array(
									'Name' => 'tplBetweenCategories',
									'Plugin' => 'banners_'. $f_key
								)
							),
							'hooks'
						);

						// set approval for banners
						$baseSide = $bannersInfoBase['Side'];
						$bannersInfoBase = unserialize($bannersInfoBase['Banners']);

						if ( ($box_type == 'def' && $f_side != $baseSide) || $bannersInfoBase['width'] != $bannersInfo['width'] || $bannersInfoBase['height'] != $bannersInfo['height'] )
						{
							$rlDb -> query("UPDATE `". RL_DBPREFIX ."banners` SET `Status` = 'approval' WHERE `Box` = '{$f_key}' AND `Status` = 'active'");
						}

						// update the lang_keys
						foreach($allLangs as $key => $value)
						{
							if ( $rlDb -> getOne('ID', "`Key` = 'blocks+name+{$f_key}' AND `Code` = '{$allLangs[$key]['Code']}'", 'lang_keys') )
							{
								// edit names
								$update_phrases = array(
									'fields' => array(
										'Value' => !empty($f_name[$allLangs[$key]['Code']]) ? $f_name[$allLangs[$key]['Code']] : $f_name[$config['lang']]
									),
									'where' => array(
										'Code' => $allLangs[$key]['Code'],
										'Key' => 'blocks+name+'. $f_key
									)
								);
								$rlActions -> updateOne($update_phrases, 'lang_keys');
							}
							else
							{
								// insert names
								$insert_phrases = array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Key' => 'blocks+name+'. $f_key,
									'Value' => !empty($f_name[$allLangs[$key]['Code']]) ? $f_name[$allLangs[$key]['Code']] : $f_name[$config['lang']],
									'Plugin' => 'banners'
								);
								$rlActions -> insertOne($insert_phrases, 'lang_keys');
							}
						}
					}

					$message = $lang['block_edited'];
					$aUrl = array('controller' => $controller, 'module' => 'banner_boxes');
				}

				if ( $action )
				{
					unset($_SESSION['categories']);

					$reefless -> loadClass('Notice');
					$rlNotice -> saveNotice($message);
					$reefless -> redirect($aUrl);
				}
			}
		}
	}

	$rlXajax -> registerFunction(array('getCatLevel', $rlCategories, 'ajaxGetCatLevel'));
	$rlXajax -> registerFunction(array('openTree', $rlCategories, 'ajaxOpenTree'));
}
else
{
	$bcAStep = $lang['banners_listOfBoxes'];

	$rlXajax -> registerFunction(array('prepareDeleting', $rlBanners, 'ajaxPrepareDeleting'));
	$rlXajax -> registerFunction(array('deleteBox', $rlBanners, 'ajaxDeleteBannerBox'));
}