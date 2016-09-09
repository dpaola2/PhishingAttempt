<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLVBULLETIN.CLASS.PHP
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

class rlVBulletin extends reefless {

	/**
	* Get vBulletin Root path
	**/
	function getPath() {
		global $config, $lang;

		if ( empty($config['vbulletin_destination']) || empty($config['vbulletin_path']) )
			return false;

		$dir = trim($config['vbulletin_path'], RL_DS);
		$path = false;

		# inside: [path]/public_html/flynax/forum/
		# outside: [path]/public_html/forum/flynax/
		# parallel: [path]/public_html/flynax/ | [path]/public_html/forum/

		switch( $config['vbulletin_destination'] ) {
			case 'root':
				$path = RL_DS . $dir . RL_DS;
				break;

			case 'inside':
				$path = RL_ROOT . $dir . RL_DS;
				break;

			case 'outsite':
				$path = str_replace($dir, '', RL_ROOT);
				$path = RL_DS . trim($path, RL_DS) . RL_DS;
				break;

			case 'parallel':
				$part = explode(',', $dir, 2);
				if ( count($part) === 2 ) {
					$path = str_replace($part[0], $part[1], RL_ROOT);
				}
				break;
		}
		return $path;
	}

	/**
	* Init vBulletin main object
	*
	* @param bool $loginFunc - if true load 'functions_login.php'
	* @return object/bool - vbulletin object or false
	**/
	function vbInit($loginFunc = false) {
		global $vbulletin, $config;

		if ( !$config['vbulletin_use_module'] )
			return false;

		// reconnect to vBulletin DB
		if ( is_object($vbulletin) ) {
			$this -> reConnect('forum');
			return $vbulletin;
		}

		$forumDir = $this -> getPath();
		if ( !is_dir($forumDir) || !file_exists($forumDir .'includes/class_bootstrap.php') )
			return false;

		define('VB_AREA', 'Flynax');
		define('SKIP_SESSIONCREATE', 1);
		define('SKIP_USERINFO', 1);

		// simulate global.php file
		chdir($forumDir);
		require_once('./includes/class_bootstrap.php');

		try {
			$this -> initForumBootstrap();
		} catch (Exception $e) {
			$GLOBALS['rlDebug'] -> logger('[vBulletinBridge] Caught exception:'. $e -> getMessage());
			$GLOBALS['rlConfig'] -> setConfig('vbulletin_use_module', 0);
		}

		chdir(RL_ROOT);

		// reconnect to vBulletin DB
		if ( is_object($vbulletin) ) {
			$this -> reConnect('forum');
			return $vbulletin;
		}
		return false;
	}

	/**
	* init Forum Bootstrap
	*/
	function initForumBootstrap() {
		if ( !class_exists('vB_Bootstrap_Forum') ) {
			throw new Exception("Class 'vB_Bootstrap_Forum' doesn't found");
		}
		else {
			$bootstrap = new vB_Bootstrap_Forum();
			if ( !method_exists($bootstrap, 'bootstrap') ) {
				throw new Exception("Undefined method 'bootstrap()' in class 'vB_Bootstrap_Forum'");
			}
			else {
				$bootstrap -> bootstrap();
			}
		}
	}

