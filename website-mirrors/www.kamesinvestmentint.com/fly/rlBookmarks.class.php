<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLBOOKMARKS.CLASS.PHP
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

class rlBookmarks extends reefless
{
	/**
	* @var services - addone services list
	**/
	var $services = array(
		'google_plusone' => 'Google Plus',
		'facebook_like' => 'Facebook Like',
		'tweet' => 'Tweet',
		'counter' => 'AddThis Counter',
		
		'compact' => 'AddThis Button',
		'facebook' => 'Facebook',
		'twitter' => 'Twitter',
		'email' => 'Email',
		'print' => 'Print',
		'google' => 'Google',
		'myspace' => 'MySpace',
		'stumbleupon' => 'StumbleUpon',
		'favorites' => 'Favorites',
		'live' => 'Messenger',
		'delicious' => 'Delicious',
		'digg' => 'Digg',
		'orkut' => 'orkut',
		'blogger' => 'Blogger',
		'gmail' => 'Gmail',
		'yahoomail' => 'Y! Mail',
		'reddit' => 'Reddit',
		'vk' => 'vk.com',
		'aim' => 'Aol Lifestream',
		'meneame' => 'Meneame',
		'mailto' => 'Email App',
		'googlebuzz' => 'Google Buzz',
		'hotmail' => 'Hotmail',
		'linkedin' => 'LinkedIn',
		'yahoobkm' => 'Y! Buzz',
		'viadeo' => 'Viadeo',
		'aolmail' => 'AOL Mail',
		'friendfeed' => 'FriendFeed',
		'tumblr' => 'tumblr',
		'friendster' => 'Friendster',
		'baidu' => 'Baidu',
		'wordpress' => 'Wordpress',
		'yahoobkm' => 'Y! Bookmarks',
		'100zakladok' => '100zakladok',
		'misterwong_de' => 'Mister Wong DE',
		'hyves' => 'Hyves',
		'sonico' => 'Sonico',
		'amazonwishlist' => 'Amazon',
		'bebo' => 'Bebo',
		'bitly' => 'Bit.ly',
		'addio' => 'Add.io',
		'bobrdobr' => 'Bobrdobr',
		'adifni' => 'Adifni',
		'dotnetshoutout' => 'DotNetShoutout',
		'2tag' => '2 Tag',
		'googlereader' => 'Google Reader',
		'studivz' => 'studiVZ',
		'fark' => 'Fark',
		'livejournal' => 'LiveJournal',
		'allmyfaves' => 'All My Faves',
		'oyyla' => 'Oyyla'
	);
	
	/**
	* @var bookmarks - bookmark types
	**/
	var $bookmarks = array(
		'googleplus_like_tweet' => array(
			'Key' => 'googleplus_like_tweet',
			'Name' => 'bsh_googleplus_like_tweet',
			'Align' => true,
			'Services' => array(
				'counter',
				'google_plusone',
				'facebook_like',
				'tweet'
			)
		),
		'floating_bar' => array(
			'Key' => 'floating_bar',
			'Name' => 'bsh_floating_bar',
			'Align' => true,
			'Services' => true,
			'Color' => true
		),
		'vertical_share_counter' => array(
			'Key' => 'vertical_share_counter',
			'Name' => 'bsh_vertical_share_counter',
			'Align' => true
		),
		'horizontal_share_counter' => array(
			'Key' => 'horizontal_share_counter',
			'Name' => 'bsh_horizontal_share_counter',
			'Align' => true
		),
		'tweet_like_share' => array(
			'Key' => 'tweet_like_share',
			'Name' => 'bsh_tweet_like_share',
			'Align' => true
		),
		'toolbox_facebook_like' => array(
			'Key' => 'toolbox_facebook_like',
			'Name' => 'bsh_toolbox_facebook_like',
			'Align' => true,
			'Services' => true
		),
		'32x32_icons_addthis' => array(
			'Key' => '32x32_icons_addthis',
			'Name' => 'bsh_32x32_icons_addthis',
			'Align' => true,
			'Services' => true
		),
		'64x64_icons_aquaticus' => array(
			'Key' => '64x64_icons_aquaticus',
			'Name' => 'bsh_64x64_icons_aquaticus',
			'Align' => true,
			'Services' => array(
				'compact',
				'facebook',
				'twitter',
				'myspace',
				'stumbleupon',
				'delicious',
				'reddit'
			)
		),
		'css3_share_buttons' => array(
			'Key' => 'css3_share_buttons',
			'Name' => 'bsh_css3_share_buttons',
			'Align' => true,
			'Color' => true
		),
		'32x32_vertical_icons' => array(
			'Key' => '32x32_vertical_icons',
			'Name' => 'bsh_32x32_vertical_icons',
			'Align' => true,
			'Services' => true
		),
		'share_button' => array(
			'Key' => 'share_button',
			'Name' => 'bsh_share_button',
			'Align' => true
		),
		'vertical_layout_menu' => array(
			'Key' => 'vertical_layout_menu',
			'Name' => 'bsh_vertical_layout_menu',
			'Align' => true,
			'Color' => true,
			'Services' => true
		)/*,
		'wibiya_bar' => array(
			'Key' => 'wibiya_bar',
			'Name' => 'bsh_wibiya_bar'
		)*/
	);
	
