<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLGOOGLEWALLET.CLASS.PHP
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

class rlGoogleWallet extends reefless
{
	var $rlJWT;

	function __construct()
	{
		global $rlJWT;

		$this -> rlJWT = &$rlJWT;
	}

	function generateToken( $request = false )
	{
		$token = $this -> createPayLoad( $request );
		$jwt_token = $this -> rlJWT -> encode( $token, $GLOBALS['config']['google_wallet_secret_key'] );

		return $jwt_token; 
	}

	function createPayLoad( $request = false )
	{
    	if ( !$request )
		{
			return false;
		}
	
		$payload = array(
				'iss' => $GLOBALS['config']['google_wallet_account_id'],
				'aud' => "Google",
				'typ' => "google/payments/inapp/item/v1",
				'exp' => time() + 3600,
				'iat' => time(),
				'request' => $request
			);

		return $payload;
	}

	function ajaxGoogleSuccessHandler( $seller_data = false )
	{
		global $_response;

		if ( $_SESSION['complete_payment'] )
		{
			$seller_data = explode( '|', base64_decode( $seller_data ) );
			$txn_id = $seller_data[8];

			if ( $_SESSION['complete_payment']['txn_id'] == $txn_id )
			{
				$_response -> redirect( str_replace( "&amp;", "&", $_SESSION['complete_payment']['callback']['success_url'] ) );
			}
			else
			{
				$_response -> script( "printMessage('error', '{$GLOBALS['lang']['google_wallet_session_expired']}');" );
			}

			unset( $_SESSION['complete_payment'] );
		}
		else
		{
			$_response -> script( "printMessage('error', '{$GLOBALS['lang']['google_pay_failed']}');" );
		}

		return $_response;
	}
	
	function ajaxGoogleFailureHandler( $seller_data = false )
	{
		global $_response;

		if ( $_SESSION['complete_payment'] )
		{
			$_response -> redirect( str_replace( "&amp;", "&", $_SESSION['complete_payment']['callback']['cancel_url'] ) );
			
			unset( $_SESSION['complete_payment'] );
		}
		else
		{
			$_response -> script( "printMessage('error', '{$GLOBALS['lang']['google_pay_failed']}');" );
		}

		return $_response;
	}
}