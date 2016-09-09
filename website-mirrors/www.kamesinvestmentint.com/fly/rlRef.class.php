<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLREF.CLASS.PHP
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

class rlRef extends reefless
{
	/**
	* @var language class object
	**/
	var $rlLang;
	
	/**
	* @var validator class object
	**/
	var $rlValid;
	
	/**
	* class constructor
	**/
	function rlRef()
	{
		global $rlLang, $rlValid;
		
		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
	}
	
	/**
	 * generate unique reference number
	 *
	 * @param $listing_id - Listing ID
	 * @return $ref - unique ref number ex: RF4361870;
	 * 
	 **/

	function generate( $listing_id = false, $ref_tpl = 'RF******' )
	{
		$rlength = substr_count($ref_tpl, '*');
		
		$rand = substr( mt_rand(), 0, $rlength);
		$ref = str_replace(str_repeat('*', $rlength), $rand, $ref_tpl);
		$ref = str_replace('#ID#', $listing_id, $ref);

		if( $this -> getOne("ID", "`ref_number` = '{$ref}' AND `ID` != '".$listing_id."'", 'listings') )
		{
			return $this -> generate( $listing_id, $ref_tpl );
		}
		else
		{
			return $ref;
		}
	}
	
	/**
	 * search for reference number
	 *
	 * @param $ref - reference number
	 * 
	 **/
	function ajaxRefSearch($ref)
	{
		global $_response, $lang;

		$ref = $GLOBALS['rlValid'] -> xSql($ref);

	
		/* get listing info */
		$sql = "SELECT `T1`.*, `T2`.`Path`, `T2`.`Key` AS `Cat_key`, `T3`.`Image`, `T2`.`Type` AS `Cat_type` ";
		$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
		$sql .= "WHERE UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T3`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW() OR `T3`.`Listing_period` = 0) ";
		$sql .= "AND `T1`.`ref_number` = '{$ref}' AND `T1`.`Status` = 'active' LIMIT 1";
			
		$listing_data = $GLOBALS['rlDb'] -> getRow( $sql );

		$GLOBALS['reefless'] -> loadClass('Listings');

		$listing_title = $GLOBALS['rlListings'] -> getListingTitle( $listing_data['Category_ID'], $listing_data, $listing_data['Cat_type'] );

		$listing = $GLOBALS['rlDb'] -> fetch('*', array('ref_number' => $ref), null, null, 'listings', 'row');
			
		if( $listing_data )
		{
			$page_path = $GLOBALS['pages'][ $GLOBALS['rlListingTypes'] -> types[$listing_data['Cat_type']]['Page_key'] ];
			$link = $GLOBALS['config']['mod_rewrite'] ? SEO_BASE . $page_path .'/'. $listing_data['Path'] .'/'. $GLOBALS['rlSmarty'] -> str2path( array('string' => $listing_title) ) .'-'. $listing_data['ID'] .'.html' : RL_URL_HOME .'index.php?page='. $page_path .'&amp;id='. $listing_data['ID'] ;

			$_response -> redirect($link);
		}
		else
		{	
			$_response ->script("
				$('form[name=refnumber_lookup] input[type=submit]').val('{$lang['search']}');
				printMessage('error', '{$lang['ref_not_found']}');
			");
		}
		return $_response;
	}

	function ajaxRebuildRefs( $self, $start )
	{
		global $_response, $lang;

		$GLOBALS['reefless'] -> loadClass('Ref', null, 'ref');

		$start = $start ? $start : 0;
		$limit = 1000;

		$listings = $GLOBALS['rlDb'] -> fetch( array('ID'), NULL, NULL, array($start, $limit), 'listings');

		foreach( $listings as $key => $listing )
		{
			$rn = $GLOBALS['rlRef'] -> generate($listing['ID'], $GLOBALS['config']['ref_tpl']);
			$sql = "UPDATE `".RL_DBPREFIX."listings` SET `ref_number` = '".$rn."' WHERE `ID` = '".$listing['ID']."'";
			$GLOBALS['rlDb'] -> query($sql);
		}

		if( count($listings) == $limit )
		{
			$next_limit = $start+$limit;
			$_response -> script("xajax_rebuildRefs('{$self}','{$next_limit}');");
			return $_response;
		}

		$_response -> script( "printMessage('notice', '{$lang['ref_rebuilt']}')" );
		$_response -> script( "$('{$self}').val('{$lang['rebuild']}');" );

		return $_response;
	}
}
