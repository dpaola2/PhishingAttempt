<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLIMPORTEXPORTCATEGORIES.CLASS.PHP
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

class rlImportExportCategories extends reefless
{
	/**
	* 
	*/
	var $tmp_file;

	/**
	* 
	*/
	var $fields_map = array(
		0 => 'type',
		1 => 'name',
		2 => 'parent',
		3 => 'path',
		4 => 'level',
		5 => 'locked'
	);

	/**
	* 
	*/
	var $calc_rows = 0;

	/**
	* 
	*/
	var $last_import_count = 0;

	/**
	* Class constructor
	*/
	function rlImportExportCategories()
	{
		$this -> tmp_file = RL_TMP .'upload'. RL_DS .'impotr_export_categories.tmp';
	}

	/**
	* Get categories list
	*/
	function getCategories($start, $stop, $import = false)
	{
		require_once(RL_PLUGINS .'importExportCategories'. RL_DS .'admin'. RL_DS .'lib'. RL_DS .'reader.php');

		$xml = new Spreadsheet_Excel_Reader();
		$xml -> setOutputEncoding('UTF-8');

		$categories = array();
		if ( false !== $xml -> read($this->tmp_file) )
		{
			$this -> calc_rows = (int)$xml -> sheets[0]['numRows'];
			$ltype_key = $_SESSION['imex_plugin']['listing_type'];

			$index = 0;
			for ($row = $start; $row < $stop; $row++) {
				if ( true !== $import ) {
					$categories[$index][$this -> fields_map[0]] = $GLOBALS['lang']['listing_types+name+'. $ltype_key]; // listing type
				}

				for ($col = 1; $col <= $xml -> sheets[0]['numCols']; $col++) {
					$value = trim($xml -> sheets[0]['cells'][$row][$col]);
					if ( !empty($value) ) {
						if ( $this -> fields_map[$col] == 'path' ) {
							$value = $this -> str2path($value, true);
						}
						$categories[$index][$this -> fields_map[$col]] = $value;
					}
				}

				// deep checking ;)
				if ( empty($categories[$index][$this -> fields_map[1]]) ) // category_name
				{
					unset($categories[$index]);
					continue;
				}
				$index++;
			}
		}
		unset($xml);

		return $categories;
	}

	/**
	 * str2path
	 */
	function str2path($str, $keep_slashes = false)
	{
		$rx = $keep_slashes ? '\/' : '';
		$str = preg_replace("/[^a-z0-9{$rx}\.]+/i", '-', $str);
		$str = preg_replace('/\-+/', '-', $str);
		$str = strtolower( $str );
		$str = trim($str, '-');
		$str = trim($str, '/');
		$str = trim($str);

		return empty($str) ? false : $str;
	}

