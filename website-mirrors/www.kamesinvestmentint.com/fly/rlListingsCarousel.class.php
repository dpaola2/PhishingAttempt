<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLLISTINGSCAROUSEL.CLASS.PHP
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

class rlListingsCarousel extends reefless
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
	* class constructor
	**/
	function rlListingsCarousel()
	{
		global $rlLang, $rlValid;
		
		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
	}
	
	/**
	* 
	* update configuration for carousel 
	*
	**/
	function updateCarouselBlock()
	{
		global $rlDb;
		
		$box = $rlDb -> getAll("SELECT * FROM `". RL_DBPREFIX ."listings_carousel` WHERE `Status` = 'active' ");
		
		$content = 'global $rlSmarty, $carousel_options;';
		$content .= 'if ( !$_REQUEST["xjxfun"] ){unset($_SESSION["carousel"]);}';		
		if( $box )
		{
			$content .='$carousel_options = array(';
		
			foreach($box as $key => $item)
			{
				
				$block_ids = explode(',',$item['Block_IDs']);
				foreach($block_ids as $keyId => $itemId)
				{
					if($itemId)
					{
						$content .=(int)$itemId.'=> array( ';
						$content .='"Direction" => "'.$item['Direction'].'",';
						$content .='"Number" => "'.$item['Number'].'",';
						$content .='"Delay" => "'.$item['Delay'].'",';
						$content .='"Per_slide" => "'.$item['Per_slide'].'",';
						$content .='"Visible" => "'.$item['Visible'].'",';
						$content .='"Round" => "'.$item['Round'].'"';						
						$content .='),';
					}
				}
			}
			$content = substr($content, 0, -1);
			$content .= ');';
		}

		$content .='$rlSmarty -> assign("carousel_options", $carousel_options);';
		
		if($rlDb -> query("UPDATE  `". RL_DBPREFIX ."hooks` SET `Code` = '{$content}' WHERE `Name` = 'init' AND `Plugin` = 'listings_carousel'  LIMIT 1 ;"))
		{
			return true;
		}
	}
	
	/**
	* change content block for carousel
	*
	* @param array $contents -  options of carousel
	*
	**/
	function changeContentBlock( $contents = false )
	{
		global $rlSmarty, $rlListings, $carousel_options, $blocks, $rlDb, $tpl_settings, $page_info;
		
		$array_templates = array("auto_flatty","realty_flatty","pets_flatty","boats_flatty");
		
		if ( !$_REQUEST['xjxfun'] )
		{
			$_SESSION['carousel']['all_ids'] = $rlListings -> selectedIDs;
		}
		
		if ( in_array($tpl_settings['name'], $array_templates) && $tpl_settings['type'] == 'responsive_42' && $page_info['Key'] == 'home')
		{
			$sql = "SELECT `Key` FROM `". RL_DBPREFIX ."blocks` ";
			$sql .= "WHERE `Status` = 'active' AND `Key` RLIKE 'ltfb_(.*)$' AND (FIND_IN_SET( '{$page_info['ID']}', `Page_ID` ) > '0' OR `Sticky` = '1') AND FIND_IN_SET( `Side`, 'top,middle,bottom') ORDER BY FIND_IN_SET( `Side`, 'top,middle,bottom') LIMIT 1";
			$first_featured = $rlDb -> getRow($sql);
		}
		
		foreach ($blocks as $sKey => $sVal)
		{
			foreach ($contents as $key => $val)
			{
				if( $sVal['ID'] == $key )
				{
					if($blocks[$sKey]['Type'] != 'smarty')
					{
						$option = $carousel_options[$blocks[$sKey]['ID']];
						/* get field/value */
						// preg_match('/getListings\(\s"(\w*)",\s"(\w*)",\s"([0-9]*)",\s"([0-9]*?)"\s\);/', $sVal['Content'], $matches);
						preg_match('/getListings\(\s"([\w,]*)",\s"(\w*)",\s"([0-9]*)",\s"([0-9]*?)"\s\);/', $sVal['Content'], $matches);
						/* get replace type and listings */
						
						$content_block = 'global $rlSmarty, $reefless;
							$reefless -> loadClass("ListingsBox", null, "listings_box");
							global $rlListingsBox;
							$listings_box = $rlListingsBox -> getListings( "'.$matches[1].'", "'.$matches[2].'", '.$option['Visible'].', "'.$matches[4].'" );
							foreach($listings_box as $key => $val)
							{
								$ids[] = $val["ID"];
								$_SESSION["carousel"]["all_ids"][] = $val["ID"];
							}
							$_SESSION["carousel"]["'.$sVal['Key'].'"] = $ids;
							$rlSmarty -> assign_by_ref( "listings", $listings_box );
							$rlSmarty -> assign( "type", "listings" );
							$rlSmarty -> display( RL_PLUGINS . "listings_carousel" . RL_DS . "carousel.block.tpl" );';
						
						$blocks[$sKey]['Content'] = $content_block;						
						$blocks[$sKey]['options'] = "listing_box|".$blocks[$sKey]['Key']."|".$matches[1]."|".$matches[2]."|".$matches[4];

					}
					else
					{
						if ($first_featured['Key'] != $sVal['Key'])
						{
							/* get field/value */
							preg_match("/listings=(.*)\s+type='(\w+)'(\s+field='(\w+)')?(\s+value='(\w+)')?/", $sVal['Content'], $matches);
							$blocks[$sKey]['Content'] ='{include file=$smarty.const.RL_PLUGINS|cat:"listings_carousel"|cat:$smarty.const.RL_DS|cat:"carousel.block.tpl" listings='.$matches[1].' type="'.$matches[2].'"}';
							$blocks[$sKey]['options'] = "featured|".$blocks[$sKey]['Key']."|".$matches[2]."|".$matches[4]."|".$matches[6];
						}
						
					}
				}
			}
		}
		$rlSmarty -> assign_by_ref("blocks", $blocks);
	}
	
	/**
	* load  listings
	*
	* @package xAjax
	*
	* @param varcher $id -  id li
	* @param int $limit -  limit
	* @param varchar $options -  options
	* @param varchar $number -  max listings
	* @param bool $priceTag -  price tag
	*
	**/
	function ajaxLoadListings( $id = false, $limit = 1, $options = false , $number = false, $priceTag = false)
	{
		global $_response, $rlListings, $rlListingTypes, $rlSmarty, $reefless, $config, $tpl_settings, $lang;

		// $id = (int)$id;
		// if ( !$id )
		// {
			// return $_response;
		// }
		
		$GLOBALS['rlValid'] -> sql($options);
		$options = explode('|', $options);

		// if limit more number
		if ( $number - $limit < 0 )
		{
			$limit = $number;
		}

		//get listing types
		$listing_types = $rlListingTypes -> types;

		// get listings by type
		if ( $options[0] == 'featured' )
		{
			$rlListings -> selectedIDs = $_SESSION['carousel']['all_ids'];
			$listings = $rlListings -> getFeatured($options[2], $limit, $options[3], $options[4]);
		}
		else
		{
			// get listing box
			$reefless -> loadClass('ListingsBox', null, 'listings_box');
			if ( $options[4] )
			{
				$rlListings -> selectedIDs = $_SESSION['carousel']['all_ids'];
			}
			else
			{
				$rlListings -> selectedIDs = $_SESSION['carousel'][$options[1]];
			}
			$listings = $GLOBALS["rlListingsBox"] -> getListings($options[2], $options[3], $limit, '1');
		}
		// print_r($_SESSION);
		if ($listings)
		{
			
			// add new listings
			foreach($listings as $key => $listing)
			{
				if ($options[0] != 'featured')
				{
					$_SESSION['carousel'][$options[1]][] = $listing['ID'];
				}
				if (!in_array( $listing['ID'], $_SESSION['carousel']['all_ids'])) 
				{
					$_SESSION['carousel']['all_ids'][] = $listing['ID'];
				}
				
				// assign listing, type and page key
				$listing_type = $listing['Listing_type'];
				$page_key = $listing_types[$listing_type]['Page_key'];
				$rlSmarty -> assign('type', $listing_type);
				$rlSmarty -> assign('page_key', $page_key);
				$rlSmarty -> assign('featured_listing', $listing);
				
				if ($tpl_settings['type'] == 'responsive_42')
				{
					$tpl = 'blocks' . RL_DS . 'featured_item.tpl';
					
					// add items in the box
					$li_id =  $options[1] .'_'. $listing['ID'];
					$li = '<li id="'. $options[1] .'_'. $listing['ID']. '" class="item"></li>';			
					$_response -> script( "$('#carousel_". $options[1] ." ul.featured>li').eq(". $id .").after('". $li ."');" );
					$_response -> append( $li_id, 'innerHTML', $rlSmarty -> fetch( $tpl, null, null, false ) );
					$_response -> script( "$('#carousel_". $options[1] ." ul.featured>li#".$li_id.">li').unwrap();" );
				}
				else
				{
					$tpl = RL_PLUGINS . 'listings_carousel' . RL_DS . 'carousel.listing.tpl';
					
					// add items in the box
					$li_id =  $options[1] .'_'. $listing['ID'];
					$li = '<li id="'. $options[1] .'_'. $listing['ID']. '" class="item"></li>';			
					$_response -> script( "$('#carousel_". $options[1] ." ul.featured>li').eq(". $id .").after('". $li ."');" );
					$_response -> assign( $li_id, 'innerHTML', $rlSmarty -> fetch( $tpl, null, null, false ) );
				}
				$id++;
			}
		}
		$count_listings = count($listings);
		if ( !$listings )
		{
			$conf = 'rlCarousel["carousel_'. $options[1].'"] = 0';
			$count_listings = 0;
		}
		else
		{
			$conf = 'rlCarousel["carousel_'. $options[1].'"] = '. ($number - $count_listings);
		}
		$_response -> script( $conf );

		if ( $GLOBALS['aHooks']['currencyConverter'] && $config['currencyConverter_featured'] )
		{
			$block_key = 'carousel_'.$options[1];
		}

		$_response -> script( "$('#carousel_". $options[1] ."').data('carousel')._afterLoadAjax( '". $block_key ."', '".$count_listings."' );" );

		return $_response;
	}

	/**
	* delete box 
	*
	* @package xAjax
	*
	* @param int $id -  id
	*
	**/
	function ajaxDeleteCarouselBox( $id = false )
	{
		global $_response;
		$id = (int)$id;
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$_response -> redirect( RL_URL_HOME . ADMIN . '/index.php?action=session_expired' );
			return $_response;
		}

		if ( !$id )
		{
			return $_response;
		}

		// delete box
		$this -> query("DELETE FROM `". RL_DBPREFIX ."listings_carousel` WHERE `ID` = '{$id}' LIMIT 1");

		// update carousel boxs
		$this -> updateCarouselBlock();

		$_response -> script("
			listingsCarousel.reload();
			printMessage('notice', '{$GLOBALS['lang']['block_deleted']}')
		");

		return $_response;
	}
}
