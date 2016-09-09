<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLFAQS.CLASS.PHP
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

class rlFAQs extends reefless
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
	* @var configurations class object
	**/
	var $rlConfig;
	/**
	* @var calculate faqs
	**/
	var $calc_faqs;
	
	function rlFAQs()
	{
		global $rlLang, $rlValid, $rlConfig;
		
		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
		$this -> rlConfig = & $rlConfig;
	}
	
	
	/**
	* get faqs
	*
	* @param int $id - faqs id
	* @param bool $page - page mode
	* @param int $pg - start position
	* @param bool $calc_fr - append SQL_CALC_FOUND_ROWS
	*
	* @return array - faqs array
	**/
	function get( $id = false, $page = false, $pg = 1, $calc_fr = false )
	{
		$id = (int)$id;
		$sql = "SELECT ";

		if ( $calc_fr === true )
		{
			$sql .= "SQL_CALC_FOUND_ROWS ";
		}

		$sql .= "`ID`, `ID` AS `Key`, `Date`, `Path` FROM `" . RL_DBPREFIX . "faqs` ";
		$sql .= "WHERE `Status` = 'active' ";

		if ( $id )
		{
			$sql .= "AND `ID` = '{$id}'";
		}
		
		$GLOBALS['rlHook'] -> load('rlFAQsGetSql', $sql); // from v4.1.0
		
		$sql .= "ORDER BY `Date` DESC ";
		
		if ( $page === 'block' )
		{
			$sql .= "LIMIT " . $GLOBALS['config']['faqs_block_in_block'];
		}
		else
		{
			$start = 0;
			if( $pg > 1 )
			{
				$start = ($pg-1)*$GLOBALS['config']['faqs_at_page'];
			}
			
			$sql .= "LIMIT {$start}," . $GLOBALS['config']['faqs_at_page'];
		}
		
		if( $id )
		{
			$faqs = $this -> getRow( $sql );
		}
		else
		{
			$faqs = $this -> getAll( $sql );
		}

		if ( $calc_fr === true )
		{
			$faqs_number = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
			$this -> calc_faqs = $faqs_number['calc'];
		}

		$faqs = $this -> rlLang -> replaceLangKeys( $faqs, 'faqs', array( 'title', 'content' ) );
		
		return $faqs;
	}
	
	/**
	* delete FAQs
	*
	* @package ajax
	*
	* @param string $id - faq ID
	*
	**/
	function ajaxDeleteFAQs( $id )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}

		if ( !$id )
			return $_response;

		$id = (int)$id;
		$lang_keys[] = array(
			'Key' => 'faqs+title+' . $id
		);
		$lang_keys[] = array(
			'Key' => 'faqs+content+' . $id
		);

		$GLOBALS['rlActions'] -> delete( array( 'ID' => $id ), array('faqs'), null, null, $id, $lang_keys );

		$del_mode = $GLOBALS['rlActions'] -> action;

		$_response -> script("
			faqsGrid.reload();
			printMessage('notice', '{$lang['faq_' . $del_mode]}');
		");

		return $_response;
	}
}
