<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLRSSFEED.CLASS.PHP
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

class rlRssFeed extends reefless
{
	/**
	* box content pattern
	**/
	var $box_content;

	/**
	* class constructor
	**/
	function rlRssFeed()
	{
		$this -> box_content = <<< VS
global \$rlSmarty;

\$rss_feed = <<< FL
{data}
FL;
\$rlSmarty -> assign('rss_feed', unserialize(\$rss_feed));
\$rlSmarty -> display(RL_PLUGINS .'rssfeed'. RL_DS . 'block.tpl');
VS;
	}

	/**
	* get RSS feed content
	*
	* @param int $voted - voted rss id
	*
	* @return array - rss data
	**/
	function get( $url = false, $number = 3 )
	{
		if ( !$url )
			return false;

		$this -> loadClass('Rss');

		unset($GLOBALS['rlRss'] -> items['description']);
		$GLOBALS['rlRss'] -> items_number = $number;

		$GLOBALS['rlRss'] -> createParser($this -> getPageContent($url));
		$rss_feed = $GLOBALS['rlRss'] -> getRssContent();
		$GLOBALS['rlRss'] -> clear();

		return $rss_feed;
	}

	/**
	* validate url
	*
	* @package xAjax
	*
	* @param string $url - validate rss url and build sample content
	*
	* @todo build sample content
	**/
	function ajaxValidate( $url = false )
	{
		global $_response, $lang;

		$rss_feed = $this -> get($url, 3);
		
		if ( $rss_feed ) {
			$GLOBALS['rlSmarty'] -> assign_by_ref('rss_feed', $rss_feed);

			$tpl = RL_PLUGINS .'rssfeed'. RL_DS .'admin'. RL_DS .'sample.tpl';
			$_response -> assign('feed_sample', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch($tpl, null, null, false));

			$_response -> script("$('#rssfeed_url input[type=text]').removeClass('error');");
		}
		else {
			$_response -> script("
				printMessage('error', '{$lang['rssfeed_empty_feed']}');
				$('#rssfeed_url input[type=text]').addClass('error');
				$('#feed_sample').html('');
			");
		}

		$_response -> script("$('#rssfeed_url input[type=button]').val('{$lang['rssfeed_validate']}');");

		return $_response;
	}
	
	/**
	* delete Rss
	*
	* @package xAjax
	*
	* @param int $id - rss feed id
	*
	**/
	function ajaxDeleteRss( $id = false )
	{
		global $_response, $lang;
		
		$id = (int)$id;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$_response -> redirect( RL_URL_HOME . ADMIN . '/index.php?action=session_expired' );
			return $_response;
		}
		
		if ( !$id )
		{
			return $_response;
		}
		
		// delete rss feed
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "rss_feed` WHERE `ID` = '{$id}' LIMIT 1");
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "blocks` WHERE `Key` = 'rssfeed_{$id}' LIMIT 1");
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'blocks+name+rssfeed_{$id}'");
		
		$_response -> script("
			printMessage('notice', '{$lang['item_deleted']}');
			rssFeedGrid.reload();
		");
		
		return $_response;
	}

	/**
	* update all feeds
	*
	**/
	function updateFeeds(){
		$this -> setTable('rss_feed');
		$feeds = $this -> fetch(array('ID', 'Url', 'Article_num'), array('Status' => 'active'), "AND NOW() >= DATE_ADD(`Last_update`, INTERVAL `Update_delay` HOUR) OR UNIX_TIMESTAMP(`Last_update`) IS NULL || UNIX_TIMESTAMP(`Last_update`) = 0");

		$GLOBALS['rlActions'] -> rlAllowHTML = true;

		foreach ($feeds as $item) {
			$content = $this -> get($item['Url'], $item['Article_num']);
			if ( $content ) {
				$rss_feed_key = 'rssfeed_'. $item['ID'];

				/* update related block content */
				$new_content = str_replace(
					array('{data}'),
					array(serialize($content)),
					$this -> box_content
				);

				$update_block = array(
					'fields' => array('Content' => $new_content, 'Type' => 'php'),
					'where' => array('Key' => $rss_feed_key)
				);
				
				$GLOBALS['rlActions'] -> updateOne($update_block, 'blocks');

				/* update last date check of the feed */
				$update_feed = array(
					'fields' => array('Last_update' => 'NOW()'),
					'where' => array('ID' => $item['ID'])
				);
				
				$GLOBALS['rlActions'] -> updateOne($update_feed, 'rss_feed');
			}
		}

		$this -> resetTable();
	}
}
