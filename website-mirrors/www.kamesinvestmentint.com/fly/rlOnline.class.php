<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLONLINE.CLASS.PHP
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

class rlOnline extends reefless
{
	/**
	* Show online statistics
	*
	**/
	function statistics()
	{
		global $config;

		$userIP = $_SERVER['REMOTE_ADDR'];
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$sessionHash = md5($userIP . $userAgent);
		$isUser = defined('IS_LOGIN') ? 1 : 0;
		
		$nowDate = mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
		$onlineDowntime = $nowDate - ($config['online_downtime']  * 60);

		// check bots
		if ( false === $this -> isBot($userAgent) )
		{
			$online = $this -> getOne('sess_id', "`sess_id` = '{$sessionHash}'", 'online');
			if ( !empty($online) )
			{
				$this -> query("UPDATE `". RL_DBPREFIX ."online` SET `last_online` = '{$nowDate}' , `visibility` = '1', `is_login` = '{$isUser}' WHERE `sess_id` = '{$sessionHash}' LIMIT 1");
			}
			else
			{
				$this -> query("INSERT INTO `". RL_DBPREFIX ."online` ( `sess_id`, `ip`, `last_online`, `visibility`, `is_login` ) VALUES ( '{$sessionHash}', '{$userIP}', '{$nowDate}', '1', '{$isUser}' )");
			}
		}
		$this -> query("UPDATE `". RL_DBPREFIX ."online` SET `visibility` = '0' WHERE `last_online` < '{$onlineDowntime}'");
	}

	/**
	* Fetch statistics from DB
	*
	* @return array - assoc data [total/users/guests/lastHour/lastDay]
	**/
	function fetchStatisticsInfo()
	{
		global $config;

		$nowDate = mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
		$onlineLastHour = $nowDate - ($config['online_last_hour'] * 3600);
		$onlineLastDay  = $nowDate - ($config['online_last_day']  * 3600);

		$sql  = "SELECT COUNT(`ID`) AS `total`, ";
		$sql .= "( SELECT COUNT(`ID`) FROM `". RL_DBPREFIX ."online` WHERE `is_login` = '1' AND `visibility` = '1' ) AS `users`, ";
		$sql .= "( SELECT COUNT(`ID`) FROM `". RL_DBPREFIX ."online` WHERE `is_login` = '0' AND `visibility` = '1' ) AS `guests`, ";
		$sql .= "( SELECT COUNT(`ID`) FROM `". RL_DBPREFIX ."online` WHERE `last_online` > '{$onlineLastHour}' ) AS `lastHour`, ";
		$sql .= "( SELECT COUNT(`ID`) FROM `". RL_DBPREFIX ."online` WHERE `last_online` > '{$onlineLastDay}' ) AS `lastDay` ";
		$sql .= "FROM `". RL_DBPREFIX ."online` WHERE `visibility` = '1'";

		return $this -> getRow($sql);
	}

	/**
	* Show online statistics on admin panel
	*
	**/
	function ajaxAdminStatistics()
	{
		global $_response;

		$statistics = $this -> fetchStatisticsInfo();
		$GLOBALS['rlSmarty'] -> assign('onlineStatistics', $statistics);
		unset($statistics);

		$tpl = RL_PLUGINS .'online'. RL_DS .'admin'. RL_DS .'statistics_dom.tpl';
		$_response -> assign("online_block_container", 'innerHTML', $GLOBALS['rlSmarty'] -> fetch($tpl, null, null, false));
		$_response -> script("$('#online_block_container').fadeIn('normal', function() { $(this).parent().removeClass('block_loading'); });");

		return $_response;
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

		// array of bots
		$bots = array( 
			"google", "bot", "radian",
			"yahoo", "spider", "crawl",
			"archiver", "curl", "yandex",
			"python", "nambu", "eventbox",
			"twitt", "perl", "monitor",
			"sphere", "PEAR", "mechanize",
			"java", "wordpress", "facebookexternal"
		);

		foreach( $bots as $bot )
		{
			if ( false !== strpos($userAgent, $bot) )
			{
				return true;
			}
		}
		return false;
	}
}