	/**
	* delete bookmark block
	*
	* @package ajax
	*
	* @param string $ID - bookmark block ID
	*
	**/
	function ajaxDeleteBookmark( $id )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		$id = (int)$id;
		
		$key = $this -> getOne('Key', "`ID` = {$id}", 'bookmarks');
		
		/* remove bookmark entry */
		$sql = "DELETE FROM `". RL_DBPREFIX ."bookmarks` WHERE `Key` = '{$key}' LIMIT 1";
		$this -> query($sql);
		
		/* remove block entry */
		$sql = "DELETE FROM `". RL_DBPREFIX ."blocks` WHERE `Key` = 'bookmark_{$key}' LIMIT 1";
		$this -> query($sql);
		
		/* remove block entry */
		$sql = "DELETE FROM `". RL_DBPREFIX ."lang_keys` WHERE `Key` = 'blocks+name+bookmark_{$key}'";
		$this -> query($sql);
		
		$_response -> script("
			bookmarkGrid.reload();
			printMessage('notice', '{$lang['block_deleted']}');
		");

		return $_response;
	}

	/**
	 * hook afterListingDone
	 */
	function hookAfterListingDone() {
		global $listing_id, $listing_title, $listing_data, $update_status, $pages, $category, $listing_type;

		if ( $update_status['fields']['Status'] == 'active' )
		{
			$thumbnail = RL_FILES . $listing_data['Main_photo'];
			$link = RL_URL_HOME;

			if ($GLOBALS['config']['mod_rewrite']) {
				$link .= $pages[$listing_type['Page_key']] .'/'. $category['Path'] .'/'. $GLOBALS['rlSmarty']->str2path($listing_title) .'-'. $listing_id .'.html';
			}
			else {
				$link .= 'index.php?page='. $pages[$listing_type['Page_key']] .'&id='. $listing_id;
			}

			$status = $this -> post2twitter($listing_title, $link, $thumbnail);
		}
	}

	/**
	 * Post to Twitter Timeline
	 *
	 * @param string $message
	 * @param string $link
	 * @param string $image
	 */
	function post2twitter($message = false, $link = false, $image = false) {
		global $config;

		if ( empty($config['bookmarks_twitter_api_key']) || 
			 empty($config['bookmarks_twitter_api_secret']) || 
			 empty($config['bookmarks_twitter_token']) || 
			 empty($config['bookmarks_twitter_token_secret'])
		) {
			return false;
		}

		if ( empty($message) ) {
			$GLOBALS['rlDebug'] -> logger('post2twitter: message is empty');
			return false;
		}

		//
		$tmhOAuth_lib = RL_PLUGINS .'bookmarks'. RL_DS .'libs'. RL_DS .'tmhOAuth.php';
		if ( !file_exists($tmhOAuth_lib) ) {
			$GLOBALS['rlDebug'] -> logger('post2twitter: tmhOAuth lib does\'t exists.');
			return false;
		}

		require_once($tmhOAuth_lib);

		//
		$tmhOAuth = new tmhOAuth(array(
		    'consumer_key'    => $config['bookmarks_twitter_api_key'],
		    'consumer_secret' => $config['bookmarks_twitter_api_secret'],
		    'token'           => $config['bookmarks_twitter_token'],
		    'secret'          => $config['bookmarks_twitter_token_secret']
		));

		//
		$command['status'] = "{$message}\n{$link}";

		//
		if ( $image !== false && file_exists($image) ) {
			$command['media[]'] = file_get_contents($image);
			$command_url = $tmhOAuth -> url('1.1/statuses/update_with_media');
		}
		else {
			$command_url = $tmhOAuth -> url('1.1/statuses/update');
		}

		//
		$code = $tmhOAuth -> request('POST', $command_url, $command, true, true);

		//
		if ( $code !== 200 ) {
			$GLOBALS['rlDebug'] -> logger('post2twitter: post to twitter failed! error_code: '. $code);
			return false;
		}
		return true;
	}
}