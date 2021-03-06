<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLBANNERS.CLASS.PHP
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

class rlBanners extends reefless
{
	/**
	* Banner boxes
	**/
	var $bannerBoxes = array();

	/**
	* Banners list
	**/
	var $bannersList = array();

	/**
	* @var calculate items
	**/
	var $calc;

	/**
	* bots trigger
	*/
	var $isBot = false;

	/**
	* Unique key by name
	**/
	function uniqKeyByName($name = false, $table = false, $prefix = false)
	{
		// load the utf8 lib
		if ( false === function_exists('utf8_is_ascii') )
		{
			loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
		}

		if ( !utf8_is_ascii($name) )
		{
			$name = utf8_to_ascii($name);
		}
		$name = strtolower($GLOBALS['rlValid'] -> str2key($name));

		// set prefix
		if ( $prefix !== false )
		{
			$name = $prefix . $name;
		}

		// check on exists key
		$exists = $this -> getRow("SELECT COUNT(`Key`) AS `count` FROM `". RL_DBPREFIX ."{$table}` WHERE `Key` REGEXP '^{$name}(_[0-9]+)*$'");
		if ( $exists['count'] > 0 )
		{
			return "{$name}_". intval($exists['count'] + 1);
		}
		return $name;
	}

	/**
	* Get current session hash
	**/
	function sessionHash()
	{
		return md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
	}

	/**
	* Get all plans by banners
	*
	* @return array of plans
	**/
	function getBannerPlans($field = false, $value = false, $action = 'all')
	{
		global $account_info;

		$where = !defined('REALM') ? "AND `Admin` = '0' AND (FIND_IN_SET('{$account_info['Type']}', `Allow_for`) > 0 OR `Allow_for` = '')" : "";

		if ( $field && $value )
		{
			$where .= " AND `{$field}` = '{$value}'";
		}

		$plans = $this -> fetch('*', array('Status' => 'active'), "{$where} ORDER BY `Position`", null, 'banner_plans', $action);
		$plans = $GLOBALS['rlLang'] -> replaceLangKeys($plans, 'banner_plans', array('name', 'des'));

		return $plans;
	}

	/**
	* 
	**/
	function makeBoxContent($boxKey = false, $limit = 5, $info = false)
	{
		$content = '
			global $reefless, $rlSmarty;

			$reefless -> loadClass("Banners", null, "banners");
			$banners = $GLOBALS["rlBanners"] -> getBanners("'. $boxKey .'", "'. $limit .'");
			$rlSmarty -> assign("banners", $banners);
			$rlSmarty -> assign("info", array(
					"limit"  => '. intval($info['limit'])  .',
					"width"  => '. intval($info['width'])  .',
					"height" => '. intval($info['height']) .',
					"slider" => '. intval($info['slider']) .',
					"folder" => "banners/"
				)
			);
			unset($banners);

			$rlSmarty -> display(RL_PLUGINS ."banners". RL_DS ."banners_box.tpl");
		';

		return preg_replace("'(\r|\n|\t)'", "", $content);
	}

	/**
	* 
	*/
	function makeFakeCategoryBox($boxKey = false, $categoryKey = false, $info = false)
	{
		$hook = '
			$boxInfo = array(
				"width" => '. $info['width'] .',
				"height" => '. $info['height'] .',
				"folder" => "banners/",
			);
			$GLOBALS["rlBanners"] -> boxBetweenCategories("'. $categoryKey .'", "'. $boxKey .'", $boxInfo);
		';

		return preg_replace("'(\r|\n|\t)'", "", $hook);
	}

	/**
	* Get steps for add banner process
	**/
	function getSteps()
	{
		global $lang;

		$steps = array(
			'plan' => array(
				'name' => $lang['select_plan'],
				'caption' => true,
				'path' => 'select-a-plan'
			),
			'form' => array(
				'name' => $lang['fill_out_form'],
				'caption' => true,
				'path' => 'fill-out-a-form'
			),
			'media' => array(
				'name' => $lang['banners_addMedia'],
				'caption' => true,
				'path' => 'add-media'
			),
			'checkout' => array(
				'name' => $lang['checkout'],
				'caption' => true,
				'path' => 'checkout'
			),
			'done' => array(
				'name' => $lang['reg_done'],
				'path' => 'done'
			)
		);

		return $steps;
	}

	/**
	* Get my banners
	*
	* @param int $accountID - account ID
	**/
	function getMyBanners($accountID = false, $order = 'Last_show', $order_type = 'asc', $start = 0, $limit = false)
	{
		// define start position
		$start = $start > 1 ? ($start - 1) * $limit : 0;

		$sql  = "SELECT SQL_CALC_FOUND_ROWS DISTINCT `T1`.*, `T2`.`Value` AS `name`, `T3`.`Key`, `T3`.`Plan_type`, COUNT(`T4`.`ID`) AS `clicks` ";
		$sql .= "FROM `". RL_DBPREFIX ."banners` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."lang_keys` AS `T2` ON CONCAT('banners+name+', `T1`.`ID`) = `T2`.`Key` AND `T2`.`Code` = '". RL_LANG_CODE ."' AND `T2`.`Plugin` = 'banners' ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."banner_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."banners_click` AS `T4` ON `T1`.`ID` = `T4`.`Banner_ID` ";
		$sql .= "WHERE `T1`.`Account_ID` = '{$accountID}' AND `T1`.`Status` <> 'trash' GROUP BY `T1`.`ID` ";
		if ( $order )
		{
			if ( $order == 'Clicks' )
			{
				$sql .= "ORDER BY `clicks` ". strtoupper($order_type) ." ";
			}
			else
			{
				$sql .= "ORDER BY `T1`.`{$order}` ". strtoupper($order_type) ." ";
			}
		}
		else
		{
			$sql .= "ORDER BY `T1`.`Date_release` DESC ";
		}

		$sql .= "LIMIT {$start},{$limit}";
		$banners = $this -> getAll($sql);

		if ( empty($banners) ) return false;

		$calc = $this -> getRow("SELECT FOUND_ROWS() AS `calc`");
		$this -> calc = $calc['calc'];

		return $banners;
	}

	/**
	* Prepare banners list
	*/
	function prepareBannersList()
	{
		global $block_keys, $blocks, $rlHook, $rlXajax, $config, $page_info;

		// bots detector
		$this -> isBot = $this -> isBot($_SERVER['HTTP_USER_AGENT']);

		// search banners in boxes
		foreach ($block_keys as $key => $value)
		{
			if ( (bool)preg_match('/^bb_/', $key) )
			{
				// collect banner boxes
				array_push($this -> bannerBoxes, $key);

				// get banners limit
				if ( false !== $limit = $this -> getBoxLimit($blocks[$key]['Content']) )
				{
					if ( false === $this -> updateGlobalBanners($key, $limit) )
					{
						if ( $config['banners_hide_empty_boxes'] && (!isset($_GET['listing_id']) || !empty($_SESSION['geo_location'])) )
						{
							unset($block_keys[$key], $blocks[$key]);
						}
					}
				}
			}
		}

		// search banners in hooks [boxes between categories]
		$tbc_name = 'tplBetweenCategories';
		$boxes_betweenCategories = $rlHook -> rlHooks[$tbc_name];
		if ( !empty($boxes_betweenCategories) )
		{
			if ( is_array($boxes_betweenCategories) )
			{
				foreach ($boxes_betweenCategories as $index => $box)
				{
					if ( false !== $key = $this -> getBoxKeyFromHook($box) )
					{
						if ( false === $this -> updateGlobalBanners($key, 1) && $config['banners_hide_empty_boxes'] )
						{
							unset($rlHook -> rlHooks[$tbc_name][$index]);
						}
					}
				}
			}
			else
			{
				if ( false !== $key = $this -> getBoxKeyFromHook($boxes_betweenCategories) )
				{
					if ( false === $this -> updateGlobalBanners($key, 1) && $config['banners_hide_empty_boxes'] )
					{
						unset($rlHook -> rlHooks[$tbc_name]);
					}
				}
			}

			// clear
			if ( $config['banners_hide_empty_boxes'] )
			{
				if ( array_key_exists($tbc_name, $rlHook -> rlHooks) && empty($rlHook -> rlHooks[$tbc_name]))
				{
					unset($rlHook -> rlHooks[$tbc_name]);
				}
			}
		}

		// add clicks handler
		if ( !$this -> isBot )
		{
			$rlXajax -> registerFunction(array('bannerClick', $this, 'ajaxBannerClick'));
		}
	}

