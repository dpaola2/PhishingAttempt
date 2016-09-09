<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: REQUESTS.INC.PHP
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

$data = array();

switch($_POST['cmd'])
{
	case 'multifield':
		// deep checking ;)
		if ( file_exists(RL_PLUGINS .'multiField'. RL_DS .'rlMultiField.class.php') )
		{
			$reefless -> loadClass('MultiField', null, 'multiField');

			$mf_parent = $rlValid -> xSql($_POST['parent']);
			$mf_childrens = $rlMultiField -> getMDF($mf_parent, 'alphabetic');

			if ( !empty($mf_childrens) )
			{
				foreach($mf_childrens as $key => $entry)
				{
					array_push($data, array(
							'key' => $entry['Key'],
							'name' => $iPhone -> pValid(!empty($entry['name']) ? $entry['name'] : $lang[$entry['pName']])
						)
					);
				}
			}
		}
		break;

	case 'make_model':
		$make = (int)$_POST['mmodel'];
		$type = $rlValid -> xSql($_POST['type']);

		if ( $make && $type )
		{
			$reefless -> loadClass('Categories');

			$models = $rlCategories -> getCategories($make, $type);
			if ( !empty($models) )
			{
				foreach($models as $key => $model)
				{
					array_push($data, array(
						'key' => $model['ID'],
						'name' => $iPhone -> pValid(!empty($model['name']) ? $model['name'] : $lang[$model['pName']])
					));
				}
			}
		}
		break;
}

$iPhone -> printAsXml($data);