<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLUPS.CLASS.PHP
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

class rlUPS
{
	var $api_endpoint;
	var $request;

    function __construct()
	{
		$this -> api_endpoint = $GLOBALS['config']['shc_ups_test_mode'] ? 'https://wwwcie.ups.com/ups.app/xml/Rate' : 'https://www.ups.com/ups.app/xml/Rate';
	}

	function post( $xml = false )
	{
		global $errors, $rlDebug, $order_info;

        if( !$xml )
		{
			return false;
		}
		
		$curl = curl_init( $this -> api_endpoint );

		curl_setopt( $curl, CURLOPT_HEADER, 0 );
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 60 );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $xml );

		$response_xml = curl_exec( $curl );

		curl_close( $curl );

		/*$response_xml = '<?xml version="1.0"?><RatingServiceSelectionResponse><Response><TransactionReference><CustomerContext>Bare Bones Rate Request</CustomerContext><XpciVersion>1.0001</XpciVersion></TransactionReference><ResponseStatusCode>1</ResponseStatusCode><ResponseStatusDescription>Success</ResponseStatusDescription></Response><RatedShipment><Service><Code>08</Code></Service><RatedShipmentWarning>Your invoice may vary from the displayed reference rates</RatedShipmentWarning><BillingWeight><UnitOfMeasurement><Code>LBS</Code></UnitOfMeasurement><Weight>1.0</Weight></BillingWeight><TransportationCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>75.25</MonetaryValue></TransportationCharges><ServiceOptionsCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>0.00</MonetaryValue></ServiceOptionsCharges><TotalCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>75.25</MonetaryValue></TotalCharges><GuaranteedDaysToDelivery/><ScheduledDeliveryTime/><RatedPackage><TransportationCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>0.00</MonetaryValue></TransportationCharges><ServiceOptionsCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>0.00</MonetaryValue></ServiceOptionsCharges><TotalCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>0.00</MonetaryValue></TotalCharges><Weight>0.5</Weight><BillingWeight><UnitOfMeasurement><Code>LBS</Code></UnitOfMeasurement><Weight>0.0</Weight></BillingWeight></RatedPackage></RatedShipment><RatedShipment><Service><Code>65</Code></Service><RatedShipmentWarning>Your invoice may vary from the displayed reference rates</RatedShipmentWarning><BillingWeight><UnitOfMeasurement><Code>LBS</Code></UnitOfMeasurement><Weight>1.0</Weight></BillingWeight><TransportationCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>77.74</MonetaryValue></TransportationCharges><ServiceOptionsCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>0.00</MonetaryValue></ServiceOptionsCharges><TotalCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>77.74</MonetaryValue></TotalCharges><GuaranteedDaysToDelivery>1</GuaranteedDaysToDelivery><ScheduledDeliveryTime/><RatedPackage><TransportationCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>0.00</MonetaryValue></TransportationCharges><ServiceOptionsCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>0.00</MonetaryValue></ServiceOptionsCharges><TotalCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>0.00</MonetaryValue></TotalCharges><Weight>0.5</Weight><BillingWeight><UnitOfMeasurement><Code>LBS</Code></UnitOfMeasurement><Weight>0.0</Weight></BillingWeight></RatedPackage></RatedShipment><RatedShipment><Service><Code>11</Code></Service><RatedShipmentWarning>Your invoice may vary from the displayed reference rates</RatedShipmentWarning><BillingWeight><UnitOfMeasurement><Code>LBS</Code></UnitOfMeasurement><Weight>1.0</Weight></BillingWeight><TransportationCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>20.70</MonetaryValue></TransportationCharges><ServiceOptionsCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>0.00</MonetaryValue></ServiceOptionsCharges><TotalCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>20.70</MonetaryValue></TotalCharges><GuaranteedDaysToDelivery/><ScheduledDeliveryTime/><RatedPackage><TransportationCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>0.00</MonetaryValue></TransportationCharges><ServiceOptionsCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>0.00</MonetaryValue></ServiceOptionsCharges><TotalCharges><CurrencyCode>USD</CurrencyCode><MonetaryValue>0.00</MonetaryValue></TotalCharges><Weight>0.5</Weight><BillingWeight><UnitOfMeasurement><Code>LBS</Code></UnitOfMeasurement><Weight>1.0</Weight></BillingWeight></RatedPackage></RatedShipment></RatingServiceSelectionResponse>';*/
		$dom = new DOMDocument( '1.0', 'UTF-8' );
		$dom -> loadXml( $response_xml );	

		$rating_service_selection_response = $dom->getElementsByTagName('RatingServiceSelectionResponse')->item(0);
		$response = $rating_service_selection_response->getElementsByTagName('Response')->item(0);

		$response_status_code = $response->getElementsByTagName('ResponseStatusCode');

		if ( $response_status_code->item(0)->nodeValue != '1' ) 
		{
			$message = $response->getElementsByTagName('Error')->item(0)->getElementsByTagName('ErrorCode')->item(0)->nodeValue . ': ' . $response->getElementsByTagName('Error')->item(0)->getElementsByTagName('ErrorDescription')->item(0)->nodeValue;
			$errors[] = $message;			
			$rlDebug -> logger( 'shoppingCart: ' . $message );
			return false;
		}
		else
		{
			$quote_data = false;
			$services = $this -> getShippingServices();

			$rated_shipments = $rating_service_selection_response -> getElementsByTagName( 'RatedShipment' );
			
			foreach ( $rated_shipments as $rated_shipment ) 
			{                                                                               
				$service = $rated_shipment->getElementsByTagName('Service')->item(0);
				$code = $service -> getElementsByTagName('Code')->item(0)->nodeValue;
				$total_charges = $rated_shipment -> getElementsByTagName( 'TotalCharges' ) -> item(0);
				$cost = $total_charges->getElementsByTagName('MonetaryValue')->item(0)->nodeValue;	
				$currency = $total_charges->getElementsByTagName('CurrencyCode')->item(0)->nodeValue;

				if (!($code && $cost)) 
				{
					continue;
				}

				if($order_info['UPSService'] == $code)
				{
					$quote_data = array(
						'ups_code' => $code,
						'code' => 'UPS',
						'quote' => $this -> convert($currency, $cost),
						'currency' => $currency,
						'title' => $services[$code]['name'],
						'days' => '',
						'error' => false
					);
				}
			}

			return $quote_data;
		}

		return $response;
	}

	function convert( $currency = false, $total = false )
	{
		if ( !$currency || !$total )
		{
			return false;
		}     

		$symbol = trim( $GLOBALS['shcRates'][$currency]['Symbol'] );
	
		if($GLOBALS['config']['system_currency'] != $currency && $GLOBALS['config']['system_currency'] != $sysbol )
		{
			$rate = (float)$GLOBALS['shcRates'][$currency]['Rate'];
			$new_total = round( $total * $rate, 2 );

			return $new_total;
		}
		
		return $total;
	}

	function getPickupMethods( $output = false )
	{
		$list = array(
					'01' => $GLOBALS['lang']['shc_ups_pickup_regular_daily_pickup'],
					'03' => $GLOBALS['lang']['shc_ups_pickup_customer_counter'],
					'06' => $GLOBALS['lang']['shc_ups_pickup_one_time_pickup'],
					'07' => $GLOBALS['lang']['shc_ups_pickup_on_call_air'],
					'19' => $GLOBALS['lang']['shc_ups_pickup_letter_center'],
					'20' => $GLOBALS['lang']['shc_ups_pickup_air_service_center'],
					'11' => $GLOBALS['lang']['shc_ups_pickup_suggested_retail_rates']
			);

		if( $output )
		{
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'shc_ups_pickup_methods', $list );
			return;
		}

		return $list;
	}

	function getPackagingItems( $output = false )
	{
		$list = array(
				'00' => $GLOBALS['lang']['shc_ups_packaging_unknown'],
				'01' => $GLOBALS['lang']['shc_ups_packaging_letter'],
				'02' => $GLOBALS['lang']['shc_ups_packaging_package'],
				'03' => $GLOBALS['lang']['shc_ups_packaging_tube'],
				'04' => $GLOBALS['lang']['shc_ups_packaging_pak'],
				'21' => $GLOBALS['lang']['shc_ups_packaging_express_box'],
				'24' => $GLOBALS['lang']['shc_ups_packaging_25kg_box'],
				'25' => $GLOBALS['lang']['shc_ups_packaging_10kg_box'],
				'30' => $GLOBALS['lang']['shc_ups_packaging_pallet'],
				'2a' => $GLOBALS['lang']['shc_ups_packaging_small_express_box'],
				'2b' => $GLOBALS['lang']['shc_ups_packaging_medium_express_box'],
				'2c' => $GLOBALS['lang']['shc_ups_packaging_large_express_box'],
			);

		if( $output )
		{
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'shc_ups_package_types', $list );
			return;
		}

		return $list;
	}

	function getOrigins( $output = false )
	{
		$list = array(
					array(
						'key' => 'US',
						'name' => "US Origin"
					),
					array(
						'key' => 'CA',
						'name' => "Canada Origin"
					),
					array(
						'key' => 'EU',
						'name' => "European Union Origin"
					),
					array(
						'key' => 'PR',
						'name' => "Puerto Rico Origin"
					),
					array(
						'key' => 'MX',
						'name' => "Mexico Origin"
					),
					array(
						'key' => 'other',
						'name' => $GLOBALS['lang']['ups_origin_other']
					)
			);

		if( $output )
		{
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'ups_origins', $list );
			return;
		}

		return $list;
	}

	function getShippingServices( $output = false )
	{
		$list = array(
					/* US,CA,PR */
					'01' => array(
						'origin' => "US,CA,PR",
						'code' => "01",
						'name' => "UPS Next Day Air"
					),
					'02' => array(
						'origin' => "US,CA,PR",
						'code' => "02",
						'name' => "UPS 2nd Day Air"
					),
					'03' => array(
						'origin' => "US,PR",
						'code' => "03",
						'name' => "UPS Ground"
					),
					'12' => array(
						'origin' => "US,CA",
						'code' => "12",
						'name' => "UPS 3 Day Select"
					),
					'13' => array(
						'origin' => "US,CA",
						'code' => "13",
						'name' => "UPS Next Day Air Saver"
					),
					'14' => array(
						'origin' => "US,CA,PR",
						'code' => "14",
						'name' => "UPS Express Early A.M."
					),
					'59' => array(
						'origin' => "US",
						'code' => "59",
						'name' => "UPS 2nd Day Air AM"
					),
                     
					/* ALL */
					'07' => array(
						'origin' => "US,CA,PR,MX,EU,other",
						'code' => "07",
						'name' => "UPS Express"
					),
					'08' => array(
						'origin' => "US,CA,PR,MX,EU,other",
						'code' => "08",
						'name' => "UPS Expedited"
					),
					'11' => array(
						'origin' => "US,CA,EU,other",
						'code' => "11",
						'name' => "UPS Standard"
					),
					'54' => array(
						'origin' => "US,CA,PR,MX,EU,other",
						'code' => "54",
						'name' => "UPS Worldwide Express Plus"
					),
					'65' => array(
						'origin' => "US,CA,PR,MX,EU,other",
						'code' => "65",
						'name' => "UPS Saver"
					),

					/* EU */
					'82' => array(
						'origin' => "EU",
						'code' => "82",
						'name' => "UPS Today Standard"
					),
					'83' => array(
						'origin' => "EU",
						'code' => "83",
						'name' => "UPS Today Dedicated Courier"
					),
					'84' => array(
						'origin' => "EU",
						'code' => "84",
						'name' => "UPS Today Intercity"
					),
					'85' => array(
						'origin' => "EU",
						'code' => "85",
						'name' => "UPS Today Express"
					),
					'86' => array(
						'origin' => "EU",
						'code' => "86",
						'name' => "UPS Today Express Saver"
					)
			);

		if( $output )
		{
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'shc_ups_services', $list );
			return;
		}

		return $list;
	}

	function getQuoteTypes( $output = false )
	{
		global $rlSmarty;

		$list = array(
					array(
						'key' => 'residential',
						'name' => $GLOBALS['lang']['shc_ups_quote_type_residential']
					),
					array(
						'key' => 'commercial',
						'name' => $GLOBALS['lang']['shc_ups_quote_type_commercial']
					)
			);

		if( $output )
		{
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'ups_quote_type', $list );
			return;
		}

		return $list;		
	}

	function getClassifications( $output = false )
	{
		$list = array( '01', '03', '04' );
		
		if ( $output )
		{
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'ups_classification', $list );
			return;
		}

		return $list;	
	}

	function outputStaticData()
	{
		$this -> getPickupMethods( true );
		$this -> getPackagingItems( true );
		$this -> getOrigins( true );
		$this -> getShippingServices( true );
		$this -> getQuoteTypes( true );
		$this -> getClassifications( true );
	}

	function build_xml_schema()
	{
		$xml_schema = '
<?xml version="1.0"?>  
<AccessRequest xml:lang="en-US">  
	<AccessLicenseNumber>'.$GLOBALS['config']['shc_ups_key'].'</AccessLicenseNumber>
	<UserId>'.$GLOBALS['config']['shc_ups_username'].'</UserId>
	<Password>'.$GLOBALS['config']['shc_ups_password'].'</Password>
</AccessRequest>
<?xml version="1.0"?>
<RatingServiceSelectionRequest xml:lang="en-US">
	<Request>  
		<TransactionReference> 
			<CustomerContext>Rating and Service</CustomerContext>  
			<XpciVersion>1.0001</XpciVersion>  
		</TransactionReference> 
		<RequestAction>Rate</RequestAction>  
		<RequestOption>shop</RequestOption>  
	</Request>  
   <PickupType>
	   <Code>'.$this->request['ups_pickup'].'</Code>
   </PickupType>
' . ( $this->request['ups_country'] == 'US' && $this->request['ups_pickup']  == '11' ?
'
		<CustomerClassification>
		   <Code>'.$this->request['ups_classification'] .'</Code>
		</CustomerClassification>
'
: '' ) .
'
	<Shipment>  
		<Shipper>  
			<Address>  
				<City>'.$this->request['ups_city'].'</City>
				<StateProvinceCode>'.$this->request['ups_state'].'</StateProvinceCode>
				<CountryCode>'.$this->request['ups_country'].'</CountryCode>
				<PostalCode>'.$this->request['ups_postcode'].'</PostalCode>
			</Address> 
		</Shipper> 
		<ShipTo> 
			<Address> 
			 	<City>'.$this->request['city'].'</City>
				<StateProvinceCode>'.$this->request['zone_code'].'</StateProvinceCode>
				<CountryCode>'.$this->request['country'].'</CountryCode>
				<PostalCode>'.$this->request['postcode'].'</PostalCode>
				'.($this->request['ups_quote_type'] == 'residential' ? '<ResidentialAddressIndicator />' : '').'
			</Address> 
		</ShipTo>
		<ShipFrom> 
			<Address> 
				<City>'.$this->request['ups_city'].'</City>
				<StateProvinceCode>'.$this->request['ups_state'].'</StateProvinceCode>
				<CountryCode>'.$this->request['ups_country'].'</CountryCode>
				<PostalCode>'.$this->request['ups_postcode'].'</PostalCode>
			</Address> 
		</ShipFrom>
		<Package>
			<PackagingType>
				<Code>'.$this->request['ups_packaging'].'</Code>
			</PackagingType>
			<Dimensions>
				<UnitOfMeasurement>
					<Code>'.$this->request['length_code'].'</Code>
				</UnitOfMeasurement>
				<Length>'.$this->request['length'].'</Length>
				<Width>'.$this->request['width'].'</Width>
				<Height>'.$this->request['height'].'</Height>
			</Dimensions>
			<PackageWeight>
				<UnitOfMeasurement>
					<Code>'.$this->request['weight_code'].'</Code>
				</UnitOfMeasurement>
				<Weight>'.$this->request['weight'].'</Weight>
			</PackageWeight>
'.( $this->request['ups_insurance'] ?
'
			   <PackageServiceOptions>
				   <InsuredValue>
				       <CurrencyCode>'.$this->request['currency'].'</CurrencyCode>
				       <MonetaryValue>'.$this->request['sub_total'].'</MonetaryValue>
				   </InsuredValue>
			   </PackageServiceOptions>
' : '' ) .
'
		</Package>
	    <ShipmentServiceOptions>
	      <OnCallAir>
			<Schedule> 
				<PickupDay>02</PickupDay>
				<Method>02</Method>
			</Schedule>
	      </OnCallAir>
	    </ShipmentServiceOptions>
	</Shipment>
</RatingServiceSelectionRequest>';

		return $xml_schema;
	}
}