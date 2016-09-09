<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: CREDITS_MANAGER.INC.PHP
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
	// system config
	require_once('../../../includes/config.inc.php');
	require_once(RL_ADMIN_CONTROL .'ext_header.inc.php');
	require_once(RL_LIBS .'system.lib.php');

	// date update
	if ( $_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );

		$field = $rlValid -> xSql($_GET['field']);
		$value = $rlValid -> xSql(nl2br($_GET['value']));
		$id = $rlValid -> xSql($_GET['id']);
		$key = $rlValid -> xSql($_GET['key']);

		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);

		$rlActions -> updateOne($updateData, 'credits_manager');
		exit;
	}

	// data read
	$start = (int)$_GET['start'];
	$limit = (int)$_GET['limit'];

	$sql  = "SELECT SQL_CALC_FOUND_ROWS `T1`.* ";
	$sql .= "FROM `". RL_DBPREFIX ."credits_manager` AS `T1` ";
	$sql .= "LIMIT {$start}, {$limit}";
	$data = $rlDb -> getAll($sql);                                                                                                      
	$count = $rlDb -> getRow("SELECT FOUND_ROWS() AS `count`");

	foreach ( $data as $key => $entry )
	{
		$data[$key]['Status'] = $lang[$entry['Status']];
		$data[$key]['name'] = $lang['credits_manager+name+credit_package_'. $entry['ID']];
	}
	$output['total'] = $count['count'];
	$output['data'] = $data;

	$reefless -> loadClass('Json');
	echo $rlJson -> encode($output);
	exit;
}

