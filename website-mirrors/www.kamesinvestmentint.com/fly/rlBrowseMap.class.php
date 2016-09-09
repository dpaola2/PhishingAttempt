<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLBROWSEMAP.CLASS.PHP
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

class rlBrowseMap extends reefless
{
	/**
	* load listing details for map marker baloon
	*
	* @param int $id - lisitng ID
	*
	* @todo html - content for map marker baloon
	**/
	function loadListingData( $id = false )
	{
		global $rlListingTypes, $lang, $config, $rlListings, $pages, $rlSmarty;
		
		$id = (int)$_GET['id'];
		
		if ( !$id )
		{
			echo $lang['sbd_listing_unavailable'];
			return;
		}
		
		$listing = $rlListings -> getShortDetails($id);
		$listing_type = $rlListingTypes -> types[$listing['Listing_type']];
		
		if ( $listing )
		{
			$html = '<table class="sbd_baloon"><tr>';
			
			$link = SEO_BASE;
			$link .= $config['mod_rewrite'] ? $pages[$listing_type['Page_key']] .'/'. $listing['Category_path'] .'/'. $rlSmarty -> str2path($listing['listing_title']) .'-'. $id .'.html' : 'index.php?page='. $pages[$listing_type['Page_key']] .'&amp;id=' . $listing['ID'];
			
			if ( $listing_type['Photo'] )
			{
				$photo = $this -> getOne('Thumbnail', "`Listing_ID` = '{$id}' AND `Status` = 'active' ORDER BY `Type` DESC, `ID` ASC", 'listing_photos');
				if ( $photo )
				{
					$html .= '<td class="thumbnail"><div>';
					if ( $listing_type['Page'] )
					{
						$html .= '<a target="_blank" href="'. $link .'">';
					}
					$html .= '<img alt="'. $listing['listing_title'] .'" title="'. $listing['listing_title'] .'" src="'. RL_FILES_URL . $photo .'" />';
					if ( $listing_type['Page'] )
					{
						$html .= '</a>';
					}
					$html .= '</div></td>';
				}
			}
			
			$html .= '<td valign="top"><div class="sbd_title"><a target="_blank" href="'. $link .'">'. $listing['listing_title'] .'</a></div>';
			
			if ( $listing['fields'] )
			{
				$html .= '<table class="table">';
				foreach ($listing['fields'] as $field)
				{
					$html .= '<tr><td class="name"><div title="'. $field['name'] .'">'. $field['name'] .'</div></td><td class="value">'. $field['value'] .'</td></tr>';
				}
				$html .= '</table>';
			}
			
			$html .= '</td>';
			
			$html .= '</tr></table>';
			
			echo $html;
		}
		else
		{
			echo $lang['sbd_listing_unavailable'];
			return;
		}
	}
}