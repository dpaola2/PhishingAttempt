<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLDBMIGRATION.CLASS.PHP
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

class rlDBMigration extends reefless 
{
	/**
	* 
	**/
	var $limit = 1000;

	/**
	* 
	**/
	var $logs = array();

	/**
	* 
	**/
	var $importTables = array( 
		'admins','transactions','languages','lang_keys','email_templates','data_formats','contacts',
		'news','accounts','account_fields','account_types','categories','listings','listing_types',
		'listing_plans','listing_fields','listing_photos','listing_video','short_forms','featured_form',
		'listing_relations','listing_groups','search_forms_relations','listing_titles','pages'
	);

	/**
	* 
	**/
	var $importPluginTables = array(
		'comment' => array('comments')
	);

	/**
	* Class constructor
	**/
	function rlDBMigration()
	{
		$this -> fetchLogs();
		$this -> loadClass('Actions');
	}

	/**
	* 
	**/
	function testConnection()
	{
		global $config, $lang;

		$errors = array();

		// try connect to db
		$link = mysql_connect( $config['dbMigration_hostname'] .":". $config['dbMigration_port'], $config['dbMigration_username'], $config['dbMigration_password'] );
		if ( !$link )
		{
			array_push( $errors, $lang['dbMigration_connection_error'] );
		}

		// try select db
		$db = mysql_select_db( $config['dbMigration_db_name'], $link );
		if ( !$db )
		{
			array_push( $errors, $lang['dbMigration_select_db_error'] );
		}

		if ( empty( $errors ) )
		{
			// close the connection and reconnect to main db
			mysql_close( $link );
			$this -> connect( RL_DBHOST, RL_DBPORT, RL_DBUSER, RL_DBPASS, RL_DBNAME );
		}

		return !empty( $errors ) ? $errors : true;
	}

	/**
	* 
	**/
	function fetchLogs()
	{
		global $config, $rlSmarty;

		$this -> setTable('db_import_modules');
		$tmpLogs = $this -> fetch( array( 'Module', 'Successful', 'Failed', 'Modify', 'Plugin' ) );
		$this -> resetTable();

		foreach( $tmpLogs as $key => $log )
		{
			$this -> logs[$log['Module']] = $tmpLogs[$key];
		}
		$rlSmarty -> assign_by_ref('actions', $this -> logs);

		// clear memory
		unset( $tmpLogs );
	}

	/**
	* 
	**/
	function updateTableView()
	{
		global $_response, $rlSmarty;

		// resend logs
		$this -> fetchLogs();

		// update modules table view
		$tpl = RL_PLUGINS .'dbMigration'. RL_DS .'admin'. RL_DS .'modules.tpl';
		$_response -> assign('import_modules_dom', 'innerHTML', $rlSmarty -> fetch($tpl, null, null, false));
	}

	/**
	* 
	**/
	function updateLog( $module = false, $successful = false, $failed = false )
	{
		$successful = (int)$successful;
		$failed = (int)$failed;

		$sql  = "UPDATE `". RL_DBPREFIX ."db_import_modules` SET `Successful` = '{$successful}', `Failed` = '{$failed}', `Modify` = UNIX_TIMESTAMP() ";
		$sql .= "WHERE `Module` = '{$module}' LIMIT 1";
		if ( $this -> query( $sql ) )
		{
			$this -> updateTableView();
		}
	}

	/**
	* 
	**/
	function addModule( $key = false )
	{
		global $rlActions, $rlXajax, $rlDBMigrationPlugins;

		$exists = $this -> getOne('ID', "`Module` = '{$key}'", 'db_import_modules');
		if ( empty( $exists ) )
		{
			$rlActions -> insertOne( array(
					'Module' => $key,
					'Date'   => time(),
					'Plugin' => 1
				), 'db_import_modules'
			);

			// alter tables
			if ( !empty( $this -> importPluginTables[$key] ) )
			{
				$this -> setAlters( $this -> importPluginTables[$key] );
			}

			// register function
			$rlXajax -> registerFunction( array( "import_{$key}", $rlDBMigrationPlugins, "ajaxImport_{$key}" ) );
		}
	}

	/**
	* 
	**/
	function checkDependency( $module = false, $dependency = false )
	{
		global $_response, $lang, $rlDBMigration;

		$depErr = '';
		$parse = explode( ',', $dependency );
		foreach( $parse as $key => $value )
		{
			if ( !$rlDBMigration -> logs[$value]['Modify'] )
			{
				$depErr .= "{$lang['dbMigration_module+title+'. $value]}, ";
			}
		}

		if ( !empty( $depErr ) )
		{
			$depErr = rtrim( $depErr, ', ' );
			$_response -> script("printMessage('error','dependency error: before do <b>{$depErr}</b>');");
			$_response -> script("$('#import_{$module}').val('{$lang['dbMigration_start_module']}');importLock = false;");
			return $_response;
		}
		return true;
	}

	/**
	* 
	**/
	function reConnect( $module = 'target' )
	{
		global $config;

		if ( $config['dbMigration_db_name'] != RL_DBNAME )
		{
			if ( $module == 'source' )
			{
				$this -> connect( $config['dbMigration_hostname'], $config['dbMigration_port'], $config['dbMigration_username'], $config['dbMigration_password'], $config['dbMigration_db_name'] );
			}
			else
			{
				$this -> connect( RL_DBHOST, RL_DBPORT, RL_DBUSER, RL_DBPASS, RL_DBNAME );
			}
		}
	}

	/**
	* 
	**/
	function importCompleteNotice( &$_response = false, $langPart = 'import_complete' )
	{
		global $lang;

		$message = $lang['dbMigration_'. $langPart .'_notice'];
		$_response -> script("printMessage('notice', '{$message}');importLock = false;");
	}

	/**
	* 
	**/
	function setAlters( $tables = false )
	{
		foreach( $tables as $key => $table )
		{
			// insert import source id
			$checkImportSourceID = $this -> getRow( "SHOW COLUMNS FROM `". RL_DBPREFIX ."{$table}` LIKE 'import_source_id'" );
			if ( empty( $checkImportSourceID ) )
			{
				$this -> query( "ALTER TABLE `". RL_DBPREFIX ."{$table}` ADD `import_source_id` INT( 11 ) NOT NULL" );
			}
			unset( $checkImportSourceID );

			// import identify field
			$importIdentify = $this -> getRow( "SHOW COLUMNS FROM `". RL_DBPREFIX ."{$table}` LIKE 'importIdentify'" );
			if ( empty( $importIdentify ) )
			{
				$this -> query( "ALTER TABLE `". RL_DBPREFIX ."{$table}` ADD `importIdentify` ENUM( '0', '1' ) NOT NULL DEFAULT '0'" );
			}
			unset( $importIdentify );
		}
	}

	/**
	* 
	**/
	function ajaxImport_check_update()
	{
		global $_response, $config, $lang;

		if ( $this -> logs['check_update']['Modify'] == 0 )
		{
			$this -> setAlters( $this -> importTables );
		}

		// save logs
		$this -> updateLog( 'check_update', 0, 0 );
		$this -> importCompleteNotice( $_response, 'check_update' );

		return $_response;
	}

