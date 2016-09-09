<?php
/***copyright**/
	
header("Content-Type: text/html; charset=UTF-8");

require_once( '../../includes/config.inc.php' );

/* system controller */
require_once( RL_INC . 'control.inc.php' );
                        
/* system libs */
require_once( RL_LIBS . 'system.lib.php' );
       
$reefless -> loadClass( 'Mail' );
$reefless -> loadClass( 'Account' );
$reefless -> loadClass( 'Listings' );                     
$reefless -> loadClass( 'Cache' );

/* load system configurations */
$config = $rlConfig -> allConfig();
$GLOBALS['config'] = $config;

/* get system languages */
$lang = $rlLang -> getLangBySide( 'frontEnd', $config['lang'] );
$GLOBALS['lang'] = &$lang;

define( 'RL_LANG_CODE', $config['lang'] );

/* get page paths */
$pages_tmp = $rlDb -> fetch(array( 'Key', 'Path'), array( 'Status' => 'active' ), '', false, 'pages' );

foreach ( $pages_tmp as $page_tmp )
{
	$pages[$page_tmp['Key']] = $page_tmp['Path'];
}
unset( $pages_tmp );

$reefless -> loadClass( 'ShoppingCart', null, 'shoppingCart' );
$reefless -> loadClass( 'Auction', null, 'shoppingCart' );

$GLOBALS['rlAuction'] -> closeExipredItems();

if ( $GLOBALS['config']['shc_auto_rate'] && $GLOBALS['config']['shc_auto_rate_period'] )
{
	$GLOBALS['rlAuction'] -> setAutomaticallyRate();
}

$GLOBALS['rlShoppingCart'] -> loadCurrencyRates();

echo 'Cron jobs was finished successfully!';
exit;