	/**
	* Install Flynax product on vBulletin
	**/
	function ajaxInstallProduct() {
		global $_response, $lang, $vbulletin;

		if ( false === $vbulletin = $this -> vbInit() )
			return false;

		$product  = "INSERT INTO `". TABLE_PREFIX ."product` ( `productid`, `title`, `description`, `version`, `active`, `url` ) VALUES ";
		$product .= "( 'flynax', 'Flynax Bridge', 'Official Flynax plugin for vBulletin', '*', '1', 'http://www.flynax.com/plugins/vbulletin.html' )";
		$vbulletin -> db -> query_write($product);

		$path = RL_PLUGINS .'vbulletin'. RL_DS .'vb_hooks'. RL_DS;
		$plugins  = "INSERT INTO `". TABLE_PREFIX ."plugin` ( `title`, `hookname`, `phpcode`, `product`, `active`, `executionorder` ) VALUES ";
		$plugins .= "( 'Login', 'login_verify_success', 'if ( file_exists(\"{$path}login_verify_success.php\") )\n{\n\tdefine(\"FLYNAX_ROOT\", \"". RL_ROOT ."\");\n\trequire_once(\"{$path}login_verify_success.php\");\n}', 'flynax', '1', '1' ),";
		$plugins .= "( 'Logout', 'logout_process', 'if ( file_exists(\"{$path}logout_process.php\") )\n{\n\tdefine(\"FLYNAX_ROOT\", \"". RL_ROOT ."\");\n\trequire_once(\"{$path}logout_process.php\");\n}', 'flynax', '1', '1' ),";
		$plugins .= "( 'Registration', 'register_addmember_complete', 'if ( file_exists(\"{$path}register_addmember_complete.php\") )\n{\n\tdefine(\"FLYNAX_ROOT\", \"". RL_ROOT ."\");\n\trequire_once(\"{$path}register_addmember_complete.php\");\n}', 'flynax', '1', '1' ),";
		$plugins .= "( 'Update password', 'profile_updatepassword_complete', 'if ( file_exists(\"{$path}profile_updatepassword_complete.php\") )\n{\n\tdefine(\"FLYNAX_ROOT\", \"". RL_ROOT ."\");\n\trequire_once(\"{$path}profile_updatepassword_complete.php\");\n}', 'flynax', '1', '1' )";
		$vbulletin -> db -> query_write($plugins);

		// update the datastore
		vBulletinHook::build_datastore($vbulletin -> db);

		$_response -> script("printMessage('notice', '{$lang['vbulletin_installProductNotice']}');");
		$_response -> script("$('#install_product_dom').html('<b>{$lang['active']}</b>');");

		return $_response;
	}

	/**
	* unInstall Flynax product from vBulletin
	**/
	function unIstallProduct() {
		global $vbulletin;

		if ( false === $vbulletin = $this -> vbInit() )
			return false;

		$product = "DELETE FROM `". TABLE_PREFIX ."product` WHERE `productid` = 'flynax'";
		$vbulletin -> db -> query_write($product);

		$plugins = "DELETE FROM `". TABLE_PREFIX ."plugin` WHERE `product` = 'flynax'";
		$vbulletin -> db -> query_write($plugins);

		// update the datastore
		vBulletinHook::build_datastore($vbulletin -> db);

		// reconnect to Flynax DB
		$this -> reConnect();
	}

	/**
	* Reconnect to Flynax DB
	**/
	function reConnect($module = false) {
		global $vbulletin, $config;

		$database = is_object($vbulletin) ? $vbulletin -> db -> database : '';
		if ( $database != RL_DBNAME ) {
			if ( $module == 'forum' ) {
				$mServer = $vbulletin -> config['MasterServer'];
				$this -> connect($mServer['servername'], $mServer['port'], $mServer['username'], $mServer['password'], $vbulletin -> config['Database']['dbname']);
			}
			else {
				$this -> connect(RL_DBHOST, RL_DBPORT, RL_DBUSER, RL_DBPASS, RL_DBNAME);
			}
		}
	}

