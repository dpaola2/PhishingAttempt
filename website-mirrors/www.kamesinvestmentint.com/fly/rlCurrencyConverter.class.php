<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLCURRENCYCONVERTER.CLASS.PHP
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

class rlCurrencyConverter extends reefless
{
	/**
	* @var array $cc - country code => currency code
	**/
	var $cc = array();
	
	/**
	* @var array $systemCurrency - system currencies mapping
	**/
	var $systemCurrency = array(
		'USD' => 'dollar',
		'GBP' => 'ps',
		'EUR' => 'euro'
	);

	/**
	* specialBlock hook code template
	**/
	var $specialBlock = '	
		global $block_keys, $rlXajax, $rlSmarty, $blocks, $rlCommon, $tpl_settings;

		$GLOBALS["reefless"] -> loadClass( "CurrencyConverter", null, "currencyConverter" );
		
		$rates = array({rates_items});
    	$rlSmarty -> assign_by_ref("curConv_rates", $rates);
    	$GLOBALS["rlCurrencyConverter"] -> rates = $rates;
		
		$GLOBALS["rlCurrencyConverter"] -> detectCurrency();
		
		if ( array_key_exists( "currencyConvertor_block", $block_keys) ) {
			$rlXajax -> registerFunction( array( "setCurrency", $GLOBALS["rlCurrencyConverter"], "ajaxSetCurrency" ) );
		}
		if ( $tpl_settings["type"] == "responsive_42" && $blocks["currencyConvertor_block"] ) {
			unset($blocks["currencyConvertor_block"]);
			$rlCommon -> defineBlocksExist($blocks);
		}
	';

	/**
	* available rates
	**/
	var $rates = false;

	/**
	* class constructor
	**/
	function rlCurrencyConverter()
	{
		global $rlSmarty;
		
		$this -> setCC();
		
		if ( is_object('rlSmarty') )
		{
			$rlSmarty -> assign_by_ref('curConv_mapping', $this -> systemCurrency);
			$rlSmarty -> assign_by_ref('curConv_abbr', $this -> cc);
			$rlSmarty -> register_modifier('flHtmlEntriesDecode', 'flHtmlEntriesDecode');
		}
	}
	
	/**
	* update rates
	*
	* @package xajax
	*
	**/
	function ajaxUpdateRate()
	{
		global $_response, $lang, $rlActions, $rlNotice, $config;
		
		if ( !$this -> updateRate() )
		{
			$_response -> script("printMessage('error', '{$lang['currencyConverter_update_rss_fail']}');");
		}
		
		$_response -> script( "currencyGrid.reload();" );
		
		return $_response;
	}
	
