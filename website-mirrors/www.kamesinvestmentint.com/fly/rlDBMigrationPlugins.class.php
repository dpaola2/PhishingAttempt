<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLDBMIGRATIONPLUGINS.CLASS.PHP
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

class rlDBMigrationPlugins extends rlDBMigration
{
	/**
	* 
	**/
	var $allowPlugins = array('comment','rating');

	/**
	* 
	**/
	var $plugins = array();

	/**
	* 
	**/
	function rlDBMigrationPlugins()
	{
		global $config;

		if ( !$_REQUEST['xjxfun'] )
		{
			// get target plugins
			$targetPlugins = array();
			$tmpTargetPlugins = $this -> getAll("SELECT `Key` FROM `". RL_DBPREFIX ."plugins` WHERE `Status` = 'active'");
			foreach( $tmpTargetPlugins as $tKey => $tPlugin )
			{
				if ( in_array( $tPlugin['Key'], $this -> allowPlugins ) )
				{
					array_push( $targetPlugins, $tPlugin['Key'] );
				}
			}
			unset( $tmpTargetPlugins );

			if ( !empty( $targetPlugins ) )
			{
				// get source plugins
				$this -> reConnect('source');
				$sql = "SELECT `Key` FROM `{$config['dbMigration_table_prefix']}plugins` WHERE `Key` = '". implode("' OR `Key` = '", $targetPlugins) ."'";
				$sourcePlugins = $this -> getAll( $sql );
				$this -> reConnect();

				if ( !empty( $sourcePlugins ) )
				{
					foreach( $sourcePlugins as $sKey => $sPlugin )
					{
						// add module as plugin key
						$this -> addModule( $sPlugin['Key'] );
						array_push( $this -> plugins, $sPlugin['Key'] );
					}
					unset( $sourcePlugins );

					// update table view
					$this -> updateTableView();
				}
				unset( $targetPlugins );
			}
		}
	}

	/**
	* 
	**/
	function ajaxImport_comment( $start = false, $successful = false )
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('comment', 'accounts,listings') )
		{
			return $res;
		}

		global $_response, $config, $lang, $rlValid;

		$start = (int)$start;
		$successful = (int)$successful;

		$this -> reConnect('source');
		$sql = "SELECT `ID`,`User_ID`,`Listing_ID`,`Author`,`Title`,`Description`,`Rating`,`Date`,`Status` FROM `{$config['dbMigration_table_prefix']}comments` LIMIT {$start},{$this->limit}";
		$sourceComments = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourceComments ) )
		{
			$insertAllow = false;
			$updateCounts = array();
			$insert = "INSERT INTO `". RL_DBPREFIX ."comments` ( `User_ID`,`Listing_ID`,`Author`,`Title`,`Description`,`Rating`,`Date`,`Status`,`import_source_id`,`importIdentify` ) VALUES ";
			foreach( $sourceComments as $key => $entry )
			{
				$exists = $this -> getOne('ID', "`import_source_id` = '{$entry['ID']}'", 'comments');
				if ( empty( $exists ) )
				{
					$insertAllow = true;
					$accountID = (int)$this -> getOne('ID', "`import_source_id` = '{$entry['User_ID']}'", 'accounts');
					$listingID = (int)$this -> getOne('ID', "`import_source_id` = '{$entry['Listing_ID']}'", 'listings');
					$author = $rlValid -> xSql( $entry['Author'] );
					$title = $rlValid -> xSql( $entry['Title'] );
					$description = $rlValid -> xSql( $entry['Description'] );

					$insert .= "( '{$accountID}','{$listingID}','{$author}','{$title}','{$description}','{$entry['Rating']}','{$entry['Date']}','{$entry['Status']}','{$entry['ID']}','1' ),";

					// clear memory
					unset( $title, $description );

					// increase counter
					$updateCounts[$listingID] += 1;
				}
				$successful++;
			}

			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' ) .';';
				$this -> query( $insert );

				// update counters
				foreach( $updateCounts as $listingID => $count )
				{
					$this -> query("UPDATE `". RL_DBPREFIX ."listings` SET `comments_count` = '{$count}' WHERE `ID` = '{$listingID}' LIMIT 1");
				}

				// clear memory
				unset( $sourceComments, $insert, $updateCounts );
			}

			// start import as recurcion
			$start += $this -> limit;
			$_response -> script("xajax_import_comment({$start}, {$successful});");
			return $_response;
		}

		// save logs
		$this -> updateLog( 'comment', $successful, 0 );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}

	/**
	* 
	**/
	function ajaxImport_rating( $start = false, $successful = false )
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('rating', 'listings') )
		{
			return $res;
		}

		global $_response, $config, $lang, $rlValid;

		$start = (int)$start;
		$successful = (int)$successful;

		$this -> reConnect('source');
		$sql = "SELECT `ID`,`Rating`,`Rating_votes` FROM `{$config['dbMigration_table_prefix']}listings` WHERE `Rating` <> '0' AND `Rating_votes` <> '0' LIMIT {$start},{$this->limit}";
		$sourceRatings = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourceRatings ) )
		{
			foreach( $sourceRatings as $key => $entry )
			{
				$this -> query("UPDATE `". RL_DBPREFIX ."listings` SET `lr_rating` = '{$entry['Rating']}',`lr_rating_votes` = '{$entry['Rating_votes']}' WHERE `import_source_id` = '{$entry['ID']}' LIMIT 1");
				$successful++;
			}

			// clear memory
			unset( $sourceRatings );

			// start import as recurcion
			$start += $this -> limit;
			$_response -> script("xajax_import_rating({$start}, {$successful});");
			return $_response;
		}

		// save logs
		$this -> updateLog( 'rating', $successful, 0 );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}
}