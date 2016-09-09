<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLLOCATIONFINDER.CLASS.PHP
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

class rlLocationFinder extends reefless
{
	/**
	* save settings
	*
	* @package xajax
	*
	* @param string $position - block position
	* @param string $type - position type
	*
	**/
	function ajaxSave($position = false, $type = false)
	{
		global $_response, $lang, $rlActions, $rlNotice;
		
		$update = array(
			array(
				'fields' => array(
					'Default' => $position
				),
				'where' => array(
					'Key' => 'locationFinder_position'
				)
			),
			array(
				'fields' => array(
					'Default' => $type
				),
				'where' => array(
					'Key' => 'locationFinder_type'
				)
			)
		);
		$rlActions -> update($update, 'config');
		
		$_response -> script("
			printMessage('notice', '{$lang['locationFinder_settings_saved']}');
			$('#lf_button').val('{$lang['save']}').attr('disabled', false);
		");
		
		return $_response;
	}
	
	/**
	* assign location data
	**/
	function assignLocation()
	{
		global $lf_listing_id;
		
		if ( !$lf_listing_id )
			return;
		
		$this -> loadClass('Actions');	
		$data = $_POST['f']['lf'];
		
		if ( $data['use'] && $data['lat'] != '' && $data['lng'] != '' )
		{
			$update = array(
				'fields' => array(
					'Loc_latitude' => $data['lat'],
					'Loc_longitude' => $data['lng'],
					'lf_zoom' => $data['zoom'],
					'lf_use' => $data['use']
				),
				'where' => array(
					'ID' => $lf_listing_id
				)
			);
			
			$GLOBALS['rlActions'] -> updateOne($update, 'listings');
		}
		else if ( !$data['use'] )
		{
			$update = array(
				'fields' => array(
					'lf_zoom' => '',
					'lf_use' => '0'
				),
				'where' => array(
					'ID' => $_SESSION['add_listing']['listing_id']
				)
			);
			
			$GLOBALS['rlActions'] -> updateOne($update, 'listings');
		}
	}
}