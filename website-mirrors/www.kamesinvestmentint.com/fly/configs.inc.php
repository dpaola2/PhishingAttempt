<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: CONFIGS.INC.PHP
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

$reefless -> loadClass( 'Account' );
$reefless -> loadClass( 'Actions' );
$reefless -> loadClass( 'Notice' );
$reefless -> loadClass( 'ShoppingCart', null, 'shoppingCart' );
$reefless -> loadClass( 'UPS', null, 'shoppingCart' );  

/* get available listing groups */
$rlDb -> setTable('listing_groups');
$groups = $rlDb -> fetch( array( 'ID', 'Key' ), array( 'Status' => 'active' ) );
$groups = $rlLang -> replaceLangKeys( $groups, 'listing_groups', array( 'name' ), RL_LANG_CODE, 'admin' );

/* get listing fields */
$listing_fields = $rlDb -> fetch( array( 'Key', 'ID', 'Type' ), array( 'Type' => 'price', 'Status' => 'active' ), null, 1, 'listing_fields' ); 
$listing_fields = $rlLang -> replaceLangKeys( $listing_fields, 'listing_fields', array( 'name' ), RL_LANG_CODE, 'common' );

$rlSmarty -> assign_by_ref( 'shc_countries', $rlCategories -> getDF( 'countries' ) );
$rlShoppingCart -> getShippingMethods( true );

$rlSmarty -> assign_by_ref( 'groups', $groups );
$rlSmarty -> assign_by_ref( 'listing_fields', $listing_fields ); 

/* init ups data */
$rlUPS -> outputStaticData();

$account_types = $rlAccount -> getAccountTypes();
$rlSmarty -> assign_by_ref('account_types', $account_types);

$bcAStep[] = array( 'name' => $lang['shc_configs'] );

if($_POST['form'] == 'submit')
{
	$configs = $_POST['config'];

	foreach($configs as $cKey => $cVal) 
	{
		$update[] = array(
			'fields' => array(
				'Default' => is_array($cVal) ? implode(",", $cVal) : $cVal
			),
			'where' => array(
				'Key' => $cKey
			)
		);
	}
	
	$action = $rlActions -> update( $update, 'config' );
	
	if ( $action )
	{   
		$aUrl = array('controller' => $controller, 'module' => 'configs');

		$reefless -> loadClass('Notice');
		$rlNotice -> saveNotice($GLOBALS['lang']['shc_settings_saved']);
		$reefless -> redirect($aUrl);
	}
}                                                            

$rlXajax -> registerFunction( array( 'saveConfigs', $rlShoppingCart, 'ajaxSaveConfigs' ) );