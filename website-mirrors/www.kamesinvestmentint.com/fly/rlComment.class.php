<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLCOMMENT.CLASS.PHP
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

class rlComment extends reefless
{
	/**
	* include comment block in listing details
	*
	**/
	function show_tab()
	{
		$GLOBALS['rlSmarty'] -> display( RL_PLUGINS.'comment/comment.block.tpl' );
	}
	
	function getComments( $listing_id, $page = 0, $no_display = false )
	{
		$listing_id = (int)$listing_id;
		$limit = (int)$GLOBALS['config']['comments_per_page'];
		$start = $page > 1 ? ($page - 1) * $limit : 0;

		$sql ="SELECT SQL_CALC_FOUND_ROWS `T1`.*, `T2`.`Own_address` FROM `".RL_DBPREFIX."comments` AS `T1` ";
		$sql .="LEFT JOIN `".RL_DBPREFIX."accounts` AS `T2` ON `T2`.`ID` = `T1`.`User_ID` ";
		$sql .="WHERE `T1`.`Listing_ID` = ".$listing_id." AND `T1`.`Status` = 'active' ";
		$sql .="LIMIT {$start}, {$limit}";

		$comments = $this -> getAll( $sql );

		$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );
		
		foreach ($comments as $key => $comment )
		{
			$comments[$key]['Description'] = preg_replace('/(https?\:\/\/[^\s]+)/', '<a href="$1">$1</a>', $comment['Description']);
		}
		
		$GLOBALS['rlSmarty'] -> assign_by_ref('comments', $comments);
		$GLOBALS['rlSmarty'] -> assign( 'comment_calc', $calc['calc'] );
		$GLOBALS['rlSmarty'] -> assign('comment_page', $page );
		
		if ( $no_display )
			return $comments;

