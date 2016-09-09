<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: IPHONE.CLASS.PHP
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

class iPhone extends reefless
{
	/**
	* Current page
	**/
	var $currentPages;

	/**
	* Total pages
	**/
	var $totalPages;

	/**
	* pValid - make valid string for iPhone parser
	* 
	* @param string $string - string for validate
	* @param string $trim - custom char for trim
	* @return string $string - valid a string
	**/
	function pValid($string = '', $trim = false)
	{
		$string = preg_replace("~<a[^>]+href\s*=\s*[\x27\x22]?[^\x20\x27\x22\x3E]+[\x27\x22]?[^>]*>(.+?)</a>~is", '$1', $string);
		$string = preg_replace('~<img.*src="(.*?)".*\/?>~is', '$1', $string);
		$string = strip_tags($string);
		$string = str_replace('&quot;', '"', $string);

		if ( $trim !== false )
		{
			$string = str_replace($trim, '', $string);
		}
		$string = trim($string);

		return $string;
	}

	/**
	* Print data as XML
	* 
	* @param array $data - data for Property List
	* @param array $info - additional info
	**/
	function printAsXml($data = false, $info = false)
	{
		global $globalInfo;

		// merge
		$info = ($info !== false) ? $info : array('skip' => 'yes');
		if ( !empty($globalInfo) )  {
			$info = array_merge_recursive($info, $globalInfo);
		}

		$out['data'] = $data;
		$out['info'] = $info;

		$plist = new PropertyList($out);
		$xml = $plist -> xml();

		// print
		$this -> printAsText($xml);
	}

	/**
	* Print data as Text
	* 
	* @param string $string - string for output
	**/
	function printAsText($string = false)
	{
		if ( function_exists('gzencode') && APP_USE_GZIP === true )
		{
			echo gzencode($string);
		}
		else
		{
			echo $string;
		}
		exit;
	}

	/**
	* Build listings short form
	*
	* @param array &$listings - listings data array
	* @param int $limit - limit of listings
	* @param bool $my_listings - trigger
	*
	* @return array $data - structured data for App
	**/
	function buildListingsShortForm(&$listings, $limit = false, $my_listings = false)
	{
		global $rlListingTypes, $config, $lang;

		$data = array();
		foreach( $listings as $key => $entry )
		{
			$thumbnail = false;
			if ( $rlListingTypes -> types[$entry['Listing_type']]['Photo'] )
			{
				if ( empty($entry['Main_photo']) || !file_exists(RL_FILES . $entry['Main_photo']) )
				{
					$thumbnail = RL_TPL_BASE .'img/no-picture.jpg';
				}
				else
				{
					$thumbnail = RL_FILES_URL . $entry['Main_photo'];
				}
			}

			$data[$key] = array(
				'id' => (int)$entry['ID'],
				'title' => $entry['listing_title'],
				'featured' => ($entry['Featured'] || $entry['Featured_expire']) ? true : false
			);

			if ( false !== $thumbnail )
			{
				$data[$key]['thumbnail'] = $thumbnail;
			}

			if ( $config['grid_photos_count'] )
			{
				$data[$key]['photosCount'] = (int)$entry['Photos_count'];
			}

			// plugins info
			if ( $GLOBALS['aHooks']['comment'] )
			{
				$data[$key]['comments'] = (int)$entry['comments_count'];
			}

			if ( $GLOBALS['aHooks']['rating'] )
			{
				$averageRating = round($entry['lr_rating'] / $entry['lr_rating_votes'], 1);
				$data[$key]['rating'] = $averageRating;
				$data[$key]['ratingRest'] = (float)(($averageRating * 12) + intVal($averageRating));
			}

			$fieldsLimit = $limit != false ? $limit : APP_SHORT_FORM_FIELDS_LIMIT;
			foreach( $listings[$key]['fields'] as $fKey => $field )
			{
				if ( $fieldsLimit === 0 ) break;
				if ( empty($field['value']) ) continue;

				$fIndex = count($data[$key]['fields']);
				if ( $config['iFlynaxConnect_display_fields_name'] )
				{
					$data[$key]['fields'][$fIndex]['name'] = $field['name'];
				}
				$data[$key]['fields'][$fIndex]['value'] = $this -> pValid($field['value']);
				$fieldsLimit--;
			}

			if ( $my_listings !== false )
			{
				$data[$key]['additional'] = array(
					'category' => "{$lang['category']}: {$lang[$entry['Cat_key']]}",
					'status' => "{$lang['status']}: {$lang[$entry['Status']]}",
				);

				if ( $entry['Plan_expire'] )
				{
					$string = $entry['Plan_expire'];
					$expire_date = mktime(substr($string, 8, 2),substr($string, 10, 2),substr($string, 12, 2), substr($string, 4, 2),substr($string, 6, 2),substr($string, 0, 4));
					$data[$key]['additional']['expire'] = "{$lang['active_till']}: ". date(str_replace(array('%', 'b'), array('', 'M'), RL_DATE_FORMAT), $expire_date);
				}

				if ( $entry['Featured_expire'] )
				{
					$string = $entry['Featured_expire'];
					$expire_date = mktime(substr($string, 8, 2),substr($string, 10, 2),substr($string, 12, 2), substr($string, 4, 2),substr($string, 6, 2),substr($string, 0, 4));
					$data[$key]['additional']['featuredExpire'] = "{$lang['featured_till']}: ". date(str_replace(array('%', 'b'), array('', 'M'), RL_DATE_FORMAT), $expire_date);
				}

				if ( $entry['Plan_key'] )
				{
					$data[$key]['additional']['plan'] = "{$lang['plan']}: {$lang[$entry['Plan_key']]}";
				}
			}
		}
		return $data;
	}