	/**
	* update rates
	*
	* @package xajax
	*
	* @param string $code - currency code abbr
	* @param double $rate - currency rate
	* @param string $name - currency name
	* @param string $status - currency status
	*
	**/
	function ajaxAddCurrency( $code = false, $rate = false, $name = false, $status = 'active' )
	{
		global $_response, $lang, $rlActions, $rlNotice, $config;

		$GLOBALS['rlValid'] -> sql($code);
		if ( $exist = $this -> getOne('ID', "`Code` = '{$code}'", 'currency_rate') )
		{
			$errors[] = str_replace('{code}', $code, $lang['currencyConverter_code_exists']);
		}
		
		preg_match('/([A-Z]{3})/', $code, $matches);
		if ( !$matches[1] )
		{
			$errors[] = $lang['currencyConverter_code_wrong'];
		}
		
		preg_match('/^([0-9\.]+)$/', $rate, $matches_rate);
		if ( !$matches_rate[1] )
		{
			$errors[] = $lang['currencyConverter_rate_wrong'];
		}
		
		if ( !empty($errors) )
		{
			$out = '<ul>';
			/* print errors */
			foreach ($errors as $error)
			{
				$out .= '<li>'. $error .'</li>';
			}
			$out .= '</ul>';
			$_response -> script("printMessage('error', '{$out}');");
		}
		else
		{
			$insert = array(
				'Code' => $code,
				'Rate' => $rate,
				'Key' => $code,
				'Country' => $name,
				'Date' => 'NOW()',
				'Status' => $status
			);
			$rlActions -> insertOne($insert, 'currency_rate');
			
			$this -> updateHook();

			$_response -> script("
				currencyGrid.reload();
				printNotice('notice', '{$lang['currencyConverter_added_notice']}');
				$('#new_item').slideUp('normal');
			");
		}
		
		$_response -> script("$('input[name=add_new_currency_submit]').val('{$lang['add']}');");
		
		return $_response;
	}
	
	/**
	* insert rates
	*
	* @param string $url - rss url
	*
	**/
	function insertRate( $url = false ) {
		$content = $this -> getPageContent($url ? $url : $config['currencyConverter_rss']);
		
		$this -> loadClass('Rss');
		$GLOBALS['rlRss'] -> items_number = 300;
		$GLOBALS['rlRss'] -> createParser($content);
		$rates = $GLOBALS['rlRss'] -> getRssContent();
		
		if ( !empty($rates) ) {
			$this -> query("INSERT INTO `". RL_DBPREFIX ."currency_rate` (`Rate`, `Key`, `Country`, `Date`, `Code`, `Symbol`) VALUES ('1', 'dollar', 'United States', NOW(), 'USD', '$') ");
			
			foreach ( $rates as $rate ) {
				/* get currency code */
				preg_match('/(.*)\/.*/', $rate['title'], $code_matches);
				$code = $code_matches[1];

				if ( strtolower($code) == 'usd' ) continue;
				
				/* get rate */
				preg_match('/.*\=\s([0-9\.]*)\s(.*)/', $rate['description'], $matches);
				$rate = $matches[1];
				$country = $matches[2];
				switch ($code) {
					case 'EUR':
						$symbol = '&euro;';
						$key = 'euro';
						break;
					case 'GBP':
						$symbol = '&pound;';
						$key = 'ps';
						break;
					default:
						$symbol = '';
						$key = $code;
						break;
				}
				
				$this -> query("INSERT INTO `". RL_DBPREFIX ."currency_rate` (`Rate`, `Key`, `Country`, `Date`, `Code`, `Symbol`) VALUES ('{$rate}', '{$key}', '{$country}', NOW(), '{$code}', '{$symbol}') ");
			}
		}
		
		$this -> updateHook();
	}
	
	/**
	* update rates
	*
	**/
	function updateRate()
	{
		global $config;
		
		$content = $this -> getPageContent($config['currencyConverter_rss']);
		
		$this -> loadClass('Rss');
		$GLOBALS['rlRss'] -> items_number = 300;
		$GLOBALS['rlRss'] -> createParser($content);
		$rates = $GLOBALS['rlRss'] -> getRssContent();
		
		if ( empty($rates) )
			return false;
			
		foreach ( $rates as $rate )
		{
			/* get currency code */
			preg_match('/(.*)\/.*/', $rate['title'], $code_matches);
			$code = $code_matches[1];
			
			/* get rate */
			preg_match('/.*\=\s([0-9\.\,]*)\s(.*)/', $rate['description'], $matches);
			$rate = str_replace(',', '', $matches[1]);
			$country = $matches[2];
			
			if ( $this -> getOne('ID', "`Code` = '{$code}'", 'currency_rate') )
			{
				$this -> query("UPDATE `". RL_DBPREFIX ."currency_rate` SET `Rate` = '{$rate}', `Date` = NOW() WHERE `Code` = '{$code}' LIMIT 1");
			}
			else
			{
				$this -> query("INSERT INTO `". RL_DBPREFIX ."currency_rate` (`Rate`, `Key`, `Country`, `Date`, `Code`) VALUES ('{$rate}', '{$code}', '{$country}', NOW(), '{$code}') ");
			}
		}
		
		$this -> updateHook();
		
		return true;
	}
	
	/**
	* update specialBlock hook entry
	**/
	function updateHook()
	{
		global $rlActions;
		
		$this -> setTable('currency_rate');
		$rates = $this -> fetch(array('Code', 'Key', 'Rate', 'Country', 'Symbol'), array('Status' => 'active'));
		
		if ( !$rates )
			return false;
			
		foreach ($rates as $rate)
		{
			$items .= "'{$rate['Key']}' => array(
						'Rate' => '{$rate['Rate']}',
						'Code' => '{$rate['Code']}',
						'Symbol' => '{$rate['Symbol']}',
						'Country' => '{$rate['Country']}'
					),";
		}
		
		$update['fields']['Code'] = str_replace('{rates_items}', rtrim($items, ','), $this -> specialBlock);
		$update['where'] = array(
			'Plugin' => 'currencyConverter',
			'Name' => 'specialBlock'
		);
		
		$rlActions -> rlAllowHTML = true;
		$rlActions -> updateOne($update, 'hooks');
		$rlActions -> rlAllowHTML = false;
	}
	