	/**
	* Get Box Info
	*
	* @param string &$content - content of boxes
	* @param bool $between - box between categories trigger
	* @param bool $withSize - include size info
	*/
	function getBoxInfo(&$content, $between = false, $withSize = false)
	{
		if ( $between === true )
		{
			$regex = '/boxBetweenCategories\("[a-z0-9_]+", "([a-z0-9_]+)", \$boxInfo\);/i';
		}
		else
		{
			$regex = '/getBanners\("([a-z0-9_]+)", "([0-9]+)"\);/i';
		}

		preg_match($regex, $content, $matches);
		if ( !empty($matches) )
		{
			$info = array();
			if ( isset($matches[1]) )
				$info['key'] = $matches[1];
			if ( isset($matches[2]) )
				$info['limit'] = $matches[2];

			//
			if ( !empty($info) )
			{
				if ( !$between && $withSize )
				{
					preg_match('/"width"\s*=>\s*(\d+),\s*"height"\s*=>\s*(\d+)/i', $content, $size);
					$info['width']  = intval($size[1]);
					$info['height'] = intval($size[2]);
				}
				return $info;
			}
			return false;
		}
		return false;
	}

	/**
	* Get box key
	*
	* @param string $content - content of hook
	*/
	function getBoxKey($content = false)
	{
		if ( false !== $info = $this -> getBoxInfo($content) )
		{
			return $info['key'];
		}
		return false;
	}

	/**
	* Get box key from hook
	*
	* @param string $content - content of hook
	* @param bool $between - trigger
	*/
	function getBoxKeyFromHook($hook = false)
	{
		if ( false !== $info = $this -> getBoxInfo($hook, true) )
		{
			return $info['key'];
		}
		return false;
	}

	/**
	* Get box limit
	*
	* @param string $content - content of hook
	*/
	function getBoxLimit($content = false)
	{
		if ( false !== $info = $this -> getBoxInfo($content) )
		{
			return intval($info['limit']);
		}
		return false;
	}

	/**
	* Update global banners
	*
	* @param string $key - box key
	* @param int $limit - output limit
	*/
	function updateGlobalBanners($key = false, $limit = false)
	{
		$banners = $this -> bannersInBox($key, $limit);
		if ( !empty($banners) )
		{
			$this -> bannersList[$key] = $banners;
			unset($banners);
			return true;
		}
		return false;
	}

