<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLDATAENTRIESIMPORT.CLASS.PHP
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

class rlDataEntriesImport extends reefless
{
	/**
	* tmp file locate
	**/
	var $tmpFile;

	/**
	* Default delimiters
	**/
	var $delimiters = array(
		'new_line' => "\n",
		'tab' => "\t",
		'comma' => ','
	);

	/**
	* Set delimiter
	**/
	var $delimiter;

	/**
	* Parent ID
	**/
	var $parentID;

	/**
	* Parent Key
	**/
	var $parentKey;

	/**
	* 
	**/
	var $data = array();

	/**
	* Class constructor
	**/
	function rlDataEntriesImport()
	{
		$this -> tmpFile = RL_UPLOAD .'dataEntriesImport.tmp';
	}

	/**
	* Main import function
	**/
	function import( $ext = false, $delimiter = false )
	{
		if ( method_exists('rlDataEntriesImport', "import{$ext}") )
		{
			if ( $delimiter == 'another' )
			{
				$this -> delimiter = $delimiter;
			}
			else
			{
				$this -> delimiter = $this -> delimiters[$delimiter];
			}

			$method = "import{$ext}";
			return $this -> $method();
		}
		return false;
	}

	/**
	* Import from TXT file format
	**/
	function importTXT()
	{
		global $rlValid, $rlActions;

		$file = fopen($this -> tmpFile, 'r');
		if ( $file )
		{
			$position = $this -> getRow("SELECT MAX(`position`) AS `max` FROM `". RL_DBPREFIX ."data_formats` WHERE `Parent_ID` = '{$this->parentID}'");
			$position = (int)$position['max'];

			while( $line = fgets($file) )
			{
				if ( $this -> delimiter == $this -> delimiters['new_line'] )
				{
					if ( !empty( $line ) )
					{
						$dfName = $rlValid -> xSql( $line );
						$position++;

						// add item to storage
						$this -> addItem( $this -> uniqKeyByName( $dfName ), trim( $dfName ), $position );
					}
				}
				else
				{
					$parse = explode($this -> delimiter, $line);
					foreach( $parse as $entry )
					{
						if ( !empty( $entry ) )
						{
							// validate name
							$dfName = $rlValid -> xSql( $entry );
							$position++;

							// add item to storage
							$this -> addItem( $this -> uniqKeyByName( $dfName ), trim( $dfName ), $position );
						}
					}
				}
			}
			fclose($file);

			return $this -> save();
		}
		return false;
	}

	/**
	* Import from CSV file format
	**/
	function importCSV()
	{
		global $rlValid;

		require_once( RL_PLUGINS .'dataEntriesImport'. RL_DS .'lib'. RL_DS .'parsecsv.lib.php' );

		$csv = new parseCSV();
		$csv -> delimiter = $this -> delimiter;
		$csv -> parse($this -> tmpFile);

		if ( !empty( $csv -> data ) )
		{
			$position = $this -> getRow("SELECT MAX(`position`) AS `max` FROM `". RL_DBPREFIX ."data_formats` WHERE `Parent_ID` = '{$this->parentID}'");
			$position = (int)$position['max'];

			foreach( $csv -> data as $key => $entry )
			{
				foreach( $entry as $eKey => $item )
				{
					if ( !empty( $item ) )
					{
						$dfName = $rlValid -> xSql( $item );
						$position++;

						// add item to storage
						$this -> addItem( $this -> uniqKeyByName( $dfName ), trim( $dfName ), $position );
					}
				}
			}
			return $this -> save();
		}
		return false;
	}

	/**
	* Import from XLS file format
	**/
	function importXLS()
	{
		global $rlValid;

		require_once( RL_PLUGINS .'dataEntriesImport'. RL_DS .'lib'. RL_DS .'reader.php' );

		$xlsData = new Spreadsheet_Excel_Reader();
		$xlsData -> setOutputEncoding('UTF-8');
		$xlsData -> read($this -> tmpFile);

		$position = $this -> getRow("SELECT MAX(`position`) AS `max` FROM `". RL_DBPREFIX ."data_formats` WHERE `Parent_ID` = '{$this->parentID}'");
		$position = (int)$position['max'];

		for ( $i = 1; $i <= $xlsData -> sheets[0]['numRows']; $i++ )
		{
			for ( $j = 1; $j <= $xlsData -> sheets[0]['numCols']; $j++ )
			{
				if ( !empty( $xlsData -> sheets[0]['cells'][$i][$j] ) )
				{
					$dfName = $rlValid -> xSql( $xlsData -> sheets[0]['cells'][$i][$j] );
					$position++;

					// add item to storage
					$this -> addItem( $this -> uniqKeyByName( $dfName ), trim( $dfName ), $position );
				}
			}
		}

		return $this -> save();
	}

	/**
	* Unique key by name
	*
	* recurcive method
	**/
	function uniqKeyByName( $name = false )
	{
		global $rlValid;

		// load the utf8 lib
		if ( false === function_exists('utf8_is_ascii') )
		{
			loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
		}

		if ( !utf8_is_ascii( $name ) )
		{
			$name = utf8_to_ascii( $name );
		}
		$name = $rlValid -> str2key( $name );

		// check on exists key
		$exists = $this -> getRow("SELECT COUNT(`Key`) AS `count` FROM `". RL_DBPREFIX ."data_formats` WHERE `Key` REGEXP '^{$name}(_[0-9]+)*$'");
		if ( $exists['count'] > 0 )
		{
			return "{$name}_". (int)($exists['count'] + 1);
		}
		return $name;
	}

	/**
	* Add item to tmp storage
	**/
	function addItem( $dfKey = false, $dfName = false, $dfPosition = false )
	{
		$dfKey = $this -> parentKey .'_'. $dfKey;

		$this -> data['formats'][] = array(
			'Key' => $dfKey,
			'Parent_ID' => $this -> parentID,
			'Position' => $dfPosition
		);

		foreach( $GLOBALS['languages'] as $key => $language )
		{
			$this -> data['lang_keys'][] = array(
				'Key' => "data_formats+name+{$dfKey}",
				'Value' => $dfName,
				'Code' => $language['Code'],
				'Module' => 'common'
			);
		}
	}

	/**
	* Save to database
	**/
	function save()
	{
		global $rlActions;

		if ( $rlActions -> insert($this -> data['formats'], 'data_formats') )
		{
			$affectedRows = mysql_affected_rows();
			$res = $rlActions -> insert($this -> data['lang_keys'], 'lang_keys');

			return $affectedRows;
		}
		return false;
	}

	/**
	 * 
	 */
	 function ajaxGetDFLevel($parent = false, $df_level = false)
	 {
		 global $_response, $rlLang;

		$parent = (int)$parent;

		$sql = "SELECT `ID`, `Key` FROM `". RL_DBPREFIX ."data_formats` WHERE `Parent_ID` = '{$parent}'";
		$df = $this -> getAll($sql);
		$df = $rlLang -> replaceLangKeys($df, 'data_formats', 'name');

		if ( !empty($df) )
		{
			$this -> loadClass('Json');
			$_response -> script("tmp_df_list[{$parent}] = ". $GLOBALS['rlJson'] -> encode($df) .";");
			$_response -> script("dfLevelHandler({$df_level});");
			unset($df);
		}
		else
		{
			$_response -> script("clearDfLevels({$df_level});");
		}

		return $_response;
	 }
}