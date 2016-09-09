<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLDHL.CLASS.PHP
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

class rlDHL
{
	var $_error;
	var $xmlData;
	var $currentTag;

	var $api_endpoint;

	function __construct()
	{
	 	$this -> api_endpoint = $GLOBALS['config']['shc_dhl_test_mode'] ? 'https://xmlpitest-ea.dhl.com/XMLShippingServlet' : 'https://xmlpi-ea.dhl.com/XMLShippingServlet';			
	}

	function post( $xml = false )
	{
		if(!$xml)
		{
			return $xml;
		}

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this -> api_endpoint ); 
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_BINARYTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec( $ch );

		if ( !$response )
		{
			$rlDebug -> logger( 'shoppingCart: ' . $methodName . ' failed: ' . curl_error($ch).'('.curl_errno($ch).')' );
			return false;
		}

		curl_close($ch);

		return $response;
	}

	function epXmlElementStart( $parser, $tag, $attributes )
	{
		$this -> currentTag = $tag;		
	}
	
	function epXmlElementEnd( $parser, $tag )
	{
		$this -> currentTag = "";
	}
	
	function epXmlData( $parser, $cdata )
	{
		$this -> xmlData[$this -> currentTag][] = $cdata;
	}

	function parse( $xml )
	{
	    $parser = xml_parser_create();
	    
	    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, TRUE );
	    
	    xml_set_object( $parser, $this );
	    xml_set_element_handler( $parser, "epXmlElementStart", "epXmlElementEnd" );
	    xml_set_character_data_handler( $parser, "epXmlData" );
	    
	    xml_parse( $parser, $xml, TRUE );

	    if ( xml_get_error_code( $parser ) == XML_ERROR_NONE )
		{
	        if ( strpos( $xml, "<Faults>" ) !== false )
			{
				$myError = $this -> xmlData["CODE"][0];
			    $myErrorMessage = $this -> xmlData["DESC"][0];
			    $myErrorMessage .= $this -> xmlData["DESCRIPTION"][0];
				$this -> _error .= "Error($myError):".$myErrorMessage ;
				return false;
			}

		    return $this -> xmlData;
	    } 
		else 
		{
	        $myError = xml_get_error_code( $parser ) + XML_ERROR_OFFSET;
	        $myErrorMessage = xml_error_string( $myError );
			$this -> _error = "Error($myError):".$myErrorMessage ;
			return false;
	    }

	    xml_parser_free( $parser );
	}

	var $xml_schema = '
<?xml version="1.0" encoding="UTF-8"?>
<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd ">
  <GetQuote>
    <Request>
      <ServiceHeader>
        <MessageTime>{message_time}</MessageTime>
        <MessageReference>{reference}</MessageReference>
        <SiteID>{siteid}</SiteID>
        <Password>{password}</Password>
      </ServiceHeader>
    </Request>
    <From>
      <CountryCode>{from_country_code}</CountryCode>
      <Postalcode>{from_postal_code}</Postalcode>
	  <City>{from_city}</City>
	  <VatNo>{vat_no}</VatNo>
    </From>
    <BkgDetails>
      <PaymentCountryCode>{payment_country_code}</PaymentCountryCode>
      <Date>{bkg_date}</Date>
      <ReadyTime>{bkg_ready_time}</ReadyTime>
      <ReadyTimeGMTOffset>+01:00</ReadyTimeGMTOffset>
      <DimensionUnit>CM</DimensionUnit>
      <WeightUnit>KG</WeightUnit>
      <Pieces>
        <Piece>
          <PieceID>1</PieceID>
          <Height>1</Height>
          <Depth>1</Depth>
          <Width>1</Width>
          <Weight>{bkg_weight}</Weight>
        </Piece>
      </Pieces> 
	  <PaymentAccountNumber></PaymentAccountNumber>	  
      <IsDutiable>N</IsDutiable>
      <NetworkTypeCode>AL</NetworkTypeCode>
	  <QtdShp>
		 <GlobalProductCode>D</GlobalProductCode>
	     <LocalProductCode>D</LocalProductCode>		
	     <QtdShpExChrg>
            <SpecialServiceType>AA</SpecialServiceType>
         </QtdShpExChrg>
	  </QtdShp>
    </BkgDetails>
    <To>
      <CountryCode>{to_country_code}</CountryCode>
      <Postalcode>{to_postal_code}</Postalcode>
	  <City>{to_city}</City>
	  <VatNo>{vat_no}</VatNo>
    </To>
  </GetQuote>
</p:DCTRequest>';

}