	/**
	* Import categories
	**/
	function import($categories = false, $ltype = false) {
		global $lang, $config, $rlValid, $rlCache, $rlCategories;

		if ( !empty( $categories ) && $ltype )
		{
			$addedCategoryCount = 0;
			$langs_add = false;
			$maxPos = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `". RL_DBPREFIX ."categories`");
			$maxPos = (int)$maxPos['max'];

			foreach( $categories as $iKey => $iCat )
			{
				$maxPos++;
				$path = trim(str_replace('//', '/', $iCat['path']), '/');
				$path = $this -> str2path($path, true);

				$parentInfo = $this -> getParentInfo($path);
				$parent_id = (int)$parentInfo['ID'];
				$original_key = strtolower($rlValid -> str2key($iCat['name']));
				$c_key = trim(join('_', array($parentInfo['Key'], $original_key)), '_');
				$c_name = $rlValid -> xSql($iCat['name']);
				$cTree = $parent_id ? "{$parentInfo['Tree']}.{$maxPos}": $maxPos;

				// prepare data
				$categoryInfo = array(
					'Key' => $c_key,
					'Parent_ID' => $parent_id,
					'Position' => $maxPos,
					'Path' => $path,
					'Type' => $ltype,
					'Lock' => (int)$iCat['locked'],
					'Modified' => date('Y-m-d h:i:s'),
					'Status' => 'active',
					'Level' => (int)$iCat['level'],
					'Tree' => $cTree
				);

				// get category parents id
				if ($rlCategories && method_exists($rlCategories, 'getParentIDs')) {
					$parent_ids = $rlCategories->getParentIDs($parent_id, array($parent_id));
					$categoryInfo['Parent_IDs'] = implode(',', $parent_ids);
				}

				// make sql query
				$sql  = "INSERT INTO `". RL_DBPREFIX ."categories` ( `". implode('`,`', array_keys($categoryInfo)) ."` ) VALUES ";
				$sql .= "( '". implode("','", array_values($categoryInfo)) ."' )";

				if ( $this -> query($sql) )
				{
					$addedCategoryCount++;
					foreach( $GLOBALS['languages'] as $lkey => $lvalue )
					{
						$lang_keys[] = array(
							'Code' => $lvalue['Code'],
							'Module' => 'common',
							'Status' => 'active',
							'Key' => 'categories+name+'. $c_key,
							'Value' => $c_name,
						);
					}
				}
			}

			// save phrases
			if ( !empty( $lang_keys ) )
			{
				$this -> loadClass('Actions');
				$GLOBALS['rlActions'] -> insert($lang_keys, 'lang_keys');

				$this -> last_import_count = $addedCategoryCount;
				$_SESSION['imex_plugin']['ic_count'] = intval($_SESSION['imex_plugin']['ic_count']) + $addedCategoryCount;

				return true;
			}
		}
		return false;
	}

	/**
	* Get parent info by children path
	*
	* @param string $path - children path
	*/
	function getParentInfo($path = false)
	{
		if ( empty($path) )
			return false;

		$path = explode('/', $path);
		array_pop($path);
		$parentPath = implode('/', $path);

		$sql  = "SELECT `ID`, `Key`, `Position`, `Tree` FROM `". RL_DBPREFIX ."categories` ";
		$sql .= "WHERE `Path` = '{$parentPath}' AND `Status` = 'active' LIMIT 1";
		$info = $this -> getRow($sql);

		return $info;
	}

	/**
	* Export categories
	**/
	function export()
	{
		global $lang;

		if ( isset($_POST['cat_sticky']) )
		{
			$categoriesExport = array();
			$tmpCategories = $this -> getAll("SELECT `ID` FROM `". RL_DBPREFIX ."categories`");
			foreach( $tmpCategories as $key => $entry )
			{
				array_push($categoriesExport, $entry['ID']);
			}
			unset($tmpCategories);
		}
		else
		{
			$categoriesExport = $_POST['categories'];
		}

		// send headers
		$file_name = 'categories_'. date('Y_m_d') .'.xls';
		header('Content-type: application/ms-excel');
		header("Content-Disposition: attachment; filename={$file_name}");
		header("Pragma: no-cache");
		header("Expires: 0");

		if ( !empty( $categoriesExport ) )
		{
			$categories = '';
			foreach( $categoriesExport as $key => $id )
			{
				$sql = "SELECT `Key`, `Parent_ID`, `Path`, `Lock`, `Level` FROM `". RL_DBPREFIX ."categories` WHERE `ID` = '{$id}'";
				$catInfo = $this -> getRow($sql);

				if ( $catInfo['Parent_ID'] != 0 )
				{
					$parent_key = $this -> getOne('Key', "`ID` = '{$catInfo['Parent_ID']}'", 'categories');
					$catInfo['parent'] = $lang['categories+name+'. $parent_key];
				}
				else
				{
					$catInfo['parent'] = '';
				}
				unset($catInfo['Parent_ID']);

				$catInfo['name'] = $lang['categories+name+'. $catInfo['Key']];
				$categories .= "{$catInfo['name']}\t{$catInfo['parent']}\t{$catInfo['Path']}\t{$catInfo['Level']}\t{$catInfo['Lock']}\n";
			}

			echo $categories;
			unset($categories);
			exit;
		}
		else
		{
			echo $lang['importExportCategories_empty'];
			exit;
		}
	}
}