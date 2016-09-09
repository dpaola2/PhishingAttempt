<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: WEATHERFORECAST.INC.PHP
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

$reefless -> loadClass('WeatherForecast', null, 'weatherForecast');
$rlXajax -> registerFunction( array( 'saveWoeid', $rlWeatherForecast, 'ajaxSaveWoeid' ) );
$rlXajax -> registerFunction( array( 'savePosition', $rlWeatherForecast, 'ajaxSavePosition' ) );
$rlXajax -> registerFunction( array( 'saveMapping', $rlWeatherForecast, 'ajaxSaveMapping' ) );

if ( !$_POST['xjxfun'] )
{
	/* get available listing groups */
	$rlDb -> setTable('listing_groups');
	$groups = $rlDb -> fetch(array('ID', 'Key'), array('Status' => 'active'));
	$groups = $rlLang -> replaceLangKeys( $groups, 'listing_groups', array( 'name' ), RL_LANG_CODE, 'admin' );
	
	$rlSmarty -> assign_by_ref('groups', $groups);
	
	/* get location fields */
	$rlDb -> setTable('listing_fields');
	$wFields = $rlDb -> fetch(array('ID', 'Key'), array('Status' => 'active', 'Map' => 1));
	$wFields = $rlLang -> replaceLangKeys($wFields, 'listing_fields', array('name'));

	$rlSmarty -> assign_by_ref('wFields', $wFields);
}