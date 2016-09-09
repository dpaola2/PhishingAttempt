<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: REQUEST.PHP
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

/* load configs */
include_once( dirname(__FILE__) . "/../../includes/config.inc.php");

/* system controller */
require_once( RL_INC . 'control.inc.php' );

$mode = $_GET['mode'];

if ( $mode == 'wf' )
{
	$woeid = $_GET['w'];
	$units = $_GET['u'];
	$url = "http://weather.yahooapis.com/forecastrss?w={$woeid}&u={$units}";
	
	if ( $woeid && $units )
	{
		$content = $reefless -> getPageContent($url);
		
		if ( $content )
		{
			$reefless -> loadClass('WeatherForecast', null, 'weatherForecast');
			
			/* parse location */
			$location = $rlWeatherForecast -> parse($content, 'yweather:location', true, array('city', 'region', 'country'));
			$response['location'] = $location;
			
			/* parse condition */
			$location = $rlWeatherForecast -> parse($content, 'yweather:condition', true, array('text', 'code', 'temp', 'date'));
			$response['condition'] = $location;
			
			/* parse forecast */
			$location = $rlWeatherForecast -> parse($content, 'yweather:forecast', true, array('day', 'date', 'low', 'high', 'text', 'code'));
			$response['forecast'] = $location;
	
			$reefless -> loadClass( 'Json' );
		
			$output['total'] = count($response);
			$output['data'] = $response;
		
			echo $rlJson -> encode( $output );
		}
	}
	else
	{
		echo false;
	}
}
elseif ( $mode == 'query' )
{
	$query = $_GET['query'];
	$direct = (int)$_GET['direct'];

	if ( !empty($query) )
	{
		$query = urlencode($query);
		
		if ( $direct ) {
			$query = explode(",", $query);
			$url = "http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20geo.placefinder%20where%20text%3D%22{$query[0]}%2C{$query[1]}%22%20and%20gflags%3D%22R%22";
		}
		else {
			$url = "http://query.yahooapis.com/v1/public/yql?q=select%20woeid%20from%20geo.places%20where%20text%3D%22{$query}%22&format=xml";
		}
		$content = $reefless -> getPageContent($url);
		
		if ( $content )
		{
			$reefless -> loadClass('WeatherForecast', null, 'weatherForecast');
			
			/* parse forecast */
			$woeid = $rlWeatherForecast -> parse($content, 'woeid', false);
			$response['woeid'] = $woeid;
	
			$reefless -> loadClass( 'Json' );
		
			$output['total'] = count($response);
			$output['data'] = $response;
		
			echo $rlJson -> encode( $output );
		}
	}
}