	/**
	* Get new forum posts
	**/
	function newPosts() {
		global $config, $vbulletin;

		if ( false === $vbulletin = $this -> vbInit() ) return false;

		$forumids = array_keys($vbulletin -> forumcache);
		$forumchoice = $postarray = array();

		foreach($forumids as $forumid) {
			$forumperms = &$vbulletin -> userinfo['forumpermissions'][$forumid];
			if ( $forumperms & $vbulletin -> bf_ugp_forumpermissions['canview']
				&& ( $forumperms & $vbulletin -> bf_ugp_forumpermissions['canviewothers'] )
				&& ( ( $forumperms & $vbulletin -> bf_ugp_forumpermissions['canviewthreads'] ) )
				&& verify_forum_password($forumid, $vbulletin -> forumcache[$forumid]['password'], false)
			)
			{
				array_push($forumchoice, $forumid);
			}
		}

		if ( !empty($forumchoice) ) {
			$forumsql = "`T2`.`forumid` IN (". implode(',', $forumchoice) .")";

			// remove threads from users on the global ignore list if user is not a moderator
			$globalignore = '';
			if ( trim($vbulletin -> options['globalignore']) != '' ) {
				require_once(DIR .'/includes/functions_bigthree.php');
				if ( $Coventry = fetch_coventry('string') ) {
					$globalignore = "AND `T1`.`userid` NOT IN ($Coventry) ";
				}
			}

			// set encode
			//mysql_set_charset('latin1');

			$sql  = "SELECT `T1`.`pagetext` AS `message`, `T1`.`postid`, `T2`.`threadid`, `T2`.`title`, `T2`.`prefixid`, `T3`.`forumid`";
			$sql .= "FROM `". TABLE_PREFIX ."post` AS `T1` ";
			$sql .= "JOIN `". TABLE_PREFIX ."thread` AS `T2` ON (`T2`.`threadid` = `T1`.`threadid`) ";
			$sql .= "JOIN `". TABLE_PREFIX ."forum` AS `T3` ON (`T3`.`forumid` = `T2`.`forumid`) ";
			$sql .= "WHERE {$forumsql} AND `T1`.`visible` = 1 AND `T2`.`visible` = 1 AND `T2`.`open` <> '10' ";
			$sql .= "{$globalignore} ORDER BY `T1`.`dateline` DESC LIMIT 0,{$config['vbulletin_limit_posts']}";
			$posts = $vbulletin -> db -> query_read_slave($sql);

			while($post = $vbulletin -> db -> fetch_array($posts)) {
				$title = fetch_trimmed_title($post['title'], $config['vbulletin_number_symbols_posts_title']);
				$message = strip_bbcode(strip_quotes($post['message']), false, true);
				$message = fetch_trimmed_title($message, $config['vbulletin_number_symbols_posts']);

				$postarray[$post['postid']] = array(
					'title' => $title,
					'message' => $message,
					'url' => rtrim($vbulletin -> options['bburl'], '/') .'/'. fetch_seo_url('thread', $post, array('p' => $post['postid'])) .'#post'. $post['postid']
				);
			}
			unset($posts);
		}

		// reconnect to Flynax DB
		$this -> reConnect();

		return $postarray ? $postarray : false;
	}

	/**
	* Login to vBulletin
	*
	* @param string $username - username
	* @param string $password - password
	* @param bool   $secured - password in md5 format
	**/
	function login($username = false, $password = false, $secured = false) {
		if ( !$username || !$password || false === $vbulletin = $this -> vbInit() )
			return false;

		// include login functions
		require_once(DIR .'/includes/functions_login.php');

		// can the user login?
		$strikes = verify_strike_status($username);
		$md5_password = $secured ? $password : md5($password);

		// make sure our user info stays as whoever we were (for example, we might be logged in via cookies already)
		$originalUserInfo = $vbulletin -> userinfo;

		if ( !verify_authentication($username, $password, $md5_password, $md5_password, 1, true) ) {
			exec_strike_user($vbulletin -> userinfo['username']);
			$vbulletin -> userinfo = $originalUserInfo;

			$error_msg = fetch_error('badlogin_passthru', $vbulletin -> options['bburl'], $vbulletin -> session -> vars['sessionurl']);
			$GLOBALS['rlDebug'] -> logger("[vBulletinBridge] Login: {$error_msg}");
		}
		exec_unstrike_user($username);

		// create new session
		process_new_login('', 1, '');

		// reconnect to Flynax DB
		$this -> reConnect();
	}

	/**
	* Logout from vBulletin
	**/
	function logOut()
	{
		global $block_keys;

		if ( false === $vbulletin = $this -> vbInit() ) return false;

		// include login functions
		require_once(DIR .'/includes/functions_login.php');

		// logout
		process_logout();

		// reconnect to Flynax DB
		if ( !array_key_exists('vbulletin_new_posts', $block_keys) )
		{
			$this -> reConnect();
		}
	}

	/**
	* Create account on vBulletin
	**/
	function createAccount($username = false, $password = false, $email = false, $import = false)
	{
		global $config;

		if ( $username === false || $password === false || $email === false || false === $vbulletin = $this -> vbInit() ) return false;

		define('VB_API', true);
		$password = $import ? $password : md5($password);

		$manager = &datamanager_init('User', $vbulletin, ERRTYPE_ARRAY);
		$manager -> set('username', $username);
		$manager -> set('email', $email);
		$manager -> set('password', $password);
		$manager -> set('ipaddress', $_SERVER['REMOTE_ADDR']);
		$manager -> user['password'] = md5($password . $manager -> user['salt']);

		$group = (int)$config['vbulletin_user_group'];
		$manager -> set('usergroupid', $group ? $group : 2);

		if ( empty($manager -> errors) )
		{
			$manager -> save();

			// reconnect to Flynax DB
			if ( !$import )
			{
				$this -> reConnect();
			}
			return true;
		}
		else
		{
			$action = $import ? 'Import VB' : 'Registration';
			$GLOBALS['rlDebug'] -> logger("[vBulletinBridge] {$action}: Username/Mail already in use");
		}

		// reconnect to Flynax DB
		if ( !$import )
		{
			$this -> reConnect();
		}

		return false;
	}

