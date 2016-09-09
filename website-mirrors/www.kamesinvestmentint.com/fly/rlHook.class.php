<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: {version}
 *	LICENSE: RETAIL - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: xxxxxxxxxxxx.com
 *	FILE: RLHOOK.CLASS.PHP
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

class rlHook extends reefless
{
	/**
	* @var hooks array
	**/
	var $rlHooks = array();

	/**
	* TODO: desc
	*/
	var $aHooks = array();

	/**
	* @var index of func
	**/
	var $index = 1;

	/**
	* class constructor
	**/
	function rlHook()
	{
		/* get hooks */
		$this -> getHooks();
	}

	/**
	* Get all active hooks
	**/
	function getHooks()
	{
		$hooks = array();
		$this -> setTable('hooks');
		$tmp_hooks = $this -> fetch(array('Name', 'Code', 'Plugin'), array('Status' => 'active'));
		$this -> resetTable();

		foreach ($tmp_hooks as $key => $value)
		{
			// collect keys of installed plugins
			$this -> aHooks[$value['Plugin']] = true;

			// adapt hooks to tree
			if ( !array_key_exists($value['Name'], $hooks) )
			{
				$hooks[$value['Name']] = $value['Code'];
			}
			else
			{
				$tmp_hook = $hooks[$value['Name']];
				unset($hooks[$value['Name']]);

				if ( is_array($tmp_hook) )
				{
					$tmp_hook[] = $value['Code'];
					$hooks[$value['Name']] = $tmp_hook;
				}
				else
				{
					$hooks[$value['Name']][] = $value['Code'];
					$hooks[$value['Name']][] = $tmp_hook;
				}
			}
			unset($tmp_hook);
		}

		$this -> rlHooks = $hooks;
		unset($tmp_hooks, $hooks);
	}

	/**
	* load hook
	*
	* @param mixed $name - hook name
	*
	* @param mixed $param1 - hook param by ref
	* @param mixed $param2 - hook param by ref
	* @param mixed $param3 - hook param by ref
	* @param mixed $param4 - hook param by ref
	* @param mixed $param5 - hook param by ref
	*
	**/
	function load( $name = false, &$param1, &$param2, &$param3, &$param4, &$param5 )
	{
		if ( is_array($name) )
		{
			$name = $name['name'];
		}

		// $GLOBALS['rlHook'] this is voodoo magic ;)
		$hooks = $GLOBALS['rlHook'] -> rlHooks;
		$code = isset($hooks[$name]) ? $hooks[$name] : '';

		if ( !empty($code) )
		{
			if ( is_array($code) )
			{
				foreach( $code as $item )
				{
					$func = "{$name}Hook". $GLOBALS['rlHook'] -> index;
					$wrapper  = "function {$func}(&\$param1, &\$param2, &\$param3, &\$param4, &\$param5) { ". PHP_EOL;
					$wrapper .= "[code]". PHP_EOL;
					$wrapper .= "}";

					@eval(str_replace('[code]', $item, $wrapper));
					if ( function_exists($func) ) {
						$func($param1, $param2, $param3, $param4, $param5);
					}
					else {
						// TODO: errors wrapper
					}

					$GLOBALS['rlHook'] -> index++;
				}
			}
			else
			{
				$func = "{$name}Hook". $GLOBALS['rlHook'] -> index;
				$wrapper  = "function {$func}(&\$param1, &\$param2, &\$param3, &\$param4, &\$param5) { ". PHP_EOL;
				$wrapper .= "[code]". PHP_EOL;
				$wrapper .= "}";

				@eval(str_replace('[code]', $code, $wrapper));
				if ( function_exists($func) ) {
					$func($param1, $param2, $param3, $param4, $param5);
				}
				else {
					// TODO: errors wrapper
				}

				$GLOBALS['rlHook'] -> index++;
			}
		}
	}
}