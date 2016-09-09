<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: DB_MIGRATION.INC.PHP
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

// check settings
if ( empty( $config['dbMigration_hostname'] ) || empty( $config['dbMigration_port'] ) || empty( $config['dbMigration_username'] ) || empty( $config['dbMigration_db_name'] ) )
{
	$errors[] = $lang['dbMigration_configs_empty'];
}
else
{
	$reefless -> loadClass('DBMigration', false, 'dbMigration');

	// try connect to db
	if ( true !== $res = $rlDBMigration -> testConnection() )
	{
		$errors = $res;
	}

	if ( !is_readable( rtrim( $config['dbMigration_location_root_path'], '/' ) . RL_DS .'includes'. RL_DS .'config.inc.php' ) )
	{
		$errors[] = $lang['dbMigration_location_old_version_error'];
	}
}

if ( empty( $errors ) )
{
	$reefless -> loadClass('DBMigrationPlugins', false, 'dbMigration');
	
	foreach( $rlDBMigration -> logs as $key => $action )
	{
		$rlXajax -> registerFunction( array( "import_{$action['Module']}", $action['Plugin'] ? $rlDBMigrationPlugins : $rlDBMigration, "ajaxImport_{$action['Module']}" ) );
	}

	$rlXajax -> registerFunction( array( "import_lang_keys", $rlDBMigration, "ajaxImport_lang_keys" ) );
	$rlXajax -> registerFunction( array( "rebuildImportedDFParents", $rlDBMigration, "ajaxRebuildImportedDFParents" ) );
	$rlXajax -> registerFunction( array( "importListingPhotos", $rlDBMigration, "ajaxImportListingPhotos" ) );
	$rlXajax -> registerFunction( array( "importListingsVideo", $rlDBMigration, "ajaxImportListingsVideo" ) );

	$reefless -> loadClass( 'Controls', 'admin' );
	$rlXajax -> registerFunction( array( 'recountListings', $rlControls, 'ajaxRecountListings' ) );
}