<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLSMSCOIN.CLASS.PHP
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

class rlSMSCoin extends reefless
{
	function rlSMSCoin() {}

	// generate transaction ID
	function generate($number = 8)
	{
		$laters = range('a', 'z');
		$laters = array_merge($laters, range('A', 'Z'));
		
		for ($i = 0; $i < $number; $i++)
		{
			$step = rand(1, 2);
			if ( $step == 1 )
			{
				$out .= rand(0, 9);
			}
			elseif ($step == 2)
			{
				$index = rand(0, count($laters) - 1);
				$out .= $laters[$index];
			}
		}
		
		return $out;
	}

	function writeLog($line = false)
	{
		if(!empty($line))
		{
			$file = fopen(RL_PLUGINS_URL . 'smsCoin/' . 'error.log', 'a');

			if($file)
			{
		   		$line = $line."\n";
           		fwrite($file, $line);
		   		fclose($file);
			}
		}
	}
	
	function generateNumber($number = 8)  
	{                                        
		$arr = array(1,2,3,4,5,6,7,8,9,0);            

		for($i = 0; $i < $number; $i++)    
		{                                      
			$index = rand(0, count($arr) - 1);  
			$rnumber .= $arr[$index];              
		}

		return $rnumber;           
	} 
}