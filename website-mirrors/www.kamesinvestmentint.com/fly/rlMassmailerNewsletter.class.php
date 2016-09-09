<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLMASSMAILERNEWSLETTER.CLASS.PHP
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

class rlMassmailerNewsletter extends reefless
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
	function rlMassmailerNewsletter()
	{
		global $rlLang, $rlValid;
		
		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
	}
	
	/**
	* save massmailer
	*
	* @package xAjax
	*
	* @param string $id - massmailer ID
	* @param string $* - form data
	*
	**/
	function ajaxMassmailerSave( $id = false, $from = false, $status = false, $subject = false, $body = false, $res_newsletter = false, $res_accounts = false, $res_contact_us = false )
	{
		global $_response, $rlActions, $lang;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );

			return $_response;
		}
		
		$update_data = array(
			'fields' => array( 
				'From' => $from,
				'Status' => $status,
				'Date' => 'NOW()',
				'Subject' => trim($subject),
				'Body' => trim($body),
				'Recipients_newsletter' => $res_newsletter,
				'Recipients_accounts' => $res_accounts,
				'Recipients_contact_us' => $res_contact_us
			),
			'where' => array( 'ID' => $id )	
		);
		
		if ( $rlActions -> updateOne( $update_data, 'massmailer' ) )
		{
			$_response -> script("massmailer.send({$id}, 0);");
		}
		else
		{
			$GLOBALS['rlDebug'] -> logger("Unable to update massmailer item, updateOne() fail");
			$_response -> script("printMessage('error', 'Unable to save massmailer entry, please contact Flynax Support.');");
		}
		
		$_response -> script("$('input#confirm').val('{$lang['massmailer_newsletter_send_and_save']}')");
		
		return $_response;
	}
	
	/**
	* subscribe / unsubscribe
	*
	* @package xAjax
	*
	* @param string $mode - mode
	* @param string $name - real name
	* @param string $email - real email
	*
	**/
	function ajaxSubscribe( $mode = 'subscribe', $name = false, $email = false )
	{
		global $_response, $pages, $lang, $config;
		
		$errors = array();
		
		$name = $this -> rlValid -> xSql($name);
		$email = $this -> rlValid -> xSql($email);
		
		$exist = $this -> fetch(array('ID', 'Mail', 'Name', 'Date'), array('Mail' => $email), null, 1, 'subscribers', 'row');
		
		if ( !$this -> rlValid -> isEmail($email) )
		{
			$errors[] = $lang['massmailer_newsletter_incorrect_email'];
		}

		if ( strlen($name) < 3  && $mode == 'subscribe' )
		{
			$errors[] = $GLOBALS['lang']['massmailer_newsletter_name_is_to_short'];
		}

		if ( empty($errors) )
		{
			if ( $mode == 'subscribe' )
			{
				if ( !empty($exist) )
				{
					$_response -> script("printMessage('error', '{$lang['massmailer_newsletter_subscribe_email_exist']}')");
				}
				else
				{
					$insert = "INSERT INTO `".RL_DBPREFIX."subscribers` (`Name`, `Mail`, `Date`) VALUES ('{$name}', '{$email}', NOW())";
					$this -> query($insert);
					
					$msg = $lang['massmailer_newsletter_subscription_completed'];
					$msg = str_replace('{sitename}', $lang['pages+title+home'], $msg);
					$_response -> script("printMessage('notice', '{$msg}')");			
					$_response -> script( "$('#newsletter_name').val('')");
					$_response -> script( "$('#newsletter_email').val('')");
				}
				$_response -> script( "$('#nl_subscribe input[type=\"button\"]').val('{$lang['massmailer_newsletter_subscribe']}')" );	
			}
			else
			{
				if ( empty($exist) )
				{
					$msg = str_replace('[email]', $email, $lang['massmailer_newsletter_subscribe_email_not_exist']);
					$_response -> script("printMessage('error', '{$msg}')");
					$_response -> script( "$('#nl_unsubscribe input[type=\"button\"]').val('{$lang['massmailer_newsletter_unsubscribe']}')" );
				}
				else
				{
					$this -> loadClass('Mail');
					
					$unsubscribe_code = '2'. md5($exist['Mail']) . md5($exist['Date']);
					
					// create unsubscribe link
					$unsubscribe_link = RL_URL_HOME;
					$unsubscribe_link .= $config['mod_rewrite'] ? $pages['massmailer_newsletter_unsubscribe'] . ".html?hash=" : "index.php?page={$pages['massmailer_newsletter_unsubscribe']}&amp;hash=" ;
					$unsubscribe_link .= $unsubscribe_code;
					$unsubscribe_link = '<a href="' . $unsubscribe_link . '">' . $unsubscribe_link . '</a>';
					
					$mail_tpl = $GLOBALS['rlMail'] -> getEmailTemplate( 'massmailer_unsubscribe' );
					$mail_tpl['body'] = str_replace( array('{username}', '{link}','{site_url}', '{sitename}'), array($exist['Name'], $unsubscribe_link, $lang['reefless_url'], $lang['pages+title+home']), $mail_tpl['body'] );
					if ($GLOBALS['rlMail'] -> send( $mail_tpl, $email ))
					{
						$msg = $lang['massmailer_newsletter_unsubscription_completed'];
						$_response -> script("printMessage('notice', '{$msg}')");
						$_response -> script( "$('#un_newsletter_email').val('')");
						$_response -> script( "$('#nl_unsubscribe input[type=\"button\"]').val('{$lang['massmailer_newsletter_unsubscribe']}')" );
					}
				}
			}
		}
		else
		{
			$error_content = '<ul>';
			foreach ($errors as $error)
			{
				$error_content .= "<li>" . $error . "</li>";
			}
			$error_content .= '</ul>';
			
			$error_fields = $error_fields ? substr($error_fields, 0, -1) : '';
			$_response -> script("printMessage('error', '{$error_content}', '{$error_fields}')");
			if ( $mode == 'subscribe' )
			{
				$_response -> script( "$('#nl_subscribe input[type=\"button\"]').val('{$GLOBALS['lang']['massmailer_newsletter_subscribe']}')" );
			}
			else
			{
				$_response -> script( "$('#nl_unsubscribe input[type=\"button\"]').val('{$GLOBALS['lang']['massmailer_newsletter_unsubscribe']}')" );	
			}
		}	

		return $_response;
	}
	
	/**
	* delete Massmailer
	*
	* @package xAjax
	*
	* @param int $mass_id - massmailer id
	*
	**/
	function ajaxDeleteMassmailerNewsletter( $mass_id = false )
	{
		global $_response, $lang;
		
		$mass_id = (int)$mass_id;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$_response -> redirect( RL_URL_HOME . ADMIN . '/index.php?action=session_expired' );
			return $_response;
		}
		
		$items = $this -> getOne('Key', "`ID` = '{$mass_id}'", 'massmailer');
		
		// delete rss feed
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "massmailer` WHERE `ID` = '{$mass_id}' LIMIT 1");
		
		// delete land's phrases
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'massmailer+name+{$items['Key']}'");
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'massmailer+desc+{$items['Key']}'");
		
		$_response -> script("
			massmailerGrid.reload();
			printMessage('notice', '{$lang['item_deleted']}');
		");
		
		return $_response;
	}
	
	/**
	* delete Newsletter
	*
	* @package xAjax
	*
	* @param int $id - newsletter id
	*
	**/
	function ajaxDeleteNewsletter( $id = false )
	{
		global $_response, $lang;
		
		$id = (int)$id;

		if ( !$id )
			return $_response;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$_response -> redirect( RL_URL_HOME . ADMIN . '/index.php?action=session_expired' );
			return $_response;
		}

		// delete newsletter account
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "subscribers` WHERE `ID` = '{$id}' LIMIT 1");
		
		$_response -> script("
			newsletterGrid.reload();
			printMessage('notice', '{$lang['item_deleted']}');
		");
		
		return $_response;
	}
}
