<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: CONFIG.INC.PHP
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

define('APP_USE_GZIP', false);

define('APP_SHORT_FORM_FIELDS_LIMIT', 5); 
define('APP_FEATUREDS_FIELDS_LIMIT', 3);

// iphone router paths
define('RL_IPHONE_CONTROLLERS', RL_PLUGINS .'iFlynaxConnect'. RL_DS .'controllers'. RL_DS);
define('RL_IPHONE_CLASSES', RL_PLUGINS .'iFlynaxConnect'. RL_DS .'classes'. RL_DS);