<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLLISTINGNAVIGATOR.CLASS.PHP
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

class rlListingNavigator extends reefless
{
	/**
	* session name for listing type search
	**/
	var $lts = 'lnp_listingTypeSearch';
	
	/**
	* session name for keyword search
	**/
	var $kws = 'lnp_keyword_search';
	
	/**
	* session name for browse category page
	**/
	var $bc = 'lnp_browse_category';
	
	/**
	* session name for recently added page
	**/
	var $ra = 'lnp_recently_added';
	
	/**
	* session name for account details page
	**/
	var $al = 'lnp_account_listings';
	
	/**
	* class constructor
	**/
	function rlListingNavigator()
	{
		// TODO
	}
	
	/**
	* get navigation details by requested listing ID
	*
	* @param int $id - requested listing ID
	* @param int $pass_current_stack - passed current stack number
	* @param int $pass_current_index - passed current index
	*
	* @todo assign listing navigation data to the template
	**/
	function get( $id = false, &$listing_data, $pass_current_stack = false, $pass_current_index = false ) {
		global $rlSmarty, $page_info, $config, $pages, $listing_type, $listing_data, $listing_title;
		
		/* add cannonical */
		if ( $config['mod_rewrite'] ) {
			$real_url = $pages[$listing_type['Page_key']] .'/'. $listing_data['Cat_path'] .'/'. $rlSmarty -> str2path($listing_title) .'-' . $id . '.html';
			$seo_base = SEO_BASE;
			if ( $GLOBALS['geo_filter_data']['geo_url'] ) {
				$seo_base = str_replace($GLOBALS['geo_filter_data']['geo_url'] .'/', '', $seo_base);
			}
			$urlBase = (defined('RL_MOBILE') && RL_MOBILE) ? RL_MOBILE_URL : $seo_base;
			$page_info['canonical'] = $urlBase . $real_url;
		}

		/* define item key */
		$get_item = $this -> getItemKey($page_info, $id, $listing_data);
		$item = $_GET['request'] ? $_GET['request'] : $get_item;
		
		if ( !$item || !$id || !$_SESSION[$item] )
			return false;
		
		$current_stack = $pass_current_stack;
		$current_index = $pass_current_index;

		if ( $current_stack === false && $current_index === false ) {
			foreach ($_SESSION[$item]['stacks'] as $stack_id => &$stacks) {
				foreach ($stacks as $index => $listing) {
					if ( $id == $listing['ID'] ) {
						$current_stack = $stack_id;
						$current_index = $index;
					}
				}
			}
		}
		
		/* get previous listing data */
		if ( $_SESSION[$item]['stacks'][$current_stack][$current_index-1] ) {
			$data_prev = $_SESSION[$item]['stacks'][$current_stack][$current_index-1];
		}
		elseif ( $_SESSION[$item]['stacks'][$current_stack-1][count($_SESSION[$item]['stacks'][$current_stack-1]) - 1] ) {
			$data_prev = $_SESSION[$item]['stacks'][$current_stack-1][count($_SESSION[$item]['stacks'][$current_stack-1]) - 1];
		}
		else {
			if ( $pass_current_stack === false && $current_stack > 1 ) {
				$this -> getNextStack($item, $current_stack, 'prev');
				$this -> get($id, $listing_data, $current_stack-1, count($_SESSION[$item]['stacks'][$current_stack-1])); // we don't know which is the latest index in the stack :(
			}
		}
		
		if ( $data_prev ) {
			$rlSmarty -> assign_by_ref('lnp_data_prev', $data_prev);
		}
		
		/* get next listing data */
		if ( $_SESSION[$item]['stacks'][$current_stack][$current_index+1] ) {
			$data_next = $_SESSION[$item]['stacks'][$current_stack][$current_index+1];
		}
		elseif ( $_SESSION[$item]['stacks'][$current_stack+1][0] ) {
			$data_next = $_SESSION[$item]['stacks'][$current_stack+1][0];
		}
		else {
			if ( $pass_current_stack === false ) {
				if ( $this -> getNextStack($item, $current_stack, 'next') ) {
					$this -> get($id, $listing_data, $current_stack + 1, -1);
				}
			}
		}
		
		if ( $data_next ) {
			$rlSmarty -> assign_by_ref('lnp_data_next', $data_next);
		}
	}
	
