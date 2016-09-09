<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLBOOOKINGFIELDS.CLASS.PHP
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

class rlBoookingFields extends reefless
{
	/**
	* @var language class object
	**/
	var $rlLang;

	/**
	* @var validator class object
	**/
	var $rlValid;

	/**
	* @var configurations class object
	**/
	var $rlConfig;

	/**
	* @var actions class object
	**/
	var $rlActions;

	/**
	* @var notice class object
	**/
	var $rlNotice;

	/**
	* class constructor
	**/
	function rlBoookingFields()
	{
		global $rlLang, $rlValid, $rlConfig, $rlActions, $rlNotice;

		$this -> rlLang   =  & $rlLang;
		$this -> rlValid  =  & $rlValid;
		$this -> rlConfig =  & $rlConfig;
		$this -> rlActions = & $rlActions;
		$this -> rlNotice =  & $rlNotice;
	}

	/**
	* create new booking field
	*
	* @param string $type - field type
	* @param array $data - new field information
	* @param array $langs - available system languages
	*
	* @return bool
	**/
	function createBookingField( $type, $data, $langs )
	{
		$info = array();
		$lang_keys = array();

		$max_postition = $this -> getRow("SELECT MAX(`Position`) AS `Max` FROM `". RL_DBPREFIX ."booking_fields` LIMIT 1");

		// insert field information
		$info = array(
			'Key' => $data['key'],
			'Type' => $type,
			'Required' => $data['required'],
			'Status' => $data['status'],
			'Position' => $max_postition['Max'] + 1
		);

		foreach ($langs as $key => $value)
		{
			$lang_keys[] = array(
				'Code' => $langs[$key]['Code'],
				'Module' => 'common',
				'Status' => 'active',
				'Key' => 'booking_fields+name+' . $data['key'],
				'Value' => $data['names'][$langs[$key]['Code']],
			);

			if ( !empty($data['description'][$langs[$key]['Code']]) )
			{
				$lang_keys[] = array(
					'Code' => $langs[$key]['Code'],
					'Module' => 'common',
					'Status' => 'active',
					'Key' => 'booking_fields+description+' . $data['key'],
					'Value' => $data['description'][$langs[$key]['Code']],
				);
			}
		}

		// generate lang keys and type's additional information
		switch ( $type ){
			case 'text':
				if ( !empty($data['condition']) )
				{
					$info['Condition'] = $data['condition'];
				}
				$info['Values'] = $data['maxlength'] > 255 ? 255 : $data['maxlength'];

				foreach ($langs as $key => $value)
				{
					if ( !empty($data['default'][$langs[$key]['Code']]) )
					{
						$info['Default'] = 1;

						$lang_keys[] = array(
							'Code' => $langs[$key]['Code'],
							'Module' => 'common',
							'Status' => 'active',
							'Key' => 'booking_fields+default+' . $data['key'],
							'Value' => $data['default'][$langs[$key]['Code']],
						);
					}
				}

				// alter table field
				$alter = "ALTER TABLE `" . RL_DBPREFIX . "booking_requests` ADD `{$data['key']}` VARCHAR( {$data['maxlength']} ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
			break;

			case 'textarea':
				$info['Values'] = empty($data['maxlength']) ? 500 : $data['maxlength'];

				// alter table field
				$alter = "ALTER TABLE `" . RL_DBPREFIX . "booking_requests` ADD `{$data['key']}` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
			break;

			case 'number':
				$info['Default'] = $data['max'];

				// alter table field
				$alter = "ALTER TABLE `" . RL_DBPREFIX . "booking_requests` ADD `{$data['key']}` DOUBLE NOT NULL";
			break;

			case 'bool':
				$info['Default'] = $data['default'];

				// alter table field
				$alter = "ALTER TABLE `" . RL_DBPREFIX . "booking_requests` ADD `{$data['key']}` ENUM( '0', '1' ) DEFAULT '{$data['default']}' NOT NULL";
			break;
		};

		if ( $this -> query( $alter ) )
		{
			// run additional alter query
			if ( !empty($additional_alter) )
			{
				if ( !$this -> query( $additional_alter ) )
				{
					trigger_error( "Can not create additional booking field (MYSQL ALTER QUERY FAIL)", E_WARNING );
					$GLOBALS['rlDebug'] -> logger("Can not create additional booking field (MYSQL ALTER QUERY FAIL)");
				}
			}

			// insert new fiels information
			$this -> rlActions -> insertOne( $info, 'booking_fields' );

			// insert new fiels language's keys
			$this -> rlActions -> insert( $lang_keys, 'lang_keys' );

			return true;
		}
		else 
		{
			trigger_error( "Can not create new booking field (MYSQL ALTER QUERY FAIL)", E_WARNING );
			$GLOBALS['rlDebug'] -> logger("Can not create new booking field (MYSQL ALTER QUERY FAIL)");
		}

		return false;
	}

