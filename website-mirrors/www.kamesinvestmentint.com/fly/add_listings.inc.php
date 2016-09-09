<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: ADD_LISTINGS.INC.PHP
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

if ( empty($account_info['Abilities']) )
{
	$iPhone -> printAsXml(array(), array('message' => $lang['add_listing_deny']));
}
$reefless -> loadClass('Categories');
$reefless -> loadClass('Actions');
$reefless -> loadClass('Plan');

$categoryID = (int)$_POST['category'];
$data = array();
$errors = array();

if ( in_array($_POST['step'], array('form', 'save_form', 'done')) )
{
	$category = $rlCategories -> getCategory($categoryID);

	// get posting type of listing
	$listing_type = $rlListingTypes -> types[$category['Type']];
	$_SESSION['add_listing']['listing_type'] = $listing_type['Type'];

	// get plan info
	if ( $_POST['plan'] )
	{
		$plan_info = $rlPlan -> getPlan((int)$_POST['plan'], $account_info['ID']);
	}

	// check category lock
	if ( !$category['Lock'] )
	{
		if ( $category !== false )
		{
			$form = $rlCategories -> buildListingForm($category['ID'], $listing_type);

			if ( empty($form) )
			{
				array_push($errors, $lang['notice_no_fields_related']);
			}
		}
		else
		{
			array_push($errors, 'category false');
		}
	}
	else
	{
		array_push($errors, 'category lock');
	}
}

