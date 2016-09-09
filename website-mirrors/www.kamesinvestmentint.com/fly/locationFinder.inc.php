<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: LOCATIONFINDER.INC.PHP
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

/* get available listing groups */
$rlDb -> setTable('listing_groups');
$groups = $rlDb -> fetch(array('ID', 'Key'), array('Status' => 'active'));
$groups = $rlLang -> replaceLangKeys( $groups, 'listing_groups', array( 'name' ), RL_LANG_CODE, 'admin' );

$rlSmarty -> assign_by_ref('groups', $groups);
$rlSmarty -> assign_by_ref('Actions');
$rlSmarty -> assign_by_ref('Notice');

$reefless -> loadClass('LocationFinder', null, 'locationFinder');
$rlXajax -> registerFunction( array( 'save', $rlLocationFinder, 'ajaxSave' ) );