	/**
	* Adapt build Search for iPhone app
	*
	* @param array $form - flynax build form
	* @return array $adapt - adapted form
	**/
	function adaptBuildSearch($form = false)
	{
		if ( $form === false ) return array();
		global $lang;

		$adapt = array();
		$index = 0;
		$indexChange = false;

		foreach( $form as $key => $group )
		{
			if ( $group['Group_ID'] == 0 )
			{
				$index = $indexChange == true ? $index + 1 : $index;
				$indexChange = false;
				$fIndex = count($adapt[$index]['Fields']);

				$adapt[$index]['Fields'][$fIndex] = $group['Fields'][0];
			}
			else
			{
				$index++;
				$indexChange = true;

				$adapt[$index]['name'] = $lang[$group['pName']];
				$adapt[$index]['Fields'] = $group['Fields'];
			}
			$adapt[$index]['form'] = $group['Form_key'];

			// remove subcategories
			foreach( $adapt[$index]['Fields'] as $fKey => $fField )
			{
				if ( $fField['Key'] == 'Category_ID' )
				{
					foreach( $fField['Values'] as $vKey => $vValue )
					{
						if ( $vValue['Level'] != 0 )
						{
							unset($adapt[$index]['Fields'][$fKey]['Values'][$vKey]);
						}
					}
					break;
				}
			}
		}
		return $adapt;
	}

	/**
	* Adapt listing details form
	*
	* @param array $form - listing details form
	**/
	function adaptListingDetailSections(&$form)
	{
		if ( $form === false ) return false;

		foreach( $form as $sKey => $entry )
		{
			$countFields = count( $entry['Fields'] );
			$emptyFields = 0;
			foreach( $entry['Fields'] as $fKey => $field )
			{
				// remove field
				if ( empty($field['value']) )
				{
					unset($form[$sKey]['Fields'][$fKey]);
					$emptyFields++;
				}
			}

			// remove section if all fields empty
			if ( $countFields == $emptyFields )
			{
				unset($form[$sKey]);
			}
		}
	}

