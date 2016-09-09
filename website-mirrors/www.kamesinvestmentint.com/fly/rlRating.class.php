<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLRATING.CLASS.PHP
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

class rlRating extends reefless
{
	/**
	* set rating
	*
	* @package xAjax
	*
	* @param int $id - listing id
	* @param int $stars - stars rating
	*
	**/
	function ajaxRate( $id = false, $stars = false )
	{
		global $_response, $lang, $config, $rlSmarty;

		$id = (int)$id;
		$stars = (int)$stars;
		
		if ( empty($id) || empty($stars) || ($config['rating_prevent_visitor'] && !defined('IS_LOGIN') ) )
		{
			return $_response;
		}
		
		$hours = date("G");
		$minutes = date("i");
		$seconds = date("s");
		$today_period = ($hours * 3600) + ($minutes * 60) + $seconds;

		$voted = explode(',', $_COOKIE['rating']);

		if ( !in_array( $id, $voted ) )
		{
			$this -> query("UPDATE `" .RL_DBPREFIX . "listings` SET `lr_rating_votes` = `lr_rating_votes` + 1, `lr_rating` = `lr_rating` + {$stars}  WHERE `ID` = '{$id}' LIMIT 1");
			
			/* save vote in cookie */
			$voted[] = $id;
			$value = implode(',', $voted);
			$expire_time = time()+(86400 - $today_period);

			setcookie( 'rating', $value, $expire_time, '/' );
			
			$_response -> script("printMessage('notice', '{$lang['rating_vote_accepted']}');");
	
			$listing_info = $this -> fetch(array('lr_rating_votes', 'lr_rating'), array('ID' => $id), null, 1, 'listings', 'row');
	
			$rlSmarty -> assign_by_ref('listing_data', $listing_info);
			$rlSmarty -> assign('rating_denied', 'true');
			
			$tpl = RL_PLUGINS . 'rating' . RL_DS . 'dom.tpl';
			$_response -> assign('listing_rating_dom', 'innerHTML', $rlSmarty -> fetch($tpl, null, null, false));
		}
		
		return $_response;
	}
}