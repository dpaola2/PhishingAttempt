<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.0
 *	LISENSE: http://www.flynax.com/license-agreement.html
 *	PRODUCT: General Classifieds
 *	
 *	FILE: RLRSS.CLASS.PHP
 *
 *	This script is a commercial software and any kind of using it must be 
 *	coordinate with Flynax Owners Team and be agree to Flynax License Agreement
 *
 *	This block may not be removed from this file or any other files with out 
 *	permission of Flynax respective owners.
 *
 *	Copyrights Flynax Classifieds Software | 2012
 *	http://www.flynax.com/
 *
 ******************************************************************************/

class rlRss extends reefless
{
	/**
	* @var items list
	**/
	var $items = array('title', 'link', 'description');
	
	var $items_number = 5;
	var $mXmlParser = null;
	var $mLevel = null;
	var $mTag = null;
	var $mKey = null;
	var $mItem = false;
	var $mRss = array();

	/**
	* clear data
	**/
	function clear()
	{
		$this->mXmlParser = null;
		$this->mLevel = null;
		$this->mTag = null;
		$this->mKey = null;
		$this->mItem = false;
		$this->mRss = array();
	}
	
	/**
	* start element for parser
	*
	* @param string $parser - parser object
	* @param string $name - item name
	* 
	**/
	function startElement($parser, $name) 
	{
		$this->mLevel++;
		$this->mTag = strtolower($name);
		
		if('item' == $this->mTag)
		{
			$this->mItem = true;
			$this->mKey++;
		}
	}

	/**
	* end element for parser
	*
	* @param string $parser - parser object
	* @param string $name - item name
	* 
	**/
	function endElement($parser, $name) 
	{
		$this->mLevel--;
		
		if('item' == $this->mTag)
		{
			$this->mItem = false;
		}
	}

	/**
	* data collection
	*
	* @param string $parser - parser object
	* @param string $data - item data
	*
	**/
	function charData($parser, $data)
	{
		if ( $this->mKey <= $this->items_number )
		{
			$data = trim($data);

			$items = $this -> items;
			foreach ($items as $item)
			{
				if( $item == $this -> mTag && $this->mItem )
				{
					if(!empty($data))
					{
						$this -> mRss[$this->mKey][$item] .= $data;
					}
				}
			}
		}
	}

	/**
	* create parser
	*
	* @param string $content - content data
	* 
	**/
	function createParser($content)
	{
		$this->mXmlParser = xml_parser_create();

		xml_set_element_handler($this->mXmlParser, array(&$this, "startElement"), array(&$this, "endElement"));
		xml_set_character_data_handler($this->mXmlParser, array(&$this, "charData"));

		xml_parse($this->mXmlParser, $content);

		xml_parser_free($this->mXmlParser);
	}

	/**
	* get RSS content
	* 
	**/
	function getRssContent()
	{
		return $this->mRss;
	}
}