	/**
	* Adapt form sections
	*
	* @param array $form - form sections
	**/
	function adaptFormSections(&$form)
	{
		if ( $form === false ) return false;

		$skipFieldsMass = array('file');
		foreach( $form as $sKey => $sVal )
		{
			$countFields = count($sVal['Fields']);
			$skipFields = 0;
			foreach( $sVal['Fields'] as $fKey => $fVal )
			{
				if ( in_array($fVal['Type'], $skipFieldsMass) )
				{
					// remove field
					unset($form[$sKey]['Fields'][$fKey]);
					$skipFields++;
				}
			}

			if ( $countFields == $skipFields )
			{
				// remove section if all fields empty
				unset($form[$sKey]);
			}
		}
	}

	/**
	* Get Text Load More
	*
	* @param int $next - next page
	* @return array - array of load more
	**/
	function getTextLoadMore($next = 2)
	{
		global $config, $lang;

		$moreCount = (int)$config['iFlynaxConnect_listings_per_page'];
		$calcCount = (int)($this -> totalPages - $this -> currentPages);

		if ( $moreCount > $calcCount )
		{
			$moreCount = $calcCount;
		}

		return array(
			'name' => str_replace(array('[count]', '[total]'), array($moreCount, $calcCount), $lang['iFlynaxConnect_load_more_results']),
			'pg' => (int)$next,
			'type' => 'loadMore'
		);
	}

	/**
	* Build paging
	*
	* @param int $calc - calculated items
	* @param int $total - total items
	* @param int $per_page - per page items number
	* @return int $next_page - load next page
	**/
	function paging($aParams)
	{
		$calc = $aParams['calc'];
		$total = $aParams['total'];
		$per_page = $aParams['per_page'];
		$current = $aParams['current'] == 0 ? 1 : $aParams['current'];
		$next_page = 0;

		if ( $calc > $total )
		{
			$pages = ceil($calc / $per_page);
			$this -> totalPages = $calc;
			$this -> currentPages = $total * $current;
		}

		if ( $current < $pages )
		{
			$next_page = $current + 1;
		}

		return (int)$next_page;
	}

	/**
	* Get custom pages for iFlynax
	**/
	function getCustomPages() 
	{
		global $lang;

		$pages = array();
		$sql  = "SELECT `Key` FROM `". RL_DBPREFIX ."pages` ";
		$sql .= "WHERE `Status` = 'active' AND FIND_IN_SET('9', `Menus`) > 0 ORDER BY `Position`";
		$result = $this -> getAll($sql);

		if ( !empty($result) )
		{
			foreach( $result as $key => $page )
			{
				array_push($pages, array(
						'key' => $page['Key'],
						'name' => $lang['pages+name+'. $page['Key']]
					)
				);
			}
			unset($result);
		}
		return $pages;
	}

	/**
	* Get site language for app
	**/
	function getSiteLanguage()
	{
		global $config;

		if ( isset($_POST['language']) && !empty($_POST['language']) )
		{
			$code = $this -> getOne('Code', "`Code` = '{$_POST['language']}' AND `Status` = 'active'", 'languages');
			if ( !empty($code) )
			{
				return $code;
			}
		}
		return $config['lang'];
	}

	/**
	* Get website languages
	**/
	function getLanguagesList()
	{
		$list = array();
		$languages = $GLOBALS['rlLang'] -> getLanguagesList('active');
		foreach( $languages as $key => $language )
		{
			$list[$language['Code']] = $language['name'];
		}
		return $list;
	}

	/**
	* Get lang keys for iFlynax app
	**/
	function getLangKeysForApp()
	{
		global $config;

		$langs = array();
		$sql  = "SELECT `Key`, `Value` FROM `". RL_DBPREFIX ."lang_keys` ";
		$sql .= "WHERE `Module` = 'frontEnd' AND `Code` = '". RL_LANG_CODE ."' AND `Key` LIKE 'iflynax+%' AND `Status` = 'active'";
		$result = $this -> getAll($sql);

		if ( !empty($result) )
		{
			foreach($result as $key => $row)
			{
				if ( $row['Key'] == 'iflynax+add+planAutoApproved' )
				{
					$langs[$row['Key']] = str_replace('{day}', $config['iFlynaxConnect_keepActiveAfterAdd'], $row['Value']);
				}
				else
				{
					$langs[$row['Key']] = $row['Value'];
				}
			}
			unset($result);
		}
		return $langs;
	}