if ( $_POST['step'] == 'category' )
{

	$sections = $rlCategories -> getCatTree($categoryID, $account_info['Abilities'], true);

	if ( !empty($sections) )
	{
		if ($categoryID != 0)
		{
			$categories = array();
			foreach( $sections as $cKey => $category )
			{
				array_push($categories, array(
						'id' => (int)$category['ID'],
						'key' => $category['Key'],
						'name' => $iPhone -> pValid($category['name']),
						'locked' => (bool)$category['Lock'],
						'subCategories' => (int)$category['Sub_cat']
					)
				);
			}
			array_push($data, array(
					'name' => '',
					'rows' => $categories
				)
			);
			unset($categories);
		}
		else
		{
			foreach( $sections as $key => $value )
			{
				if ( empty($value['Categories']) ) continue;

				$categories = array();
				foreach( $value['Categories'] as $cKey => $category )
				{
					array_push($categories, array(
							'id' => (int)$category['ID'],
							'key' => $category['Key'],
							'name' => $iPhone -> pValid($category['name']),
							'locked' => (bool)$category['Lock'],
							'subCategories' => (int)$category['Sub_cat']
						)
					);
				}
				array_push($data, array(
						'name' => $iPhone -> pValid($value['name']),
						'rows' => $categories
					)
				);
				unset($categories);
			}
		}
		unset($sections);
	}
	$iPhone -> printAsXml($data);
}
else if ( $_POST['step'] == 'plans' )
{
	if ( isset($account_info['Type']) )
	{
		$tmp_plans = $rlPlan -> getPlanByCategory($categoryID, $account_info['Type']);
	}

	if ( empty($tmp_plans) )
	{
		array_push($errors, $lang['notice_no_plans_related']);
	}
	else
	{
		$plans = array();
		foreach($tmp_plans as $key => $plan)
		{
			// tmp prevent non free plans
			if ( $plan['Price'] )
				continue;

			$index = count($plans);
			$plans[$index] = array(
				'id' => (int)$plan['ID'],
				'key' => $plan['Key'],
				'type' => $plan['Type'],
				'typeName' => $lang[$plan['Type'] .'_plan_short'],
				'price' => $plan['Price'] ? $config['system_currency'] . $plan['Price'] : $lang['free'],
				'planNotice' => ($config['iFlynaxConnect_listing_auto_approval'] || !$config['iFlynaxConnect_keepActiveAfterAdd']) ? 0.0 : (double)$plan['Price'],
				'cross' => (int)$plan['Cross'],
				'limit' => (int)$plan['Limit'],
				'using' => (int)$plan['Using'],
				'lremains' => (int)$plan['Listings_remains'],
				'name' => $iPhone -> pValid($plan['name']),
				'desc' => $iPhone -> pValid($plan['des'])
			);

			if ( !empty($plan['Color']) )
			{
				$plans[$index]['color'] = $plan['Color'];
			}
		}
		array_push($data, array(
				'name' => 'Plans',
				'rows' => $plans
			)
		);
		unset($tmp_plans);
	}
}
else if ( $_POST['step'] == 'form' )
{
	if ( empty($errors) )
	{
		// remove field types: file,
		$iPhone -> adaptFormSections($form);
		$fieldValuesIsArray = array('price');

		foreach( $form as $key => $section )
		{
			$fields = array();
			foreach( $section['Fields'] as $fKey => $field )
			{
				$fIndex = count($fields);
				$fields[$fIndex] = array(
					'id' => (int)$field['ID'],
					'key' => $field['Key'],
					'name' => $iPhone -> pValid($lang[$field['pName']]),
					'desc' => $iPhone -> pValid($lang[$field['pDescription']]),
					'type' => $field['Type'],
					'condition' => $field['Condition'],
					'required' => (bool)$field['Required']
				);

				$isMultiField = (bool)preg_match('/_level([0-9]+)$/', $field['Key'], $matches);
				if ( $isMultiField && !empty($matches[1]) )
				{
					$mf_level = (int)$matches[1];
					if ( $mf_level > 1 )
					{
						$mf_pattern = '/[0-9]+$/';
						$mf_replace = $mf_level - 1;
					}
					else
					{
						$fields[$fIndex-1]['mf_level'] = 0;
						$mf_pattern = '/_level[0-9]+$/';
						$mf_replace = '';
					}

					$fields[$fIndex]['mf_parent'] = preg_replace($mf_pattern, $mf_replace, $field['Key']);
					$fields[$fIndex]['mf_level'] = $mf_level;
				}

				if ( !empty($field['Default']) )
				{
					$fields[$fIndex]['default'] = $field['Default'];
				}

				if ( (is_array($field['Values']) || in_array($field['Type'], $fieldValuesIsArray)) && !$isMultiField)
				{
					if ( $field['Type'] == 'price' )
					{
						$source = $rlCategories -> getDF('currency');
						$cKey = 'Key';
					}
					elseif ( $fField['Key'] == 'Category_ID' )
					{
						$source = $field['Values'];
						$cKey = 'ID';
					}
					else
					{
						$source = $field['Values'];
						$cKey = 'Key';
					}

					$values = array();
					foreach( $source as $sKey => $value )
					{
						$valueKey = $value[$cKey];

						if ( $field['Type'] == 'radio' || $field['Type'] == 'checkbox' || $field['Type'] == 'select' )
						{
							$valueKey = $sKey;
							if ( !empty($field['Condition']) )
							{
								$valueKey = $value['Key'];
							}
						}

						array_push($values, array(
								'key' => strval($valueKey),
								'name' => $iPhone -> pValid($value['name'] ? $value['name'] : $lang[$value['pName']])
							)
						);
					}
					$fields[$fIndex]['values'] = $values;
					unset($values);
				}
				else
				{
					if ( $field['Type'] == 'number' || $field['Type'] == 'text' || $field['Type'] == 'textarea' )
					{
						$fields[$fIndex]['maxlength'] = (int)$field['Values'];
					}
				}
			}

			array_push($data, array(
					'name' => $iPhone -> pValid($lang[$section['pName']]),
					'rows' => $fields
				)
			);
			unset($fields);
		}
		unset($form);
	}
}
else if ( $_POST['step'] == 'save_form' )
{
	if ( empty($errors) )
	{
		$data['added'] = false;
		$listing_data = $_SESSION['add_listing']['listing_data'] = $_POST['f'];
		$listingId = (int)$_POST['listingId'];

		// load category fields
		$category_fields = $rlCategories -> fields;

		// deep checking ;)
		foreach($category_fields as $cKey => $cField)
		{
			if ( !$listing_data[$cField['Key']] )
			{
				if ( $cField['Type'] == 'checkbox' )
				{
					$listing_data[$cField['Key']][0] = '';
				}
				else
				{
					$listing_data[$cField['Key']] = '';
				}
			}
		}

		// check form fields
		if ( $back_errors = $rlCommon -> checkDynamicForm($listing_data, $category_fields) )
		{
			foreach( $back_errors as $error )
			{
				array_push($errors, $iPhone -> pValid($error));
			}
		}

		if ( empty($errors) )
		{
			$reefless -> loadClass('Listings');

			$rlHook -> load('addListingAdditionalInfo');

			if ( $listingId )
			{
				$rlListings -> edit($listingId, $plan_info, $listing_data, $category_fields);

				$rlHook -> load('afterListingEdit');

				$data['added'] = true;
				$data['listingId'] = $listingId;
			}
			else
			{
				if ( $rlListings -> create($plan_info, $listing_data, $category_fields) )
				{
					$listing_id = $_SESSION['add_listing']['listing_id'] = $rlListings -> id;
					$data['added'] = true;
					$data['listingId'] = (int)$listing_id;
				}
			}
			$data['allowPhotos'] = (bool)$listing_type['Photo'];
		}
	}
}
else if ( $_POST['step'] == 'done' )
{
	$reefless -> loadClass('Mail');
	$listing_id = (int)$_SESSION['add_listing']['listing_id'];
	$listing_data = $_SESSION['add_listing']['listing_data'];
	$data['done'] = false;

	if ( !empty($listing_data) && $listing_id )
	{
		$featured = false;
		if ( $plan_info['Featured'] && (!$plan_info['Advanced_mode'] || ($plan_info['Advanced_mode'] && $_SESSION['add_listing']['listing_type'] == 'featured')) )
		{
			$featured = true;
		}

		$reefless -> loadClass('Listings');

		// get listing title
		$listing_title = $rlListings -> getListingTitle($category['ID'], $listing_data, $listing_type['Key']);

		// change listing status
		$update_status = array(
			'fields' => array(
				'Status' => $config['iFlynaxConnect_listing_auto_approval'] ? 'active' : 'pending',
				'Pay_date' => 'NOW()',
				'Featured_ID' => $featured ? $plan_info['ID'] : 0,
				'Featured_date' => $featured ? 'NOW()' : ''
			),
			'where' => array(
				'ID' => $listing_id
			)
		);
		$rlActions -> updateOne($update_status, 'listings');

		$rlHook -> load('afterListingDone');

		// free listing or exist/free package mode
		if ( ($plan_info['Type'] == 'package' && ($plan_info['Package_ID'] || $plan_info['Price'] <= 0) ) || ($plan_info['Type'] == 'listing' && $plan_info['Price'] <= 0) )
		{
			// available package mode
			if ( $plan_info['Type'] == 'package' && $plan_info['Package_ID'] )
			{
				if ( $plan_info['Listings_remains'] != 0 )
				{
					$update_entry = array(
						'fields' => array(
							'Listings_remains' => $plan_info['Listings_remains'] - 1
						),
						'where' => array(
							'ID' => $plan_info['Package_ID']
						)
					);
					
					if ( $plan_info[ucfirst($_SESSION['add_listing']['listing_type']) .'_listings'] != 0 )
					{
						$update_entry['fields'][ucfirst($_SESSION['add_listing']['listing_type']) .'_remains'] = $plan_info[ucfirst($_SESSION['add_listing']['listing_type']) .'_remains'] - 1;
					}
					
					$rlActions -> updateOne($update_entry, 'listing_packages');
				}
				
				// set paid status
				$paid_status = $lang['purchased_packages'];
			}
			// free package mode
			elseif ( $plan_info['Type'] == 'package' && !$plan_info['Package_ID'] && $plan_info['Price'] <= 0 )
			{
				$insert_entry = array(
					'Account_ID' => $account_info['ID'],
					'Plan_ID' => $plan_info['ID'],
					'Listings_remains' => $plan_info['Listing_number'] - 1,
					'Type' => 'package',
					'Date' => 'NOW()',
					'IP' => $_SERVER['REMOTE_ADDR']
				);
				
				if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $plan_info['Standard_listings'] )
				{
					$insert_entry['Standard_remains'] = $plan_info['Standard_listings'] - 1;
				}
				
				if ( $plan_info['Featured'] && $plan_info['Advanced_mode'] && $plan_info['Featured_listings'] )
				{
					$insert_entry['Featured_remains'] = $plan_info['Featured_listings'] - 1;
				}
				$rlActions -> insertOne($insert_entry, 'listing_packages');
				
				// set paid status
				$paid_status = $lang['package_plan'] .'('. $lang['free'] .')';
			}
			// limited listing mode
			elseif ($plan_info['Type'] == 'listing' && $plan_info['Limit'] > 0 )
			{
				// update/insert limited plan using entry
				if ( empty($plan_info['Using']) )
				{
					$plan_using_insert = array(
						'Account_ID' => $account_info['ID'],
						'Plan_ID' => $plan_info['ID'],
						'Listings_remains' => $plan_info['Limit']-1,
						'Type' => 'limited',
						'Date' => 'NOW()',
						'IP' => $_SERVER['REMOTE_ADDR']
					);
					$rlActions -> insertOne($plan_using_insert, 'listing_packages');
				}
				else
				{
					$plan_using_update = array(
						'fields' => array(
							'Account_ID' => $account_info['ID'],
							'Plan_ID' => $plan_info['ID'],
							'Listings_remains' => $plan_info['Using']-1,
							'Type' => 'limited',
							'Date' => 'NOW()',
							'IP' => $_SERVER['REMOTE_ADDR']
						),
						'where' => array(
							'ID' => $plan_info['Plan_using_ID']
						)
					);
					$rlActions -> updateOne($plan_using_update, 'listing_packages');
				}
				
				// set paid status
				$paid_status = $plan_info['Price'] ? $lang['not_paid'] : $lang['free'];
			}

			// recount category listings count
			if ( $config['iFlynaxConnect_listing_auto_approval'] )
			{
				$rlCategories -> listingsIncrease($category['ID']);
			}
			
			// send message to listing owner
			$mail_tpl = $rlMail -> getEmailTemplate($config['iFlynaxConnect_listing_auto_approval'] ? 'free_active_listing_created' : 'free_approval_listing_created');
			
			$link = RL_URL_HOME;
			if ( $config['iFlynaxConnect_listing_auto_approval'] )
			{
				$link .= $config['mod_rewrite'] ? $pages['lt_'. $listing_type['Key']] .'/'. $category['Path'] .'/'. $rlSmarty -> str2path($listing_title) .'-'. $listing_id .'.html' : '?page='. $pages['lt_'. $listing_type['Key']].'&id='. $listing_id;
			}
			else
			{
				$link .= $config['mod_rewrite'] ? $pages['my_'. $listing_type['Key']] .'.html' : '?page='. $pages['my_'. $listing_type['Key']];
			}

			$mail_tpl['body'] = str_replace(array('{username}', '{link}'), array($account_info['Username'], '<a href="'. $link .'">'. $link .'</a>'), $mail_tpl['body']);
			$rlMail -> send($mail_tpl, $account_info['Mail']);
		}

		// send admin notification
		$mail_tpl = $rlMail -> getEmailTemplate('admin_listing_added');
		
		$m_find = array('{username}', '{link}', '{date}', '{status}', '{paid}');
		$m_replace = array(
			$account_info['Username'], 
			'<a href="'. RL_URL_HOME . ADMIN .'/index.php?controller=listings&action=view&id='. $listing_id .'">'. $listing_title . '</a>', 
			date(str_replace(array('b', '%'), array('M', ''), RL_DATE_FORMAT)),
			$lang[$config['iFlynaxConnect_listing_auto_approval'] ? 'active' : 'pending'],
			$paid_status
		);
		$mail_tpl['body'] = str_replace($m_find, $m_replace, $mail_tpl['body']);
		
		if ( $config['iFlynaxConnect_listing_auto_approval'] )
		{
			$mail_tpl['body'] = preg_replace('/\{if activation is enabled\}(.*)\{\/if\}/', '', $mail_tpl['body']);
		}
		else
		{
			$activation_link = RL_URL_HOME . ADMIN .'/index.php?controller=listings&action=remote_activation&id='. $listing_id . '&hash='. md5($rlDb -> getOne('Date', "`ID` = '{$listing_id}'", 'listings'));
			$activation_link = '<a href="'. $activation_link .'">'. $activation_link .'</a>';
			$mail_tpl['body'] = preg_replace('/(\{if activation is enabled\})(.*)(\{activation_link\})(.*)(\{\/if\})/', '$2 '. $activation_link .' $4', $mail_tpl['body']);
		}
		$rlMail -> send($mail_tpl, $config['notifications_email']);

		// clear saved step for current listing
		$update_step = array(
			'fields' => array(
				'Cron' => '0',
				'Cron_notified' => '0',
				'Cron_featured' => '0',
				'Last_step' => '',
			),
			'where' => array(
				'ID' => $listing_id
			)
		);
		$rlActions -> updateOne($update_step, 'listings');

		$data['done'] = true;
		$data['message'] = $config['iFlynaxConnect_listing_auto_approval'] ? $lang['notice_after_listing_adding_auto'] : $lang['notice_after_listing_adding'];
	}
	else
	{
		$data['message'] = 'listing data is empTy';
	}
}

$alert = false;
if ( !empty($errors) )
{
	$alert = array('message' => implode("\n", $errors));
}
$iPhone -> printAsXml($data, $alert);