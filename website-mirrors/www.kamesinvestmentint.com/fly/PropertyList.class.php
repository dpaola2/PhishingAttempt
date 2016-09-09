<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: PROPERTYLIST.CLASS.PHP
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

class PropertyList
{
	var $obj;

	function PropertyList($obj) {
		$this -> obj = $obj;
	}

	function is_assoc($array) {
		return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
	}

	function xml() {
		$x = new XMLWriter();
		$x -> openMemory();
		$x -> setIndent(TRUE);
		$x -> startDocument('1.0', 'UTF-8');
		$x -> writeDTD('plist', '-//Apple//DTD PLIST 1.0//EN', 'http://www.apple.com/DTDs/PropertyList-1.0.dtd');
		$x -> startElement('plist');
		$x -> writeAttribute('version', '1.0');
		$this -> xmlWriteValue($x, $this -> obj);
		$x -> endElement(); // plist
		$x -> endDocument();
		return $x -> outputMemory();
	}

	function xmlWriteDict(XMLWriter $x, &$dict) {
		$x -> startElement('dict');
		foreach($dict as $k => &$v) {
			$x -> writeElement('key', $k);
			$this -> xmlWriteValue($x, $v);
		}
		$x -> endElement(); // dict
	}

	function xmlWriteArray(XMLWriter $x, &$arr) {
		$x -> startElement('array');
		foreach($arr as &$v) {
			$this -> xmlWriteValue($x, $v);
		}
		$x -> endElement(); // array
	}

	function xmlWriteValue(XMLWriter $x, &$v) {
		if (is_int($v) || is_long($v))
			$x -> writeElement('integer', $v);
		elseif (is_float($v) || is_real($v) || is_double($v))
			$x -> writeElement('real', $v);
		elseif (is_string($v))
			$x -> writeElement('string', $v);
		elseif (is_bool($v))
			$x -> writeElement($v ? 'true' : 'false');
		elseif ($this -> is_assoc($v))
			$this -> xmlWriteDict($x, $v);
		elseif (is_array($v))
			$this -> xmlWriteArray($x, $v);
		else {
			trigger_error("Unsupported data type in plist ($v)", E_USER_WARNING);
			$x -> writeElement('string', $v);
		}
	}
}