<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: SIMILAR_LISTINGS.INC.PHP
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
	
$reefless -> loadClass('Categories');

if ($_GET['action'] == 'build')
{
	$category_key = $rlValid -> xSql($_GET['key']);

	/* get current category info */
	$category_info = $rlDb -> fetch( array('ID', 'Key'), array( 'Key' => $category_key ), "AND `Status` <> 'trash'", null, 'categories', 'row' ) ;
	$category_info = $rlLang -> replaceLangKeys( $category_info, 'categories', array( 'name' ), RL_LANG_CODE, 'admin' );
	$rlSmarty -> assign_by_ref( 'category_info', $category_info );
	
	if ( !$category_info )
	{
		$sError = true;
	}
	else
	{
		$rlSmarty -> assign('cpTitle', $category_info['name']);
		
		$reefless -> loadClass( 'Builder', 'admin' );

		$rlSmarty -> assign('no_groups', true);
		
		switch ($_GET['form']){
			case 'similar_listings_form':
				$rlBuilder -> rlBuildTable = 'similar_listings_form';
				$rlBuilder -> rlBuildField = 'Field_ID';
			
				/* additional bread crumb step */
				$bcAStep = $lang['featured_form_builder'];
				break;
		}


		/* get available fields for current category */
		$a_fields = $rlBuilder -> getAvailableFields($category_info['ID']);
		$relations = $rlBuilder -> getFormRelations( $category_info['ID'] );
	
		$rlSmarty -> assign_by_ref( 'relations', $relations );
	
		foreach ( $relations as $rKey => $rValue )
		{
			$no_groups[] = $relations[$rKey]['Key'];
			
			$f_fields = $relations[$rKey]['Fields'];
			
			if ( $relations[$rKey]['Group_ID'] )
			{
				foreach ( $f_fields as $fKey => $fValue )
				{
					$no_fields[] = $f_fields[$fKey]['Key'];
				}
			}
			else
			{
				$no_fields[] = $relations[$rKey]['Fields']['Key'];
			}
		}
	
		/* get listing fields */
		if ( !empty($a_fields) )
		{	
			$a_fields[] = 88;
			$add_cond = "AND(`ID` = '". implode("' OR `ID` = '", $a_fields) ."') ";
			$add_cond .="AND (`Type` != 'checkboxes' AND `Type` != 'textarea' AND `Type` != 'price' ) ";
			$add_cond .="AND (`Key` != 'account_address_on_map') ";
			
			$fields = $rlDb -> fetch( array('ID', 'Key', 'Type', 'Status'), null, "WHERE `Status` <> 'trash' {$add_cond}", null, 'listing_fields' );
			$fields = $rlLang -> replaceLangKeys( $fields, 'listing_fields', array( 'name' ), RL_LANG_CODE, 'admin' );			

			// hide already using fields
			if ( !empty( $no_fields ) )
			{
				foreach ($fields as $fKey => $fVal)
				{
					if ( false !== array_search( $fields[$fKey]['Key'], $no_fields ) )
					{
						$fields[$fKey]['hidden'] = true;
					}
				}
			}
		
			$rlSmarty -> assign_by_ref( 'fields', $fields );
		}
		/* register ajax methods */
		$rlXajax -> registerFunction( array( 'buildForm', $rlBuilder, 'ajaxBuildForm' ) );
	}
}else
{
	$aUrl['controller'] = 'categories';
	$reefless -> redirect($aUrl);
}

