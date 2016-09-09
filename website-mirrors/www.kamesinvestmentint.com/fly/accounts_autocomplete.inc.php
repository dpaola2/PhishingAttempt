<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.0
 *	LISENSE: http://www.flynax.com/license-agreement.html
 *	PRODUCT: General Classifieds
 *	
 *	FILE: ACCOUNTS_AUTOCOMPLETE.INC.PHP
 *
 *	This script is a commercial software and any kind of using it must be 
 *	coordinate with Flynax Owners Team and be agree to Flynax License Agreement
 *
 *	This block may not be removed from this file or any other files with out 
 *	permission of Flynax respective owners.
 *
 *	Copyrights Flynax Classifieds Software | 2012
 *	http://www.flynax.com/
 *
 ******************************************************************************/

/* system config */
require_once( '../../includes/config.inc.php' );
require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
	
/* load system lib */
require_once( RL_LIBS . 'system.lib.php' );

$str = $_GET['str'];
$fields = $_GET['add_id'] ? ', `ID`' : '';

$sql = "SELECT `Username` {$fields} FROM `" . RL_DBPREFIX . "accounts` WHERE `Username` REGEXP '^".$str."' AND `Status` ='active'";
$rlHook -> load('apPhpAccountsAutoCompleteSql');
$output = $rlDb -> getAll($sql);

$reefless -> loadClass( 'Json' );
echo $rlJson -> encode( $output );