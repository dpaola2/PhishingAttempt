<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLPAGSEGURO.CLASS.PHP
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

include_once RL_PLUGINS . 'pagSeguro/lib/PagSeguroLibrary.php';

class rlPagSeguro extends reefless
{
	var $rlDebug;

	function __construct()
	{
		global $rlDebug;

		$this -> rlDebug = &$rlDebug;
	}

	function createRequest( $data = false )
	{
		global $account_info;

		if( !$data )
		{
			return;
		}

		$request = new PagSeguroPaymentRequest();
		
        /* Sets the currency */
        $request -> setCurrency( $GLOBALS['config']['pag_seguro_currency'] );

        /* Add an item for this payment request */
        $request -> addItem( '0001', $data['item_name'], 1, $data['price'] );

        /* in future notifications */
        $request -> setReference( $data['txn_id'] );

        /* Sets your customer information. */
		/* adapt account phone */
		$sql = "SELECT * FROM `" . RL_DBPREFIX . "account_fields` WHERE `Key` = 'phone' LIMIT 1";
		$phone_field = $this -> getRow( $sql );

		if ( $phone_field )
		{
			$phone_tmp = $this -> parsePhone( $account_info['phone'], $phone_field );
			$phone_tmp = explode( "-", $phone_tmp );

			$phone['area'] = count( $phone_tmp ) > 2 ? $phone_tmp[1] : $phone_tmp[0];
			$phone['number'] = count( $phone_tmp ) > 2 ? $phone_tmp[2] : $phone_tmp[1];

			unset( $phone_tmp );
		}

        /*$request -> setSender(
			$account_info['Full_name'],
			$account_info['Mail'],
			$phone['area'] ? $phone['area'] : null,
			$phone['number'] ? $phone['number'] : null,
			$account_info['pag_cpf'] ? $account_info['pag_cpf'] : null,
			$account_info['pag_cpf_number'] ? $account_info['pag_cpf_number'] : null
        );*/

        /* Sets the url used by PagSeguro for redirect user after ends checkout process */
        $request -> setRedirectUrl( $data['redirect_url'] );

        /* Another way to set checkout parameters */
        $request -> addParameter( 'notificationURL', $data['notification_url'] );

        try {
            $credentials = new PagSeguroAccountCredentials( $GLOBALS['config']['pag_seguro_email'], $GLOBALS['config']['pag_seguro_token'] );
            $url = $request -> register( $credentials );

			return $url;
        } 
		catch ( PagSeguroServiceException $e ) 
		{
			$this -> rlDebug -> logger( "pagSeguro: " . $e -> getMessage() );
			echo $e -> getMessage();
        }
	}

	function _truncate( $string = false, $limit = false, $endchars = '...' )
	{
		if ( !$string || !$limit )
		{
			return;
		}

		$stringLength = strlen( $string );
		$endcharsLength = strlen( $endchars );

		if ( $stringLength > (int)$limit )
		{
			$cut = (int)( $limit - $endcharsLength );
			$string = substr( $string, 0, $cut ) . $endchars;
		}

		return $string;
	}
	
	function checkTransaction( $code = false, $type = false ) 
	{
		if ( !$type || !$code )
		{
			return false;
		}

        $notificationType = new PagSeguroNotificationType( $type );
        $strType = $notificationType -> getTypeFromValue();
		
		if ( $strType == 'TRANSACTION')
		{ 
			$credentials = new PagSeguroAccountCredentials( $GLOBALS['config']['pag_seguro_email'], $GLOBALS['config']['pag_seguro_token'] );

	        try {
	            $transaction = PagSeguroNotificationService::checkTransaction( $credentials, $code );
	            
				return $transaction;
	        } 
			catch ( PagSeguroServiceException $e ) 
			{
				$this -> rlDebug -> logger( "pagSeguro: " . $e -> getMessage() );
	        }
		}
		else
		{
			$this -> rlDebug -> logger( "pagSeguro: " . "Unknown notification type [" . $notificationType -> getValue() . "]" );
		}
		
		return false;
	}

	function getTransaction( $code = false )
	{
		if( !$code )
		{
			return false;
		}

        try {
            $credentials = new PagSeguroAccountCredentials( $GLOBALS['config']['pag_seguro_email'], $GLOBALS['config']['pag_seguro_token'] );
            $transaction = PagSeguroTransactionSearchService::searchByCode( $credentials, $code );

            return $transaction;

        } 
		catch ( PagSeguroServiceException $e )
		{                         
			$this -> rlDebug -> logger( "pagSeguro: " . $e -> getMessage() );
        }

		return false;		
	}
	
	function clearStr( $str )
	{
		if ( !get_magic_quotes_gpc() )
		{
			$str = addslashes( $str );
		}
		
		return $str;
	}

	function install()
	{
		$sql = "CREATE TABLE `" . RL_DBPREFIX . "pagseguro_transactions` (
			`ID` int(11) NOT NULL auto_increment,
			`Txn_ID` varchar(255) NOT NULL default '',
			`Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`Item` text NOT NULL,
			`Status` enum('active','completed') NOT NULL default 'active',
		  PRIMARY KEY (`ID`)
		) DEFAULT CHARSET=utf8";

		$this -> query( $sql );

		/* shoppingCart plugin */
		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_pagSeguro_enable` ENUM('0','1') NOT NULL DEFAULT '0';";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_pagSeguro_email` varchar(50) NOT NULL default ''";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_pagSeguro_token` varchar(50) NOT NULL default ''";
		$this -> query( $sql );

		$sql = "ALTER TABLE `" . RL_DBPREFIX . "accounts` ADD `shc_pagSeguro_currency` varchar(3) NOT NULL default ''";
		$this -> query( $sql );
	}

	function uninstall()
	{
		$sql = "DROP TABLE `" . RL_DBPREFIX . "pagseguro_transactions`";
		$this -> query( $sql );
		
		/* shoppingCart plugin */
		$this -> query( "ALTER TABLE `". RL_DBPREFIX ."accounts` DROP `shc_pagSeguro_enable`" );
		$this -> query( "ALTER TABLE `". RL_DBPREFIX ."accounts` DROP `shc_pagSeguro_email`" );
		$this -> query( "ALTER TABLE `". RL_DBPREFIX ."accounts` DROP `shc_pagSeguro_token`" );
		$this -> query( "ALTER TABLE `". RL_DBPREFIX ."accounts` DROP `shc_pagSeguro_currency`" );
	}

	function getPaymentDetails( $txn_id = false, $ignore_status = false )
	{
		if ( !$txn_id )
		{
			return;
		}

		$sql = "SELECT * FROM `" . RL_DBPREFIX . "pagseguro_transactions` WHERE `Txn_ID` = '{$txn_id}' ";

		if ( !$ignore_status )
		{
			$sql .= "AND `Status` = 'active' ";
		}
		$sql .= "LIMIT 1";

		$item = $this -> getRow( $sql );

		if ( !empty( $item ) )
		{
			$items = explode( "|", base64_decode( $item['Item'] ) );

			return $items;
		}

		return false;
	}
}