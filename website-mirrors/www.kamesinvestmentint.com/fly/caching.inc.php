<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: CACHING.INC.PHP
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

$reefless -> loadClass('Search');
$reefless -> loadClass('Categories');
$reefless -> loadClass('Account');

$arrayTypes = array('price', 'select', 'radio', 'mixed', 'checkbox');
$skipFieldTypes = array('file', 'image', 'accept');
$dfCurrency = $rlCategories -> getDF('currency', 'position');
$data = array();

foreach( $rlListingTypes -> types as $key => $type )
{
	// listing types
	$index = count($data['listingType']);
	$data['listingType'][$index] = array(
		'key' => $type['Key'],
		'name' => $type['name'],
		'info' => array(
			'search_page' => $type['Search_page'] ? true : false,
			'photo' => $type['Photo'] ? true : false
		)
	);

	// search forms
	$formKey = strtolower("{$key}_{$config['iFlynaxConnect_search_form']}");

	if ( $type['Search_page'] )
	{
		if ( $tmpSearchForm = $rlSearch -> buildSearch($formKey, $key) )
		{
			$tmpSearchForm = $iPhone -> adaptBuildSearch($tmpSearchForm);
			foreach($tmpSearchForm as $sfKey => $sfEntry)
			{
				$fields = array();
				foreach($sfEntry['Fields'] as $ffKey => $field)
				{
					if ( empty($field['Type']) || in_array($field['Type'], $skipFieldTypes) )
						continue;

					$fIndex = count($fields);
					$fields[$fIndex] = array(
						'key' => $field['Key'],
						'type' => $field['Type'],
						'name' => $lang[$field['pName']]
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

					// Make Model?
					if ( $field['Key'] == 'Category_ID' )
					{
						$fields[$fIndex]['makeModel'] = true;
					}

					if ( !empty($field['Default']) )
					{
						$fields[$fIndex]['default'] = $field['Default'];
					}

					if ( in_array($field['Type'], $arrayTypes) && !$isMultiField )
					{
						$fSource = $field['Values'];
						$sKey = 'Key';

						if ( $field['Type'] == 'price' )
						{
							$fSource = $dfCurrency;
							$sKey = 'Key';
						}

						$fValues = array();
						foreach($fSource as $fvKey => $fvEntry)
						{
							$valuesKey = $fvEntry[$sKey];
							if ( $field['Type'] == 'radio' || $field['Type'] == 'checkbox' || $field['Type'] == 'select' )
							{
								$valuesKey = $fvEntry['ID'];
								if ( !empty($field['Condition']) )
								{
									$valuesKey = $fvEntry['Key'];
								}
							}

							array_push($fValues, array(
									'key' => strval($valuesKey),
									'name' => strval(!empty($lang[$fvEntry['pName']]) ? $lang[$fvEntry['pName']] : $fvEntry['name'])
								)
							);
						}
						$fields[$fIndex]['values'] = $fValues;
						unset($fValues);
					}
					else
					{
						if ( $field['Type'] == 'number' || $field['Type'] == 'text' || $field['Type'] == 'textarea' )
						{
							$fields[$fIndex]['maxlength'] = (int)$field['Values'];
						}
					}
				}

				$data['searchForms'][$key][] = array(
					'key' => $formKey,
					'name' => $sfEntry['name'],
					'fields' => $fields
				);
			}
			unset($tmpSearchForm);
		}
	}
}

// account types
$tmpAccountTypes = $rlAccount -> getAccountTypes('visitor');
if ( !empty($tmpAccountTypes) )
{
	foreach( $tmpAccountTypes as $key => $aType )
	{
		$aIndex = count($data['accountTypes']);
		$data['accountTypes'][$aIndex] = array(
			'key' => strval($aType['ID']),
			'name' => $aType['name'],
			'location' => (bool)$aType['Own_location']
		);
	}
	unset($tmpAccountTypes);
}

// custom pages
$data['customPages'] = $iPhone -> getCustomPages();

// languages
$data['languagesList'] = $iPhone -> getLanguagesList();

// lang keys
$data['langKeys'] = $iPhone -> getLangKeysForApp();

// print
$iPhone -> printAsXml($data);