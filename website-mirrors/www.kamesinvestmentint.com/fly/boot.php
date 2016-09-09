<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: BOOT.PHP
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

global $page_info, $listing_data, $config, $lang, $rlSmarty, $photos, $tpl_settings;

if ( $page_info['Controller'] == 'listing_details' ) {

	/* add price to meta data */
	if ( $listing_data[$config['smd_price_key']] ) {
		$price = $listing_data[$config['smd_price_key']];
		$price = explode( '|', $price );

		$price = array (
			'currency_code' => $price[1],
			'currency' => $price[1] ? $lang['data_formats+name+' . $price[1]] : '',
			'value' => $GLOBALS['rlValid'] -> str2money($price[0]),
			'og_value' => $price[0] . '.00'
		);

		$rlSmarty -> assign('smd_price', $price);
	}

	/* add second field of product to meta data */
	if ( $short_info = $GLOBALS['rlListings'] -> getShortDetails($listing_data['ID']) ) {
		$count_fields = 1;
		foreach ($short_info['fields'] as $key => $field) {
			if ( $count_fields >= 2 && $key != $config['smd_price_key'] ) {
				$smd_second_field['key'] = $field['name'];
				$smd_second_field['value'] = $field['value'];
				break;
			}

			$count_fields++;
		}

		if ( $smd_second_field['key'] && $smd_second_field['value'] )
			$rlSmarty -> assign('smd_second_field', $smd_second_field);
	}

	/* add large main photo of listing */
	if ( $listing_data['Main_photo'] && is_array($photos[0]) ) {
		$smd_logo = $tpl_settings['type'] == 'responsive_42' ? $photos[0]['Photo'] : RL_FILES_URL . $photos[0]['Photo'];
		$rlSmarty -> assign('smd_logo', $smd_logo);
	} else {
		/* add custom logo for all pages except listing details */
		if ( $config['smd_logo'] && file_exists(RL_PLUGINS . 'socialMetaData' . RL_DS . $config['smd_logo']) ) {
			$smd_logo = RL_URL_HOME . 'plugins/socialMetaData' . '/' . $config['smd_logo'];
			$rlSmarty -> assign('smd_logo', $smd_logo);
		}		
	}

	/* add default meta description if it not exist */
	if ( !$page_info['meta_description'] && $lang['pages+meta_description+view_details'] )
		$page_info['meta_description'] = $lang['pages+meta_description+view_details'];
} else {
	/* add custom logo for all pages except listing details */
	if ( $config['smd_logo'] && file_exists(RL_PLUGINS . 'socialMetaData' . RL_DS . $config['smd_logo']) ) {
		$smd_logo = RL_URL_HOME . 'plugins/socialMetaData' . '/' . $config['smd_logo'];
		$rlSmarty -> assign('smd_logo', $smd_logo);
	}
}