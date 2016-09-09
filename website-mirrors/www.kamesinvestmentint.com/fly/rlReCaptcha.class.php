<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLRECAPTCHA.CLASS.PHP
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

class rlReCaptcha extends reefless
{
	/**
	* class constructor
	**/
	function rlReCaptcha($mode = false)
	{
		$this -> clearCompile($mode);
	}
	
	/**
	*
	* clear compile directory
	*
	**/
	function clearCompile($mode = false)
	{
		global $config;
		
		if ( $mode )
		{
			$compile = $this -> scanDir(RL_TMP .'compile');
			foreach ($compile as $file)
			{
				unlink(RL_TMP .'compile'. RL_DS . $file);
			}
		}
		else
		{
			$post_configs = isset($_POST['post_config']) ? 'post_config' : 'config';
			$group_id = $this -> getOne('ID', "`Key` = 'reCaptcha'", 'config_groups');

			if ( $_POST['group_id'] == $group_id && !$config['reCaptcha_module'] && $_POST[$post_configs]['reCaptcha_module']['value'] )
			{
				$compile = $this -> scanDir(RL_TMP .'compile');
				foreach ($compile as $file)
				{
					unlink(RL_TMP .'compile'. RL_DS . $file);
				}
			}
		}
	}
}