if ( isset( $_GET['action'] ) )
{
	if ( $_GET['action'] == 'add' || $_GET['action'] == 'edit' )
	{
		// additional bread crumb step
		$bcAStep[0] = array('name' => $_GET['action'] == 'add' ? $lang['paygc_add_item'] : $lang['paygc_edit_item']);

		$id = (int)$_GET['item'];

		// get all languages
		$allLangs = $GLOBALS['languages'];
		$rlSmarty -> assign_by_ref('allLangs', $allLangs);

		// get account types
		$reefless -> loadClass('Account');

		// get current plan info
		if ( isset($_GET['item']) )
		{
			$credits_manager = $rlDb -> fetch(array('ID', 'Price', 'Credits', 'Status'), array('ID' => $id), null, null, 'credits_manager', 'row');
			$credits_manager['Key'] = 'credit_package_'. $credits_manager['ID'];
			$rlSmarty -> assign_by_ref('credits_manager', $credits_manager);
		}

		if ( $_GET['action'] == 'edit' && !$_POST['fromPost'] )
		{
			$_POST['status'] = $credits_manager['Status'];
			$_POST['price'] = $credits_manager['Price'];
			$_POST['credits'] = $credits_manager['Credits'];

			// get names
			$names = $rlDb -> fetch(array('Code', 'Value'), array('Key' => 'credits_manager+name+'. $credits_manager['Key']), "AND `Status` <> 'trash'", null, 'lang_keys');
			foreach ( $names as $pKey => $pVal )
			{
				$_POST['name'][$pVal['Code']] = $pVal['Value'];
			}
		}

		if ( isset($_POST['submit']) )
		{
			$reefless -> loadClass('Actions');
			$errors = $error_fields = array();

			// check name
			$f_names = $_POST['name'];
			if ( empty($f_names[$config['lang']]) )
			{
				$langName = count($allLangs) > 1 ? "{$lang['name']}({$allLangs[$config['lang']]['name']})" : $lang['name'];
				array_push($errors, str_replace('{field}', "<b>{$langName}</b>", $lang['notice_field_empty']));
				array_push($error_fields, "name[{$config['lang']}]");
			}

			if ( !empty($errors) )
			{
				$rlSmarty -> assign_by_ref('errors', $errors);
			}
			else
			{
				if ( $_GET['action'] == 'add' )
				{
					// get max position
					$position = $rlDb -> getRow("SELECT MAX(`Position`) AS `max` FROM `". RL_DBPREFIX ."credits_manager`");

					// write main plan information
					$data = array( 
						'Price' => $_POST['price'],
						'Credits' => $_POST['credits'],
						'Position' => $position['max'] + 1,
						'Status' => $_POST['status']
					);

					if ( $action = $rlActions -> insertOne($data, 'credits_manager') )
					{
						$id_credit = mysql_insert_id();
						$f_key = 'credit_package_'. $id_credit;

						// write name's phrases
						foreach ( $allLangs as $key => $value )
						{
							$lang_keys[] = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'credits_manager+name+'. $f_key,
								'Value' => !empty($f_names[$allLangs[$key]['Code']]) ? $f_names[$allLangs[$key]['Code']] : $f_names[$config['lang']],
								'Plugin' => 'payAsYouGoCredits'
							);
						}
						$rlActions -> insert($lang_keys, 'lang_keys');

						$message = $lang['paygc_item_added'];
						$aUrl = array("controller" => $controller);
					}
					else
					{
						trigger_error("Can't add new credit package (MYSQL problems)", E_WARNING);
						$rlDebug -> logger("an't add new credit package (MYSQL problems)");
					}
				}
				elseif ( $_GET['action'] == 'edit' )
				{
					$update_date = array(
						'fields' => array(
							'Price' => $_POST['price'],
							'Credits' => $_POST['credits'],
							'Status' => $_POST['status']
						 ),
						'where' => array('ID' => $id)
					);

					if ( $action = $GLOBALS['rlActions'] -> updateOne($update_date, 'credits_manager') )
					{
						$f_key = 'credit_package_'. $id;

						foreach ( $allLangs as $key => $value )
						{
							if ( $rlDb -> getOne('ID', "`Key` = 'credits_manager+name+{$f_key}' AND `Code` = '{$allLangs[$key]['Code']}'", 'lang_keys') )
							{
								// edit names
								$update_names = array(
									'fields' => array(
										'Value' => !empty($f_names[$allLangs[$key]['Code']]) ? $f_names[$allLangs[$key]['Code']] : $f_names[$config['lang']]
									),
									'where' => array(
										'Code' => $allLangs[$key]['Code'],
										'Key' => 'credits_manager+name+'. $f_key
									)
								);

								// update
								$rlActions -> updateOne($update_names, 'lang_keys');
							}
							else
							{
								// insert names
								$insert_names = array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Key' => 'credits_manager+name+'. $f_key,
									'Value' => !empty($f_names[$allLangs[$key]['Code']]) ? $f_names[$allLangs[$key]['Code']] : $f_names[$config['lang']],
									'Plugin' => 'payAsYouGoCredits'
								);

								// insert
								$rlActions -> insertOne($insert_names, 'lang_keys');
							}
						}
					}

					$message = $lang['bwt_item_edited'];
					$aUrl = array("controller" => $controller);
				}
				unset($credit_info);

				// update config
				$select = "SELECT MAX(@Price_one:=`Price`/`Credits`) AS `MaxPriceCredit` FROM `". RL_DBPREFIX ."credits_manager` LIMIT 1";
				$sql  = "UPDATE `". RL_DBPREFIX ."config` SET `Default` = ROUND(({$select}), 2) ";
				$sql .= "WHERE `Key` = 'paygc_rate_hide' LIMIT 1";
				$rlDb -> query($sql);

				if ( $action )
				{
					$reefless -> loadClass('Notice');
					$rlNotice -> saveNotice($message);
					$reefless -> redirect($aUrl);
				}
			}
		}
	}
}
else
{
	$reefless -> loadClass('PayAsYouGoCredits', null, 'payAsYouGoCredits'); 
	$rlXajax -> registerFunction(array('deleteCreditItem', $rlPayAsYouGoCredits, 'ajaxDeleteCreditItem'));
}