	/**
	* get next stack data
	*
	* @param string $item - item key
	* @param int $stack - requested stack
	* @param string $direction - search direction, next or prev
	*
	* @todo populate next step and run get method
	**/
	function getNextStack( $item = false, $current_stack = false, $direction = 'next' ) {
		global $config, $rlListingTypes, $sorting;
		
		$stack = $direction == 'next' ? $current_stack + 1 : $current_stack - 1;
		
		switch ($item) {
			case $this -> lts:
				$this -> loadClass('Search');
			
				$GLOBALS['rlSearch'] -> fields = $_SESSION[$this -> lts]['data']['fields'];
			
				$listings = $GLOBALS['rlSearch'] -> search(
					$_SESSION[$this -> lts]['data']['data'],
					$_SESSION[$this -> lts]['data']['listing_type_key'],
					$stack,
					$config['listings_per_page']
				);
				
				if ( empty($listings) )
					return false;
				
				$this -> listingTypeSearch($listings, $stack);
				
				break;
				
			case $this -> kws;
				$this -> loadClass('Search');
				
				$GLOBALS['rlSearch'] -> fields['keyword_search'] = array(
					'Key' => 'keyword_search',
					'Type' => 'text'
				);
				
				$sorting = $_SESSION[$this -> kws]['data']['sorting'];
				$listings = $GLOBALS['rlSearch'] -> search($_SESSION[$this -> kws]['data']['data'], false, $stack, $config['listings_per_page']);
				
				if ( empty($listings) )
					return false;
				
				$this -> keywordSearch($listings, $stack);
				
				break;
				
			case $this -> bc;
				$this -> loadClass('Listings');
				
				$sorting = $_SESSION[$this -> bc]['data']['sorting'];
				$listings = $GLOBALS['rlListings'] -> getListings(
					$_SESSION[$this -> bc]['data']['category_id'],
					$_SESSION[$this -> bc]['data']['order_field'],
					$_SESSION[$this -> bc]['data']['sort_type'],
					$stack,
					$config['listings_per_page']
				);
				
				if ( empty($listings) )
					return false;
				
				$this -> browseCategory($listings, $stack);
				
				break;
				
			case $this -> ra;
				$this -> loadClass('Listings');
				
				$requested_type = $_SESSION['recently_added_type'];
				$listings = $GLOBALS['rlListings'] -> getRecentlyAdded($stack, $config['listings_per_page'], $requested_type);
				
				if ( empty($listings) )
					return false;
				
				$this -> recentlyAdded($listings, $stack);
				
				break;
				
			case $this -> al;
				$this -> loadClass('Listings');

				$sorting = $_SESSION[$this -> at]['data']['sorting'];
				$listings = $GLOBALS['rlListings'] -> getListingsByAccount(
					$_SESSION[$this -> at]['data']['account_id'],
					$_SESSION[$this -> at]['data']['sort_by'],
					$_SESSION[$this -> at]['data']['sort_type'],
					$stack,
				$config['listings_per_page']);
				
				if ( empty($listings) )
					return false;
				
				$this -> accountListings($listings, $stack);
				
				break;
		}
		
		return true;
	}
	
	/**
	* get item key by previous visited page key
	*
	* @param array $page_info - current page info
	* @param int $id - requested listing ID
	* @param array $listing_data - referent to listing data array
	*
	* @return item key
	**/
	function getItemKey( $page_info = false, $id = false, &$listing_data ) {
		if ( ereg('^lt_.*_search', $page_info['prev']) ) {
			$item = $this -> lts;
		}
		elseif ( $page_info['prev'] == 'search' ) {
			$item = $this -> kws;
		}
		elseif ( ereg('^lt_', $page_info['prev']) ) {
			$item = $this -> bc;
		}
		elseif ( $page_info['prev'] == 'listings' ) {
			$item = $this -> ra;
		}
		elseif ( ereg('^at_', $page_info['prev']) ) {
			$item = $this -> al;
		}
		else {
			$item = $this -> bc;
			
			$this -> directListing($id, $listing_data);
		}

		return $item;
	}
	
	/**
	* simulate the browse category behavior for the direct listing request
	*
	* @param int $id - requested listing ID
	* @param array $listing_data - referent to listing data array
	*
	* @todo prepare the data by listing category
	**/
	function directListing($id = false, &$listing_data) {
		if ( !$id || !$listing_data )
			return;
		
		$this -> loadClass('Listings');
		$listings = $GLOBALS['rlListings'] -> getListings($listing_data['Category_ID'], false, null, 1, 30);
		
		/* get sorting form fields */
		$sorting_fields = $GLOBALS['rlListings'] -> getFormFields($listing_data['Category_ID'], 'short_forms', $listing_data['Cat_type'] );
		foreach ( $sorting_fields as &$field ) {
			if ( $field['Details_page'] ) {
				$sorting[$field['Key']] = $field;
			}
		}
		unset($sorting_fields);
		
		$_SESSION[$this -> bc]['data'] = array(
			'category_id' => $listing_data['Category_ID'],
			'order_field' => false,
			'sort_type' => 'ASC',
			'sorting' => $sorting
		);
		
		$this -> browseCategory($listings, 1);
	}
	