	/**
	* 
	**/
	function ajaxImport_admins()
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('admins', 'check_update') )
		{
			return $res;
		}

		global $_response, $config, $lang, $rlValid, $rlSmarty;

		$this -> reConnect('source');
		$sql  = "SELECT `ID`, `User`, `Name`, `Pass`, `Email`, `Type`, `Rights`, `Status` ";
		$sql .= "FROM `{$config['dbMigration_table_prefix']}admins` LIMIT {$this -> limit}";
		$sourceAdmins = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourceAdmins ) )
		{
			$insertAllow = false;
			$successful = $failed = 0;
			$insert  = "INSERT INTO `". RL_DBPREFIX ."admins` ( `User`, `Name`, `Pass`, `Email`, `Type`, `Rights`, `Status`, `import_source_id`, `importIdentify` ) VALUES ";
			foreach( $sourceAdmins as $key => $entry )
			{
				$username = $rlValid -> xSql( $entry['User'] );
				$fullName = $rlValid -> xSql( $entry['Name'] );
				$exists = $this -> getRow( "SELECT `User`, `importIdentify` FROM `". RL_DBPREFIX ."admins` WHERE `User` = '{$username}' LIMIT 1" );

				if ( empty( $exists['User'] ) )
				{
					$successful++;
					$insert .= "( '{$username}', '{$fullName}', '{$entry['Pass']}', '{$entry['Email']}', '{$entry['Type']}', '{$entry['Rights']}', '{$entry['Status']}', '{$entry['ID']}', '1' ),";
					$insertAllow = true;
				}
				elseif( $exists['importIdentify'] != 0 )
				{
					$successful++;
				}
				else
				{
					if ( $exists['importIdentify'] == 0 )
					{
						$this -> query("UPDATE `". RL_DBPREFIX ."admins` SET `import_source_id` = '{$entry['ID']}' WHERE `User` = '{$entry['User']}' AND `importIdentify` = '1' LIMIT 1");
					}
					$failed++;
				}
			}

			// insert inport data
			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' ) .';';
				$this -> query( $insert );
			}

			// clear memory
			unset( $sourceAdmins, $insert );
		}

		// save logs
		$this -> updateLog( 'admins', $successful, $failed );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}

	/**
	* 
	**/
	function ajaxImport_languages( $updateExists = false )
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('languages', 'check_update') )
		{
			return $res;
		}

		global $_response, $config, $lang;

		$this -> reConnect('source');
		$sourceLanguages = $this -> getAll( "SELECT `ID`,`Code`,`Direction`,`Key`,`Status`,`Date_format` FROM `{$config['dbMigration_table_prefix']}languages` LIMIT {$this->limit}" );
		$this -> reConnect();

		$insertAllow = false;
		$successful = $failed = 0;
		$updateExists = (int)$updateExists;
		$insertLanguages = "INSERT INTO `". RL_DBPREFIX ."languages` ( `Code`,`Direction`,`Key`,`Status`,`Date_format`,`import_source_id`,`importIdentify` ) VALUES ";
		foreach( $sourceLanguages as $key => $entry )
		{
			$exists = $this -> getRow("SELECT `Code`, `importIdentify` FROM `". RL_DBPREFIX ."languages` WHERE `Code` = '{$entry['Code']}' LIMIT 1");
			if ( empty( $exists['Code'] ) )
			{
				$insertLanguages .= "( '{$entry['Code']}','{$entry['Direction']}','{$entry['Key']}','{$entry['Status']}','{$entry['Date_format']}','{$entry['ID']}','1' ),";
				$successful++;
				$insertAllow = true;
			}
			elseif ( $exists['importIdentify'] == 0 && $updateExists === 1 )
			{
				$sql  = "UPDATE `". RL_DBPREFIX ."languages` SET `Direction`='{$entry['Direction']}',`Status`='{$entry['Status']}',`Date_format`='{$entry['Date_format']}',`import_source_id`='{$entry['ID']}' ";
				$sql .= "WHERE `Key`='{$entry['Key']}' LIMIT 1";
				$this -> query( $sql );

				$successful++;
			}
			elseif ( $exists['importIdentify'] != 0 )
			{
				$successful++;
			}
			else
			{
				$failed++;
			}
		}
		if ( $insertAllow === true )
		{
			$insertLanguages = rtrim( $insertLanguages, ',' ) .';';
			$this -> query( $insertLanguages );
		}

		// clear memory
		unset( $sourceLanguages, $insertLanguages, $_SESSION['dbMigration_languages'] );

		// import lang_keys
		$_SESSION['dbMigration_languages'] = array( 'successful' => $successful, 'failed' => $failed );
		$_response -> script("xajax_import_lang_keys({$updateExists}, 0);");

		return $_response;
	}

	/**
	* 
	**/
	function ajaxImport_lang_keys( $updateExists = false, $start = false )
	{
		global $_response, $config, $lang, $rlValid;

		$start = (int)$start;
		$updateExists = (int)$updateExists;

		$this -> reConnect('source');
		$sql = "SELECT `ID`,`Code`,`Module`,`Key`,`Value`,`Plugin`,`Status` FROM `{$config['dbMigration_table_prefix']}lang_keys` LIMIT {$start},{$this->limit}";
		$sourceLangKeys = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourceLangKeys ) )
		{
			$insertAllow = false;
			$insert = "INSERT INTO `". RL_DBPREFIX ."lang_keys` ( `Code`,`Module`,`Key`,`Value`,`Plugin`,`Status`,`import_source_id`, `importIdentify` ) VALUES ";
			foreach( $sourceLangKeys as $key => $entry )
			{
				$value = $rlValid -> xSql( $entry['Value'] );
				$exists = $this -> getRow( "SELECT `Key`,`importIdentify` FROM `". RL_DBPREFIX ."lang_keys` WHERE `Code`='{$entry['Code']}' AND `Key`='{$entry['Key']}'" );
				if ( empty( $exists['Key'] ) )
				{
					$insert .= "( '{$entry['Code']}','{$entry['Module']}','{$entry['Key']}','{$value}','{$entry['Plugin']}','{$entry['Status']}','{$entry['ID']}','1' ),";

					unset( $value );
					$insertAllow = true;
				}
				elseif ( $exists['importIdentify'] == 0 && $updateExists === 1 )
				{
					$sql  = "UPDATE `". RL_DBPREFIX ."lang_keys` SET `Value`='{$value}',`Status`='{$entry['Status']}',`import_source_id`='{$entry['ID']}' ";
					$sql .= "WHERE `Key`='{$entry['Key']}' AND `Code`='{$entry['Code']}' LIMIT 1";
					$this -> query( $sql );
				}
			}
			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' ) .';';
				$this -> query( $insert );
			}

			// clear memory
			unset( $sourceLangKeys, $insert );

			// start import as recurcion
			$start += $this -> limit;
			$_response -> script("xajax_import_lang_keys({$updateExists}, {$start});");
			return $_response;
		}

		// save logs
		$this -> updateLog( 'languages', $_SESSION['dbMigration_languages']['successful'], $_SESSION['dbMigration_languages']['failed'] );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}

	/**
	* 
	**/
	function ajaxImport_email_templates( $updateExists = false )
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('email_templates', 'check_update,languages') )
		{
			return $res;
		}

		global $_response, $config, $lang;

		$this -> reConnect('source');
		$sql = "SELECT `ID`,`Key`,`Position`,`Plugin`,`Status` FROM `{$config['dbMigration_table_prefix']}email_templates`";
		$sourceEmailTemplates = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourceEmailTemplates ) )
		{
			$insertAllow = false;
			$successful = $failed = 0;
			$updateExists = (int)$updateExists;
			$insert = "INSERT INTO `". RL_DBPREFIX ."email_templates` ( `Key`,`Type`,`Position`,`Plugin`,`Status`,`import_source_id`,`importIdentify` ) VALUES ";
			foreach( $sourceEmailTemplates as $key => $entry )
			{
				$exists = $this -> getRow( "SELECT `Key`,`importIdentify` FROM `". RL_DBPREFIX ."email_templates` WHERE `Key` = '{$entry['Key']}'" );
				if ( empty( $exists['Key'] ) )
				{
					$insertAllow = true;
					$successful++;
					$insert .= "( '{$entry['Key']}','plain','{$entry['Position']}','{$entry['Plugin']}','{$entry['Status']}','{$entry['ID']}','1' ),";
				}
				elseif ( $exists['importIdentify'] == 0 && $updateExists === 1 )
				{
					$sql  = "UPDATE `". RL_DBPREFIX ."email_templates` SET `Position`='{$entry['Position']}',`Status`='{$entry['Status']}',`import_source_id`='{$entry['ID']}' ";
					$sql .= "WHERE `Key`='{$entry['Key']}' LIMIT 1";
					$this -> query( $sql );
					$successful++;
				}
				elseif ( $exists['importIdentify'] != 0 )
				{
					$successful++;
				}
				else
				{
					$failed++;
				}
				unset( $exists );
			}
			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' ) .';';
				$this -> query( $insert );
			}
		}

		// clear memory
		unset( $sourceEmailTemplates, $insert );

		// save logs
		$this -> updateLog( 'email_templates', $successful, $failed );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}

	/**
	* 
	**/
	function ajaxImport_accounts( $start = false, $successful = false, $failed = false )
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('accounts', 'check_update,languages') )
		{
			return $res;
		}

		global $_response, $config, $lang, $rlListingTypes, $rlValid;

		$start = (int)$start;
		$successful = (int)$successful;
		$failed = (int)$failed;

		if ( $start === 0 )
		{
			$this -> reConnect('source');
			$sql  = "SELECT `ID`,`Key`,`Position`,`Types_ID`,`Type`,`Default`,`Values`,`Condition`,`Required`,`Map`,`Search`,`Short_form`,`Status` ";
			$sql .= "FROM `{$config['dbMigration_table_prefix']}account_fields`";
			$sourceRegistrationFields = $this -> getAll( $sql );
			$this -> reConnect();

			if ( !empty( $sourceRegistrationFields ) )
			{
				$insertAllow = false;
				$alterFields = array();
				$insert = "INSERT INTO `". RL_DBPREFIX ."account_fields` ( `Key`,`Type`,`Default`,`Values`,`Condition`,`Required`,`Map`,`Status`,`import_source_id`,`importIdentify` ) VALUES ";
				foreach( $sourceRegistrationFields as $key => $entry )
				{
					$exists = $this -> getRow("SELECT `Key`,`importIdentify` FROM `". RL_DBPREFIX ."account_fields` WHERE `Key` = '{$entry['Key']}' LIMIT 1");
					if ( empty( $exists['Key'] ) )
					{
						$insert .= "( '{$entry['Key']}','{$entry['Type']}','{$entry['Default']}','{$entry['Values']}','{$entry['Condition']}',";
						$insert .= "'{$entry['Required']}','{$entry['Map']}','{$entry['Status']}','{$entry['ID']}','1' ),";
						$insertAllow = true;

						array_push( $alterFields, $entry['Key'] );
					}
				}

				if ( $insertAllow === true )
				{
					$insert = rtrim( $insert, ',' ) .';';
					if ( $this -> query( $insert ) )
					{
						// alter fields
						$this -> reConnect('source');
						$sourceAccountsTableFields = $this -> getAll("SHOW COLUMNS FROM `{$config['dbMigration_table_prefix']}accounts`");
						$this -> reConnect();

						foreach( $sourceAccountsTableFields as $key => $field )
						{
							if ( in_array( $field['Field'], $alterFields ) )
							{
								$fieldExists = $this -> getRow("SHOW COLUMNS FROM `". RL_DBPREFIX ."accounts` LIKE '{$field['Field']}'");
								if ( empty( $fieldExists ) )
								{
									$default = $field['Default'] ? "DEFAULT {$field['Default']}" : '';
									$this -> query("ALTER TABLE `". RL_DBPREFIX ."accounts` ADD `{$field['Field']}` {$field['Type']} NOT NULL {$default}");
								}
							}
						}
					}
				}

				// clear memory
				unset( $sourceRegistrationFields, $insert, $sourceAccountsTableFields );

				// import account types
				$this -> reConnect('source');
				$sql = "SELECT `ID`,`Key`,`Position`,`Abilities`,`Status` FROM `{$config['dbMigration_table_prefix']}account_types`";
				$sourceAccountTypes = $this -> getAll( $sql );
				$this -> reConnect();

				if ( !empty( $sourceAccountTypes ) )
				{
					$abilities = '';
					foreach( $rlListingTypes -> types as $tKey => $tEntry )
					{
						$abilities .= "{$tEntry['Key']},";
					}
					$abilities = rtrim( $abilities, ',' );

					$insertAllow = false;
					$insert = "INSERT INTO `". RL_DBPREFIX ."account_types` ( `Key`,`Position`,`Abilities`,`Page`,`Own_location`,`Status`,`import_source_id`,`importIdentify` ) VALUES ";
					foreach( $sourceAccountTypes as $key => $entry )
					{
						$exists = $this -> getRow("SELECT `Key`,`importIdentify` FROM `". RL_DBPREFIX ."account_types` WHERE `Key` = '{$entry['Key']}' LIMIT 1");
						if ( empty( $exists['Key'] ) )
						{
							$insertAllow = true;
							$extraAbilities = strpos( $entry['Abilities'], 'dealer_privilege' ) ? 1 : 0;
							$insert .= "( '{$entry['Key']}','{$entry['Position']}','{$abilities}','{$extraAbilities}','{$extraAbilities}','{$entry['Status']}','{$entry['ID']}','1' ),";
						}
					}

					if ( $insertAllow === true )
					{
						$insert = rtrim( $insert, ',' ) .';';
						$this -> query( $insert );
					}

					// clear memory
					unset( $sourceAccountTypes, $insert, $abilities );
				}
			}
		}

		// import accounts
		$this -> reConnect('source');
		$sourceAccounts = $this -> getAll("SELECT * FROM `{$config['dbMigration_table_prefix']}accounts` LIMIT {$start},{$this->limit}");
		$this -> reConnect();

		if ( !empty( $sourceAccounts ) )
		{
			$fieldsArray = array();
			$fieldsList = $this -> getAll("SHOW COLUMNS FROM `". RL_DBPREFIX ."accounts`");
			foreach( $fieldsList as $fKey => $fEntry )
			{
				if ( $fEntry['Field'] != 'ID' )
				{
					array_push( $fieldsArray, $fEntry['Field'] );
				}
			}
			unset( $fieldsList );

			$this -> reConnect('source');
			$filesFields = $this -> getAll("SELECT `Key` FROM `{$config['dbMigration_table_prefix']}account_fields` WHERE `Type`='file' OR `Type`='image' GROUP BY `Type`");
			$this -> reConnect();

			$fileImageKeys = array('Photo');
			foreach( $filesFields as $fiKey => $fiEntry )
			{
				array_push( $fileImageKeys, $fiEntry['Key'] );
			}
			unset( $filesFields );

			$insertAllow = false;
			$insert = "INSERT INTO `". RL_DBPREFIX ."accounts` ( `". implode( '`,`', $fieldsArray ) ."` ) VALUES ";
			foreach( $sourceAccounts as $key => $entry )
			{
				$exists = $this -> getRow("SELECT `Username`,`importIdentify` FROM `". RL_DBPREFIX ."accounts` WHERE `Username` = '{$entry['Username']}' LIMIT 1");
				if ( empty( $exists['Username'] ) )
				{
					$insert .= "(";
					foreach( $fieldsArray as $fKey => $fEntry )
					{
						$fieldValue = '';
						switch( $fEntry )
						{
							case 'import_source_id':
								$fieldValue = (int)$entry['ID'];
								break;
							case 'importIdentify':
								$fieldValue = 1;
								break;
							case 'Quick':
								$fieldValue = 0;
								break;
							case 'Own_address':
								loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
								$username = $entry['Username'];
								if ( !utf8_is_ascii( $username ) )
								{
									$username = utf8_to_ascii( $username );
								}
								$fieldValue = $rlValid -> str2path( $username );
								break;
							default:
								$fieldValue = $rlValid -> xSql( $entry[$fEntry] );
								break;
						}

						// copy files for types: [file/image] and field `Photo`
						if ( in_array( $fEntry, $fileImageKeys ) )
						{
							$source = rtrim( $config['dbMigration_location_root_path'], '/' ) . RL_DS .'files'. RL_DS . $entry[$fEntry];
							if ( !empty( $entry[$fEntry] ) && is_readable( $source ) )
							{
								$target = RL_FILES . $entry[$fEntry];
								if ( copy( $source, $target ) )
								{
									$fieldValue = $entry[$fEntry];
								}
							}
						}

						$insert .= "'{$fieldValue}',";
						unset( $fieldValue );
					}

					$insert = rtrim( $insert, ',' );
					$insert .= "),";

					$insertAllow = true;
					$successful++;
				}
				elseif ( $exists['importIdentify'] != 0 )
				{
					$successful++;
				}
				else
				{
					$this -> query("UPDATE `". RL_DBPREFIX ."accounts` SET `import_source_id` = '{$entry['ID']}' WHERE `Username` = '{$entry['Username']}' AND `importIdentify` = '0' LIMIT 1");
					$failed++;
				}
			}

			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' );
				$this -> query( $insert );
			}

			// clear memory
			unset( $sourceAccounts, $insert, $fieldsArray );

			// start import as recurcion
			$start += $this -> limit;
			$_response -> script("xajax_import_accounts({$start}, {$successful}, {$failed});");
			return $_response;
		}

		// save logs
		$this -> updateLog( 'accounts', $successful, $failed );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}

	/**
	* 
	**/
	function ajaxImport_data_formats( $updateExists = false, $start = false, $successful = false, $failed = false )
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('data_formats', 'check_update,languages') )
		{
			return $res;
		}

		global $_response, $config, $lang, $rlCache;

		$successful = (int)$successful;
		$failed = (int)$failed;
		$updateExists = (int)$updateExists;
		$start = (int)$start;

		$this -> reConnect('source');
		$sql = "SELECT `ID`,`Parent_ID`,`Position`,`Default`,`Key`,`Plugin`,`Status` FROM `{$config['dbMigration_table_prefix']}data_formats` LIMIT {$start},{$this->limit}";
		$sourceDataFormats = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourceDataFormats ) )
		{
			$insertAllow = false;
			$insert = "INSERT INTO `". RL_DBPREFIX ."data_formats` ( `Parent_ID`,`Order_type`,`Position`,`Key`,`Default`,`Plugin`,`Status`,`import_source_id`,`importIdentify` ) VALUES ";
			foreach( $sourceDataFormats as $key => $entry )
			{
				$exists = $this -> getRow( "SELECT `Key`,`importIdentify` FROM `". RL_DBPREFIX ."data_formats` WHERE `Key` = '{$entry['Key']}'" );
				if ( empty( $exists['Key'] ) )
				{
					$insertAllow = true;
					$insert .= "( '{$entry['Parent_ID']}','alphabetic','{$entry['Position']}','{$entry['Key']}','{$entry['Default']}','{$entry['Plugin']}','{$entry['Status']}','{$entry['ID']}','1' ),";
					$successful++;
				}
				elseif ( $exists['importIdentify'] == 0 && $updateExists === 1 )
				{
					$sql  = "UPDATE `". RL_DBPREFIX ."data_formats` SET `Position`='{$entry['Position']}',`Default`='{$entry['Default']}',`Status`='{$entry['Status']}',`import_source_id`='{$entry['ID']}' ";
					$sql .= "WHERE `Key`='{$entry['Key']}' LIMIT 1";
					$this -> query( $sql );
					$successful++; 
				}
				elseif ( $exists['importIdentify'] != 0 )
				{
					$successful++;
				}
				else
				{
					if ( $updateExists === 0 )
					{
						$this -> query( "UPDATE `". RL_DBPREFIX ."data_formats` SET `import_source_id`='{$entry['ID']}' WHERE `Key` = '{$entry['Key']}' LIMIT 1" );
					}
					$failed++;
				}
			}
			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' ) .';';
				$this -> query( $insert );
			}

			// clear memory
			unset( $sourceDataFormats, $insert );

			// start import as recurcion
			$start += $this -> limit;
			$_response -> script("xajax_import_data_formats({$updateExists}, {$start}, {$successful}, {$failed});");
			return $_response;
		}

		// rebuild parents
		$_response -> script("xajax_rebuildImportedDFParents();");

		// update cache
		$rlCache -> updateDataFormats();

		// save logs
		$this -> updateLog( 'data_formats', $successful, $failed );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}

	function ajaxRebuildImportedDFParents( $start = false )
	{
		global $_response;

		$start = (int)$start;

		$sql  = "SELECT `ID`,`Parent_ID` FROM `". RL_DBPREFIX ."data_formats` ";
		$sql .= "WHERE `Parent_ID` <> '0' AND `importIdentify` <> '0' LIMIT {$start},{$this->limit};";
		$importedDF = $this -> getAll( $sql );

		if ( !empty( $importedDF ) )
		{
			foreach( $importedDF as $key => $entry )
			{
				$newParent = $this -> getOne( 'ID', "`import_source_id` = '{$entry['Parent_ID']}'", 'data_formats' );
				$this -> query( "UPDATE `". RL_DBPREFIX ."data_formats` SET `Parent_ID` = '{$newParent}' WHERE `ID` = '{$entry['ID']}' LIMIT 1;" );
			}

			// clear memory
			unset( $importedDF );

			// start import as recurcion
			$start += $this -> limit;
			$_response -> script("xajax_rebuildImportedDFParents({$start});");
			return $_response;
		}

		return $_response;
	}

	/**
	* 
	**/
	function importListingTypes()
	{
		global $config, $lang, $rlValid, $rlActions, $rlCache, $rlListingTypes;

		// check categories for lType
		$this -> reConnect('source');
		$listingTypesInCategories = $this -> getAll("SELECT `Type` FROM `{$config['dbMigration_table_prefix']}categories` GROUP BY `Type`");
		$this -> reConnect();

		// import listing types
		$langKeys = array();
		$maxOrderListingType = $this -> getRow("SELECT MAX(`Order`) AS `max` FROM `". RL_DBPREFIX ."listing_types`");
		$maxPositionPages = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `". RL_DBPREFIX ."pages`");
		$maxPositionBlocks = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `". RL_DBPREFIX ."blocks`");

		$maxOrderListingType = (int)$maxOrderListingType['max'];
		$maxPositionPages = (int)$maxPositionPages['max'];
		$maxPositionBlocks = (int)$maxPositionBlocks['max'];

		foreach( $listingTypesInCategories as $key => $entry )
		{
			$f_key = $entry['Type'];
			$exists = $this -> getOne('Key', "`Key` = '{$f_key}'", 'listing_types');
			if ( empty( $exists ) )
			{
				$maxOrderListingType++;
				$catPosition = 'top';
				$advertising = $entry['Type'] == 'advertising' ? true : false;

				// write main listing type information
				$data = array(
					'Key' => $f_key,
					'Order' => $maxOrderListingType,
					'Page' => $advertising ? 0 : 1,
					'Photo' => $advertising ? 0 : 1,
					'Video' => $advertising ? 0 : 1,
					'Admin_only' => 0,
					'Cat_general_cat' => 0,
					'Cat_position' => $catPosition,
					'Cat_columns_number' => $advertising ? 0 : 3,
					'Cat_visible_number' => $advertising ? 0 : 15,
					'Cat_listing_counter' => $advertising ? 0 : 1,
					'Cat_postfix' => 0,
					'Cat_order_type' => 'position',
					'Cat_custom_adding' => 0,
					'Cat_show_subcats' => $advertising ? 0 : 1,
					'Cat_subcat_number' => $advertising ? 0 : 3,
					'Ablock_pages' => 0,
					'Ablock_position' => 0,
					'Ablock_columns_number' => 0,
					'Ablock_visible_number' => 0,
					'Ablock_show_subcats' => 0,
					'Ablock_subcat_number' => 0,
					'Search' => $advertising ? 0 : 1,
					'Search_home' => 0,
					'Search_page' => $advertising ? 0 : 1,
					'Advanced_search' => $advertising ? 0 : 1,
					'Search_display' => 'content_and_block',
					'Submit_method' => 'post',
					'Featured_blocks' => 1,
					'Random_featured' => $advertising ? 0 : 1,
					'Random_featured_type' => 'multi',
					'Random_featured_number' => $advertising ? 0 : 5,
					'Arrange_field' => 0,
					'Arrange_values' => '',
					'Arrange_search' => 0,
					'Arrange_featured' => 0,
					'Arrange_stats' => 0,
					'Search_multi_categories' => 0,
					'Search_multicat_levels' => 0,
					'Status' => 'active',
					'import_source_id' => 0,
					'importIdentify' => 1
				);

				$update_cache_key = $f_key;

				if ( $action = $rlActions -> insertOne( $data, 'listing_types' ) )
				{
					// add enum option to search form table
					$rlActions -> enumAdd('search_forms', 'Type', $f_key);
					$rlActions -> enumAdd('categories', 'Type', $f_key);
					$rlActions -> enumAdd('account_types', 'Abilities', $f_key);
					$rlActions -> enumAdd('saved_search', 'Listing_type', $f_key);

					// allow all account types to add listing to new listing type
					$this -> query("UPDATE `". RL_DBPREFIX ."account_types` SET `Abilities` = IF (LOCATE(',', `Abilities`) > 0, CONCAT(`Abilities`, ',{$f_key}'), '{$f_key}')");

					// write name's phrases
					$f_name = $rlValid -> xSql( $lang['l_type_'. $f_key] );
					foreach( $GLOBALS['languages'] as $lKey => $lCode )
					{
						array_push( $langKeys, array(
								'Code' => $lCode['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => "listing_types+name+{$f_key}",
								'Value' => $f_name,
								'importIdentify' => 1
							)
						);

						// individual page names
						array_push( $langKeys, array(
								'Code' => $lCode['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'pages+name+lt_'. $f_key,
								'Value' => $f_name,
								'importIdentify' => 1
							)
						);

						// individual page titles
						array_push( $langKeys, array(
								'Code' => $lCode['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'pages+title+lt_'. $f_key,
								'Value' => $f_name,
								'importIdentify' => 1
							)
						);

						// my listings page names
						array_push( $langKeys, array(
								'Code' => $lCode['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'pages+name+my_'. $f_key,
								'Value' => str_replace('{type}', $f_name, $lang['my_listings_pattern']),
								'importIdentify' => 1
							)
						);

						// my listings page titles
						array_push( $langKeys, array(
								'Code' => $lCode['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'pages+title+my_'. $f_key,
								'Value' => str_replace('{type}', $f_name, $lang['my_listings_pattern']),
								'importIdentify' => 1
							)
						);

						// featured listings block names
						array_push( $langKeys, array(
								'Code' => $lCode['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'blocks+name+ltfb_'. $f_key,
								'Value' => str_replace('{type}', $f_name, $lang['featured_block_pattern']),
								'importIdentify' => 1
							)
						);

						if ( !$advertising )
						{
							// category search form names
							array_push( $langKeys, array(
									'Code' => $lCode['Code'],
									'Module' => 'common',
									'Status' => 'active',
									'Key' => 'search_forms+name+'. $f_key .'_quick',
									'Value' => $f_name,
									'importIdentify' => 1
								)
							);

							// category search form names
							array_push( $langKeys, array(
									'Code' => $lCode['Code'],
									'Module' => 'common',
									'Status' => 'active',
									'Key' => 'search_forms+name+'. $f_key .'_advanced',
									'Value' => $f_name,
									'importIdentify' => 1
								)
							);

							// create search block names
							array_push( $langKeys, array(
									'Code' => $lCode['Code'],
									'Module' => 'common',
									'Status' => 'active',
									'Key' => 'blocks+name+ltsb_'. $f_key,
									'Value' => str_replace('{type}', $f_name, $lang['refine_search_pattern']),
									'importIdentify' => 1
								)
							);
						}
					}

					$page_id = false;
					if ( !$advertising )
					{
						// create individual page
						$maxPositionPages++;
						$individual_page = array(
							'Parent_ID' => 0,
							'Page_type' => 'system',
							'Login' => 0,
							'Key' => 'lt_'. $f_key,
							'Position' => $maxPositionPages,
							'Path' => $rlValid -> str2path($f_key),
							'Controller' => 'listing_type',
							'Tpl' => 1,
							'Menus' => 1,
							'Modified' => 'NOW()',
							'Status' => 'active',
							'Readonly' => 1
						);
						$rlActions -> insertOne( $individual_page, 'pages' );
						$page_id = mysql_insert_id();
						unset( $individual_page, $page_position );
					}

					// create my listings page
					$maxPositionPages++;
					$my_page = array(
						'Parent_ID' => 0,
						'Page_type' => 'system',
						'Login' => 1,
						'Key' => 'my_'. $f_key,
						'Position' => $maxPositionPages,
						'Path' => 'my-'. $rlValid -> str2path($f_key),
						'Controller' => 'my_listings',
						'Tpl' => 1,
						'Menus' => 2,
						'Modified' => 'NOW()',
						'Status' => 'active',
						'Readonly' => 1
					);
					$rlActions -> insertOne( $my_page, 'pages' );
					unset( $my_page, $page_position );

					// create featured block
					$maxPositionBlocks++;
					$featured_block = array(
						'Page_ID' => $page_id ? $page_id : 1,
						'Sticky' => 0,
						'Key' => 'ltfb_'. $f_key,
						'Position' => $maxPositionBlocks,
						'Side' => 'left',
						'Type' => 'smarty',
						'Content' => '{include file=\'blocks\'|cat:$smarty.const.RL_DS|cat:\'featured.tpl\' listings=$featured_'. $f_key .' type=\''. $f_key .'\'}',
						'Tpl' => 1,
						'Status' => 'active',
						'Readonly' => 1
					);
					$rlActions -> insertOne( $featured_block, 'blocks' );
					unset( $featured_block, $f_block_position );

					if ( !$advertising )
					{
						// create quick search form
						$search_form = array(
							'Key' => $f_key . '_quick',
							'Type' => $f_key,
							'Mode' => 'quick',
							'Groups' => 0,
							'Status' => 'active',
							'Readonly' => 1
						);
						$rlActions -> insertOne( $search_form, 'search_forms' );
						unset( $search_form );

						// create advanced search form
						$search_form = array(
							'Key' => $f_key . '_advanced',
							'Type' => $f_key,
							'Mode' => 'advanced',
							'Groups' => 1,
							'Status' => 'active',
							'Readonly' => 1
						);
						$rlActions -> insertOne( $search_form, 'search_forms' );
						unset( $search_form );

						// create search block
						$maxPositionBlocks++;
						$search_block = array(
							'Page_ID' => $page_id ? $page_id : 1,
							'Sticky' => 0,
							'Key' => 'ltsb_'. $f_key,
							'Position' => $maxPositionBlocks,
							'Side' => 'left',
							'Type' => 'smarty',
							'Content' => '{include file=$refine_block_controller}',
							'Tpl' => 1,
							'Status' => 'active',
							'Readonly' => 1
						);
						$rlActions -> insertOne( $search_block, 'blocks' );
						unset( $search_block, $s_block_position );
					}
				}

				// clear memory
				unset( $data );

				$rlListingTypes -> get();

				// update cache
				$rlCache -> updateCategories();
			}
		}

		// clear memory
		unset( $listingTypesInCategories );

		if ( !empty( $langKeys ) )
		{
			$rlActions -> insert( $langKeys, 'lang_keys' );
			unset( $langKeys );
		}
	}

	/**
	* 
	**/
	function ajaxImport_categories( $start = false, $successful = false, $failed = false )
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('categories', 'check_update,languages') )
		{
			return $res;
		}

		global $_response, $config, $lang, $rlValid, $rlCache;

		$start = (int)$start;
		$successful = (int)$successful;
		$failed = (int)$failed;

		// import listing types
		if ( $start === 0 && $this -> logs['categories']['Modified'] == 0 )
		{
			$this -> importListingTypes();
		}

		// import categories
		$this -> reConnect('source');
		$sql  = "SELECT `ID`,`Position`,`Path`,`Level`,`Tree`,`Section_ID`,`Parent_ID`,`Type`,`Key`,`Count`,`Lock`,`Add`,`Add_sub`,`Modified`,`Status` ";
		$sql .= "FROM `{$config['dbMigration_table_prefix']}categories` LIMIT {$start},{$this->limit}";
		$sourceCategories = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourceCategories ) )
		{
			$insertAllow = false;
			$insert  = "INSERT INTO `". RL_DBPREFIX ."categories` ( `Position`,`Path`,`Level`,`Tree`,`Parent_ID`,`Type`,`Key`,`Count`,";
			$insert .= "`Lock`,`Add`,`Add_sub`,`Modified`,`Status`,`import_source_id`,`importIdentify` ) VALUES ";

			foreach( $sourceCategories as $key => $entry )
			{
				$exists = $this -> getRow("SELECT `Key`,`importIdentify` FROM `". RL_DBPREFIX ."categories` WHERE `Key` = '{$entry['Key']}' LIMIT 1");
				if ( empty( $exists['Key'] ) )
				{
					$insertAllow = true;
					$insert .= "( '{$entry['Position']}','{$entry['Path']}','{$entry['Level']}','{$entry['Tree']}','{$entry['Parent_ID']}','{$entry['Type']}','{$entry['Key']}','0',";
					$insert .= "'{$entry['Lock']}','{$entry['Add']}','{$entry['Add_sub']}','{$entry['Modified']}','{$entry['Status']}','{$entry['ID']}','1' ),";
					$successful++;
				}
				elseif ( $exists['importIdentify'] != 0 )
				{
					$successful++;
				}
				else
				{
					$this -> query( "UPDATE `". RL_DBPREFIX ."categories` SET `import_source_id` = '{$entry['ID']}' WHERE `Key` = '{$entry['Key']}' AND `importIdentify` = '0' LIMIT 1" );
					$failed++;
				}
			}

			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' ) .';';
				$this -> query( $insert );
			}

			// clear memory
			unset( $sourceCategories );

			// rebuild parents
			if ( $this -> logs['categories']['Modified'] == 0 )
			{
				$this -> rebuildCategoryParents( $start );
			}

			// start import as recurcion
			$start += $this -> limit;
			$_response -> script("xajax_import_categories({$start}, {$successful}, {$failed});");
			return $_response;
		}

		// update general category
		$importedTypes = $this -> getAll("SELECT `Key` FROM `". RL_DBPREFIX ."listing_types` WHERE `importIdentify` = '1'");
		foreach( $importedTypes as $key => $entry )
		{
			$sql  = "UPDATE `". RL_DBPREFIX ."listing_types` SET `Cat_general_cat` = ( SELECT `ID` FROM `". RL_DBPREFIX ."categories` WHERE `Type` = '{$entry['Key']}' AND `Status` = 'active' LIMIT 1 ) ";
			$sql .= "WHERE `Key` = '{$entry['Key']}' LIMIT 1";
			$this -> query( $sql );
		}
		
		// update cache
		$rlCache -> updateCategories();
		
		// save logs
		$this -> updateLog( 'categories', $successful, $failed );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}

	/**
	* 
	**/
	function rebuildCategoryParents( $start = false )
	{
		$start = (int)$start;

		$sql  = "SELECT `ID`,`Parent_ID` FROM `". RL_DBPREFIX ."categories` ";
		$sql .= "WHERE `Parent_ID` <> '0' AND `importIdentify` <> '0' LIMIT {$start},{$this->limit}";
		$categories = $this -> getAll( $sql );

		if ( !empty( $categories ) )
		{
			foreach( $categories as $key => $entry )
			{
				$newParent = $this -> getOne( 'ID', "`import_source_id`='{$entry['Parent_ID']}'", 'categories' );
				$this -> query( "UPDATE `". RL_DBPREFIX ."categories` SET `Parent_ID`='{$newParent}' WHERE `ID`='{$entry['ID']}' LIMIT 1" );
			}

			// clear memory
			unset( $categories );
		}
	}

	/**
	* 
	**/
	function importListingPlans()
	{
		global $config, $lang;

		$this -> reConnect('source');
		$sql  = "SELECT `ID`,`Key`,`Type_ID`,`Position`,`Type`,`Sticky`,`Kind_ID`,`Subcategories`,`Limit`,`Price`,`Days`,`Image`,`Video`,`Listing_number`,`Cross`,`Status` ";
		$sql .= "FROM `{$config['dbMigration_table_prefix']}listing_plans`";
		$sourcePlans = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourcePlans ) )
		{
			$insertAllow = false;
			$insert  = "INSERT INTO `". RL_DBPREFIX ."listing_plans` ( `Key`,`Position`,`Type`,`Allow_for`,`Sticky`,`Category_ID`,`Subcategories`,`Featured`,`Advanced_mode`,";
			$insert .= "`Standard_listings`,`Featured_listings`,`Color`,`Limit`,`Price`,`Listing_period`,`Plan_period`,`Image`,`Image_unlim`,`Video`,`Video_unlim`,`Listing_number`,";
			$insert .= "`Cross`,`Status`,`import_source_id`,`importIdentify` ) VALUES ";

			foreach( $sourcePlans as $key => $entry )
			{
				$exists = $this -> getRow("SELECT `Key`,`importIdentify` FROM `". RL_DBPREFIX ."listing_plans` WHERE `Key` = '{$entry['Key']}' LIMIT 1");
				if ( empty( $exists['Key'] ) )
				{
					$insertAllow = true;

					$insert .= "( '{$entry['Key']}','{$entry['Position']}','{$entry['Type']}','','1','','{$entry['Subcategories']}','0','0',";
					$insert .= "'0','0','','{$entry['Limit']}','{$entry['Price']}','{$entry['Days']}','{$entry['Days']}','{$entry['Image']}','0','{$entry['Video']}','0',";
					$insert .= "'{$entry['Listing_number']}','{$entry['Cross']}','{$entry['Status']}','{$entry['ID']}','1' ),";
				}
				else
				{
					if ( $exists['importIdentify'] == 0 )
					{
						$this -> query("UPDATE `". RL_DBPREFIX ."listing_plans` SET `import_source_id` = '{$entry['ID']}' WHERE `Key` = '{$entry['Key']}' AND `importIdentify` = '0' LIMIT 1");
					}
				}
			}

			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' ) .';';
				$this -> query( $insert );
			}

			// clear memory
			unset( $sourcePlans, $insert );
		}
	}

	/**
	* 
	**/
	function importListingFields()
	{
		global $config, $lang;

		$this -> reConnect('source');
		$sql = "SELECT `ID`,`Key`,`Type`,`Default`,`Values`,`Condition`,`Details_page`,`Add_page`,`Required`,`Map`,`Status` FROM `{$config['dbMigration_table_prefix']}listing_fields`";
		$sourceFields = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourceFields ) )
		{
			$insertAllow = false;
			$alterFields = array();
			$insert  = "INSERT INTO `". RL_DBPREFIX ."listing_fields` ( `Key`,`Type`,`Default`,`Values`,`Condition`,`Multilingual`,`Details_page`,`Add_page`,`Required`,`Map`,";
			$insert .= "`Opt1`,`Opt2`,`Status`,`Readonly`,`import_source_id`,`importIdentify` ) VALUES ";

			foreach( $sourceFields as $key => $entry )
			{
				// skip Kind_ID field
				if ( $entry['Key'] == 'Kind_ID' ) continue;

				$exists = $this -> getRow("SELECT `Key`,`importIdentify` FROM `". RL_DBPREFIX ."listing_fields` WHERE `Key` = '{$entry['Key']}' LIMIT 1");
				if ( empty( $exists['Key'] ) )
				{
					$insertAllow = true;
					$type = $entry['Type'] != 'unit' ? $entry['Type'] : 'mixed';
					$insert .= "( '{$entry['Key']}','{$type}','{$entry['Default']}','{$entry['Values']}','{$entry['Condition']}','0','{$entry['Details_page']}',";
					$insert .= "'{$entry['Add_page']}','{$entry['Required']}','{$entry['Map']}','0','0','{$entry['Status']}','0','{$entry['ID']}','1' ),";

					array_push( $alterFields, $entry['Key'] );

					// alter multi date
					if ( $entry['Type'] == 'date' && $entry['Default'] == 'multi' )
					{
						array_push( $alterFields, "{$entry['Key']}_multi" );
					}
				}
				else
				{
					if ( $exists['importIdentify'] == 0 )
					{
						$this -> query("UPDATE `". RL_DBPREFIX ."listing_fields` SET `import_source_id` = '{$entry['ID']}' WHERE `Key` = '{$entry['Key']}' LIMIT 1");
					}
				}
			}

			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' ) .';';
				if ( $this -> query( $insert ) )
				{
					// alter fields
					$this -> reConnect('source');
					$sourceListingTableFields = $this -> getAll("SHOW COLUMNS FROM `{$config['dbMigration_table_prefix']}listings`");
					$this -> reConnect();

					foreach( $sourceListingTableFields as $key => $field )
					{
						if ( in_array( $field['Field'], $alterFields ) )
						{
							$fieldExists = $this -> getRow("SHOW COLUMNS FROM `". RL_DBPREFIX ."listings` LIKE '{$field['Field']}'");
							if ( empty( $fieldExists ) )
							{
								$default = $field['Default'] ? "DEFAULT {$field['Default']}" : '';
								$this -> query("ALTER TABLE `". RL_DBPREFIX ."listings` ADD `{$field['Field']}` {$field['Type']} NOT NULL {$default}");
							}
						}
					}

					// clear memory
					unset( $sourceListingTableFields, $insert );
				}
			}

			// clear memory
			unset( $sourceFields );

			// update
			$this -> updateAllForms();
		}
	}

	/**
	* 
	**/
	function importListingGroups()
	{
		global $config;

		$this -> reConnect('source');
		$sourceGroups = $this -> getAll("SELECT `ID`,`Key`,`Display`,`Status` FROM `{$config['dbMigration_table_prefix']}listing_groups`");
		$this -> reConnect();

		if ( !empty( $sourceGroups ) )
		{
			$insertAllow = false;
			$insert = "INSERT INTO `". RL_DBPREFIX ."listing_groups` ( `Key`,`Display`,`Status`,`import_source_id`,`importIdentify` ) VALUES ";
			foreach( $sourceGroups as $key => $entry )
			{
				$exists = $this -> getOne('ID', "`Key` = '{$entry['Key']}'", 'listing_groups');
				if ( empty( $exists ) )
				{
					$insert .= "( '{$entry['Key']}','{$entry['Display']}','{$entry['Status']}','{$entry['ID']}','1' ),";
					$insertAllow = true;
				}
			}

			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' ) .';';
				$this -> query( $insert );
			}

			// clear memory
			unset( $sourceGroups, $insert );
		}
	}

	/**
	* 
	**/
	function updateSearchForms()
	{
		global $config;

		$this -> reConnect('source');
		$sourceSearchForms = $this -> getAll("SELECT `ID`,`Key`,`Type`,`Groups` FROM `{$config['dbMigration_table_prefix']}search_forms` WHERE `Status` = 'active'");
		$this -> reConnect();

		if ( !empty( $sourceSearchForms ) )
		{
			// only imported listing types
			$lTypes = $this -> getAll("SELECT `Key` FROM `". RL_DBPREFIX ."listing_types` WHERE `importIdentify` = '1'");
			$lTypesAdapted = array();
			foreach( $lTypes as $key => $type )
			{
				array_push( $lTypesAdapted, $type['Key'] );
			}
			unset( $lTypes );

			foreach( $sourceSearchForms as $sfKey => $sfEntry )
			{
				if ( !in_array( $sfEntry['Type'], $lTypesAdapted ) || $sfEntry['Type'] == 'advertising' ) continue;

				$this -> reConnect('source');
				$sql = "SELECT `ID`,`Position`,`Kind_ID`,`Group_ID`,`Fields` FROM `{$config['dbMigration_table_prefix']}search_forms_relations` WHERE `Kind_ID` = '{$sfEntry['ID']}'";
				$sourceRelations = $this -> getAll( $sql );
				$this -> reConnect();

				if ( !empty( $sourceRelations ) )
				{
					$insertAllow = false;
					$insert = "INSERT INTO `". RL_DBPREFIX ."search_forms_relations` ( `Position`,`Category_ID`,`Group_ID`,`Fields`,`import_source_id`,`importIdentify` ) VALUES ";
					foreach( $sourceRelations as $srKey => $srEntry )
					{
						$exists = $this -> getOne('ID', "`import_source_id` = '{$srEntry['ID']}'", 'search_forms_relations');
						if ( empty( $exists ) )
						{
							$fieldsIDs = array();
							$fPrefix = strpos( $sfEntry['Key'], 'quick' ) !== false ? 'quick' : 'advanced';
							$categoryID = (int)$this -> getOne('ID', "`Key` = '{$sfEntry['Type']}_{$fPrefix}'", 'search_forms');
							$groupID = (int)$this -> getOne('ID', "`import_source_id` = '{$srEntry['Group_ID']}'", 'listing_groups');
							$sourceFields = rtrim( $srEntry['Fields'], ',' );
							$parseFields = explode(',', $sourceFields);

							foreach( $parseFields as $field )
							{
								$newID = (int)$this -> getOne('ID', "`import_source_id` = '{$field}'", 'listing_fields');
								if ( $newID !== 0 )
								{
									array_push( $fieldsIDs, $newID );
								}
							}

							if ( empty( $fieldsIDs ) ) continue;

							$insert .= "( '{$srEntry['Position']}','{$categoryID}','{$groupID}','". implode(',', $fieldsIDs) ."','{$srEntry['ID']}','1' ),";
							$insertAllow = true;
						}
					}

					if ( $insertAllow === true )
					{
						$insert = rtrim( $insert, ',' ) .';';
						$this -> query( $insert );
					}

					// clear memory
					unset( $sourceRelations, $insert );
				}
			}

			// clear memory
			unset( $sourceSearchForms, $lTypesAdapted );
		}
	}

	/**
	* 
	**/
	function updateAllForms()
	{
		global $config, $rlCache;

		// update short_forms & featured_forms & listing_titles
		$forms = array('short_forms', 'featured_form', 'listing_titles');
		foreach( $forms as $table )
		{
			$this -> reConnect('source');
			$sourceForm = $this -> getAll("SELECT `ID`,`Position`,`Kind_ID`,`Field_ID` FROM `{$config['dbMigration_table_prefix']}{$table}`");
			$this -> reConnect();

			if ( !empty( $sourceForm ) )
			{
				$insertAllow = false;
				$insert = "INSERT INTO `". RL_DBPREFIX ."{$table}` ( `Position`,`Category_ID`,`Field_ID`,`import_source_id`,`importIdentify` ) VALUES ";
				foreach( $sourceForm as $key => $entry )
				{
					$exists = $this -> getOne('ID', "`import_source_id` = '{$entry['ID']}'", $table);
					if ( empty( $exists ) )
					{
						$categoryID = (int)$this -> getOne('ID', "`import_source_id` = '{$entry['Kind_ID']}'", 'categories');
						$FieldID = (int)$this -> getOne('ID', "`import_source_id` = '{$entry['Field_ID']}'", 'listing_fields');

						$insert .= "( '{$entry['Position']}','{$categoryID}','{$FieldID}','{$entry['ID']}','1' ),";
						$insertAllow = true;
					}
				}

				if ( $insertAllow === true )
				{
					$insert = rtrim( $insert, ',' ) .';';
					$this -> query( $insert );
				}

				// clear memory
				unset( $sourceShortForms, $insert );
			}
		}

		// import listing groups
		$this -> importListingGroups();

		// update listing_relations
		$this -> reConnect('source');
		$sourceRelations = $this -> getAll("SELECT `ID`,`Position`,`Kind_ID`,`Group_ID`,`Fields` FROM `{$config['dbMigration_table_prefix']}listing_relations`");
		$this -> reConnect();

		if ( !empty( $sourceRelations ) )
		{
			$insertAllow = false;
			$insert = "INSERT INTO `". RL_DBPREFIX ."listing_relations` ( `Position`,`Category_ID`,`Group_ID`,`Fields`,`import_source_id`,`importIdentify` ) VALUES ";
			foreach( $sourceRelations as $key => $entry )
			{
				$exists = $this -> getOne('ID', "`import_source_id` = '{$entry['ID']}'", 'listing_relations');
				if ( empty( $exists ) )
				{
					$fieldsIDs = array();
					$categoryID = (int)$this -> getOne('ID', "`import_source_id` = '{$entry['Kind_ID']}'", 'categories');
					$groupID = (int)$this -> getOne('ID', "`import_source_id` = '{$entry['Group_ID']}'", 'listing_groups');
					$sourceFields = rtrim( $entry['Fields'], ',' );
					$parseFields = explode(',', $sourceFields);

					foreach( $parseFields as $field )
					{
						$newID = (int)$this -> getOne('ID', "`import_source_id` = '{$field}'", 'listing_fields');
						if ( $newID !== 0 )
						{
							array_push( $fieldsIDs, $newID );
						}
					}

					if ( empty( $fieldsIDs ) ) continue;

					$insert .= "( '{$entry['Position']}','{$categoryID}','{$groupID}','". implode(',', $fieldsIDs) ."','{$entry['ID']}','1' ),";
					$insertAllow = true;

					// clear memory
					unset( $fieldsIDs, $sourceFields, $parseFields );
				}
			}

			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' ) .';';
				$this -> query( $insert );
			}

			// clear memory
			unset( $sourceRelations, $insert );
		}

		// update search_forms_relations
		$this -> updateSearchForms();

		// update cache
		$rlCache -> updateForms();
	}

	/**
	* 
	**/
	function ajaxImportListingsVideo( $start = false )
	{
		global $_response,$config, $rlValid, $rlActions;

		$start = (int)$start;

		$this -> reConnect('source');
		$sql  = "SELECT `T1`.`ID`,`T1`.`Listing_ID`,`T1`.`Type`,`T1`.`Video`,`T1`.`Preview`,`T1`.`Embed`,`T2`.`Date` FROM `{$config['dbMigration_table_prefix']}listing_video` AS `T1` ";
		$sql .= "LEFT JOIN `{$config['dbMigration_table_prefix']}listings` AS `T2` ON `T1`.`Listing_ID` = `T2`.`ID` ";
		$sql .= "WHERE NOT ISNULL(`T2`.`Date`) GROUP BY `T1`.`ID` LIMIT {$start},{$this->limit}";
		$sourceVideos = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourceVideos ) )
		{
			$youtubeVideo = array();
			foreach( $sourceVideos as $key => $entry )
			{
				$exists = $this -> getOne('ID', "`import_source_id` = '{$entry['ID']}'", 'listing_video');
				if ( empty( $exists ) )
				{
					$position = $this -> getRow("SELECT MAX(`Position`) AS `max` FROM `". RL_DBPREFIX ."listing_video`");
					$position = (int)$position['max'] + 1;

					if ( $entry['Type'] == 'youtube' )
					{
						$source = $entry['Embed'];
						if ( !empty( $source ) )
						{
							// parse video key from url
							if ( 0 === strpos($source, 'http') )
							{
								// parse from short link
								if ( false !== strpos($source, 'youtu.be') )
								{
									$matches[1] = array_pop(explode('/', $source));
								}
								else
								{
									preg_match('/v=([\w-_]*)/', $source, $matches);
								}
							}
							else
							{
								// parse video key from tags
								preg_match('/src=".*v\/(.*)\?.*"/', $source, $matches);

								if ( !$matches[1] )
								{
									preg_match('/src=".*embed\/([\w\-]*)"/', $source, $matches);
								}
							}

							if ( $matches[1] )
							{
								array_push( $youtubeVideo, array(
										'import_source_id' => (int)$entry['ID'],
										'importIdentify' => 1,
										'Listing_ID' => (int)$this -> getOne('ID', "`import_source_id` = '{$entry['Listing_ID']}'", 'listings'),
										'Type' => 'youtube',
										'Video' => '',
										'Preview' => $matches[1],
										'Position' => $position
									)
								);
							}
						}
					}
					else
					{
						$sourceVideo = rtrim( $config['dbMigration_location_root_path'], '/' ) . RL_DS .'files'. RL_DS . $entry['Video'];
						$sourcePreview = rtrim( $config['dbMigration_location_root_path'], '/' ) . RL_DS .'files'. RL_DS . $entry['Preview'];

						if ( is_readable( $sourceVideo ) && is_readable( $sourcePreview ) )
						{
							// mkdir if not exists
							$parseDate = explode('-', $entry['Date']);
							$targetDate = "{$parseDate[1]}-{$parseDate[0]}";
							$targetDateFolder = RL_FILES . $targetDate;
							if ( !is_dir( $targetDateFolder ) )
							{
								mkdir( $targetDateFolder );
								chmod( $targetDateFolder, 0777 );
							}

							$newListingID = (int)$this -> getOne('ID', "`import_source_id` = '{$entry['Listing_ID']}'", 'listings');
							$targetAdFolder = $targetDateFolder . RL_DS ."ad{$newListingID}";
							if ( !is_dir( $targetAdFolder ) )
							{
								mkdir( $targetAdFolder );
								chmod( $targetAdFolder, 0777 );
							}

							$parseVideo = explode('.', $entry['Video']);
							$parsePreview = explode('.', $entry['Preview']);
							$storagePrefix = $targetDate . RL_DS ."ad{$newListingID}". RL_DS;

							$targetVideoName = 'video_'. mt_rand() . time() .'.'. $parseVideo[1];
							$targetVideo = $targetAdFolder . RL_DS . $targetVideoName;

							$targetPreviewName = 'preview_'. mt_rand() . time() .'.'. $parsePreview[1];
							$targetPreview = $targetAdFolder . RL_DS . $targetPreviewName;

							if ( copy( $sourceVideo, $targetVideo ) && copy( $sourcePreview, $targetPreview ) )
							{
								array_push( $youtubeVideo, array(
										'import_source_id' => (int)$entry['ID'],
										'importIdentify' => 1,
										'Listing_ID' => $newListingID,
										'Type' => 'local',
										'Video' => "{$targetDate}/ad{$newListingID}/{$targetVideoName}",
										'Preview' => "{$targetDate}/ad{$newListingID}/{$targetPreviewName}",
										'Position' => $position
									)
								);
							}
						}
					}
				}
			}

			if ( !empty( $youtubeVideo ) )
			{
				$rlActions -> insert( $youtubeVideo, 'listing_video' );
				unset( $youtubeVideo );
			}

			// clear memory
			unset( $sourceVideos );

			// start recurcion
			$start += $this -> limit;
			$_response -> script("xajax_importListingsVideo({$start});");
			return $_response;
		}

		return $_response;
	}

	/**
	* 
	**/
	function ajaxImportListingPhotos( $start = false )
	{
		global $_response,$config, $rlValid;

		$start = (int)$start;

		$this -> reConnect('source');
		$sql  = "SELECT `T1`.`ID`,`T1`.`Listing_ID`,`T1`.`Position`,`T1`.`Photo`,`T1`.`Thumbnail`,`T1`.`Original`,`T1`.`Description`,`T1`.`Type`,`T1`.`Status`,`T2`.`Date` ";
		$sql .= "FROM `{$config['dbMigration_table_prefix']}listing_photos` AS `T1` ";
		$sql .= "LEFT JOIN `{$config['dbMigration_table_prefix']}listings` AS `T2` ON `T1`.`Listing_ID` = `T2`.`ID` ";
		$sql .= "WHERE NOT ISNULL(`T2`.`Date`) GROUP BY `T1`.`ID` LIMIT {$start},{$this->limit}";
		$sourcePhotos = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourcePhotos ) )
		{
			foreach( $sourcePhotos as $key => $entry )
			{
				$sourcePhoto = rtrim( $config['dbMigration_location_root_path'], '/' ) . RL_DS .'files'. RL_DS . $entry['Photo'];
				$sourceThumbnail = rtrim( $config['dbMigration_location_root_path'], '/' ) . RL_DS .'files'. RL_DS . $entry['Thumbnail'];
				$sourceOriginal = rtrim( $config['dbMigration_location_root_path'], '/' ) . RL_DS .'files'. RL_DS . $entry['Original'];
				$original = $thumbnail = $photo = '';

				if ( is_readable( $sourcePhoto ) || is_readable( $sourceThumbnail ) || is_readable( $sourceOriginal ) )
				{
					// mkdir if not exists
					$parseDate = explode('-', $entry['Date']);
					$targetDate = "{$parseDate[1]}-{$parseDate[0]}";
					$targetDateFolder = RL_FILES . $targetDate;
					if ( !is_dir( $targetDateFolder ) )
					{
						mkdir( $targetDateFolder );
						chmod( $targetDateFolder, 0777 );
					}

					$newListingID = (int)$this -> getOne('ID', "`import_source_id` = '{$entry['Listing_ID']}'", 'listings');
					$targetAdFolder = $targetDateFolder . RL_DS ."ad{$newListingID}";
					if ( !is_dir( $targetAdFolder ) )
					{
						mkdir( $targetAdFolder );
						chmod( $targetAdFolder, 0777 );
					}

					// check readable and try copy
					$parse = explode('.', $entry['Original']);
					$filePrefixAndExp = mt_rand() . time() .'.'. $parse[1];
					$storagePrefix = $targetDate . RL_DS ."ad{$newListingID}". RL_DS;

					if ( is_readable( $sourcePhoto ) )
					{
						$targetPhotoName = 'large_'. $filePrefixAndExp;
						$targetPhoto = $targetAdFolder . RL_DS . $targetPhotoName;
						$photo = copy( $sourcePhoto, $targetPhoto ) ? "{$targetDate}/ad{$newListingID}/{$targetPhotoName}" : '';
					}

					if ( is_readable( $sourceThumbnail ) )
					{
						$targetThumbnailName = 'thumb_'. $filePrefixAndExp;
						$targetThumbnail = $targetAdFolder . RL_DS . $targetThumbnailName;
						$thumbnail = copy( $sourceThumbnail, $targetThumbnail ) ? "{$targetDate}/ad{$newListingID}/{$targetThumbnailName}" : '';
					}

					if ( is_readable( $sourceOriginal ) )
					{
						$targetOriginalName = 'orig_'. $filePrefixAndExp;
						$targetOriginal = $targetAdFolder . RL_DS . $targetOriginalName;
						$original = copy( $sourceOriginal, $targetOriginal ) ? "{$targetDate}/ad{$newListingID}/{$targetOriginalName}" : '';
					}
				}

				$rlValid -> sql( $entry['Description'] );
				$insert  = "INSERT INTO `". RL_DBPREFIX ."listing_photos` ( `Listing_ID`,`Position`,`Photo`,`Thumbnail`,`Original`,`Description`,`Type`,`Status`,`import_source_id`,`importIdentify` ) ";
				$insert .= "VALUES ( '{$newListingID}','1','{$photo}','{$thumbnail}','{$original}','{$entry['Description']}','{$entry['Type']}','{$entry['Status']}','{$entry['ID']}','1' )";
				$this -> query( $insert );
			}

			// clear memory
			unset( $sourcePhotos );

			// start recurcion
			$start += $this -> limit;
			$_response -> script("xajax_importListingPhotos({$start});");
			return $_response;
		}
	}

	/**
	* 
	**/
	function ajaxImport_listings( $start = false, $successful = false, $failed = false )
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('listings', 'check_update,languages,accounts,categories,data_formats') )
		{
			return $res;
		}

		global $_response, $config, $lang, $rlValid, $rlCache;

		$start = (int)$start;
		$successful = (int)$successful;
		$failed = (int)$failed;

		// import listing fields and listing plans
		if ( $start === 0 && $this -> logs['listings']['Modified'] == 0 )
		{
			$this -> importListingPlans();
			$this -> importListingFields();
		}

		// import accounts
		$this -> reConnect('source');
		$limit = ceil( $this -> limit / 4 );
		$sourceListings = $this -> getAll("SELECT * FROM `{$config['dbMigration_table_prefix']}listings` LIMIT {$start},{$limit}");
		$this -> reConnect();

		if ( !empty( $sourceListings ) )
		{
			$fieldsArray = array();
			$fieldsList = $this -> getAll("SHOW COLUMNS FROM `". RL_DBPREFIX ."listings`");
			foreach( $fieldsList as $fiKey => $fiEntry )
			{
				if ( $fiEntry['Field'] != 'ID' )
				{
					array_push( $fieldsArray, $fiEntry['Field'] );
				}
			}
			unset( $fieldsList );

			$this -> reConnect('source');
			$filesFields = $this -> getAll("SELECT `Key` FROM `{$config['dbMigration_table_prefix']}listing_fields` WHERE `Type` = 'file' OR `Type` = 'image' GROUP BY `Type`");
			$this -> reConnect();

			$fileImageKeys = array('Photo');
			foreach( $filesFields as $fiKey => $fiEntry )
			{
				array_push( $fileImageKeys, $fiEntry['Key'] );
			}
			unset( $filesFields );

			$insertAllow = false;
			$insert = "INSERT INTO `". RL_DBPREFIX ."listings` ( `". implode( '`,`', $fieldsArray ) ."` ) VALUES ";
			foreach( $sourceListings as $key => $entry )
			{
				$exists = $this -> getRow("SELECT `importIdentify` FROM `". RL_DBPREFIX ."listings` WHERE `import_source_id` = '{$entry['ID']}' LIMIT 1");
				if ( empty( $exists['importIdentify'] ) || $exists['importIdentify'] == 0 )
				{
					$insert .= "(";
					foreach( $fieldsArray as $fKey => $fEntry )
					{
						$fieldValue = '';
						switch( $fEntry )
						{
							case 'import_source_id':
								$fieldValue = (int)$entry['ID'];
								break;
							case 'importIdentify':
								$fieldValue = 1;
								break;
							case 'Category_ID':
								$fieldValue = (int)$this -> getOne('ID', "`import_source_id` = '{$entry['Kind_ID']}'", 'categories');
								break;
							case 'Account_ID':
								$fieldValue = (int)$this -> getOne('ID', "`import_source_id` = '{$entry[$fEntry]}'", 'accounts');
								break;
							case 'Plan_ID':
								$fieldValue = (int)$this -> getOne('ID', "`import_source_id` = '{$entry[$fEntry]}'", 'listing_plans');
								break;
							case 'Featured_ID':
								$fieldValue = (int)$this -> getOne('ID', "`import_source_id` = '{$entry[$fEntry]}'", 'listing_plans');
								break;
							case 'Crossed':
								if ( !empty( $entry[$fEntry] ) )
								{
									$sourceCrossed = explode(',', $entry[$fEntry] );
									$targetCrossed = array();

									foreach( $sourceCrossed as $scEntry )
									{
										$newCategoryID = (int)$this -> getOne('ID', "`import_source_id` = '{$scEntry}'", 'categories');
										if ( $newCategoryID !== 0 )
										{
											array_push( $targetCrossed, $newCategoryID );
										}
									}

									$fieldValue = implode(',', $targetCrossed);
									unset( $targetCrossed );
								}
								break;

							default:
								$fieldValue = $rlValid -> xSql( $entry[$fEntry] );
								break;
						}

						// copy files for types: [file/image]
						if ( in_array( $fEntry, $fileImageKeys ) )
						{
							$source = rtrim( $config['dbMigration_location_root_path'], '/' ) . RL_DS .'files'. RL_DS . $entry[$fEntry];
							if ( !empty( $entry[$fEntry] ) && is_readable( $source ) )
							{
								$target = RL_FILES . $entry[$fEntry];
								if ( copy( $source, $target ) )
								{
									$fieldValue = $entry[$fEntry];
								}
							}
						}

						$insert .= "'{$fieldValue}',";
						unset( $fieldValue );
					}

					$insert = rtrim( $insert, ',' );
					$insert .= "),";

					$insertAllow = true;
					$successful++;
				}
				elseif ( $exists['importIdentify'] != 0 )
				{
					$successful++;
				}
				else
				{
					$failed++;
				}
			}

			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' );
				$this -> query( $insert );
			}

			// clear memory
			unset( $sourceListings, $insert, $fieldsArray );

			// start import as recurcion
			$start += ceil( $this -> limit / 4 );
			$_response -> script("xajax_import_listings({$start}, {$successful}, {$failed});");
			return $_response;
		}

		// import images
		$this -> ajaxImportListingPhotos();

		// import video
		$this -> ajaxImportListingsVideo();

		// recount the listing number for each category
		$_response -> script("xajax_recountListings(0,1);");

		// update cache
		$rlCache -> update();

		// save logs
		$this -> updateLog( 'listings', $successful, $failed );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}

	/**
	* 
	**/
	function ajaxImport_pages( $updateExists = false )
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('pages', 'check_update,languages') )
		{
			return $res;
		}

		global $_response, $config, $lang, $rlValid, $rlActions;

		$updateExists = (int)$updateExists;
		$successful = $failed = 0;

		$this -> reConnect('source');
		$sql  = "SELECT `ID`,`Parent_ID`,`Page_type`,`Login`,`Key`,`Position`,`Path`,`Get_vars`,`Controller`,`Tpl`,`Menus`,`Deny`,`Plugin`,`No_follow`,`Modified`,`Status`,`Readonly` ";
		$sql .= "FROM `{$config['dbMigration_table_prefix']}pages` WHERE `Page_type` <> 'system' AND `Plugin` = ''";
		$sourcePages = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourcePages ) )
		{
			$pages = $update = array();
			foreach( $sourcePages as $key => $entry )
			{
				$exists = $this -> getRow("SELECT `Key`,`importIdentify` FROM `". RL_DBPREFIX ."pages` WHERE `Key` = '{$entry['Key']}' LIMIT 1");
				if ( empty( $exists['Key'] )  )
				{
					array_push( $pages, array(
							'import_source_id' => (int)$entry['ID'],
							'importIdentify' => 1,
							'Page_type' => $entry['Page_type'],
							'Controller' => $entry['Controller'],
							'Login' => (int)$entry['Login'],
							'Key' => $entry['Key'],
							'Position' => (int)$entry['Position'],
							'Path' => $entry['Path'],
							'Tpl' => (int)$entry['Tpl'],
							'Menus' => $entry['Menus'],
							'Deny' => $entry['Deny'],
							'No_follow' => (int)$entry['No_follow'],
							'Modified' => $entry['Modified'],
							'Status' => $entry['Status'],
							'Readonly' => (int)$entry['Readonly']
						)
					);
					$successful++;
				}
				elseif ( $exists['importIdentify'] != 0 || $updateExists === 1 )
				{
					array_push( $update, array(
							'fields' => array(
								'import_source_id' => (int)$entry['ID'],
								'Page_type' => $entry['Page_type'],
								'Controller' => $entry['Controller'],
								'Login' => (int)$entry['Login'],
								'Key' => $entry['Key'],
								'Position' => (int)$entry['Position'],
								'Path' => $entry['Path'],
								'Tpl' => (int)$entry['Tpl'],
								'Menus' => $entry['Menus'],
								'Deny' => $entry['Deny'],
								'No_follow' => (int)$entry['No_follow'],
								'Modified' => $entry['Modified'],
								'Status' => $entry['Status'],
								'Readonly' => (int)$entry['Readonly']
							),
							'where' => array( 
								'ID' => (int)$entry['ID'],
								'importIdentify' => 0
							)
						)
					);
					$successful++;
				}
				else
				{
					$failed++;
				}
			}

			// insert new pages
			if ( !empty( $pages ) )
			{
				$rlActions -> insert( $pages, 'pages' );
				unset( $pages );
			}

			// update exists pages
			if ( !empty( $update ) )
			{
				$rlActions -> update( $update, 'pages' );
				unset( $update );
			}
		}

		// save logs
		$this -> updateLog( 'pages', $successful, $failed );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}

	/**
	* 
	**/
	function ajaxImport_news()
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('news', 'check_update,languages') )
		{
			return $res;
		}

		global $_response, $config, $lang, $rlValid;

		$this -> reConnect('source');
		$sql  = "SELECT `ID`, `Date`, `Status` FROM `". $config['dbMigration_table_prefix'] ."news`";
		$sourceNews = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourceNews ) )
		{
			foreach( $sourceNews as $key => $entry )
			{
				$exists = $this -> getOne( 'importIdentify', "`import_source_id` = '{$entry['ID']}'", 'news' );
				if ( empty( $exists ) )
				{
					loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');
					$newsTitle = $lang['news+title+'. $entry['ID']];
					if ( !utf8_is_ascii( $newsTitle ) )
					{
						$newsTitle = utf8_to_ascii( $newsTitle );
					}
					$path = $rlValid -> str2path( $newsTitle );
					$path = !empty( $path ) ? $path : "imported-news-{$entry['ID']}";
					$insert  = "INSERT INTO `". RL_DBPREFIX ."news` ( `Date`,`Path`,`Status`,`import_source_id`,`importIdentify` ) VALUES ";
					$insert .= "( '{$entry['Date']}','{$path}','{$entry['Status']}','{$entry['ID']}','1' );";
					$this -> query( $insert );

					$id = mysql_insert_id();
					$this -> query( "UPDATE `". RL_DBPREFIX ."lang_keys` SET `Key` = 'news+title+{$id}' WHERE `Key` = 'news+title+{$entry['ID']}' AND `import_source_id` <> '0'" );
					$this -> query( "UPDATE `". RL_DBPREFIX ."lang_keys` SET `Key` = 'news+content+{$id}' WHERE `Key` = 'news+content+{$entry['ID']}' AND `import_source_id` <> '0'" );
				}
			}

			// clear memory
			unset( $sourceNews );
		}

		// save log
		$this -> updateLog( 'news', count( $sourceNews ), 0 );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}

	/**
	* 
	**/
	function ajaxImport_contacts( $start = false, $successful = false )
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('contacts', 'check_update') )
		{
			return $res;
		}

		global $_response, $config, $lang, $rlValid;

		$start = (int)$start;
		$successful = (int)$successful;

		$this -> reConnect('source');
		$sql = "SELECT `ID`,`Name`,`Email`,`Message`,`Date`,`Status` FROM `{$config['dbMigration_table_prefix']}contacts` LIMIT {$start},{$this->limit}";
		$sourceContacts = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourceContacts ) )
		{
			$insert = "INSERT INTO `". RL_DBPREFIX ."contacts` ( `Name`,`Email`,`Message`,`Date`,`Status`,`import_source_id`,`importIdentify` ) VALUES ";
			foreach( $sourceContacts as $key => $entry )
			{
				$name = $rlValid -> xSql( $entry['Name'] );
				$message = $rlValid -> xSql( $entry['Message'] );

				$insert .= "( '{$name}','{$entry['Email']}','{$message}','{$entry['Date']}','{$entry['Status']}','{$entry['ID']}','1' ),";
				$successful++;

				// clear memory
				unset( $name, $message );
			}
			$insert = rtrim( $insert, ',' ) .';';
			$this -> query( $insert );

			// clear memory
			unset( $sourceContacts, $insert );

			// start import as recurcion
			$start += $this -> limit;
			$_response -> script("xajax_import_contacts({$start},{$successful});");
			return $_response;
		}

		$this -> updateLog( 'contacts', $successful, 0 );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}

	/**
	* 
	**/
	function ajaxImport_transactions( $start = false, $successful = false )
	{
		// dependency checking
		if ( true !== $res = $this -> checkDependency('transactions', 'check_update,listings,accounts') )
		{
			return $res;
		}

		global $_response, $config, $lang;

		$start = (int)$start;
		$successful = (int)$successful;

		$this -> reConnect('source');
		$sql  = "SELECT `T1`.`ID`,`T1`.`Listing_ID`,`T1`.`Plan_ID`,`T1`.`Txn_ID`,`T1`.`Total`,`T1`.`Gateway`,`T1`.`Date`,`T1`.`Status`,`T2`.`Account_ID`,`T3`.`Type` AS `Service` ";
		$sql .= "FROM `{$config['dbMigration_table_prefix']}transactions` AS `T1` ";
		$sql .= "LEFT JOIN `{$config['dbMigration_table_prefix']}listings` AS `T2` ON `T1`.`Listing_ID`=`T2`.`ID` ";
		$sql .= "LEFT JOIN `{$config['dbMigration_table_prefix']}listing_plans` AS `T3` ON `T1`.`Plan_ID`=`T3`.`ID` ";
		$sql .= "LIMIT {$start},{$this->limit};";
		$sourceTransactions = $this -> getAll( $sql );
		$this -> reConnect();

		if ( !empty( $sourceTransactions ) )
		{
			$insertAllow = false;
			$insert = "INSERT INTO `". RL_DBPREFIX ."transactions` ( `Service`,`Account_ID`,`Item_ID`,`Plan_ID`,`Txn_ID`,`Total`,`Gateway`,`Date`,`Status`,`import_source_id`,`importIdentify` ) VALUES ";
			foreach( $sourceTransactions as $key => $entry )
			{
				$exists = $this -> getOne( 'ID', "`Date` = '{$entry['Date']}' AND `importIdentify` = '1'", 'transactions' );
				if ( empty( $exists ) )
				{
					$accountID = (int)$this -> getOne('ID', "`import_source_id` = '{$entry['Account_ID']}'", 'accounts');
					$listingID = (int)$this -> getOne('ID', "`import_source_id` = '{$entry['Listing_ID']}'", 'listings');
					$planID = (int)$this -> getOne('ID', "`import_source_id` = '{$entry['Plan_ID']}'", 'listing_plans');

					$insert .= "( '{$entry['Service']}','{$accountID}','{$listingID}','{$planID}','{$entry['Txn_ID']}','{$entry['Total']}',";
					$insert .= "'{$entry['Gateway']}','{$entry['Date']}','{$entry['Status']}','{$entry['ID']}','1' ),";
					$insertAllow = true;
				}
				$successful++;
			}

			if ( $insertAllow === true )
			{
				$insert = rtrim( $insert, ',' ) .';';
				$this -> query( $insert );
			}

			// clear memory
			unset( $sourceTransactions, $insert );

			// start import as recurcion
			$start += $this -> limit;
			$_response -> script("xajax_import_transactions({$start},{$successful});");
			return $_response;
		}

		// save log
		$this -> updateLog( 'transactions', $successful, 0 );
		$this -> importCompleteNotice( $_response );

		return $_response;
	}
}