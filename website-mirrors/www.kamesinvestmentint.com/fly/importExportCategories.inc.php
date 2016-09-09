<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: IMPORTEXPORTCATEGORIES.INC.PHP
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

if ( $_GET['q'] == 'ext' )
{
	require_once('../../../includes/config.inc.php');
	require_once(RL_ADMIN_CONTROL .'ext_header.inc.php');
	require_once(RL_LIBS .'system.lib.php');

	// data read
	$start = (int)$_GET['start'] + 1;
	$limit = (int)$_GET['limit'];
	$stop = $start + $limit;

	$reefless -> loadClass('ImportExportCategories', false, 'importExportCategories');
	$data = $rlImportExportCategories -> getCategories($start, $stop);

	$out = array(
		'data' => $data,
		'total' => $rlImportExportCategories -> calc_rows
	);

	$reefless -> loadClass('Json');
	echo $rlJson -> encode($out);

	// clear memory
	unset($data, $out);
	exit;
}
else if ( isset($_GET['remove_categories']) )
{
	$reefless -> loadClass('Categories');
	$rlDb -> setTable('categories');

	$listing_type = $_GET['remove_categories'];
	if ( $categories = $rlDb -> fetch(array('Key'), array('Type' => $listing_type)) )
	{
		foreach ($categories as $category)
		{
			$rlCategories -> ajaxDeleteCategory($category['Key'], false, true);
		}
	}
	echo 'Done';
}
else
{
	$reefless -> loadClass('Categories');
	$reefless -> loadClass('ImportExportCategories', false, 'importExportCategories');

	$allowed_types = array('application/vnd.ms-excel', 'application/ms-excel', 'application/octet-stream');

	//
	if ( isset($_GET['done']) && $_SESSION['imex_plugin']['ic_count'] )
	{
		// update cache
		$rlCache -> updateCategories();

		$reefless -> loadClass('Notice');
		$rlNotice -> saveNotice(str_replace("[count]", $_SESSION['imex_plugin']['ic_count'], $lang['importExportCategories_count']));
		unset($_SESSION['imex_plugin']['count']);
		$reefless -> redirect(array("controller" => "importExportCategories"));
	}

	if ( !isset($_GET['action']) )
	{
		@unlink($rlImportExportCategories -> tmp_file );
		unset($_SESSION['imex_plugin']);

		if ( isset($_SESSION['imex_plugin']['errors']) )
		{
			$errors[] = $_SESSION['imex_plugin']['errors'];
			$rlSmarty -> assign_by_ref('errors', $errors);
			unset($_SESSION['imex_plugin']['errors']);
		}

		// refresh counter
		unset($_SESSION['imex_plugin']['ic_count']);
	}

	// additional bread crumb step
	if ( isset( $_GET['action'] ) )
	{
		$bcAStep = $_GET['action'] == 'import' ? (isset($_SESSION['imex_plugin']) ? $lang['importExportCategories_import_preview'] : $lang['importExportCategories_import']) : $lang['importExportCategories_export'];
	}

	if ( $_GET['action'] == 'import' )
	{
		if ( isset($_POST['submit']) )
		{
			$errors = array();
			$fileInfo = $_FILES['file_import'];

			$pathInfo = pathinfo($fileInfo['name']);

			if ( empty($pathInfo['filename']) )
			{
				array_push( $errors, str_replace('[field]', "<b>{$lang['file']}</b>", $lang['notice_field_empty']) );
			}
			elseif ( !in_array($_FILES['file_import']['type'], $allowed_types) )
			{
				array_push($errors, $lang['importExportCategories_incorrect_file_ext']);
			}

			// try move
			if ( !empty($fileInfo['tmp_name']) )
			{
				if ( move_uploaded_file($fileInfo['tmp_name'], $rlImportExportCategories -> tmp_file) )
				{
					chmod($rlImportExportCategories -> tmp_file, 0644);
				}
				else
				{
					array_push($errors, $lang['importExportCategories_not_move_file']);
				}
			}

			if ( empty($_POST['listing_type']) )
			{
				array_push($errors, 'listing type error');
			}

			if ( !empty($errors) )
			{
				$rlSmarty -> assign_by_ref('errors', $errors);
			}
			else {
				$_SESSION['imex_plugin']['listing_type'] = $_POST['listing_type'];
				$reefless -> redirect(array("controller" => "importExportCategories", "action" => "import"));
			}
		}
	}
	else
	{
		// get categories/section
		if ( !$_REQUEST['xjxfun'] )
		{
			$sections = $rlCategories -> getCatTree(0, false, true);
			$rlSmarty -> assign_by_ref('sections', $sections);
		}

		if ( isset($_POST['submit']) )
		{
			$rlImportExportCategories -> export();
		}

		$rlXajax -> registerFunction(array('getCatLevel', $rlCategories, 'ajaxGetCatLevel'));
	}
}