	/**
	* edit booking field
	*
	* @param string $type - field type
	* @param array $data - new field information
	* @param array $langs - available system languages
	*
	* @return bool
	**/
	function editBookingField( $type, $data, $langs )
	{
		$info = array();
		$lang_keys = array();
		$lang_rewrite = true;

		// edit field information
		$info['where'] = array(
			'Key' => $data['key']
		);

		$info['fields'] = array(
			'Required' => $data['required'],
			'Status' => $data['status']
		);

		foreach ($langs as $key => $value)
		{
			if ( $this -> getOne('ID', "`Key` = 'booking_fields+name+{$data['key']}' AND `Code` = '{$langs[$key]['Code']}'", 'lang_keys') )
			{
				// edit names
				$update_phrases = array(
					'fields' => array(
						'Value' => $data['names'][$langs[$key]['Code']]
					),
					'where' => array(
						'Code' => $langs[$key]['Code'],
						'Key' => 'booking_fields+name+' . $data['key']
					)
				);

				// update
				$this -> rlActions -> updateOne( $update_phrases, 'lang_keys' );
			}
			else
			{
				// insert names
				$insert_phrases = array(
					'Code' => $langs[$key]['Code'],
					'Module' => 'common',
					'Key' => 'booking_fields+name+' . $data['key'],
					'Value' => $data['names'][$langs[$key]['Code']]
				);

				// insert
				$this -> rlActions -> insertOne( $insert_phrases, 'lang_keys' );
			}

			$exist_description = $this -> getOne('ID', "`Key` = 'booking_fields+description+{$data['key']}' AND `Code` = '{$langs[$key]['Code']}'", 'lang_keys');
			if ( $exist_description )
			{
				// update fields description
				$lang_keys_desc['where'] = array(
					'Code' => $langs[$key]['Code'],
					'Key' => 'booking_fields+description+' . $data['key']
				);

				$lang_keys_desc['fields'] = array(
					'Value' => $data['description'][$langs[$key]['Code']]
				);
				$this -> rlActions -> updateOne( $lang_keys_desc, 'lang_keys' );
			}
			else
			{
				// insert new description
				if ( !empty($data['description'][$langs[$key]['Code']]) )
				{
					$field_description = array(
						'Code' => $langs[$key]['Code'],
						'Module' => 'common',
						'Status' => 'active',
						'Key' => 'booking_fields+description+' . $data['key'],
						'Value' => $data['description'][$langs[$key]['Code']],
					);
					$this -> rlActions -> insertOne( $field_description, 'lang_keys' );
				}
			}
		}

		// generate lang keys and types for additional information
		switch ( $type ){
			case 'text':
				$info['fields']['Condition'] = $data['condition'];
				$info['fields']['Values'] = $data['maxlength'] > 255 ? 255 : $data['maxlength'];

				$additional_alter = "ALTER TABLE `". RL_DBPREFIX ."booking_requests` CHANGE `{$data['key']}` `{$data['key']}` VARCHAR({$info['fields']['Values']}) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";

				foreach ($langs as $key => $value)
				{
					if ( !empty($data['default'][$langs[$key]['Code']]) )
					{
						$info['fields']['Default'] = 1;

						$lang_keys[] = array(
							'Code' => $langs[$key]['Code'],
							'Module' => 'common',
							'Status' => 'active',
							'Key' => 'booking_fields+default+' . $data['key'],
							'Value' => $data['default'][$langs[$key]['Code']],
						);
					}
					else 
					{
						$info['fields']['Default'] = 0;
					}
				}

			break;

			case 'textarea':
				$info['fields']['Values'] = $data['maxlength'];
			break;

			case 'number':
				$info['fields']['Default'] = $data['min'];
				$info['fields']['Values'] = $data['max'];
			break;
                    
                        case 'bool':
				$info['fields']['Default'] = $data['default'];
			break;
		};

		if ( !empty($info) )
		{
			// run additional alter query
			if ( !empty($additional_alter) )
			{
				if ( !$this -> query( $additional_alter ) )
				{
					trigger_error( "Can not create additional booking field (MYSQL ALTER QUERY FAIL)", E_WARNING );
					$GLOBALS['rlDebug'] -> logger("Can not create additional booking field (MYSQL ALTER QUERY FAIL)");
				}
			}
			// insert new fiels information
			$this -> rlActions -> updateOne( $info, 'booking_fields' );
		}

		if ( !empty($lang_keys) && $lang_rewrite === true )
		{
			// delete languages phrases by current field
			$lSql = "DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` REGEXP '^booking_fields(.*){$data['key']}_([0-9][a-zA-Z]*)$' AND `Key` <> 'booking_fields+name+{$data['key']}'";
			$this -> query( $lSql );

			// insert new fiels language's keys
			$this -> rlActions -> insert( $lang_keys, 'lang_keys' );
		}

		return true;
	}

