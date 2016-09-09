<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLANDROIDLANG.CLASS.PHP
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

class rlAndroidLang extends reefless {

	/**
	* @var validator class object
	**/
	var $rlValid;
	
	/**
	* @var actions class object
	**/
	var $rlActions;
	
	/**
	* @var configurations class object
	**/
	var $rlConfig;
	
	/**
	* class constructor
	**/
	function rlAndroidLang()
	{
		global $rlValid, $rlActions, $rlConfig;
		
		$this -> rlValid  = & $rlValid;
		$this -> rlActions = & $rlActions;
		$this -> rlConfig = & $rlConfig;
	}
	
	/**
	* add new language (copy from exist)
	*
	* @package ajax
	*
	* @param array $data - new language data
	*
	**/
	function ajax_addLanguage( $data )
	{
		global $_response;
		
		$this -> isSessActive();
		
		loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
		$lang_name = $lang_key = $this -> rlValid -> xSql(str_replace(array('"', "'"), array('', ''), $data[0][1]));
		
		if ( empty($lang_name) )
		{
			$error[] = $GLOBALS['lang']['name_field_empty'];
		}
		
		if ( !utf8_is_ascii( $lang_name ) )
		{
			$lang_key = utf8_to_ascii( $lang_name );
		}
		
		$lang_key = strtolower( str_replace( array( '"', "'" ), array( '', '' ), $lang_key ) );
		
		$iso_code = $this -> rlValid -> xSql( $data[1][1] );
		
		if ( !utf8_is_ascii( $iso_code ) )
		{
			$error = $GLOBALS['lang']['iso_code_incorrect_charset'];
		}
		else 
		{
			if ( strlen( $iso_code )!= 2 )
			{
				$error[] = $GLOBALS['lang']['iso_code_incorrect_number'];
			}
			
			//check language exist
			$lang_exist = $this -> fetch( '*', array( 'Code' => $iso_code ), null, null, 'android_languages' );

			if ( !empty( $lang_exist ) )
			{
				$error[] = $GLOBALS['lang']['iso_code_incorrect_exist'];
			}
		}

		/* check direction */
		$direction = $data[4][1];
		
		if ( !in_array($direction, array('rtl', 'ltr')) )
		{
			$error[] = $GLOBALS['lang']['text_direction_fail'];
		}
		
		/* check date format */
		$date_format = $this -> rlValid -> xSql( $data[2][1] );
		
		if ( empty($date_format) || strlen($date_format) < 5 )
		{
			$error[] = $GLOBALS['lang']['language_incorrect_date_format'];
		}
		
		if ( !empty($error) )
		{
			/* print errors */
			$error_content = '<ul>';
			foreach ($error as $err)
			{
				$error_content .= "<li>{$err}</li>";
			}
			$error_content .= '</ul>';
			$_response -> script( 'printMessage("error", "'. $error_content .'")' );
		}
		else 
		{
			/* get & optimize new language phrases*/
			$source_code = $this -> rlValid -> xSql( $data[3][1] );
			$this -> setTable('android_phrases');
			
			$copy_from_key = $this -> getOne('Key', "`Code` = '{$source_code}'", 'android_languages');
			$source_phrases = $this -> fetch('*', array('Code' => $source_code), "AND `Key` <> 'android_{$copy_from_key}'");
			
			if ( !empty($source_phrases) )
			{
				$step = 1;
				
				foreach ( $source_phrases as $item => $row )
				{
					$insert_phrases[$item] = $source_phrases[$item];
					$insert_phrases[$item]['Code'] = $iso_code;
				
					unset($insert_phrases[$item]['ID']);
						
					if ($step % 500 == 0)
					{
						$this -> rlActions -> insert($insert_phrases, 'android_phrases');
						unset($insert_phrases);
						$step = 1;
					}
					else
					{
						$step++;
					}
				}
				
				if ( !empty($insert_phrases) )
				{
					$this -> rlActions -> insert($insert_phrases, 'android_phrases');
				}
	
				$additional_row = array(
					'Code' => $iso_code,
					'Key'  => 'android_' . $lang_key,
					'Value' => $lang_name
				);
				
				$this -> rlActions -> insertOne($additional_row, 'android_phrases');
			}
			else 
			{
				$error[] = $GLOBALS['lang']['language_no_phrases'];
			}
			
			if ( !empty($error) )
			{
				/* print errors */
				$_response -> script("printMessage('error', '{$error}')");
			}
			else
			{
				$insert = array(
					'Code' => $iso_code,
					'Direction' => $direction,
					'Key' => $lang_key,
					'Status' => 'active',
					'Date_format' => $date_format
				);
				$this -> rlActions -> insertOne($insert, 'android_languages');
								
				/* print notice */
				$_response -> script("
					printMessage('notice', '{$GLOBALS['lang']['language_added']}');
					show('lang_add_container');
					languagesGrid.reload();
				");
			}
		}
		
		$_response -> script("$('#lang_add_load').fadeOut('slow');");

		return $_response;
	}
	
	/**
	* set language as default
	*
	* @package ajax
	*
	* @param string $object - DOM object id
	* @param string $code - language code
	*
	**/
	function ajaxSetDefault( $object, $code )
	{
		global $_response, $lang;

		$this -> isSessActive();
		
		if ( $this -> rlConfig -> setConfig('android_lang', $code) )
		{
			$_response -> script("
				languagesGrid.reload();
				printMessage('notice', '{$lang['changes_saved']}')
			");
		}
		else 
		{
			trigger_error( "Android: Can not set default language, MySQL problems", E_WARNING );
			$GLOBALS['rlDebug'] -> logger("Android: Can not set default language, MySQL problems");
		}

		return $_response;
	}
	
	/**
	* delete language
	*
	* @package ajax
	*
	* @param int $id - language ID
	*
	**/
	function ajaxDeleteLang( $id )
	{
		global $_response, $config, $lang;

		$this -> isSessActive();

		$id = (int)$id;
		$code = $this -> getOne('Code', "`ID` = '{$id}'", 'android_languages');
		
		if ( !$code || !$id )
			return $_response;

		if ( $config['android_lang'] != $code )
		{
			$this -> query("DELETE FROM `".RL_DBPREFIX."android_phrases` WHERE `Code` = '{$code}'");
			$this -> query("DELETE FROM `".RL_DBPREFIX."android_languages` WHERE `Code` = '{$code}'");
			
			$_response -> script("
				printMessage('notice', '{$lang['language_deleted']}');
				languagesGrid.reload();
			");
		}
		else
		{
			trigger_error( "The default language desabled for deleting or droping to trash.", E_USER_WARNING );
			$GLOBALS['rlDebug'] -> logger("The default language desabled for deleting or droping to trash.");
		}
		
		return $_response;
	}
	
	/**
	* add new language phrase
	*
	* @package ajax
	*
	* @param array $data - new phrase data
	*
	**/
	function ajax_addPhrase( $data, $values )
	{
		global $_response, $lang;

		$this -> isSessActive();
		
		loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
		
		$key = str_replace(array('"', "'"), array("", ""), $data[0][1]);
		$key = $this -> rlValid -> xSql(trim($key));

		if ( strlen($key) < 2 )
		{
			$error[] = $lang['incorrect_phrase_key'];
		}
		
		if ( !utf8_is_ascii( $key ) )
		{
			$error[] = $lang['key_incorrect_charset'];
		}
		
		$key = $this -> rlValid -> str2key($key);
		
		//check key exists
		$key_exist = $this -> fetch( 'ID', array( 'Key' => $key ), null, null, 'android_phrases', 'row' );
		
		if (!empty($key_exist))
		{
			$error[] = str_replace( '{key}', "'<b>{$key}</b>'", $lang['notice_key_exist'] );
		}
		
		$side = $this -> rlValid -> xSql( $data[1][1] );

		if ( !empty($error) )
		{
			/* print errors */
			$error_content = '<ul>';
			foreach ($error as $err)
			{
				$error_content .= "<li>{$err}</li>";
			}
			$error_content .= '</ul>';
			$_response -> script( 'printMessage("error", "'. $error_content .'")' );
		}
		else 
		{
			foreach ( $values as $index => $field )
			{
				$phrase[] = array(
					'Code' => $values[$index][0],
					'Value' => $values[$index][1],
					'Key' => $key
				);
			}

			if ($this -> rlActions -> insert( $phrase, 'android_phrases' ))
			{
				/* hide add phrase block */
				$_response -> script("
					show('lang_add_phrase');
					$('#lang_add_phrase textarea').val('');
					$('#lang_add_phrase input').val('');
				");
	
				/* print notice */
				$_response -> script( "printMessage('notice', '{$lang['lang_phrase_added']}')" );
			}
		}

		$_response -> script( "$('#add_phrase_submit').val('{$lang['add']}');" );

		return $_response;
	}
	
	/**
	* export language
	*
	* @package xAjax
	*
	* @param int $id - export language ID
	*
	**/
	function exportLanguage( $id = false )
	{
		global $lang, $config, $rlSmarty;
		
		if ( !$id )
			return false;

		$info = $this -> fetch(array('Code', 'Key', 'Direction', 'Date_format'), array('ID' => $id), null, 1, 'android_languages', 'row');
		$name = $this -> getOne('Value', "`Key` = 'android_{$info['Key']}'", 'android_phrases');
		$phrases = $this -> fetch(array('Value', 'Key'), array('Code' => $info['Code']), null, null, 'android_phrases');
		
		if ( $phrases )
		{
			$content = <<<VS
<?xml version="1.0" encoding="UTF-8" ?>
<phrases>\r\n
VS;
			
			foreach ( $phrases as $key => $value )
			{
				$value['Value'] = str_replace("&", "&amp;", $value['Value']);
				$tmp = <<<VS
	<phrase key="{$value['Key']}"><![CDATA[{$value['Value']}]]></phrase>\r\n
VS;

				$content .= $tmp;
			}
			$content .= '</phrases>';
			
			header('Content-Type: text/xml');
    		header('Content-Disposition: attachment; filename='.ucfirst($info['Key']).'('.strtoupper($info['Code']).').xml');
    		header('Content-Transfer-Encoding: binary');
			echo $content;
			exit;
		}
		else
		{
			$alerts[] = $lang['lang_export_empty_alert'];
			$rlSmarty -> assign_by_ref('alerts', $alerts);
			
			return false;
		}
	}
	
	
	/**
	* copy languages's phrases
	*
	* @package ajax
	*
	* @param int $from - language code 1
	* @param int $to - language code 2
	*
	**/
	function ajaxCopyPhrases( $from = false, $to = false, $name = false )
	{
		global $_response, $lang;

		$this -> isSessActive();

		$phrases = $_SESSION['source_'.$from];
		$compare_to = $_SESSION['compare_'.$to];
		$lang_code = $_SESSION['lang_'.$to];

		if ( empty($phrases) || empty($lang_code) )
		{
			return $_response;
		}
		
		foreach ($phrases as $key => $value)
		{
			$insert = array();
			
			$insert = array(
				'Code' => $lang_code,
				'Key' => $phrases[$key]['Key'],
				'Value' => $phrases[$key]['Value']
			);
			
			$GLOBALS['rlActions'] -> insertOne($insert, 'lang_keys');
			
			$compare_to[] = array(
				'ID' => mysql_insert_id(),
				'Code' => $lang_code,
				'Key' => $phrases[$key]['Key'],
				'Value' => $phrases[$key]['Value']
			);
		}

		if ( !empty($insert) )
		{			
			$_SESSION['compare_'.$to] = $compare_to;
			
			/* print notice */			
			$_response -> script( "printMessage('notice', '{$lang['compare_phrases_copied']}')" );
			
			$_response -> script("$('#copy_button_{$from}').slideUp('slow');");
			$_response -> script( "$('#loading_{$from}').fadeOut('fast');" );
			
			$_response -> script( "compareGrid{$to}.reload();" );
		}
		
		return $_response;
	}
	
	function isSessActive()
	{
		global $_response;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
	}
}