	/**
	* Login as default
	*
	* @param string $username - account username
	* @param string $password - account password
	* @param bool $direct - allow login by MD5 password
	* @return array $data
	**/
	function login($username = false, $password = false, $direct = false)
	{
		if ( !$username || !$password ) return false;
		global $rlAccount, $config;

		$data = array();
		if ( true === $res = $rlAccount -> login($username, $password, $direct) )
		{
			$data['logged'] = true;
			$data['useToken'] = $_SESSION['account']['App_token'];

			if ( empty($data['useToken']) )
			{
				$data['useToken'] = md5($this -> generateHash() . md5($config['security_key']));

				// save token for this account
				$sql  = "UPDATE `". RL_DBPREFIX ."accounts` SET `App_token` = '{$data['useToken']}' ";
				$sql .= "WHERE `ID` = '". (int)$_SESSION['id'] ."' LIMIT 1";
				$this -> query($sql);
			}
		}
		else
		{
			$data['logged'] = false;
			$data['message'] = implode("\n", $res);
		}
		return $data;
	}

	/**
	* Login with token
	*
	* @param string $token - account token
	* @return bool true/false
	**/
	function loginWithToken($token = false)
	{
		if ( !$token ) return false;
		global $rlAccount, $rlValid, $globalInfo;

		$sql  = "SELECT `Username`, `Password` FROM `". RL_DBPREFIX ."accounts` ";
		$sql .= "WHERE `Status` = 'active' AND `App_token` = '{$token}' LIMIT 1";
		$account = $this -> getRow($sql);

		if ( !empty($account) )
		{
			$rlValid -> sql($account);
			$result = $this -> login($account['Username'], $account['Password'], true);
			if ( isset($result['message']) )
			{
				$globalInfo['alertMessage'] = $result['message'];
			}
			return (bool)$result['logged'];
		}
		return false;
	}

	/**
	* Delete listing
	*
	* @param int $id - listing id
	* @return bool true/false
	**/
	function deleteListing($id = false)
	{
		global $config, $account_info, $rlActions, $rlHook, $rlListings;
		if ( !$id || !$account_info ) return false;

		$sql  = "SELECT `T1`.`ID`, `T1`.`Category_ID`, `T2`.`Type`, `T1`.`Crossed`, `T1`.`Status`, `T2`.`Type` AS `Listing_type` ";
		$sql .= "FROM `". RL_DBPREFIX ."listings` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "WHERE `T1`.`ID` = '{$id}' AND `T1`.`Account_ID` = '{$account_info['ID']}' AND `T1`.`Status` <> 'trash'";
		$info = $this -> getRow($sql);

		if ( !empty($info) )
		{
			$rlHook -> load('phpListingsAjaxDeleteListing', $id, $info); // v4.0.1

			if ( !$config['trash'] )
			{
				$rlListings -> deleteListingData($info['ID'], $info['Category_ID'], $info['Crossed'], $info['Listing_type'] );
			}
			$rlActions -> delete(array('ID' => $info['ID']), 'listings', $info['ID'], 1);

			return true;
		}
		return false;
	}

	/**
	* Get listing photos
	*
	* @param int $id - listing id
	* @param int $limit = photos limit
	* @return array $data - listing photos
	**/
	function getListingPhotos($id = false, $limit = false)
	{
		$photos = $this -> fetch(array('ID', 'Photo', 'Thumbnail'), array( 'Listing_ID' => $id, 'Status' => 'active' ), "AND `Thumbnail` <> '' AND `Photo` <> '' ORDER BY `Position`", $limit, 'listing_photos');
		$data = array();

		if ( !empty($photos) ) 
		{
			foreach( $photos as $key => $row )
			{
				array_push($data, array(
						'id' => (int)$row['ID'],
						'photo' => RL_FILES_URL . $row['Photo'],
						'thumbnail' => RL_FILES_URL . $row['Thumbnail']
					)
				);
			}
			unset($photos);
		}
		return !empty($data) ? $data : false;
	}
}