	/**
	* populate the stack listings data
	*
	* @param array $listings - passed listings
	* @param int $pass_stack - passed stack
	* @param string $item - item key
	*
	* @return item key
	**/
	function populate( &$listings, $pass_stack = false, $item = false ) {
		global $config, $rlListingTypes, $pages, $rlValid;
		
		if ( empty($listings) )
			return;

		$stack = (int)$_GET['pg'] ? (int)$_GET['pg'] : 1;
		$work_stack = $pass_stack ? $pass_stack : $stack;

		/* clear stack array */
		$_SESSION[$item]['stacks'][$work_stack] = array();
		
		/* add listings to the array */
		foreach ($listings as &$listing) {
			if ( !$rlListingTypes -> types[$listing['Listing_type']]['Page'] )
				continue;

			$href = SEO_BASE;
			if ( $config['mod_rewrite'] ) {
				$href .= $pages[$rlListingTypes -> types[$listing['Listing_type']]['Page_key']] .'/'. 
							$listing['Path'] .'/'. $rlValid -> str2path($listing['listing_title']) .'-'. $listing['ID'] .'.html?request='. $item;
			}
			else {
				$href .= '?page='. $pages[$rlListingTypes -> types[$listing['Listing_type']]['Page_key']] .'&id='. $listing['ID'] .'&request='. $item;
			}
			
			$_SESSION[$item]['stacks'][$work_stack][] = array(
				'ID' => $listing['ID'],
				'listing_title' => $listing['listing_title'],
				'href' => $href
			);
		}
	}
	
	/**
	* Search results on listing type page
	*
	* @access Hook: searchMiddle
	**/
	function listingTypeSearch($pass_listings = false, $pass_stack = false) {
		global $listings, $rlListingTypes, $listing_type_key;
		
		if ( $_REQUEST['action'] == 'search' || $_SESSION[$this -> lts]['data']['listing_type_key'] != $listing_type_key ) {
			unset($_SESSION[$this -> lts]['stacks']);
		}
		
		$work_listings = $pass_listings ? $pass_listings : $listings;
		$this -> populate($work_listings, $pass_stack, $this -> lts);
	}
	
	/**
	* Keyword searh results page
	*
	* @access Hook: searchBottom
	**/
	function keywordSearch($pass_listings = false, $pass_stack = false) {
		global $listings;

		if ( $_POST['form'] == 'keyword_search' ) {
			unset($_SESSION[$this -> kws]['stacks']);
		}
		
		$work_listings = $pass_listings ? $pass_listings : $listings;
		$this -> populate($work_listings, $pass_stack, $this -> kws);
	}
	
	/**
	* browse categories page
	*
	* @access Hook: browseMiddle
	**/
	function browseCategory($pass_listings = false, $pass_stack = false) {
		global $listings, $page_info;

		if ( $page_info['prev'] != $page_info['Key'] ) {
			unset($_SESSION[$this -> bc]['stacks']);
		}
		
		$work_listings = $pass_listings ? $pass_listings : $listings;
		$this -> populate($work_listings, $pass_stack, $this -> bc);
	}
	
	/**
	* recently added listings page
	*
	* @access Hook: listingsBottom
	**/
	function recentlyAdded($pass_listings = false, $pass_stack = false) {
		global $listings, $page_info;

		if ( $page_info['prev'] != $page_info['Key'] ) {
			unset($_SESSION[$this -> ra]['stacks']);
		}
		
		$work_listings = $pass_listings ? $pass_listings : $listings;
		$this -> populate($work_listings, $pass_stack, $this -> ra);
	}
	
	/**
	* account details page
	*
	* @access Hook: accountTypeAccount
	**/
	function accountListings($pass_listings = false, $pass_stack = false) {
		global $listings, $page_info;

		if ( $page_info['prev'] != $page_info['Key'] ) {
			unset($_SESSION[$this -> al]['stacks']);
		}
		
		$work_listings = $pass_listings ? $pass_listings : $listings;
		$this -> populate($work_listings, $pass_stack, $this -> al);
	}
}