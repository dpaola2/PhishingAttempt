<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLWEATHERFORECAST.CLASS.PHP
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

class rlWeatherForecast extends reefless
{
	/**
	* class constructor
	*
	**/
	function rlWeatherForecast()
	{
		//
	}
	
	/**
	* parse xml
	*
	* @param text $content - xml content
	* @param array $tag - tag name
	* @param bool $close - is tag closed
	* @param text $attr - tag attribute
	*
	* @return array - tag and attr(optional) values
	**/
	function parse( $content = false, $tag = false, $close = true, $attr = false )
	{
		if ( !$tag || !$content )
		{
			return false;
		}
		
		if ( !empty($attr) )
		{
			$attributes = '.*';
			if ( is_array($attr) )
			{
				foreach ($attr as $attrs)
				{
					$attributes .= $attrs .'="(.*)".*';
				}
			}
			else
			{
				$attributes = $attr .'="(.*)".*';
			}
		}
		
		if ( $close )
		{
			$pattern = "/<{$tag}{$attributes}\/>/";
		}
		else
		{
			$pattern = "/<{$tag}{$attributes}>([0-9]*)<\/{$tag}>/";
		}
		
		preg_match_all( $pattern, $content, $matches );
		
		$index = 1;
		if ( count($matches[0]) == 1 )
		{
			if ( $attr )
			{
				foreach ($attr as $attrs)
				{
					$out[$attrs] = $matches[$index][0];
					$index++;
				}
			}
			else
			{
				$out = $matches[$index][0];
			}
		}
		else
		{
			if ( $attr )
			{
				foreach ($matches[0] as $key => $match)
				{
					foreach ($attr as $attrs)
					{
						$out[$key][$attrs] = $matches[$index][$key];
						$index++;
					}
					
					$index = 1;
				}
			}
			else
			{
				$set = strlen($matches[1][0]) == 8 ? count($matches[1])-1 : 0;
				$out = $matches[1][$set];
			}
		}
		
		return $out;
	}
	
	/**
	* save WOEID
	*
	* @package AJAX
	*
	* @param text $woeid - WOEID
	* @param text $listing_id - listing_id
	*
	**/
	function ajaxSaveWoeid( $woeid = false, $listing_id = false )
	{
		global $_response, $rlValid;

		$woeid = $rlValid -> xSql($woeid);
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		if ( $woeid && !$listing_id)
		{
			if ( defined('REALM') && REALM == 'admin' )
			{
				$sql = "UPDATE `". RL_DBPREFIX ."config` SET `Default` = '{$woeid}' WHERE `Key` = 'weatherForecast_woeid' LIMIT 1";
				$this -> query($sql);
			}
		}
		elseif ( $woeid && $listing_id )
		{
			$listing_id = (int)$listing_id;
			
			$sql = "UPDATE `". RL_DBPREFIX ."listings` SET `WOEID` = '{$woeid}' WHERE `ID` = '{$listing_id}' LIMIT 1";
			$this -> query($sql);
		}
		
		return $_response;
	}
	
	/**
	* save WOEID to session
	*
	* @package AJAX
	*
	* @param text $woeid - WOEID
	*
	**/
	function ajaxSessWoeid( $woeid = false )
	{
		global $_response;
		
		if ( $woeid )
		{
			$_SESSION['wf_woeid'] = $woeid;
		}
		
		return $_response;
	}

	/**
	* save position
	*
	* @package xajax
	*
	* @param string $position - block position
	*
	**/
	function ajaxSavePosition($position = false)
	{
		global $_response, $lang, $rlActions, $rlNotice;
		
		$update = array(
			array(
				'fields' => array(
					'Default' => $position
				),
				'where' => array(
					'Key' => 'weatherForecast_position'
				)
			)
		);
		$rlActions -> update($update, 'config');
		
		$_response -> script("
			$('#wf_save_poss').val('{$lang['save']}').attr('disabled', false);
			printMessage('notice', '{$lang['weatherForecast_area_position_saved']}');
		");
		
		return $_response;
	}

	/**
	* save fields mapping
	*
	* @package xajax
	*
	* @param string $country - country field value
	* @param string $state - state field value
	* @param string $city - city field value
	*
	**/
	function ajaxSaveMapping( $country = false, $state = false, $city = false )
	{
		global $_response, $lang, $rlActions, $rlNotice;
		
		$update = array(
			array(
				'fields' => array(
					'Default' => $country
				),
				'where' => array(
					'Key' => 'weatherForecast_mapping_country'
				)
			),
			array(
				'fields' => array(
					'Default' => $state
				),
				'where' => array(
					'Key' => 'weatherForecast_mapping_region'
				)
			),
			array(
				'fields' => array(
					'Default' => $city
				),
				'where' => array(
					'Key' => 'weatherForecast_mapping_city'
				)
			)
		);
		$rlActions -> update($update, 'config');
		
		$_response -> script("
			$('#wf_save_mapping').val('{$lang['save']}').attr('disabled', false);
			printMessage('notice', '{$lang['weatherForecast_fields_mapping_saved']}');
		");
		
		return $_response;
	}
}