	/**
	* Create account on Flynax
	**/
	function createFlynaxAccount($data = false)
	{
		global $rlValid, $rlActions, $config;

		if ( $data === false || empty($data) ) return false;

		// check exists
		$username = $rlValid -> xSql($data['username']);
		$exists = $this -> getOne('Username', "`Username` = '{$username}'", 'accounts');
		if ( !empty($exists) )
		{
			return false;
		}

		$insert = array(
			'Type' => $config['vbulletin_flynax_account_type'],
			'Username' => $username,
			'Own_address' => $rlValid -> str2path($username),
			'Password' => $data['password'],
			'Password_salt' => $data['salt'],
			'Mail' => $data['email'],
			'Date' => date('Y-m-d H:i:s', $data['joindate']),
			'Display_email' => 0
		);
		$result = $rlActions -> insertOne($insert, 'accounts');

		return $result;
	}

	/**
	* Fetch user groups
	**/
	function fetchUserGroups()
	{
		if ( false === $vbulletin = $this -> vbInit() ) return false;

		$groups = array();
		$sql = "SELECT `usergroupid`, `title` FROM `". TABLE_PREFIX ."usergroup` WHERE `usergroupid` > '8' OR `usergroupid` = '2'";
		$query = $vbulletin -> db -> query_read_slave($sql);
		while( $row = $vbulletin -> db -> fetch_array($query) )
		{
			array_push($groups, array('ID' => $row['usergroupid'], 'name' => $row['title']));
		}

		// reconnect to Flynax DB
		$this -> reConnect();

		return $groups;
	}

	/**
	* 
	*/
	function vbUserExists($username = false)
	{
		if ( false === $this -> vbInit() ) return false;
		$GLOBALS['rlValid'] -> sql($username);

		$sql = "SELECT `username` FROM `". TABLE_PREFIX ."user` ";
		$sql .= "WHERE `username` = '{$username}' LIMIT 1";
		$account = $this -> getRow($sql);

		return !empty($account);
	}

	/**
	* Fetch import logs
	**/
	function fetchImportLogs($getStatus = false)
	{
		global $config, $lang, $rlSmarty;

		if ( !$_POST['xjxfun'] && $getStatus === true )
		{
			if ( false === $vbulletin = $this -> vbInit() ) return false;

			// check flynax product status
			$check = "SELECT `Active` FROM `". TABLE_PREFIX ."product` WHERE `productid` = 'flynax'";
			$flynaxProduct = $vbulletin -> db -> query_first($check);

			// send product status
			$productStatus = 'install';
			if ( !empty($flynaxProduct) )
			{
				$productStatus = $flynaxProduct['Active'] ? 'active' : 'approval';
			}
			$rlSmarty -> assign('productStatus', $productStatus);

			// reconnect to Flynax DB
			$this -> reConnect();
		}

		if ( !array_key_exists('vbulletin_import_logs', $config) )
		{
			$sql  = "INSERT INTO `". RL_DBPREFIX ."config` ( `Key`, `Group_ID`, `Default`, `Plugin` ) VALUES ";
			$sql .= "( 'vbulletin_import_logs', '0', '0,0,0|0,0,0', 'vbulletin' )";
			$this -> query($sql);

			$config['vbulletin_import_logs'] = '0,0,0|0,0,0';
		}

		list($flynaxLog, $vbulletinLog) = explode('|', $config['vbulletin_import_logs'], 2);
		$flynaxLog = explode(',', $flynaxLog, 3);
		$vbulletinLog = explode(',', $vbulletinLog, 3);

		$actions[0] = array(
			'title' => $lang['vbulletin_importAccountsFromVBulletin'],
			'button' => $lang['vbulletin_importButton'],
			'func' => 'xajax_importFromVBulletin',
			'successful' => $vbulletinLog[0],
			'failed' => $vbulletinLog[1],
			'date' => $vbulletinLog[2]
		);
		$actions[1] = array(
			'title' => $lang['vbulletin_importAccountsFromFlynax'],
			'button' => $lang['vbulletin_importButton'],
			'func' => 'xajax_importFromFlynax',
			'successful' => $flynaxLog[0],
			'failed' => $flynaxLog[1],
			'date' => $flynaxLog[2]
		);
		$rlSmarty -> assign('actions', $actions);

		return true;
	}