	/**
	* Banners in box by key
	*
	* @param string $boxKey - box key
	* @param int $limit - output limit
	*/
	function bannersInBox($boxKey = false, $limit = false, $externalGeoData = false)
	{
		global $geo_filter_data;

		if ( isset($_POST['xjxfun']) || $this -> isBot ) return false;
		if ( $limit <= 0 || !$boxKey ) return false;

		$geo_location = $externalGeoData ? $externalGeoData : $geo_filter_data['location'];
		$countryCode = $_SESSION['GEOLocationData'] -> Country_code;

		// Geo Filter
		$geo_filter = 1;
		if ( $this -> mfActiveInFrontEnd() )
		{
			if ( !empty($geo_location) )
			{
				$geo_filter = '';
				foreach($geo_location as $index => $location)
				{
					$geo_filter .= "FIND_IN_SET('{$location['Key']}', `T2`.`Regions`) OR ";
				}
				$geo_filter = '('. rtrim($geo_filter, 'OR ') .')';
			}
			else
			{
				$geo_filter = 0;
			}
		}

		//
		$sql  = "SELECT `T1`.`ID`, `T1`.`Type`, `T1`.`Image`, `T1`.`Link`, `T1`.`Html`, `T1`.`Follow` FROM `". RL_DBPREFIX ."banners` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."banner_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` AND `T2`.`Status` = 'active' ";
		$sql .= "WHERE `T1`.`Box` = '{$boxKey}' AND ";

		$sql .= "IF(`T2`.`Geo` = '2', (FIND_IN_SET('{$countryCode}', `T2`.`Country`) = 0), ";
		$sql .= "IF(`T2`.`Geo` = '3', {$geo_filter}, (FIND_IN_SET('{$countryCode}', `T2`.`Country`) > 0 OR `T2`.`Geo` = '1'))) AND ";

		$sql .= "IF(`T2`.`Plan_Type` = 'views', IF(`T1`.`Shows` < `T1`.`Date_to` OR `T1`.`Date_to` = '0', 1, 0), IF(`T1`.`Date_to` > UNIX_TIMESTAMP() OR `T1`.`Date_to` = '0', 1, 0)) = 1 ";
		$sql .= "AND `T1`.`Status` = 'active' AND `T2`.`Status` = 'active' AND `T1`.`Image` <> '' ";
		$sql .= "GROUP BY `T1`.`ID` ORDER BY `T1`.`Last_show` LIMIT {$limit}";
		$banners = $this -> getAll($sql);

		if ( !empty($banners) )
		{
			$ids = array();
			foreach( $banners as $key => $row )
			{
				if ( $row['Type'] == 'html' )
				{
					$html = str_replace('<br />', "\n", $row['Html']);
					$banners[$key]['Html'] = $html;
				}
				else if ( $row['Type'] == 'image' )
				{
					if ( !is_numeric(strpos($row['Link'], RL_URL_HOME)) )
					{
						$banners[$key]['externalLink'] = true;
					}
				}

				$banners[$key]['name'] = $GLOBALS['lang']['banners+name+'. $row['ID']];
				array_push($ids, $row['ID']);
			}
			$this -> query("UPDATE `". RL_DBPREFIX ."banners` SET `Shows` = `Shows` + 1, `Last_show` = UNIX_TIMESTAMP() WHERE `ID` IN ('". implode("','", $ids) ."')");
		}
		return $banners;
	}

	/**
	* Get banners
	*
	* @param string $boxKey - box key
	* @param int $limit - output limit
	*
	* @return array - array of banners
	**/
	function getBanners($boxKey = false, $limit = false)
	{
		if ( array_key_exists($boxKey, $this -> bannersList) )
		{
			return $this -> bannersList[$boxKey];
		}
		return false;
	}

	/**
	* Fake box between categories
	*
	* @param string $categoryKey - category key
	* @param string $boxKey - banners box key
	* @param array  $boxInfo - banners box info
	*
	* hook: tplBetweenCategories
	*/
	function boxBetweenCategories($categoryKey = false, $boxKey = false, $boxInfo = false)
	{
		global $rlSmarty;

		if ( $rlSmarty -> _tpl_vars['cat']['Key'] == $categoryKey )
		{
			$banners = $this -> getBanners($boxKey, 1);

			$rlSmarty -> assign('banners', $banners);
			$rlSmarty -> assign('info', $boxInfo);
			$rlSmarty -> assign('boxBetweenCategories', true);

			$rlSmarty -> display(RL_PLUGINS .'banners'. RL_DS .'banners_box.tpl');
		}
	}

	/**
	* Get banners by listing location (MF)
	*
	* @param array &$data - listing data
	*/
	function getBannersByListingLocation(&$data)
	{
		global $block_keys, $blocks, $config, $geo_filter_data;

		if ( !$data || isset($geo_filter_data['location']) ) return false;

		$ldl_field = 'b_country';
		$egf_location = array();
		if ( !empty($data[$ldl_field]) )
			array_push($egf_location, array('Key' => $data[$ldl_field]));
		if ( !empty($data[$ldl_field .'_level1']) )
			array_push($egf_location, array('Key' => $data[$ldl_field .'_level1']));
		if ( !empty($data[$ldl_field .'_level2']) )
			array_push($egf_location, array('Key' => $data[$ldl_field. '_level2']));

		// search banners in boxes
		if ( !empty($egf_location) )
		{
			foreach ($block_keys as $key => $value)
			{
				if ( (bool)preg_match('/^bb_/', $key) )
				{
					$limit = $this -> getBoxLimit($blocks[$key]['Content']);
					$banners = $this -> bannersInBox($key, $limit, $egf_location);

					// update banners
					if ( !empty($banners) )
					{
						$this -> bannersList[$key] = $banners;
						unset($banners);
					}
					else
					{
						// remove box
						if ( $config['banners_hide_empty_boxes'] && !array_key_exists($key, $this -> bannersList) )
						{
							unset($block_keys[$key], $blocks[$key]);
						}
					}
				}
			}
		}
	}

	/**
	* Calc uniq banner clicks
	*
	* @param int $id - banner id
	**/
	function ajaxBannerClick($id = false)
	{
		global $_response;

		if ( !$id )
			return $_response;

		$id = (int)$id;
		$sessionHash = $this -> sessionHash();
		$uniqClick = $this -> getOne('ID', "`Banner_ID` = '{$id}' AND `Hash` = '{$sessionHash}'", 'banners_click');

		if ( empty( $uniqClick ) )
		{
			$countryCode = $_SESSION['GEOLocationData'] -> Country_code;
			$sql = "INSERT INTO `". RL_DBPREFIX ."banners_click` ( `Banner_ID`, `Hash`, `Country` ) VALUES ( '{$id}', '{$sessionHash}', '{$countryCode}' )";
			$this -> query($sql);
		}
		return $_response;
	}

	/**
	* Create a new banner
	*
	* @param array $planInfo - banner plan info
	* @param array $data - banner data
	**/
	function create($planInfo = false, $data = false)
	{
		if ( !$planInfo || !$data ) return false;
		global $rlActions, $config;

		$now = time();
		$insert = array(
			'Plan_ID' => (int)$planInfo['ID'],
			'Box' => $data['banner_box'],
			'Type' => $data['banner_type'],
			'Account_ID' => (int)$data['account_id'],
			'Date_release' => $now,
			'Link' => $data['banner_type'] == 'image' ? $data['link'] : '',
			'Follow' => ($data['banner_type'] == 'image' && !empty($data['link'])) ? intval($data['nofollow']) : 0,
			'Status' => (defined('REALM') && REALM == 'admin') ? $data['status'] : 'incomplete'
		);

		if ( $data['banner_type'] == 'html' ) {
			$insert['Html'] = str_replace('cookie', '', $data['html']);
			$insert['Image'] = 'html';
		}

		if ( $planInfo['Price'] == 0 || (defined('REALM') && REALM == 'admin') ) {
			$insert['Date_to'] = $planInfo['Plan_Type'] == 'period' ? ( $planInfo['Period'] == 0 ? 0 : $now + ( $planInfo['Period'] * 86400 ) ) : $planInfo['Period'];
			$insert['Date_from'] = $now;
			$insert['Pay_date'] = $now;
		}

		$sql  = "INSERT INTO `". RL_DBPREFIX ."banners` ( `". implode('`, `', array_keys($insert)) ."` ) VALUES ";
		$sql .= "( '". implode("', '", array_values($insert)) ."' )";

		if ( $this -> query($sql) )
		{
			$bannerId = mysql_insert_id();

			if ( defined('REALM') && REALM == 'admin' && $data['banner_type'] == 'flash' )
			{
				$this -> uploadFlash($bannerId);
			}

			$allLangs = $GLOBALS['languages'];
			$langKeysInsert = array();

			foreach( $allLangs as $key => $value )
			{
				array_push($langKeysInsert, array(
					'Code' => $allLangs[$key]['Code'],
					'Module' => 'common',
					'Status' => 'active',
					'Key' => 'banners+name+'. $bannerId,
					'Value' => count($allLangs) > 1 ? (!empty($data['name'][$allLangs[$key]['Code']]) ? $data['name'][$allLangs[$key]['Code']] : $data['name'][$config['lang']]) : $data['name'],
					'Plugin' => 'banners'
					)
				);
			}
			$rlActions -> insert($langKeysInsert, 'lang_keys');

			return $bannerId;
		}
		return false;
	}

	/**
	* Banner name's Handler
	*
	* @param int $bannerId - banner id
	* @param mixed $names - banner name's (string/array)
	**/
	function bannerNameHandler($bannerId = false, $names = false)
	{
		global $rlActions, $config;

		$allLangs = $GLOBALS['languages'];

		// write/update name's phrases
		$langKeysInsert = $langKeysUpdate = array();
		foreach( $allLangs as $key => $value )
		{
			$exists = $this -> getOne('Key', "`Key` = 'banners+name+{$bannerId}' AND `Code` = '{$allLangs[$key]['Code']}' AND `Plugin` = 'banners'", 'lang_keys');
			if ( empty($exists) )
			{
				array_push($langKeysInsert, array(
						'Code' => $allLangs[$key]['Code'],
						'Module' => 'common',
						'Status' => 'active',
						'Key' => 'banners+name+'. $bannerId,
						'Value' => count($allLangs) > 1 ? (!empty($names[$allLangs[$key]['Code']]) ? $names[$allLangs[$key]['Code']] : $names[$config['lang']]) : $names,
						'Plugin' => 'banners'
					)
				);
			}
			else
			{
				array_push($langKeysUpdate, array(
						'fields' => array(
							'Status' => 'active',
							'Value' => count($allLangs) > 1 ? (!empty($names[$allLangs[$key]['Code']]) ? $names[$allLangs[$key]['Code']] : $names[$config['lang']]) : $names
						),
						'where' => array(
							'Key' => 'banners+name+'. $bannerId,
							'Code' => $allLangs[$key]['Code']
						)
					)
				);
			}
		}

		if ( !empty($langKeysInsert) )
		{
			$rlActions -> insert($langKeysInsert, 'lang_keys');
		}

		if ( !empty($langKeysUpdate) )
		{
			$rlActions -> update($langKeysUpdate, 'lang_keys');
		}
	}

	/**
	* Update exists banner (my banners)
	*
	* @param int $bannerId - banner id
	* @param array $data - banner data
	**/
	function update($bannerId = false, $data)
	{
		global $account_info, $lang, $config;

		if ( !$bannerId || !$data ) return false;

		$sql  = "UPDATE `". RL_DBPREFIX ."banners` SET ";
		$sql .= "`Link` = '". ($data['banner_type'] == 'html' ? '' : $data['link']) ."', ";
		$sql .= "`Status` = '". ($config['banners_auto_approval'] ? 'active' : 'pending') ."' ";

		if ( $data['banner_type'] == 'html' )
		{
			$sql .= ",`Html` = '". str_replace('cookie', '', $data['html']) ."', ";
			$sql .= "`Image` = 'html' ";
		}
		$sql .= "WHERE `ID` = '{$bannerId}'";

		if ( $this -> query($sql) )
		{
			$this -> bannerNameHandler($bannerId, $data['name']);
		}

		if ( $data['banner_type'] == 'flash' && !empty($_FILES['flash_file']['tmp_name']) )
		{
			$this -> uploadFlash($bannerId);
		}

		// send notify to admin
		if ( !$config['banners_auto_approval'] )
		{
			$this -> loadClass('Mail');

			$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate('banners_admin_banner_edited');
			$m_find = array('{username}', '{link}', '{date}', '{status}');
			$m_replace = array(
				$account_info['Username'], 
				'<a href="'. RL_URL_HOME . ADMIN .'/index.php?controller=banners&amp;filter='. $bannerId .'">'. $lang['banners+name+'. $bannerId] . '</a>', 
				date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)), $lang['pending']
			);
			$mail_tpl['body'] = str_replace($m_find, $m_replace, $mail_tpl['body']);
			$GLOBALS['rlMail'] -> send($mail_tpl, $config['notifications_email']);
		}
	}

	/**
	* Edit exists banner (add form)
	*
	* @param int $bannerId - banner id
	* @param array $planInfo - banner plan info
	* @param array $data - banner data
	**/
	function edit($bannerId = false, $planInfo = false, $data = false)
	{
		if ( !$bannerId || !$planInfo || !$data ) return false;

		$bannerInfo = array();
		if ( defined('REALM') && REALM == 'admin' )
		{
			$sql = "SELECT `Account_ID`, `Date_release`, `Date_from`, `Date_to`, `Pay_date` FROM `". RL_DBPREFIX ."banners` WHERE `ID` = '{$bannerId}'";
			$bannerInfo = $this -> getRow($sql);
		}

		$sql  = "UPDATE `". RL_DBPREFIX ."banners` SET ";
		if ( defined('REALM') && REALM == 'admin' )
		{
			$sql .= "`Account_ID` = '". (int)$data['account_id'] ."', ";
		}

		$sql .= "`Plan_ID` = '". (int)$planInfo['ID'] ."', ";
		$sql .= "`Pay_date` = ". ($bannerInfo['Date_release'] ? $bannerInfo['Date_release'] : 'UNIX_TIMESTAMP()') .", ";
		$sql .= "`Date_release` = ". ($bannerInfo['Date_release'] ? $bannerInfo['Date_release'] : 'UNIX_TIMESTAMP()') .", ";
		$sql .= "`Date_from` = ". ($bannerInfo['Date_from'] ? $bannerInfo['Date_from'] : 'UNIX_TIMESTAMP()') .", ";

		$date_to = $planInfo['Plan_Type'] == 'period' ? ( $planInfo['Period'] == 0 ? 0 : time() + ( $planInfo['Period'] * 86400 ) ) : $planInfo['Period'];
		$sql .= "`Date_to` = '". ($bannerInfo['Date_to'] ? $bannerInfo['Date_to'] : $date_to) ."', ";
		$sql .= "`Box` = '". $data['banner_box'] ."', ";
		$sql .= "`Type` = '". $data['banner_type'] ."', ";
		$sql .= "`Link` = '". ($data['banner_type'] == 'image' ? $data['link'] : '') ."', ";
		$sql .= "`Follow` = '". (($data['banner_type'] == 'image' && !empty($data['link'])) ? intval($data['nofollow']) : 0) ."', ";
		$sql .= "`Status` = '". ((defined('REALM') && REALM == 'admin') ? $data['status'] : 'incomplete') ."' ";

		if ( $data['banner_type'] == 'html' )
		{
			$sql .= ",`Html` = '". str_replace('cookie', '', $data['html']) ."', ";
			$sql .= "`Image` = 'html' ";
		}
		$sql .= "WHERE `ID` = '{$bannerId}'";

		if ( $this -> query($sql) )
		{
			$this -> bannerNameHandler($bannerId, $data['name']);
		}

		if ( $data['banner_type'] == 'flash' && !empty($_FILES['flash_file']['tmp_name']) )
		{
			$this -> uploadFlash($bannerId);
		}
	}

	/**
	* Upgrade banner
	*
	* @param int $banner_id - banner ID
	* @param int $plan_id    - plan ID
	* @param int $account_id - account ID
	* @param string $txn_id  - txn ID
	* @param string $gateway - gateway name
	* @param double $total   - total summ
	**/
	function upgradeBanner($banner_id, $plan_id, $account_id, $txn_id, $gateway, $total)
	{
		global $lang, $config;

		$this -> loadClass('Actions');
		$this -> loadClass('Mail');

		// send payment notification email
		$account_info = $this -> fetch(array('Username', 'First_name', 'Last_name', 'Mail'), array('ID' => $account_id), null, 1, 'accounts', 'row');
		$account_name = $account_info['First_name'] || $account_info['Last_name'] ? $account_info['First_name'] .' '. $account_info['Last_name'] : $account_info['Username'];

		$search = array('{username}', '{gateway}', '{txn}', '{item}', '{price}', '{date}');
		$replace = array($account_name, $gateway, $txn_id, $lang['banners_planType'], $total, date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)));

		$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate('banners_payment_accepted');
		$mail_tpl['body'] = str_replace($search, $replace, $mail_tpl['body']);
		$GLOBALS['rlMail'] -> send($mail_tpl, $account_info['Mail']);

		// send admin notification
		$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate('banners_admin_banner_paid');
		$search = array('{id}', '{username}', '{gateway}', '{txn}', '{item}', '{price}', '{date}');
		$replace = array($banner_id, $account_info['Username'], $gateway, $txn_id, $lang['banners_planType'], $total, date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)));

		$mail_tpl['body'] = str_replace($search, $replace, $mail_tpl['body']);
		$GLOBALS['rlMail'] -> send($mail_tpl, $config['notifications_email']);

		// save transaction details
		$transaction = array(
			'Service' => 'banners',
			'Item_ID' => $banner_id,
			'Account_ID' => $account_id,
			'Plan_ID' => $plan_id,
			'Txn_ID' => $txn_id,
			'Total' => $total,
			'Gateway' => $gateway,
			'Date' => 'NOW()'
		);
		$action = $GLOBALS['rlActions'] -> insertOne($transaction, 'transactions');

		$GLOBALS['rlHook'] -> load('bannersUpgradeBanner');

		if ( $action )
		{
			$sql  = "SELECT `T1`.`ID`, `T1`.`Plan_ID`, `T1`.`Account_ID`, `T1`.`Date_to`, `T1`.`Status`, `T2`.`Plan_Type`, `T2`.`Period`, `T2`.`Price` ";
			$sql .= "FROM `". RL_DBPREFIX ."banners` AS `T1` ";
			$sql .= "LEFT JOIN `". RL_DBPREFIX ."banner_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
			$sql .= "WHERE `T1`.`ID` = '{$banner_id}'";
			$bannerInfo = $this -> getRow($sql);

			$now = time();
			$date_to = $bannerInfo['Period'] != 0 ? ($bannerInfo['Plan_Type'] == 'period' ? $now + ($bannerInfo['Period'] * 86400) : $bannerInfo['Date_to'] + $bannerInfo['Period']) : 0;

			$sql  = "UPDATE `". RL_DBPREFIX ."banners` SET `Pay_date` = '{$now}', `Status` = '". ($config['banners_auto_approval'] ? 'active' : 'pending') ."', `Date_to` = '{$date_to}' ";
			$sql .= "WHERE `ID` = '{$banner_id}'";
			$this -> query($sql);
		}

