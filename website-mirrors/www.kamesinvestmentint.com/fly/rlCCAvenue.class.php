<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLCCAVENUE.CLASS.PHP
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

class rlCCAvenue extends reefless
{
	function rlCCAvenue()
	{
	}

	function getchecksum( $MerchantId, $Amount, $OrderId, $URL, $WorkingKey )
	{
		$str = "$MerchantId|$OrderId|$Amount|$URL|$WorkingKey";
		$adler = 1;
		$adler = $this -> adler32( $adler, $str );

		return $adler;
	}

	function genchecksum( $str )
	{
		$adler = 1;
		$adler = $this -> adler32( $adler, $str );
		return $adler;
	}
	
	function verifyChecksum( $MerchantId , $OrderId, $Amount, $AuthDesc, $WorkingKey,  $CheckSum )
	{
		$str = "";
		$str = "$MerchantId|$OrderId|$Amount|$AuthDesc|$WorkingKey";
		$adler = 1;
		$adler = $this -> adler32( $adler,$str );

		if( $adler == $CheckSum ) 
		{
			return true;
		}
		else 
		{
			return false;
		}
	}

	function adler32( $adler , $str )
	{
		$BASE =  65521 ;
		$s1 = $adler & 0xffff ;
		$s2 = ( $adler >> 16 ) & 0xffff;

		for ( $i = 0 ; $i < strlen( $str ); $i++)
		{
			$s1 = ( $s1 + Ord( $str[$i] ) ) % $BASE;
			$s2 = ( $s2 + $s1 ) % $BASE;
		}
		return $this -> leftshift( $s2, 16 ) + $s1;
	}

	function leftshift( $str, $num )
	{
		$str = DecBin( $str );

		for( $i = 0 ; $i < ( 64 - strlen( $str ) ) ; $i++ )
			$str = "0" . $str;

		for( $i = 0 ; $i < $num ; $i++ )
		{
			$str = $str . "0";
			$str = substr( $str , 1);
		}

		return $this -> cdec( $str );
	}

	function cdec( $num )
	{
		$dec = 0;
		for( $n = 0; $n < strlen( $num ); $n++ )
		{
		   $temp = $num[$n];
		   $dec =  $dec + $temp * pow( 2, strlen( $num ) - $n - 1 );
		}

		return $dec;
	}

	function install()
	{
		$sql = "CREATE TABLE `" . RL_DBPREFIX . "ccavenue_transactions` (
			`ID` int(11) NOT NULL auto_increment,
			`Txn_ID` varchar(255) NOT NULL default '',
			`Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`Item` text NOT NULL,
			`Status` enum('active','completed') NOT NULL default 'active',
		  PRIMARY KEY (`ID`)
		) DEFAULT CHARSET=utf8";

		$this -> query( $sql );
	}

	function uninstall()
	{
		$sql = "DROP TABLE `" . RL_DBPREFIX . "ccavenue_transactions`";
		$this -> query( $sql );
	}

	function getPaymentDetails( $txn_id = false )
	{
		if ( !$txn_id )
		{
			return;
		}

		$sql = "SELECT * FROM `" . RL_DBPREFIX . "ccavenue_transactions` WHERE `Txn_ID` = '{$txn_id}' AND `Status` = 'active' LIMIT 1";
		$item = $this -> getRow( $sql );

		if ( !empty( $item ) )
		{
			$items = explode( "|", base64_decode( $item['Item'] ) );

			return $items;
		}

		return false;
	}
}