	/**
	* delete listing field
	*
	* @package ajax
	*
	* @param string $key - field key
	*
	**/
	function ajaxDeleteLField( $key )
	{
		global $_response;

		if ( $key == 'email' || $key == 'first_name' || $key == 'last_name' )
		{
			$error = $this -> rlNotice -> createError( str_replace('{field}', $GLOBALS['lang']['booking_fields+name+'.$key], $GLOBALS['lang']['field_protected']) );
			$_response -> assign( 'error_block', 'innerHTML', $error );

			$_response -> script( "$('#notice_obj').fadeOut('slow');" );
			$_response -> script( "$('#error_obj').fadeIn('slow');" );

			return $_response;
		}

		// get field ID
		$field_id = $this -> getOne('ID', "`Key` = '{$key}'", 'booking_fields');

		if ( !$field_id )
		{
			trigger_error( "Can not delete booking field, field with requested key does not exist", E_WARNING );
			$GLOBALS['rlDebug'] -> logger("Can not delete booking field, field with requested key does not exist");

			return $_response;
		}

		// DROP field from the lsitings table
		$dSql = "ALTER TABLE `" . RL_DBPREFIX . "booking_requests` DROP `{$key}` ";

		if ( $this -> query( $dSql ) )
		{
			// delete information from listing_fields table
			$iSql = "DELETE FROM `" . RL_DBPREFIX . "booking_fields` WHERE `Key` = '{$key}'";
			$this -> query( $iSql );

			// delete languages phrases by current field
			$lSql = "DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'booking_fields+name+{$key}' OR `Key` = 'booking_fields+default+{$key}'";
			$this -> query( $lSql );

			$_response -> script("bookingFieldsGrid();");

			$notice = $this -> rlNotice -> createNotice( $GLOBALS['lang']['field_deleted'] );
			$_response -> assign( 'notice_block', 'innerHTML', $notice );
			$_response -> script( "$('#notice_obj').fadeIn('slow');" );
		}

		return $_response;
	}
}