		$tpl = RL_PLUGINS .'comment'. RL_DS .'comment.block.tpl';
		$GLOBALS['rlSmarty'] -> display( $tpl );
	}
	
	/**
	* ajax get comments
	*
	* @package xAjax
	*
	* @param int $page - page number
	*
	**/

	function ajaxGetComments( $page )
	{
		global $_response;

		$this -> getComments($GLOBALS['listing_id'], $page, true);

		$GLOBALS['rlSmarty'] -> assign('comment_page', $page );
		$tpl = RL_PLUGINS. 'comment' .RL_DS. 'comment_dom.tpl';

		$_response -> assign('comments_dom', 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ));

		$_response -> script( '$("#comment_loading_bar").fadeOut("fast");' );

		$_response -> call('commentPaging');

		return $_response;
	}	

	/**
	* add comment 
	*
	* @package xAjax
	*
	* @param string $author - comment author
	* @param string $title - comment title
	* @param string $message - comment message
	* @param string $security_code - comment security code
	* @param int $rating - rating number
	*
	**/
	function ajaxCommentAdd( $author, $title, $message, $security_code = false, $rating = 0 )
	{
		global $_response, $page_info, $config, $listing_id, $lang, $account_info, $pages, $rlListingTypes, $rlSmarty;

		if( !$account_info['Username'] && $config['comments_login_post'])
			return $_response;

		/* check required fields */
		if ( empty($author) )
		{
			$errors[] = str_replace( '{field}', '<b>'. $lang['comment_author'] .'</b>', $lang['notice_field_empty']);
		}
		
		if ( empty($title) )
		{
			$errors[] = str_replace( '{field}', '<b>'. $lang['comment_title'] .'</b>', $lang['notice_field_empty']);
		}
		
		if ( empty($message) )
		{
			$errors[] = str_replace( '{field}', '<b>'.$lang['message'].'</b>', $lang['notice_field_empty']);
		}
		
		if ( $config['security_img_comment_captcha'] && $security_code != $_SESSION['ses_security_code_comment'] )
		{
			$errors[] = $lang['security_code_incorrect'];
		}
		
		if ( !empty($errors) )
		{
			$error_content = '<ul>';
			foreach ($errors as $error)
			{
				$error_content .= "<li>". $error ."</li>";
			}
			$error_content .= '</ul>';
			
			$_response -> script("
				printMessage('error', '{$error_content}');
				$('form[name=add_comment] input[type=submit]').val('{$lang['comment_add_comment']}');
			");
		}
		else 
		{
			$this -> loadClass('Actions');
			$this -> setTable('comments');
			
			$listing_id = (int)$listing_id;

			$account_id = intval($account_info['ID'] ? $account_info['ID'] : 0);
			$status = $config['comment_auto_approval'] ? 'active' : 'pending';
			
			$comment = array(
				'User_ID' => $account_id,
				'Listing_ID' => $listing_id,
				'Author' => $author,
				'Title' => $title,
				'Description' => $message,
				'Rating' => (int)$rating,
				'Status' => $status,
				'Date' => 'NOW()'
			);
				
			$GLOBALS['rlActions'] -> insertOne( $comment, 'comments' );

			/* increase count */
			if ( $config['comment_auto_approval'] )
			{
				$this -> query("UPDATE `". RL_DBPREFIX ."listings` SET `comments_count` = `comments_count` + 1 WHERE `ID` = '{$listing_id}' LIMIT 1");
			}
			
			if ( $config['comments_send_email_after_added_comment'] )
			{
				$this -> loadClass('Mail');
				$this -> loadClass('Listings');
				$this -> loadClass('Account');
				
				$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate('comment_email');

				$listing_info = $GLOBALS['rlListings'] -> getListing($listing_id);
				$listing_type = $rlListingTypes -> types[$listing_info['Listing_type']];
				$account_info = $GLOBALS['rlAccount'] -> getProfile((int)$listing_info['Account_ID']);
				$listing_title = $GLOBALS['rlListings'] -> getListingTitle($listing_info['Category_ID'], $listing_info, $listing_info['Listing_type']);

				$message = nl2br($message);
				
				$link = SEO_BASE;
				$link .= $config['mod_rewrite'] ? $pages[$listing_type['Page_key']] .'/'. $listing_info['Category_path'] .'/'. $rlSmarty -> str2path($listing_title) .'-'. $listing_id.'.html#comments' : '?page='. $pages[$listing_type['Page_key']] .'&amp;id='.$listing_id.'#comments';
				$link = '<a href="'.$link.'">'. $listing_title .'</a>';
				
				$mail_tpl['body'] = str_replace( array('{username}', '{author}', '{title}', '{message}', '{listing_title}'), array($account_info['Full_name'], $author, $title, $message, $link), $mail_tpl['body'] );
				$GLOBALS['rlMail'] -> send( $mail_tpl, $account_info['Mail'] );
			}

			$limit = (int)$GLOBALS['config']['comments_per_page'];
			$sql ="SELECT SQL_CALC_FOUND_ROWS `T1`.*, `T2`.`Own_address` FROM `".RL_DBPREFIX."comments` AS `T1` ";
			$sql .="LEFT JOIN `".RL_DBPREFIX."accounts` AS `T2` ON `T2`.`ID` = `T1`.`User_ID` ";
			$sql .="WHERE `T1`.`Listing_ID` = ".$listing_id." AND `T1`.`Status` = 'active' ";
			$sql .="LIMIT 0, {$limit}";

			$comments = $this -> getAll( $sql );
			
			$calc = $this -> getRow( "SELECT FOUND_ROWS() AS `calc`" );

			foreach ($comments as $key => $comment )
			{
				$comments[$key]['Description'] = preg_replace('/(https?\:\/\/[^\s]+)/', '<a href="$1">$1</a>', $comment['Description']);
			}

			$rlSmarty -> assign_by_ref('comments', $comments);
			$rlSmarty -> assign( 'comment_calc', $calc['calc'] );

			$tpl = RL_PLUGINS . "comment" . RL_DS . 'comment_dom.tpl';
			$_response -> assign('comments_dom', 'innerHTML', $rlSmarty -> fetch($tpl, null, null, false));
			
			if ( !$config['comment_auto_approval'] )
			{
				$mess = $lang['notice_comment_added_approval'];
			}
			else
			{
				$mess = $lang['notice_comment_added'];
			}
			
			$_response -> script("
				printMessage('notice', '{$mess}');
				$('#comment_title').val(''), $('#comment_message').val(''), $('#comment_security_code').val('')
				$('.comment_star').removeClass('comment_star_active');
				comment_star = false;
			");
			
			$this -> resetTable();
		}
		
		$_response ->  script("
			$('img#comment_security_img').attr('src', '".RL_LIBS_URL."kcaptcha/getImage.php?'+Math.random()+'&id=comment')
			$('form[name=add_comment] input[type=submit]').val('{$lang['comment_add_comment']}');
		");
		$_response -> call('commentPaging');

		return $_response;
	}
	
	/**
	* delete Comment by ID
	*
	* @package xAjax
	*
	* @param int $id - comment id
	*
	**/
	function ajaxDeleteComment( $id = false )
	{
		global $_response, $lang;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$_response -> redirect( RL_URL_HOME . ADMIN . '/index.php?action=session_expired' );
			return $_response;
		}
		
		$id = (int)$id;
		if ( !$id )
		{
			return $_response;
		}
		
		$listing_id = $this -> getOne('Listing_ID', "`ID` = '{$id}'", 'comments');
		
		// decrease comment count
		$this -> query("UPDATE `". RL_DBPREFIX ."listings` SET `comments_count` = `comments_count` - 1 WHERE `ID` = '{$listing_id}' LIMIT 1");
		
		// delete comment feed
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "comments` WHERE `ID` = '{$id}' LIMIT 1");
		
		$_response -> script("
			commentsGrid.reload();
			printMessage('notice', '{$lang['item_deleted']}');
		");
		
		return $_response;
	}

	/**
	* select comment into block
	*
	* @return array - last or random comments
	**/
	function selectCommentsInBlock()
	{
		global $rlListings, $rlSmarty, $rlCommon, $config, $rlListingTypes, $pages;

		$limit = $config['comments_number_comments'] ? $config['comments_number_comments'] : 5;
		
		$sql = "SELECT `T2`.*, `T1`.`Author`, `T1`.`Title`, `T1`.`Description`, `T1`.`Date`, `T3`.`Type` AS `Listing_type`, `T3`.`Path` AS `Category_path` ";
		$sql .= "FROM `". RL_DBPREFIX ."comments` AS `T1` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listings` AS `T2` ON `T1`.`Listing_ID` = `T2`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T3` ON `T2`.`Category_ID` = `T3`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T4` ON `T2`.`Plan_ID` = `T4`.`ID` ";
		$sql .= "LEFT JOIN `". RL_DBPREFIX ."accounts` AS `T5` ON `T2`.`Account_ID` = `T5`.`ID` ";
		$sql .= "WHERE `T1`.`Status` = 'active' AND `T2`.`Status` = 'active' AND `T3`.`Status` = 'active' AND `T5`.`Status` = 'active' ";
		$sql .= "AND ( UNIX_TIMESTAMP(DATE_ADD(`T2`.`Pay_date`, INTERVAL `T4`.`Listing_period` DAY)) > UNIX_TIMESTAMP(NOW()) OR `T4`.`Listing_period` = 0 )";
		if ( $config['comments_select_comments_random'] == 'Last' )
		{
			$sql .= "ORDER BY `T1`.`Date` DESC ";
		}
		else
		{
			$sql .= "ORDER BY RAND() ";
		}
		$sql .= "LIMIT {$limit}";

		$comments = $this -> getAll($sql);

		if ( !$comments )
			return false;
		
		foreach ($comments as $key => $comment)
		{
			$comments[$key]['Listing_title'] = $rlListings -> getListingTitle($comment['Category_ID'], $comment, $comment['Listing_type']);
			
			$listing_type = $rlListingTypes -> types[$comment['Listing_type']];
			$link = SEO_BASE;
			$link .= $config['mod_rewrite'] ? $pages[$listing_type['Page_key']] .'/'. $comment['Category_path'] .'/'. $rlSmarty -> str2path($comments[$key]['Listing_title']) .'-'. $comment['ID'] .'.html#comments' : '?page='. $pages[$listing_type['Page_key']] .'&amp;id='. $comment['ID'] .'#comments';
			$comments[$key]['Listing_link'] = $link;
		}
		
		return $comments;
	}
	
	/**
	* build admin panel statistics section
	**/
	function apStatistics()
	{
		global $plugin_statistics, $lang;
		
		$total = $this -> getRow("SELECT COUNT(`ID`) AS `Count` FROM `". RL_DBPREFIX ."comments`");
		$total = $total['Count'];
		
		$pending = $this -> getRow("SELECT COUNT(`ID`) AS `Count` FROM `". RL_DBPREFIX ."comments` WHERE `Status` = 'pending'");
		$pending = $pending['Count'];
		
		$link = RL_URL_HOME . ADMIN . '/index.php?controller=comment';
		
		$plugin_statistics[] = array(
			'name' => $lang['comment_tab'],
			'items' => array(
				array(
					'name' => $lang['total'],
					'link' => $link,
					'count' => $total
				),
				array(
					'name' => $lang['pending'] .' / '. $lang['new'],
					'link' => $link .'&amp;status=pending',
					'count' => $pending
				)
			)
		);
	}
}