	/**
	* Update table view
	**/
	function updateTableView()
	{
		global $_response, $rlSmarty;

		// resend logs
		$this -> fetchImportLogs();

		// update modules table view
		$tpl = RL_PLUGINS .'vbulletin'. RL_DS .'admin'. RL_DS .'modules.tpl';
		$_response -> assign('import_modules_dom', 'innerHTML', $rlSmarty -> fetch($tpl, null, null, false));
	}

	/**
	* Update import Log
	**/
	function updateImportLog( $module = false, $successful = false, $failed = false )
	{
		global $_response, $config, $lang, $rlConfig;

		$successful = (int)$successful;
		$failed = (int)$failed;

		list($flynaxLog, $vbulletinLog) = explode('|', $config['vbulletin_import_logs'], 2);

		$save = ($module == 'flynaxLog') ? "{$successful},{$failed},". time() ."|{$vbulletinLog}" : "{$flynaxLog}|{$successful},{$failed},". time();
		if ( $rlConfig -> setConfig('vbulletin_import_logs', $save) )
		{
			$config['vbulletin_import_logs'] = $save;
			$this -> updateTableView();
		}
		$_response -> script("printMessage('notice', '{$lang['vbulletin_importCompleteNotice']}');");

		return $_response;
	}

	/**
	* Import accounts from Flynax
	**/
	function ajaxImportFromFlynax($start = false, $successful = false, $failed = false)
	{
		global $_response, $lang;

		$start = (int)$start;
		$limit = 500;
		$successful = (int)$successful;
		$failed = (int)$failed;

		$sql = "SELECT `Username`, `Password`, `Mail` FROM `". RL_DBPREFIX ."accounts` LIMIT {$start},{$limit}";
		$accounts = $this -> getAll($sql);

		if ( !empty($accounts) )
		{
			foreach( $accounts as $key => $entry )
			{
				$result = $this -> createAccount($entry['Username'], $entry['Password'], $entry['Mail'], true);
				if ( $result )
				{
					$successful++;
				}
				else
				{
					$failed++;
				}
			}

			// clear memory
			unset($accounts);

			// start import as recurcion
			$start += $limit;
			$_response -> script("xajax_importFromFlynax({$start}, {$successful}, {$failed});");
			return $_response;
		}

		// update log
		$this -> updateImportLog('flynaxLog', $successful, $failed);

		return $_response;
	}

	/**
	* Import accounts from VBulletin
	**/
	function ajaxImportFromVBulletin($start = false, $successful = false, $failed = false)
	{
		global $_response, $lang;

		if ( false === $vbulletin = $this -> vbInit() ) return false;

		$start = (int)$start;
		$limit = 500;
		$successful = (int)$successful;
		$failed = (int)$failed;

		// connect to vBulletin
		$sql = "SELECT `userid`, `username`, `password`, `salt`, `email`, `joindate` FROM `". TABLE_PREFIX ."user` LIMIT {$start},{$limit}";
		$accounts = $this -> getAll($sql);

		// reconnect to Flynax DB
		$this -> reConnect();

		if ( !empty($accounts) )
		{
			$this -> loadClass('Actions');

			foreach( $accounts as $key => $entry )
			{
				$result = $this -> createFlynaxAccount($entry);
				if ( $result )
				{
					$successful++;
				}
				else
				{
					$failed++;
				}
			}

			// clear memory
			unset($accounts);

			// start import as recurcion
			$start += $limit;
			$_response -> script("xajax_importFromVBulletin({$start}, {$successful}, {$failed});");
			return $_response;
		}

		// update log
		$this -> updateImportLog('vbulletinLog', $successful, $failed);

		return $_response;
	}

	/**
	* unInstall the plugin
	**/
	function unInstall()
	{
		$this -> query("ALTER TABLE `". RL_DBPREFIX ."accounts` DROP `Password_salt`");

		// uninstall Flynax product from vBulletin
		$this -> unIstallProduct();
	}
}