	/**
	* set user currency
	*
	* @package xajax
	*
	* @param string $key - currency code abbr
	*
	**/
	function ajaxSetCurrency( $key = false )
	{
		global $_response;
		
		if ( $this -> rates[$key] )
		{
			$_SESSION['curConv_code'] = $key;
			setcookie('curConv_code', $key, time()+2678400, '/');

			$_response -> script("$('#curConv_1 b').html('". strtoupper($this -> rates[$key]['Code']) ."');");
			$_response -> script("$('#curConv_2').hide();$('#curConv_1').show();");
		}
		
		$_response -> script("$('#curConv_loading').hide();");
		$_response -> script("currencyConverter.config['currency'] = '". $key ."'; currencyConverter.convert();");
		
		return $_response;
	}
	
	/**
	* detect currency
	**/
	function detectCurrency()
	{
		global $rlSmarty;
		
		/* detect code by country */
		$curConvCountry = array(
			'Code' => $_SESSION['GEOLocationData'] -> Country_code,
			'Name' => $_SESSION['GEOLocationData'] -> Country_name
		);
		
		if ( $_SESSION['curConv_code'] || $_COOKIE['curConv_code'] )
		{
			$curConvCountry['Currency'] = $_SESSION['curConv_code'] ? $_SESSION['curConv_code'] : $_COOKIE['curConv_code'];
		}
		else
		{
			if ( $this -> rates[$this -> cc[$curConvCountry['Code']]] )
			{
				$curConvCountry['Currency'] = $this -> cc[$curConvCountry['Code']];
			}
			elseif ( $this -> cc[$curConvCountry['Code']] == 'USD' )
			{
				$curConvCountry['Currency'] = 'dollar';
			}
			elseif ( $this -> cc[$curConvCountry['Code']] == 'EUR' )
			{
				$curConvCountry['Currency'] = 'euro';
			}
			elseif ( $this -> cc[$curConvCountry['Code']] == 'GBP' && $this -> rates['ps'] )
			{
				$curConvCountry['Currency'] = 'ps';
			}
		}
		
		$rlSmarty -> assign_by_ref('curConv_country', $curConvCountry);
	}

	/**
	* prepare default convertsion for the price on the listing details
	**/
	function listingDetails() {
		global $listing, $rlSmarty, $config, $lang;

		if ( !$_COOKIE['curConv_code'] )
			return;

		$price = false;

		foreach ($listing as &$g) {
			foreach ($g['Fields'] as $f) {
				if ( $f['Key'] == $config['currencyConverter_price_field'] ) {
					$price = explode('|', $f['source'][0]);
					break;
				}
			}
		}

		if ( !$price[0] )
			return;

		/* convert price to dollar rate */
		if ( strtolower($price[1]) != 'dollar' ) {
			$price[0] /= $GLOBALS['rlCurrencyConverter'] -> rates[$price[1]]['Rate'];
		}

		/* prepare currency view */
		if ( $GLOBALS['rlCurrencyConverter'] -> rates[$_COOKIE['curConv_code']]['Symbol'] ) {
			$set_currency = explode(',', $GLOBALS['rlCurrencyConverter'] -> rates[$_COOKIE['curConv_code']]['Symbol']);
			$set_currency = $set_currency[0];
		}
		else {
			$set_currency = $GLOBALS['rlCurrencyConverter'] -> rates[$_COOKIE['curConv_code']]['Code'];
		}

		/* convert */
		$price[0] *= $GLOBALS['rlCurrencyConverter'] -> rates[$_COOKIE['curConv_code']]['Rate'];

		if ( $config['system_currency_position'] == 'before' ) {
			$new_price = $set_currency .' '. $GLOBALS['rlValid'] -> str2money($price[0]);
		}
		else {
			$new_price = $GLOBALS['rlValid'] -> str2money($price[0]) .' '. $set_currency;
		}

		if ( $config['currencyConverter_show_flag'] ) {
			$flag = '<img class="currency-flag" src="'. RL_TPL_BASE .'img/blank.gif" style="background-image: url('. RL_PLUGINS_URL .'currencyConverter/static/flags/'. strtolower($GLOBALS['rlCurrencyConverter'] -> rates[$_COOKIE['curConv_code']]['Code']) .'.png)" alt="" />';
		}

		$new = array(
			'converted_price' => array(
				'Details_page' => 1,
				'Key' => 'converted_price',
				'name' => $lang['currencyConverter_converted'],
				'Type' => 'price',
				'value' => $flag . '<span>'.$new_price.'</span>'
			)
		);

		reset($listing);
		$stack = current($listing);
		if ( $stack['Group_ID'] ) {
			reset($stack);
			$key = key($listing);
			$listing[$key]['Fields'] = array_merge($new, $listing[$key]['Fields']);
		}
		else {
			$group = array(
				'nogroup_cc' => array(
					'Group_ID' => 0,
					'Fields' => $new
				)
			);
			$listing = array_merge($group, $listing);
		}

		$rlSmarty -> assign('listing', $listing);
	}
	