		$this -> loadClass('Notice');
		$GLOBALS['rlNotice'] -> saveNotice($lang['banners_noticeBannerUpgraded']);
	}

	/**
	* Prepare deleting banner plan
	*
	* @param mixed $id - plan ID
	**/
	function ajaxPrepareDeleting($id = false)
	{
		global $_response, $rlSmarty, $lang;

		if ( !$id )
			return $_response;

		$planMode = (is_numeric($id) ? true : false);
		$field = $planMode ? '`T2`.`ID`' : '`T1`.`Box`';
		$sField = $planMode ? '`T2`.`Key`,' : '';

		$deleteDetails = array();
		$deleteTotalItems = 0;

		// check banners
		$sql = "SELECT {$sField} COUNT(`T1`.`ID`) AS `count` FROM `". RL_DBPREFIX ."banners` AS `T1` ";
		if ( $planMode )
		{
			$sql .= "LEFT JOIN `". RL_DBPREFIX ."banner_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		}
		$sql .= "WHERE {$field} = '{$id}' AND `T1`.`Status` <> 'trash'";
		$banners = $this -> getRow($sql);

		array_push($deleteDetails, array(
				'name' => $lang['banners_banner'],
				'items' => (int)$banners['count'],
				'link' => RL_TPL_BASE ."index.php?controller=banners&". ($planMode ? "plan={$id}" : "box={$id}")
			)
		);
		$deleteTotalItems += $banners['count'];

		$rlSmarty -> assign_by_ref('deleteDetails', $deleteDetails);
		$rlSmarty -> assign('planInfo', array(
				'id' => $id,
				'key' => $banners['Key'],
				'name' => $planMode ? $lang['banner_plans+name+'. $banners['Key']] : $lang['blocks+name+'. $id],
				'planMode' => $planMode
			)
		);

		if ( $deleteTotalItems )
		{
			$tpl = RL_PLUGINS .'banners'. RL_DS .'delete_preparing_banner_plan.tpl';
			$_response -> assign("delete_container", 'innerHTML', $rlSmarty -> fetch($tpl, null, null, false));
			$_response -> script("$('#delete_block').slideDown();");
		}
		else
		{
			$func = $planMode ? 'xajax_deletePlan' : 'xajax_deleteBox';
			$_response -> script("{$func}('{$id}');");
		}

		return $_response;
	}

	/**
	* Delete banner plan
	*
	* @param string $id - plan ID
	**/
	function ajaxDeletePlan($id = false)
	{
		global $_response, $lang;

		$id = (int)$id;
		if ( !$id )
			return $_response;

		$delete = "DELETE FROM `". RL_DBPREFIX ."banner_plans` WHERE `ID` = '{$id}' LIMIT 1";
		if ( $this -> query($delete) )
		{
			$sql = "SELECT `ID`, `Date_release` FROM `". RL_DBPREFIX ."banners` WHERE `Plan_ID` = '{$id}'";
			$banners = $this -> getAll($sql);

			if ( !empty($banners) )
			{
				foreach($banners as $key => $entry)
				{
					$this -> deleteBanner($entry['ID'], $entry['Date_release']);
				}
			}

			//
			$this -> ubdateAbilities();
		}

		// print message, update grid
		$_response -> script("
			bannerPlans.reload();
			printMessage('notice', '{$lang['item_deleted']}');
			$('#delete_block').slideUp();
		");

		return $_response;
	}

	/**
	* Delete banner
	*
	* @param int $id - banner ID
	* @param int $date - date release of the banner
	**/
	function deleteBanner($id = false, $date = false)
	{
		$id = (int)$id;
		$delete = "DELETE FROM `". RL_DBPREFIX ."banners` WHERE `ID` = '{$id}' LIMIT 1";
		if ( $this -> query($delete) )
		{
			$this -> query("DELETE FROM `". RL_DBPREFIX ."banners_click` WHERE `Banner_ID` = '{$id}'");
			$this -> query("DELETE FROM `". RL_DBPREFIX ."lang_keys` WHERE `Key` = 'banners+name+{$id}' AND `Plugin` = 'banners'");
			$this -> deleteDirectory(RL_FILES .'banners'. RL_DS . date('m-Y', $date) . RL_DS ."b{$id}". RL_DS);
		}
	}

	/**
	* Delete banner throught ajax
	*
	* @param int $id - banner ID
	**/
	function ajaxDeleteBanner($id = false)
	{
		global $_response, $lang, $config, $pages, $page_info;

		$id = (int)$id;

		// get banner info
		$bannerInfo = $this -> getRow("SELECT `Account_ID`, `Date_release` FROM `". RL_DBPREFIX ."banners` WHERE `ID` = '{$id}' LIMIT 1");

		// check realm/owner
		if ( !defined('REALM') && $bannerInfo['Account_ID'] != $_SESSION['id'] ) return $_response;

		// delete
		$this -> deleteBanner($id, $bannerInfo['Date_release']);

		if ( defined('REALM') )
		{
			$_response -> script("printMessage('notice', '{$lang['banners_noticeBannerDeleted']}');");
			$_response -> script("bannersGrid.reload();");
		}
		else
		{
			$sql = "SELECT COUNT(`ID`) AS `count` FROM `". RL_DBPREFIX ."banners` WHERE `Account_ID` = '{$bannerInfo['Account_ID']}' AND `Status` <> 'trash'";
			$exists = $this -> getRow($sql);
			if ( $exists['count'] == 0 )
			{
				$href = $config['mod_rewrite'] ? SEO_BASE . $pages['add_banner'] .'.html' : RL_URL_HOME .'?page='. $pages['add_banner'];
				$replace = preg_replace('/(\[(.+)\])/', '<a href="'. $href .'">$2</a>', $lang['banners_noBannersHere'] );
				$emptyMess = '<div class="info">'. $replace .'</div>';
				$_response -> assign('controller_area', 'innerHTML', $emptyMess);
			}

			// redirect user to the first page if it was the latest banner on the current page
			$bannersCount = (int)$exists['count'];
			$pagesCount = ceil($bannersCount / $config['listings_per_page']);

			$_SESSION['mb_deleted']++;

			// ( 5 <= (5 * 2) && 2 > 1 || 1 == 5 )
			if ( ( $bannersCount <= ($config['listings_per_page'] * $_GET['pg'] ) && $_GET['pg'] > 1 ) || $_SESSION['mb_deleted'] == $config['listings_per_page'] )
			{
				$url = SEO_BASE;
				$url .= $config['mod_rewrite'] ? $page_info['Path'] .'.html' : '?page='. $page_info['Path'];
				$_response -> redirect($url);
			}
			else
			{
				$_response -> script("$('#banner_{$id}').fadeOut('slow', function(){ $(this).remove() });");
				$_response -> script("printMessage('notice', '{$lang['banners_noticeBannerDeleted']}')");
			}
		}

		return $_response;
	}

	/**
	* Delete banner box by key
	*
	* @param string $key - key of box
	**/
	function ajaxDeleteBannerBox($key = false)
	{
		global $_response, $lang;

		$GLOBALS['rlValid'] -> sql($key);
		$delete = "DELETE FROM `". RL_DBPREFIX ."blocks` WHERE `Key` = '{$key}' AND `Plugin` = 'banners' LIMIT 1";
		if ( $this -> query($delete) )
		{
			// remove fake box
			$this -> query("DELETE FROM `". RL_DBPREFIX ."hooks` WHERE `Name` = 'tplBetweenCategories' AND `Plugin` LIKE 'banners_{$key}' LIMIT 1");

			// remove the box from banner plans
			$sql = "SELECT `ID`, `Boxes` FROM `". RL_DBPREFIX ."banner_plans` WHERE FIND_IN_SET('{$key}', `Boxes`) > 0";
			$plans = $this -> getAll($sql);

			if ( !empty($plans) )
			{
				$setBox = '';
				$ids = array();

				foreach($plans as $pKey => $entry)
				{
					$boxes = explode(',', $entry['Boxes']);
					$index = array_search($key, $boxes);
					unset($boxes[$index]);

					$setBox .= "WHEN {$entry['ID']} THEN '". implode(',', $boxes) ."' ";
					array_push($ids, $entry['ID']);
				}

				$updatePlans = "
				UPDATE `". RL_DBPREFIX ."banner_plans`
				    SET `Boxes` = CASE `ID`
				        {$setBox}
				    END
				WHERE `ID` IN ('". implode("','", $ids) ."')";
				$this -> query($updatePlans);

				// disable plans without boxes
				$this -> query("UPDATE `". RL_DBPREFIX ."banner_plans` SET `Status` = 'approval' WHERE `Boxes` = ''");
			}

			// remove banners
			$sql = "SELECT `ID`, `Date_release` FROM `". RL_DBPREFIX ."banners` WHERE `Box` = '{$key}'";
			$banners = $this -> getAll($sql);

			if ( !empty($banners) )
			{
				foreach($banners as $key => $entry)
				{
					$this -> deleteBanner($entry['ID'], $entry['Date_release']);
				}
			}
		}

		// print message, update grid
		$_response -> script("
			bannerBoxes.reload();
			printMessage('notice', '{$lang['item_deleted']}');
			$('#delete_block').slideUp();
		");

		return $_response;
	}

	/**
	* Mass actions for Banner manager
	*
	* @param string $ids - banners ids
	* @param string $action - mass action
	**/
	function ajaxBannersMassActions($ids = false, $action = false)
	{
		global $_response, $lang;

		if ( !$ids || !$action ) return false;

		$GLOBALS['rlValid'] -> sql($ids);
		$ids = explode('|', $ids);

		switch($action)
		{
			case 'activate':
				$this -> query("UPDATE `". RL_DBPREFIX ."banners` SET `Status` = 'active' WHERE `ID` IN ('". implode("','", $ids) ."')");
				break;
			case 'approve':
				$this -> query("UPDATE `". RL_DBPREFIX ."banners` SET `Status` = 'approval' WHERE `ID` IN ('". implode("','", $ids) ."')");
				break;
			case 'delete':
				foreach( $ids as $key => $bannerId )
				{
					$date = $this -> getOne('Date_release', "`ID` = '{$bannerId}'", 'banners');
					$this -> deleteBanner($bannerId, $date);
				}
				break;
		}
		$_response -> script("printMessage('notice', '{$lang['banners_noticeBannerMassAction_'. $action]}');");
		$_response -> script("bannersGrid.reload();");

		return $_response;
	}

	/**
	* is_animated_gif
	**/
	function is_animated_gif($filename = false)
	{
		$raw = file_get_contents($filename);

		$offset = 0;
		$frames = 0;
		while ($frames < 2) {
			$where1 = strpos($raw, "\x00\x21\xF9\x04", $offset);
			if ( $where1 === false ) {
				break;
			}
			else {
				$offset = $where1 + 1;
				$where2 = strpos( $raw, "\x00\x2C", $offset );
				if ( $where2 === false ) {
					break;
				}
				else {
					if ( $where1 + 8 == $where2 ) {
						$frames ++;
					}
					$offset = $where2 + 1;
				}
			}
		}
		return $frames > 1;
	}

	/**
	* makeBannerFolder
	**/
	function makeBannerFolder($bannerId = false, $options = false)
	{
		$dir = false;
		$curPhoto = $this -> getOne('Image', "`ID` = '{$bannerId}'", 'banners');

		if ( $curPhoto )
		{
			$expDir = explode('/', $curPhoto);
			if ( count($expDir) > 1 )
			{
				array_pop($expDir);
				$dir = RL_FILES . $options['banners_dir'] . RL_DS . implode(RL_DS, $expDir) . RL_DS;
				$dirName = implode('/', $expDir) .'/';
			}
		}

		if ( !$dir )
		{
			$dir = RL_FILES . $options['banners_dir'] . RL_DS . date('m-Y') . RL_DS .'b'. $bannerId . RL_DS;
			$dirName = date('m-Y') .'/b'. $bannerId .'/';
		}

		$url = $options['upload_url'] . $dirName;
		$this -> rlMkdir($dir);

		return array(
			'dir' => $dir,
			'url' => $url,
			'dirName' => $dirName
		);
	}

	/**
	* Upload flash banner
	*
	* @param int $bannerId - banner id
	**/
	function uploadFlash($bannerId = false)
	{
		$file_tmp_dir = $_FILES['flash_file']['tmp_name'];
		$file_name = 'banner_'. time() . mt_rand() .'.swf';

		$settings = array(
			'banners_dir' => 'banners'. RL_DS,
			'upload_url' => RL_FILES_URL .'banners/'
		);
		$folderInfo = $this -> makeBannerFolder($bannerId, $settings);
		$file_dir = $folderInfo['dir'] . $file_name;

		// copy flash file
		if ( copy($file_tmp_dir, $file_dir) )
		{
			chmod($file_dir, 0644);

			// add media to db
			$update = array(
				'fields' => array(
					'Image' => $folderInfo['dirName'] . $file_name
				),
				'where' => array('ID' => $bannerId)
			);

			if ( !defined('REALM') )
			{
				$update['fields']['Status'] = 'incomplete';
			}
			$GLOBALS['rlActions'] -> updateOne($update, 'banners');
		}
	}

	/**
	* Remove banner Flash file
	**/
	function ajaxRemoveFlash($file = false)
	{
		global $_response, $page_info;

		if ( !$file ) return $_response;

		if ( defined('REALM') && REALM == 'admin' )
		{
			$banner_id = $_GET['id'];
		}
		else
		{
			$banner_id = ($page_info['Key'] == 'banners_edit_banner') ? $_SESSION['edit_banner']['banner_id'] : $_SESSION['add_banner']['banner_id'];
		}
		$banner_id = (int)$banner_id;

		$dateRelease = explode('/', $file);
		$this -> query("UPDATE `". RL_DBPREFIX ."banners` SET `Image` = '' WHERE `ID` = '{$banner_id}' LIMIT 1");
		$this -> deleteDirectory(RL_FILES . 'banners'. RL_DS . $dateRelease[0] . RL_DS ."b{$banner_id}". RL_DS);

		$_response -> script('
			$("div.fileupload_flash").fadeOut("fast", function(){
				$(this).remove();
				$("#banner_flash_upload").fadeIn("fast");
			});
		');

		return $_response;
	}

	/**
	* Get countries list
	*/
	function getCountriesList()
	{
		$countries = '[
			{"Country_code":"AF","Country_name":"Afghanistan"},{"Country_code":"AX","Country_name":"Aland Islands"},{"Country_code":"AL","Country_name":"Albania"},
			{"Country_code":"DZ","Country_name":"Algeria"},{"Country_code":"AS","Country_name":"American Samoa"},{"Country_code":"AD","Country_name":"Andorra"},
			{"Country_code":"AO","Country_name":"Angola"},{"Country_code":"AI","Country_name":"Anguilla"},{"Country_code":"AQ","Country_name":"Antarctica"},
			{"Country_code":"AG","Country_name":"Antigua and Barbuda"},{"Country_code":"AR","Country_name":"Argentina"},{"Country_code":"AM","Country_name":"Armenia"},
			{"Country_code":"AW","Country_name":"Aruba"},{"Country_code":"AU","Country_name":"Australia"},{"Country_code":"AT","Country_name":"Austria"},
			{"Country_code":"AZ","Country_name":"Azerbaijan"},{"Country_code":"BS","Country_name":"Bahamas"},{"Country_code":"BH","Country_name":"Bahrain"},
			{"Country_code":"BD","Country_name":"Bangladesh"},{"Country_code":"BB","Country_name":"Barbados"},{"Country_code":"BY","Country_name":"Belarus"},
			{"Country_code":"BE","Country_name":"Belgium"},{"Country_code":"BZ","Country_name":"Belize"},{"Country_code":"BJ","Country_name":"Benin"},
			{"Country_code":"BM","Country_name":"Bermuda"},{"Country_code":"BT","Country_name":"Bhutan"},{"Country_code":"BO","Country_name":"Bolivia"},
			{"Country_code":"BA","Country_name":"Bosnia and Herzegovina"},{"Country_code":"BW","Country_name":"Botswana"},
			{"Country_code":"BV","Country_name":"Bouvet Island"},{"Country_code":"BR","Country_name":"Brazil"},{"Country_code":"IO","Country_name":"British Indian Ocean Territory"},
			{"Country_code":"BN","Country_name":"Brunei Darussalam"},{"Country_code":"BG","Country_name":"Bulgaria"},{"Country_code":"BF","Country_name":"Burkina Faso"},
			{"Country_code":"BI","Country_name":"Burundi"},{"Country_code":"KH","Country_name":"Cambodia"},{"Country_code":"CM","Country_name":"Cameroon"},
			{"Country_code":"CA","Country_name":"Canada"},{"Country_code":"CV","Country_name":"Cape Verde"},{"Country_code":"KY","Country_name":"Cayman Islands"},
			{"Country_code":"CF","Country_name":"Central African Republic"},{"Country_code":"TD","Country_name":"Chad"},{"Country_code":"CL","Country_name":"Chile"},
			{"Country_code":"CN","Country_name":"China"},{"Country_code":"CX","Country_name":"Christmas Island"},{"Country_code":"CC","Country_name":"Cocos (Keeling) Islands"},
			{"Country_code":"CO","Country_name":"Colombia"},{"Country_code":"KM","Country_name":"Comoros"},{"Country_code":"CG","Country_name":"Congo"},
			{"Country_code":"CD","Country_name":"Congo, The Democratic Republic of the"},{"Country_code":"CK","Country_name":"Cook Islands"},
			{"Country_code":"CR","Country_name":"Costa Rica"},{"Country_code":"CI","Country_name":"Cote D\'Ivoire"},{"Country_code":"HR","Country_name":"Croatia"},
			{"Country_code":"CU","Country_name":"Cuba"},{"Country_code":"CY","Country_name":"Cyprus"},{"Country_code":"CZ","Country_name":"Czech Republic"},
			{"Country_code":"DK","Country_name":"Denmark"},{"Country_code":"DJ","Country_name":"Djibouti"},{"Country_code":"DM","Country_name":"Dominica"},
			{"Country_code":"DO","Country_name":"Dominican Republic"},{"Country_code":"TL","Country_name":"East Timor"},{"Country_code":"EC","Country_name":"Ecuador"},
			{"Country_code":"EG","Country_name":"Egypt"},{"Country_code":"SV","Country_name":"El Salvador"},{"Country_code":"GQ","Country_name":"Equatorial Guinea"},
			{"Country_code":"ER","Country_name":"Eritrea"},{"Country_code":"EE","Country_name":"Estonia"},{"Country_code":"ET","Country_name":"Ethiopia"},
			{"Country_code":"FK","Country_name":"Falkland Islands (Malvinas)"},{"Country_code":"FO","Country_name":"Faroe Islands"},{"Country_code":"FJ","Country_name":"Fiji"},
			{"Country_code":"FI","Country_name":"Finland"},{"Country_code":"FR","Country_name":"France"},{"Country_code":"GF","Country_name":"French Guiana"},
			{"Country_code":"PF","Country_name":"French Polynesia"},{"Country_code":"TF","Country_name":"French Southern Territories"},{"Country_code":"GA","Country_name":"Gabon"},
			{"Country_code":"GM","Country_name":"Gambia"},{"Country_code":"GE","Country_name":"Georgia"},{"Country_code":"DE","Country_name":"Germany"},
			{"Country_code":"GH","Country_name":"Ghana"},{"Country_code":"GI","Country_name":"Gibraltar"},{"Country_code":"GR","Country_name":"Greece"},
			{"Country_code":"GL","Country_name":"Greenland"},{"Country_code":"GD","Country_name":"Grenada"},{"Country_code":"GP","Country_name":"Guadeloupe"},
			{"Country_code":"GU","Country_name":"Guam"},{"Country_code":"GT","Country_name":"Guatemala"},{"Country_code":"GG","Country_name":"Guernsey"},
			{"Country_code":"GN","Country_name":"Guinea"},{"Country_code":"GW","Country_name":"Guinea-Bissau"},{"Country_code":"GY","Country_name":"Guyana"},
			{"Country_code":"HT","Country_name":"Haiti"},{"Country_code":"HM","Country_name":"Heard Island and McDonald Islands"},
			{"Country_code":"VA","Country_name":"Holy See (Vatican City State)"},{"Country_code":"HN","Country_name":"Honduras"},{"Country_code":"HK","Country_name":"Hong Kong"},
			{"Country_code":"HU","Country_name":"Hungary"},{"Country_code":"IS","Country_name":"Iceland"},{"Country_code":"IN","Country_name":"India"},
			{"Country_code":"ID","Country_name":"Indonesia"},{"Country_code":"IR","Country_name":"Iran, Islamic Republic of"},{"Country_code":"IQ","Country_name":"Iraq"},
			{"Country_code":"IE","Country_name":"Ireland"},{"Country_code":"IM","Country_name":"Isle of Man"},{"Country_code":"IL","Country_name":"Israel"},
			{"Country_code":"IT","Country_name":"Italy"},{"Country_code":"JM","Country_name":"Jamaica"},{"Country_code":"JP","Country_name":"Japan"},
			{"Country_code":"JE","Country_name":"Jersey"},{"Country_code":"JO","Country_name":"Jordan"},{"Country_code":"KZ","Country_name":"Kazakhstan"},
			{"Country_code":"KE","Country_name":"Kenya"},{"Country_code":"KI","Country_name":"Kiribati"},{"Country_code":"KP","Country_name":"Korea, Democratic People\'s Republic of"},
			{"Country_code":"KR","Country_name":"Korea, Republic of"},{"Country_code":"KW","Country_name":"Kuwait"},{"Country_code":"KG","Country_name":"Kyrgyzstan"},
			{"Country_code":"LA","Country_name":"Lao People\'s Democratic Republic"},{"Country_code":"LV","Country_name":"Latvia"},{"Country_code":"LB","Country_name":"Lebanon"},
			{"Country_code":"LS","Country_name":"Lesotho"},{"Country_code":"LR","Country_name":"Liberia"},{"Country_code":"LY","Country_name":"Libyan Arab Jamahiriya"},
			{"Country_code":"LI","Country_name":"Liechtenstein"},{"Country_code":"LT","Country_name":"Lithuania"},{"Country_code":"LU","Country_name":"Luxembourg"},
			{"Country_code":"MO","Country_name":"Macau"},{"Country_code":"MK","Country_name":"Macedonia"},{"Country_code":"MG","Country_name":"Madagascar"},
			{"Country_code":"MW","Country_name":"Malawi"},{"Country_code":"MY","Country_name":"Malaysia"},{"Country_code":"MV","Country_name":"Maldives"},
			{"Country_code":"ML","Country_name":"Mali"},{"Country_code":"MT","Country_name":"Malta"},{"Country_code":"MH","Country_name":"Marshall Islands"},
			{"Country_code":"MQ","Country_name":"Martinique"},{"Country_code":"MR","Country_name":"Mauritania"},{"Country_code":"MU","Country_name":"Mauritius"},
			{"Country_code":"YT","Country_name":"Mayotte"},{"Country_code":"MX","Country_name":"Mexico"},{"Country_code":"FM","Country_name":"Micronesia, Federated States of"},
			{"Country_code":"MD","Country_name":"Moldova, Republic of"},{"Country_code":"MC","Country_name":"Monaco"},{"Country_code":"MN","Country_name":"Mongolia"},
			{"Country_code":"ME","Country_name":"Montenegro"},{"Country_code":"MS","Country_name":"Montserrat"},{"Country_code":"MA","Country_name":"Morocco"},
			{"Country_code":"MZ","Country_name":"Mozambique"},{"Country_code":"MM","Country_name":"Myanmar"},{"Country_code":"NA","Country_name":"Namibia"},
			{"Country_code":"NR","Country_name":"Nauru"},{"Country_code":"NP","Country_name":"Nepal"},{"Country_code":"NL","Country_name":"Netherlands"},
			{"Country_code":"AN","Country_name":"Netherlands Antilles"},{"Country_code":"NC","Country_name":"New Caledonia"},{"Country_code":"NZ","Country_name":"New Zealand"},
			{"Country_code":"NI","Country_name":"Nicaragua"},{"Country_code":"NE","Country_name":"Niger"},{"Country_code":"NG","Country_name":"Nigeria"},
			{"Country_code":"NU","Country_name":"Niue"},{"Country_code":"NF","Country_name":"Norfolk Island"},{"Country_code":"MP","Country_name":"Northern Mariana Islands"},
			{"Country_code":"NO","Country_name":"Norway"},{"Country_code":"OM","Country_name":"Oman"},{"Country_code":"PK","Country_name":"Pakistan"},
			{"Country_code":"PW","Country_name":"Palau"},{"Country_code":"PS","Country_name":"Palestinian Territory"},{"Country_code":"PA","Country_name":"Panama"},
			{"Country_code":"PG","Country_name":"Papua New Guinea"},{"Country_code":"PY","Country_name":"Paraguay"},{"Country_code":"PE","Country_name":"Peru"},
			{"Country_code":"PH","Country_name":"Philippines"},{"Country_code":"PN","Country_name":"Pitcairn"},{"Country_code":"PL","Country_name":"Poland"},
			{"Country_code":"PT","Country_name":"Portugal"},{"Country_code":"PR","Country_name":"Puerto Rico"},{"Country_code":"QA","Country_name":"Qatar"},
			{"Country_code":"RE","Country_name":"Reunion"},{"Country_code":"RO","Country_name":"Romania"},{"Country_code":"RU","Country_name":"Russian Federation"},
			{"Country_code":"RW","Country_name":"Rwanda"},{"Country_code":"SH","Country_name":"Saint Helena"},{"Country_code":"KN","Country_name":"Saint Kitts and Nevis"},
			{"Country_code":"LC","Country_name":"Saint Lucia"},{"Country_code":"PM","Country_name":"Saint Pierre and Miquelon"},
			{"Country_code":"VC","Country_name":"Saint Vincent and the Grenadines"},{"Country_code":"WS","Country_name":"Samoa"},{"Country_code":"SM","Country_name":"San Marino"},
			{"Country_code":"ST","Country_name":"Sao Tome and Principe"},{"Country_code":"SA","Country_name":"Saudi Arabia"},{"Country_code":"SN","Country_name":"Senegal"},
			{"Country_code":"RS","Country_name":"Serbia"},{"Country_code":"SC","Country_name":"Seychelles"},{"Country_code":"SL","Country_name":"Sierra Leone"},
			{"Country_code":"SG","Country_name":"Singapore"},{"Country_code":"SK","Country_name":"Slovakia"},{"Country_code":"SI","Country_name":"Slovenia"},
			{"Country_code":"SB","Country_name":"Solomon Islands"},{"Country_code":"SO","Country_name":"Somalia"},{"Country_code":"ZA","Country_name":"South Africa"},
			{"Country_code":"GS","Country_name":"South Georgia and the South Sandwich Islands"},{"Country_code":"ES","Country_name":"Spain"},
			{"Country_code":"LK","Country_name":"Sri Lanka"},{"Country_code":"SD","Country_name":"Sudan"},{"Country_code":"SR","Country_name":"Suriname"},
			{"Country_code":"SJ","Country_name":"Svalbard and Jan Mayen"},{"Country_code":"SZ","Country_name":"Swaziland"},{"Country_code":"SE","Country_name":"Sweden"},
			{"Country_code":"CH","Country_name":"Switzerland"},{"Country_code":"SY","Country_name":"Syrian Arab Republic"},
			{"Country_code":"TW","Country_name":"Taiwan (Province of China)"},{"Country_code":"TJ","Country_name":"Tajikistan"},{"Country_code":"TZ","Country_name":"Tanzania, United Republic of"},
			{"Country_code":"TH","Country_name":"Thailand"},{"Country_code":"TG","Country_name":"Togo"},{"Country_code":"TK","Country_name":"Tokelau"},{"Country_code":"TO","Country_name":"Tonga"},
			{"Country_code":"TT","Country_name":"Trinidad and Tobago"},{"Country_code":"TN","Country_name":"Tunisia"},{"Country_code":"TR","Country_name":"Turkey"},
			{"Country_code":"TM","Country_name":"Turkmenistan"},{"Country_code":"TC","Country_name":"Turks and Caicos Islands"},{"Country_code":"TV","Country_name":"Tuvalu"},
			{"Country_code":"UG","Country_name":"Uganda"},{"Country_code":"UA","Country_name":"Ukraine"},{"Country_code":"AE","Country_name":"United Arab Emirates"},
			{"Country_code":"GB","Country_name":"United Kingdom"},{"Country_code":"US","Country_name":"United States"},{"Country_code":"UM","Country_name":"United States Minor Outlying Islands"},
			{"Country_code":"UY","Country_name":"Uruguay"},{"Country_code":"UZ","Country_name":"Uzbekistan"},{"Country_code":"VU","Country_name":"Vanuatu"},
			{"Country_code":"VE","Country_name":"Venezuela"},{"Country_code":"VN","Country_name":"Vietnam"},{"Country_code":"VG","Country_name":"Virgin Islands, British"},
			{"Country_code":"VI","Country_name":"Virgin Islands, U.S."},{"Country_code":"WF","Country_name":"Wallis and Futuna"},{"Country_code":"EH","Country_name":"Western Sahara"},
			{"Country_code":"YE","Country_name":"Yemen"},{"Country_code":"ZM","Country_name":"Zambia"},{"Country_code":"ZW","Country_name":"Zimbabwe"}
		]';
		$countries = preg_replace('/(\n|\t|\r)?/', '', $countries);

		$this -> loadClass('Json');
		return $GLOBALS['rlJson'] -> decode($countries);
	}

	/**
	* enum/set database fields manager
	*
	* @param string $table - table
	* @param string $field - field
	* @param string $value - new value
	**/
	function enumAdd($table = false, $field = false, $value = false)
	{
		$sql = "SHOW COLUMNS FROM `". RL_DBPREFIX ."{$table}` LIKE '{$field}'";
		$enum_row = $this -> getRow($sql);

		preg_match('/([a-z]*)\((.*)\)/', $enum_row[$field], $matches);
		if ( isset($matches[2]) )
		{
			$enum_values = explode(',', $matches[2]);

			if ( false === array_search("'{$value}'", $enum_values) )
			{
				$this -> loadClass('Actions');
				$GLOBALS['rlActions'] -> enumAdd($table, $field, $value);
			}
		}
	}

	/**
	* mfActive
	*/
	function mfActive()
	{
		$status = $this -> getOne('Status', "`Key` = 'multiField'", 'plugins');
		return ($status === 'active');
	}

	/**
	* mfActiveInFrontEnd
	*/
	function mfActiveInFrontEnd()
	{
		global $rlHook;

		if ( array_key_exists('boot', $rlHook -> rlHooks) )
		{
			$boot_hook = $rlHook -> rlHooks['boot'];
			if ( is_string($boot_hook) )
			{
				return is_numeric(strpos($boot_hook, 'rlMultiField'));
			}
			else
			{
				foreach($boot_hook as $key => $hook)
				{
					if ( is_numeric(strpos($hook, 'rlMultiField')) )
					{
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	* mfGetLocations
	*/
	function mfGetLocations($parent = false)
	{
		global $rlMultiField;

		if ( !$parent )
			$parent = $this -> getOne('Key', "`Geo_filter` = '1'", 'multi_formats');

		$this -> loadClass('MultiField', null, 'multiField');
		$mf_locations = $rlMultiField -> getMDF($parent, 'alphabetic');

		if ( !empty($mf_locations) )
		{
			foreach($mf_locations as $key => $entry)
			{
				$first_child = (int)$this -> getOne('ID', "`Parent_ID` = '{$entry['ID']}'", 'data_formats');
				$mf_locations[$key]['childs'] = $first_child;
			}
		}

		return $mf_locations;
	}

	/**
	* xajax_mfGetChildrens
	*/
	function ajaxMfGetChildrens($parent = false)
	{
		global $_response, $rlMultiField, $rlSmarty;

		if ( !$parent )
			return $_response;

		$GLOBALS['rlValid'] -> sql($parent);

		$mf_locations = $this -> mfGetLocations($parent);
		if ( !empty($mf_locations) )
		{
			$rlSmarty -> assign('mf_locations', $mf_locations);

			$file = RL_PLUGINS .'banners'. RL_DS .'admin'. RL_DS .'mf_locations.tpl';
			$_response -> append("mf_tree_{$parent}", 'innerHTML', $rlSmarty -> fetch($file, null, null, false));

			$_response -> script("
				$('#mf_tree_{$parent} > ul').fadeIn('normal');
				$('#mf_tree_{$parent} > img').addClass('opened');
				$('#mf_tree_{$parent} > span.tree_loader').fadeOut(function(){
					$(this).remove();
				});
			");
			$_response -> call('mf_loaderTree');
			$_response -> script('mf_openTree(mf_tree_selected, mf_tree_parentPoints);');
		}

		return $_response;
	}

	/**
	* mfParentPoints
	*/
	function mfParentPoints($locations = false)
	{
		global $rlSmarty, $rlValid;

		$rlValid -> sql($locations);
		if ( !is_array($locations) )
		{
			$locations = trim($locations, ',');
		}

		if ( empty($locations) || empty($locations[0]) )
			return false;

		$sql  = "SELECT `ID`, `Parent_ID`, `Key` FROM `". RL_DBPREFIX ."data_formats` ";
		$sql .= "WHERE `Key` IN ('". implode("','", $locations) ."') AND `Status` = 'active'";
		$parents = $this -> getAll($sql);

		$mf_checked = array();
		if ( !empty($parents) )
		{
			$pointParents = array();
			$geo_parent = $this -> getOne('Key', "`Geo_filter` = '1'", 'multi_formats');
			foreach($parents as $key => $parent)
			{
				if ( !$parent['Parent_ID'] || in_array($parent['Key'], $mf_checked) )
					continue;

				if ( $geo_parent != $parent['Key'] )
				{
					$mf_parents = $this -> mfParents($parent['Parent_ID'], $geo_parent);
					foreach($mf_parents as $mfKey => $mfParent)
					{
						if ( !in_array($mfParent, $mf_checked) )
						{
							array_push($mf_checked, $mfParent);
						}
					}
				}
			}
			unset($parents);
		}

		$rlSmarty -> assign('mfParentPoints', $mf_checked);
	}

	/**
	* mfParents
	*/
	function mfParents($parent = false, $geo_parent = false, $pkeys = false)
	{
		$parent = (int)$parent;
		if ( !$parent )
			return false;

		$pkeys = $pkeys ? $pkeys : array();
		$sql  = "SELECT `Parent_ID`, `Key` FROM `". RL_DBPREFIX ."data_formats` ";
		$sql .= "WHERE `ID` = {$parent} LIMIT 1";
		$info = $this -> getRow($sql);

		if ( !empty($info) )
		{
			if ( $geo_parent != $info['Key'] )
			{
				array_push($pkeys, $info['Key']);
				if ( !empty($info['Parent_ID']) )
				{
					return $this -> mfParents($info['Parent_ID'], $geo_parent, $pkeys);
				}
			}
		}
		return array_reverse($pkeys);
	}

	/**
	* checkAbilities
	*/
	function checkAbilities()
	{
		global $config, $deny_pages, $account_info;

		if ( defined('IS_LOGIN') && !empty($config['banners_allow_add_banner_types']) )
		{
			if ( !in_array($account_info['Type'], explode(',', $config['banners_allow_add_banner_types'])) )
			{
				$deny_pages[] = 'add_banner';
				$deny_pages[] = 'my_banners';
			}
		}
	}

	/**
	* ubdateAbilities
	*/
	function ubdateAbilities()
	{
		global $rlConfig;

		$sql  = "SELECT `Allow_for` FROM `". RL_DBPREFIX ."banner_plans` ";
		$sql .= "WHERE `Status` = 'active' AND `Allow_for` <> '' AND `Admin` = '0'";
		$limited_plans = $this -> getAll($sql);

		$abilities = array();
		if ( !empty($limited_plans) )
		{
			foreach($limited_plans as $plan)
			{
				$abilities = array_merge($abilities, explode(',', $plan['Allow_for']));
			}
			$abilities = array_unique($abilities);
		}

		$this -> loadClass('Actions');
		$rlConfig -> setConfig('banners_allow_add_banner_types', implode(',', $abilities));
	}

	/**
	* Set status expired for banners
	**/
	function cron()
	{
		global $date_format, $rlMail, $rlAccount;

		$this -> loadClass('Mail');
		$this -> loadClass('Account');

		$sql  = "SELECT `T1`.`ID`, `T1`.`Account_ID` FROM `". RL_DBPREFIX ."banners` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."banner_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T1`.`Cron` = '0' AND `T1`.`Date_to` <> '0' AND `T1`.`Status` = 'active' AND ";
		$sql .= "IF(`T2`.`Plan_Type` = 'views', IF(`T1`.`Shows` >= `T1`.`Date_to`, 1, 0), IF(`T1`.`Date_to` <= UNIX_TIMESTAMP(), 1, 0)) = 1";
		$data = $this -> getAll($sql);

		if ( empty($data) )
		{
			$this -> query("UPDATE `". RL_DBPREFIX ."banners` SET `Cron` = '0' WHERE `Status` <> 'incomplete'");
		}
		else
		{
			$ids = array();
			$banner_expired_email = $rlMail -> getEmailTemplate('banners_cron_banner_expired');

			foreach($data as $key => $banner)
			{
				array_push($ids, $banner['ID']);

				$accountInfo = $rlAccount -> getProfile((int)$banner['Account_ID']);
				$copy_banner_expired_email = $banner_expired_email;
				$copy_banner_expired_email['body'] = str_replace('{username}', $accountInfo['Full_name'], $copy_banner_expired_email['body']);

				$rlMail -> send($copy_banner_expired_email, $accountInfo['Mail']);
			}

			if ( !empty($ids) )
			{
				$sql  = "UPDATE `". RL_DBPREFIX ."banners` SET `Status` = 'expired', `Pay_date` = '', `Cron` = '1' ";
				$sql .= "WHERE `ID` IN ('". implode("','", $ids) ."')";
				$this -> query($sql);
			}
		}
	}

	/**
	* isBot - detect bots
	*
	* @param string $userAgent - User agent
	* @return bool - true/false
	**/
	function isBot($userAgent = '')
	{
		// if no user agent is supplied then assume it's a bot
		if ( empty($userAgent) ) return true;

		// convert to lower case
		$userAgent = strtolower($userAgent);

		// array of bots
		$bots = array( 
			"google", "bot", "radian",
			"yahoo", "spider", "crawl",
			"archiver", "curl", "yandex",
			"python", "nambu", "eventbox",
			"twitt", "perl", "monitor",
			"sphere", "pear", "mechanize",
			"java", "wordpress", "facebookexternal"
		);

		foreach($bots as $bot)
		{
			if ( is_numeric(strpos($userAgent, $bot)) )
			{
				return true;
			}
		}
		return false;
	}

	/**
	* Uninstall the plugin
	**/
	function uninstall()
	{
		$this -> query("ALTER TABLE `". RL_DBPREFIX ."blocks` DROP `Banners`");
		$this -> query("DROP TABLE IF EXISTS `". RL_DBPREFIX ."banners_click`");
		$this -> query("DROP TABLE IF EXISTS `". RL_DBPREFIX ."banner_plans`");
		$this -> query("DROP TABLE IF EXISTS `". RL_DBPREFIX ."banners`");
		$this -> query("UPDATE `". RL_DBPREFIX ."transactions` SET `Item_ID` = '0', `Plan_ID` = '0' WHERE `Service` = 'banners'");

		// remove fake boxes
		$this -> query("DELETE FROM `". RL_DBPREFIX ."hooks` WHERE `Name` = 'tplBetweenCategories' AND `Plugin` LIKE 'banners_%'");

		// remove deprecated hook
		$this -> query("DELETE FROM `". RL_DBPREFIX ."hooks` WHERE `Name` = 'phpGetGEOData' AND `Plugin` = 'banners'");

		// delete all banners
		$this -> deleteDirectory(RL_FILES .'banners'. RL_DS);
	}
}