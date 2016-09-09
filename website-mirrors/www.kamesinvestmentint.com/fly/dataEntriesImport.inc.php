<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: DATAENTRIESIMPORT.INC.PHP
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

$sql  = "SELECT `T1`.`ID`, `T2`.`Value` AS `name` FROM `". RL_DBPREFIX ."data_formats` AS `T1` ";
$sql .= "LEFT JOIN `". RL_DBPREFIX ."lang_keys` AS `T2` ON CONCAT('data_formats+name+',`T1`.`Key`) = `T2`.`Key` AND `T2`.`Code` = '". RL_LANG_CODE ."' ";
$sql .= "WHERE `T1`.`Status` <> 'trash' AND `T1`.`Key` <> 'years' AND `T1`.`Parent_ID` = '0'";
$dataFormats = $rlDb -> getAll( $sql );

$rlSmarty -> assign_by_ref('data_formats', $dataFormats);

// get all languages
$allLangs = $GLOBALS['languages'];
$rlSmarty -> assign_by_ref( 'allLangs', $allLangs );

$reefless -> loadClass('Actions');
$reefless -> loadClass('DataEntriesImport', false, 'dataEntriesImport');

$rlXajax -> registerFunction(array('getDFLevel', $rlDataEntriesImport, 'ajaxGetDFLevel'));

if ( $_POST['upload'] && !$_REQUEST['xjxfun'] )
{
	// remove old tmp file
	@unlink($rlDataEntriesImport -> tmpFile);
	$sourceFile = $_FILES['source'];

	$errors = $error_fields = array();
	$allowed_types = array(
		'text/csv',
		'text/plain',
		'application/vnd.ms-excel'
	);

	if ( !in_array($sourceFile['type'], $allowed_types) )
	{
		$ext = pathinfo($sourceFile['name'], PATHINFO_EXTENSION);
		array_push($errors, str_replace('{ext}', "<b>{$ext}</b>", $lang['notice_bad_file_ext']));
	}
	else
	{
		$importTo = $_POST['import_to'];

		if ( $importTo == 'exists' )
		{
			$dfID = (int)$_POST['import_to_parent'];
			$dfKey = $rlDb -> getOne('Key', "`ID` = '{$dfID}'", 'data_formats');
			$rlDataEntriesImport -> parentKey = $dfKey;
		}
		else
		{
			$f_name = $_POST['name'];
			$defName = !empty( $f_name['en'] ) ? $f_name['en'] : current( $f_name );

			// make unique key
			$dfKey = $rlDataEntriesImport -> uniqKeyByName( $defName );

			$langKeys = array();
			foreach( $allLangs as $lkey => $lval )
			{
				if ( empty( $f_name[$allLangs[$lkey]['Code']] ) )
				{
					array_push( $errors, str_replace('{field}', "<b>{$lang['name']}({$allLangs[$lkey]['name']})</b>", $lang['notice_field_empty'] ) );
					array_push( $error_fields, "name[{$lval['Code']}]" );
				}

				array_push( $langKeys, array(
						'Key' => "data_formats+name+{$dfKey}",
						'Value' => $rlValid -> xSql( $f_name[$allLangs[$lkey]['Code']] ),
						'Code' => $allLangs[$lkey]['Code'],
						'Module' => 'common'
					)
				);
			}

			// create new dataEntry
			if ( empty( $errors ) )
			{
				$data = array(
					'Key' => $dfKey,
					'Parent_ID' => (int)$_POST['import_to_parent_new'],
					'Order_type' => in_array($_POST['order_type'], array('alphabetic','position')) ? $_POST['order_type'] : 'position'
				);

				if ( $rlActions -> insertOne($data, 'data_formats') )
				{
					$dfID = mysql_insert_id();
					$rlActions -> insert($langKeys, 'lang_keys');
				}
			}
		}
	}

	// try upload
	if ( !move_uploaded_file( $sourceFile['tmp_name'], $rlDataEntriesImport -> tmpFile ) )
	{
		array_push( $errors, $lang['dataEntriesImport_error_upload'] );
	}
	else
	{
		chmod($rlDataEntriesImport -> tmpFile, 0644);
	}

	if ( !empty( $errors ) )
	{
		$rlSmarty -> assign_by_ref('errors', $errors);
	}
	else
	{
		// import
		$sourceExt = end(explode('.', $sourceFile['name']));
		$rlDataEntriesImport -> parentID = $dfID;
		$rlDataEntriesImport -> parentKey = $dfKey;

		if ( false !== $res = $rlDataEntriesImport -> import($sourceExt, $_POST['delimiter']) )
		{
			$rlCache -> updateDataFormats();
			$rlCache -> updateForms();

			$reefless -> loadClass('Notice');
			$message = str_replace('[count]', $res, $lang['dataEntriesImport_notice'] );
			$rlNotice -> saveNotice( $message );

			$reefless -> redirect( array('controller' => 'dataEntriesImport') );
		}
	}
}