	/**
	*
	* set cc var
	*
	**/
	function setCC()
	{
		$this -> cc = array(
			'NZ' => 'NZD',
			'CK' => 'NZD',
			'NU' => 'NZD',
			'PN' => 'NZD',
			'TK' => 'NZD',
			'AU' => 'AUD',
			'CX' => 'AUD',
			'CC' => 'AUD',
			'HM' => 'AUD',
			'KI' => 'AUD',
			'NR' => 'AUD',
			'NF' => 'AUD',
			'TV' => 'AUD',
			'AS' => 'EUR',
			'AD' => 'EUR',
			'AT' => 'EUR',
			'BE' => 'EUR',
			'FI' => 'EUR',
			'FR' => 'EUR',
			'GF' => 'EUR',
			'TF' => 'EUR',
			'DE' => 'EUR',
			'GR' => 'EUR',
			'GP' => 'EUR',
			'IE' => 'EUR',
			'IT' => 'EUR',
			'LU' => 'EUR',
			'MQ' => 'EUR',
			'YT' => 'EUR',
			'MC' => 'EUR',
			'NL' => 'EUR',
			'PT' => 'EUR',
			'RE' => 'EUR',
			'WS' => 'EUR',
			'SM' => 'EUR',
			'SI' => 'EUR',
			'ES' => 'EUR',
			'VA' => 'EUR',
			'GS' => 'GBP',
			'GB' => 'GBP',
			'JE' => 'GBP',
			'IO' => 'USD',
			'GU' => 'USD',
			'MH' => 'USD',
			'FM' => 'USD',
			'MP' => 'USD',
			'PW' => 'USD',
			'PR' => 'USD',
			'TC' => 'USD',
			'US' => 'USD',
			'UM' => 'USD',
			'VG' => 'USD',
			'VI' => 'USD',
			'HK' => 'HKD',
			'CA' => 'CAD',
			'JP' => 'JPY',
			'AF' => 'AFN',
			'AL' => 'ALL',
			'DZ' => 'DZD',
			'AI' => 'XCD',
			'AG' => 'XCD',
			'DM' => 'XCD',
			'GD' => 'XCD',
			'MS' => 'XCD',
			'KN' => 'XCD',
			'LC' => 'XCD',
			'VC' => 'XCD',
			'AR' => 'ARS',
			'AM' => 'AMD',
			'AW' => 'ANG',
			'AN' => 'ANG',
			'AZ' => 'AZN',
			'BS' => 'BSD',
			'BH' => 'BHD',
			'BD' => 'BDT',
			'BB' => 'BBD',
			'BY' => 'BYR',
			'BZ' => 'BZD',
			'BJ' => 'XOF',
			'BF' => 'XOF',
			'GW' => 'XOF',
			'CI' => 'XOF',
			'ML' => 'XOF',
			'NE' => 'XOF',
			'SN' => 'XOF',
			'TG' => 'XOF',
			'BM' => 'BMD',
			'BT' => 'INR',
			'IN' => 'INR',
			'BO' => 'BOB',
			'BW' => 'BWP',
			'BV' => 'NOK',
			'NO' => 'NOK',
			'SJ' => 'NOK',
			'BR' => 'BRL',
			'BN' => 'BND',
			'BG' => 'BGN',
			'BI' => 'BIF',
			'KH' => 'KHR',
			'CM' => 'XAF',
			'CF' => 'XAF',
			'TD' => 'XAF',
			'CG' => 'XAF',
			'GQ' => 'XAF',
			'GA' => 'XAF',
			'CV' => 'CVE',
			'KY' => 'KYD',
			'CL' => 'CLP',
			'CN' => 'CNY',
			'CO' => 'COP',
			'KM' => 'KMF',
			'CD' => 'CDF',
			'CR' => 'CRC',
			'HR' => 'HRK',
			'CU' => 'CUP',
			'CY' => 'CYP',
			'CZ' => 'CZK',
			'DK' => 'DKK',
			'FO' => 'DKK',
			'GL' => 'DKK',
			'DJ' => 'DJF',
			'DO' => 'DOP',
			'TP' => 'IDR',
			'ID' => 'IDR',
			'EC' => 'ECS',
			'EG' => 'EGP',
			'SV' => 'SVC',
			'ER' => 'ETB',
			'ET' => 'ETB',
			'EE' => 'EEK',
			'FK' => 'FKP',
			'FJ' => 'FJD',
			'PF' => 'XPF',
			'NC' => 'XPF',
			'WF' => 'XPF',
			'GM' => 'GMD',
			'GE' => 'GEL',
			'GI' => 'GIP',
			'GT' => 'GTQ',
			'GN' => 'GNF',
			'GY' => 'GYD',
			'HT' => 'HTG',
			'HN' => 'HNL',
			'HU' => 'HUF',
			'IS' => 'ISK',
			'IR' => 'IRR',
			'IQ' => 'IQD',
			'IL' => 'ILS',
			'JM' => 'JMD',
			'JO' => 'JOD',
			'KZ' => 'KZT',
			'KE' => 'KES',
			'KP' => 'KPW',
			'KR' => 'KRW',
			'KW' => 'KWD',
			'KG' => 'KGS',
			'LA' => 'LAK',
			'LV' => 'LVL',
			'LB' => 'LBP',
			'LS' => 'LSL',
			'LR' => 'LRD',
			'LY' => 'LYD',
			'LI' => 'CHF',
			'CH' => 'CHF',
			'LT' => 'LTL',
			'MO' => 'MOP',
			'MK' => 'MKD',
			'MG' => 'MGA',
			'MW' => 'MWK',
			'MY' => 'MYR',
			'MV' => 'MVR',
			'MT' => 'MTL',
			'MR' => 'MRO',
			'MU' => 'MUR',
			'MX' => 'MXN',
			'MD' => 'MDL',
			'MN' => 'MNT',
			'MA' => 'MAD',
			'EH' => 'MAD',
			'MZ' => 'MZN',
			'MM' => 'MMK',
			'NA' => 'NAD',
			'NP' => 'NPR',
			'NI' => 'NIO',
			'NG' => 'NGN',
			'OM' => 'OMR',
			'PK' => 'PKR',
			'PA' => 'PAB',
			'PG' => 'PGK',
			'PY' => 'PYG',
			'PE' => 'PEN',
			'PH' => 'PHP',
			'PL' => 'PLN',
			'QA' => 'QAR',
			'RO' => 'RON',
			'RU' => 'RUB',
			'RW' => 'RWF',
			'ST' => 'STD',
			'SA' => 'SAR',
			'SC' => 'SCR',
			'SL' => 'SLL',
			'SG' => 'SGD',
			'SK' => 'SKK',
			'SB' => 'SBD',
			'SO' => 'SOS',
			'ZA' => 'ZAR',
			'LK' => 'LKR',
			'SD' => 'SDG',
			'SR' => 'SRD',
			'SZ' => 'SZL',
			'SE' => 'SEK',
			'SY' => 'SYP',
			'TW' => 'TWD',
			'TJ' => 'TJS',
			'TZ' => 'TZS',
			'TH' => 'THB',
			'TO' => 'TOP',
			'TT' => 'TTD',
			'TN' => 'TND',
			'TR' => 'TRY',
			'TM' => 'TMT',
			'UG' => 'UGX',
			'UA' => 'UAH',
			'AE' => 'AED',
			'UY' => 'UYU',
			'UZ' => 'UZS',
			'VU' => 'VUV',
			'VE' => 'VEF',
			'VN' => 'VND',
			'YE' => 'YER',
			'ZM' => 'ZMK',
			'ZW' => 'ZWD'
		);
	}
}

/**
* convert HTML entities back to characters
*
* @param string $string - reqested string
* @return converted characters
*
**/
function flHtmlEntriesDecode($string = false)
{
	return html_entity_decode($string, null, 'utf-8');
}