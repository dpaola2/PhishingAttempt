<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLCOMPRESSIONJSCSSMOBILE.CLASS.PHP
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

class rlCompressionJsCssMobile extends reefless
{
	var $path_common_js;
	var $path_common_css;

	var $links_js;
	var $links_css;

	var $links_plugin_js;
	var $links_plugin_css;

	var $files_js = array();
	var $files_css = array();

	var $counter = 0;
	var $first_step = true;
                        
    var $exceptions;	
	var $exception_plugins;
	var $exception_pages;

	var $page_keys = array( 'add_listing', 'add_photo', 'add_video', 'upgrade_listing', 'edit_listing', 'view_details' );

	var $static_js;
	var $xajax_pre_js;

	var $domain;
	var $doCompression = true;
	var $isUnderConstructions = false;

	function __construct() 
	{
		$this -> exceptions = array(
			/* add photos */
			RL_LIBS_URL . 'upload/jquery.ui.widget.js',
			RL_LIBS_URL . 'upload/tmpl.min.js',
			RL_LIBS_URL . 'upload/load-image.min.js',
			RL_LIBS_URL . 'upload/canvas-to-blob.min.js',
			RL_LIBS_URL . 'upload/bootstrap.min.js',
			RL_LIBS_URL . 'upload/jquery.iframe-transport.js',
			RL_LIBS_URL . 'upload/jquery.fileupload.js',
			RL_LIBS_URL . 'upload/jquery.fileupload-fp.js',
			RL_LIBS_URL . 'upload/jquery.fileupload-ui.js',
			RL_LIBS_URL . 'upload/main.js',
			RL_LIBS_URL . 'jquery/jquery.jcrop.js',
			RL_LIBS_URL . 'jquery/jquery.flmap.js',
			RL_LIBS_URL . 'ckeditor/ckeditor.js',
                                               
			/* listing details */
			RL_URL_HOME . 'templates/' . $GLOBALS['config']['mobile_template'] . '/js/photo_gallery.js',
			
			/* lib fancybox */
			RL_LIBS_URL . 'jquery/jquery.fancybox.js',
			RL_LIBS_URL . 'jquery/fancybox/helpers/jquery.fancybox-buttons.js',

			/* add,edit listing */
			RL_LIBS_URL . 'javascript/crossed.js',

			/* banners plugin */
			RL_PLUGINS_URL . 'banners/static/tmpl.min.js',
			RL_PLUGINS_URL . 'banners/static/jquery.iframe-transport.js',
			RL_PLUGINS_URL . 'banners/static/jquery.fileupload.js',
			RL_PLUGINS_URL . 'banners/static/jquery.fileupload-ui.js',

			/* locationFinder plugin */
			RL_PLUGINS_URL . 'locationFinder/static/lib.js'
		);

		$this -> exception_plugins = array(
				'multiField',
				'tag_cloud',
				'locationFinder',
				'street_view',
				'search_by_distance'
			);

		/* pages */
		$this -> exception_pages = array(
				'add_listing', 
				'add_photo', 
				'add_video', 
				'upgrade_listing', 
				'edit_listing', 
				'my_favorites',
				'my_listings',
				'my_messages',
				'my_packages',
				'my_profile',
				'my_services',
				'payment_history',
				'saved_search',
			);

		$this -> loadClass( 'Valid' );
		$this -> domain = $GLOBALS['rlValid'] -> getDomain( RL_URL_HOME );

        $GLOBALS['rlHook'] -> load( 'phpCompressionJsCssExceptions', $this -> exceptions, $this -> exception_plugins, $this -> exception_pages );

		$this -> files_js = (array)$GLOBALS['files_mobile_js'];
		$this -> files_css = (array)$GLOBALS['files_mobile_css'];
		
		//print_r($this -> files_js);

		$this -> path_common_js = RL_ROOT . 'templates/' . $GLOBALS['config']['mobile_template'] . '/js/compressed.js';
		$this -> path_common_css =  RL_ROOT . 'templates/' . $GLOBALS['config']['mobile_template'] . '/css/compressed.css';

		/* add system js links */
		$this -> links_js[] = RL_LIBS_URL . 'ajax/xajax_js/xajax_core.js';

		$this -> checkUnderConstructions(); 
	}

	function get( &$content, &$resource_name )
	{
		global $page_info;

		$rlTplBase = RL_URL_HOME . 'templates/' . $GLOBALS['config']['mobile_template'] . '/';

		/* get static js */
		if ( substr_count( $resource_name, 'plugins' ) > 0 && !$this -> isExceptionPlugin( $resource_name ) && $this -> doCompression )
		{
			if ( preg_match_all( "/(<script type=\"text\/javascript\">.*<\/script>).*<script type=\"text\/javascript\" src=/isU", $content, $matches ) )
			{
				foreach( $matches[1] as $stKey => $stVal )
				{
					/* add js code */
					if ( in_array( $page_info['Key'], $this -> page_keys ) )
					{
						$this->static_js[] = "{if \$pageInfo.Key == '" . $page_info['Key'] . "'}\n" . $stVal . "\n{/if}\n";
					}
					else
					{
						$this->static_js[] = $stVal;
					}

					/* replace js code */
					$content = str_replace( $stVal, '{if !$doCompression} ' . $stVal . ' {/if}', $content );
				}
			}

			unset ( $matches );
		}        
		/* search js */
		if ( preg_match_all( "/<script type=\"text\/javascript\" src=\"(.*)\".*><\/script>/iU", $content, $matches ) )
		{
			/* work */
			foreach ( $matches[1] as $k => $v )
			{
				if ( $this -> isLocal( $v ) )
				{
					$this -> links_js[] = str_replace( array( '{$smarty.const.RL_LANG_CODE}',  '{$smarty.const.RL_PLUGINS_URL}', '{$smarty.const.RL_LIBS_URL}', '{$rlTplBase}', '{$smarty.const.RL_LANG_CODE|lower}', '{if $smarty.const.RL_LANG_CODE != \'\' && $smarty.const.RL_LANG_CODE != \'en\'}&amp;language={$smarty.const.RL_LANG_CODE}{/if}', '{$config.google_map_key}' ), array( RL_LANG_CODE, RL_PLUGINS_URL, RL_LIBS_URL, $rlTplBase, strtolower( RL_LANG_CODE ), '&amp;language=' . RL_LANG_CODE, $GLOBALS['config']['google_map_key'] ), $v );
				}
			}

			/* remove js */
			foreach( $matches[0] as $k => $v )
			{
				if ( $this -> isLocal( $v ) && !$this -> isException( $v ) )
				{                          
					$content = str_replace( $v, '{if !$doCompression} ' . $v . ' {/if}', $content );
				}
			}

			/* replace numeric */
			foreach( $this -> links_js as $jKey => $jVal )
			{                        
				if ( substr_count( $jVal, 'numeric.js' ) > 0 && substr_count( $jVal, 'compressionJsCss' ) <= 0 )
				{
					$this -> links_js[$jKey] = RL_PLUGINS_URL . 'compressionJsCss/static/jquery.numeric.js';
					break;
				}
			}

			unset( $matches );
		}

		/* search css */
		if ( preg_match_all( "/<link href=\"(.*)\" type=\"text\/css\" rel=\"stylesheet\" \/\>/i", $content, $matches ) )
		{
			/* get */
			foreach( $matches[1] as $k => $v )
			{
				$this -> links_css[] = str_replace( array( '{$smarty.const.RL_PLUGINS_URL}', '{$smarty.const.RL_LIBS_URL}', '{$rlTplBase}', '{$smarty.const.RL_LANG_CODE|lower}' ), array(RL_PLUGINS_URL, RL_LIBS_URL, $rlTplBase, strtolower( RL_LANG_CODE ) ), $v );
			}

			/* remove */
			foreach( $matches[0] as $cKey => $cVal )
			{
				if ( substr_count( $cVal, "rtl" ) <= 0 && substr_count( $cVal, "aStyle.css" ) <= 0 && !in_array( $cVal, $this -> exceptions ) )
				{
					$content = str_replace( $cVal, '', $content );	
				}
			}
		}

		$this -> links_js = array_unique( $this -> links_js );
		$this -> links_css = array_unique( $this -> links_css );

		if ( $this -> doCompression )
		{
			$this -> saveJSCode();
		}
	}

	function replace( &$content )
	{
		/* remove css */
		//$content = preg_replace( "/<link href=\"(.*)\" type=\"text\/css\" rel=\"stylesheet\" \/\>/i", "", $content );

		foreach( $this -> links_css as $cKey => $cVal )
		{
			if ( !in_array( $cVal, $this -> exceptions ) )
			{
				$tmp = explode( "/", $cVal );
				$tmp = array_reverse( $tmp );

				$content = preg_replace( "/<link href=\"(.*){$tmp[0]}\" type=\"text\/css\" rel=\"stylesheet\" \/\>/i", "", $content );
			}
		}                                                                                                            
	}

	function build( &$content, &$resource_name = false )
	{
		/* check */
		if ( $this -> first_step )
		{        
			/* js */
			if ( $this -> check( 'js' ) )
			{
				$this -> reBuild( 'js' );
			}

			/* css */
			if ( $this -> check( 'css' ) )
			{
				$this -> reBuild( 'css' );
			}
		}

		$this -> get( $content, $resource_name );

		/* add links from plugin */
		if ( $this -> first_step )
		{
			foreach( $this -> links_plugin_js as $link )
			{
				$this -> links_js[] = $link;
			}

			foreach( $this -> links_plugin_css as $link )
			{
				$this -> links_css[] = $link;
			}

			unset( $this -> links_plugin_css, $this -> links_plugin_js );

		}

		/*$this -> replace( $content );*/
		$this -> prepareFilesInfo(); 

		/* build js */
		foreach( $this -> links_js as $jKey => $jVal )
		{
			$tmp = explode( "/", $jVal );

			if ( $this -> is_file_new( $jVal, 'js' ) && !in_array( $jVal, $this -> exceptions ) )
			{
				$this -> addContent( $jVal, 'js', $tmp[count( $tmp ) - 1] );
				$this -> files_js[md5( $jVal )]['time'] = (int)$this -> getDateModifyFile( $jVal, 'js' );
			}

			unset( $tmp );

		}
		/* end build js */

		/* build css */	
		foreach( $this -> links_css as $cKey => $cVal )
		{
			$tmp = explode( "/", $cVal );

			if ( $tmp[count( $tmp ) - 1] != 'rtl.css' )
			{
				if ( $this -> is_file_new( $cVal, 'css' ) && !in_array( $jVal, $this -> exceptions ) )
				{
					$this -> addContent( $cVal, 'css', $tmp[count( $tmp ) - 1] );
					$this -> files_css[md5( $cVal )]['time'] = (int)$this -> getDateModifyFile( $cVal, 'css' );
				}
			}

			unset( $tmp );

		}
		/* end build css */ 

		$this -> updateFilesInfo();
		$this -> first_step = false;
	}

	function reBuild( $type = false )
	{
		if ( !$type )
		{
			return false;
		}

		switch ( $type )
		{
			case 'js' :
				if ( file_exists( $this -> path_common_js ) )
				{
					unlink( $this -> path_common_js );
				}

				foreach( $this -> files_js as $jKey => $jVal )
				{
					$tmp = explode( "/", $jVal['link'] );

					$this -> addContent( $jVal['link'], 'js', $tmp[count( $tmp ) - 1] );
					$this -> files_js[md5($jVal['link'])]['time'] = (int)$this -> getDateModifyFile( $jVal['link'], 'js' );

					unset( $tmp );

				}
				break;
				       
			case 'css' :
				if ( file_exists( $this -> path_common_css ) )
				{
					unlink( $this -> path_common_css );
				}

				foreach( $this -> files_css as $cKey => $cVal )
				{
					$tmp = explode( "/", $cVal['link'] );

					if ( $tmp[count( $tmp ) - 1] != 'rtl.css' )
					{
						$this -> addContent( $cVal['link'], 'css', $tmp[count( $tmp ) - 1] );
						$this -> files_css[md5($cVal['link'])]['time'] = (int)$this -> getDateModifyFile( $cVal['link'], 'css' );
					}

					unset( $tmp );

				}
				break;
		}

		$this -> updateFilesInfo();
	}

	function addContent( $file = false, $type = false, $name = false )
	{
		global $rlDebug;

		if ( $file && $type ) 
		{ 
			$handle_source = fopen( $file, 'r' );

			if ( $handle_source )
			{
				while ( !feof( $handle_source ) ) 
				{
					$content .= fgets( $handle_source );
				}
				fclose ( $handle_source );

				if ( !empty( $content ) )
				{
                    if ( substr_count( $file, 'plugins' ) > 0 && $type == 'css' )
					{
						$this -> replaceURLImageInCss( $content, $file );
					}
                    if ( substr_count( $file, 'libs' ) > 0 && substr_count( $file, 'plugins' ) <= 0 && $type == 'css' )
					{
						$this -> replaceURLImageInLibsCss( $content, $file );						
					}

					switch ( $type )
					{
						case 'js' :
							/* add comments */
							$date = date( 'Y-m-d H:i:s' );
							$comment_start = "\n\n/* [{$file}] {$date} */\n";
							$comment_end = "\n/* [end {$file}] */\n\n";
							$content = $comment_start . $content . $comment_end;

							if(preg_match_all("/(\\}\\)\\(jQuery\\)[^\\;\\,])/i", $content, $matches))
							{
								$searched = trim($matches[1][0]);
								$content = str_replace($searched, "})(jQuery);", $content);

								unset($searched);
							}
							
							$handle_target = fopen( $this -> path_common_js, 'a' );
							break;
						case 'css' :
							$this -> compressCSS( $content ); 
							$handle_target = fopen( $this -> path_common_css, 'a' );
							break;
					}

					if ( $handle_target )
					{
						fwrite( $handle_target, $content );
					}
					fclose( $handle_target );
				}

				unset( $content );

				return true;
			}
		}

		return false;
	}

	function is_file_new( $file = false, $type = false ) 
	{
		if ( $type == 'js' )
		{
			if ( empty( $this -> files_js[md5( $file )]['time'] ) )
			{
				return true;
			}
		}
		elseif ( $type == 'css' )
		{
			if ( empty( $this -> files_css[md5( $file )]['time'] ) )
			{
				return true;
			}
		}

		return false;
	}

	function is_file_modify( $file, $type = false )
	{
		$time = (int)$this -> getDateModifyFile( $file, $type );

		if ( $type == 'js' )
		{                                                                                            
			if ( (int)$this -> files_js[md5( $file )]['time'] < $time && !empty( $this -> files_js[md5($file)]['time'] ) )
			{     
				return true;
			}
		}  
		elseif ( $type == 'css' )
		{
			if ( (int)$this -> files_css[md5( $file )]['time'] < $time && !empty( $this -> files_css[md5( $file )]['time'] ) )
			{
				return true;
			}
		}

		return false;
	}

	function uninstall()
	{
		/* remove common js file */  
		if ( file_exists( $this -> path_common_js ) )
		{
			unlink( $this -> path_common_js );
		}

		/* remove common css file */
		if ( file_exists( $this -> path_common_css ) )
		{
			unlink( $this -> path_common_css );
		}

		/* clear  header_common.tpl */
		$this -> saveJSCode( true );
		
		$this -> clearCashe();
	}

	function clearCashe( $directory = false )
	{
		$directory = !$directory ?  RL_TMP . 'mCompile' : $directory;

		$dir = opendir( $directory );

		while( ( $file = readdir( $dir ) ) )
		{
			if ( is_file( $directory . '/' . $file ) )
			{
				unlink( $directory . '/' . $file );
			}
			else if ( is_dir( $directory . '/' . $file ) && ( $file != "." ) && ( $file != ".." ) )
			{
				$this -> clearCashe( $directory . '/' . $file );
			}
		}

		closedir( $dir );
	}

	function prepareFilesInfo()
	{
		/* add js files */
		foreach( $this -> links_js as $key => $link )
		{
			if ( !isset( $this -> files_js[md5($link)] ) ) /* empty( $this -> files_js[md5($link)] ) */
			{
				$this -> files_js[md5($link)] = array(
						'time' => 0,
						'link' => $link
					);
			}
		}

		/* add css files */
		foreach( $this -> links_css as $key => $link )
		{
			if(!isset( $this -> files_css[md5($link)] ) )
			{
				$tmp = explode( "/", $link );

				if ( $tmp[count( $tmp ) - 1] != 'rtl.css' )
				{
					$this -> files_css[md5($link)] =  array(
							'time' => 0,
							'link' => $link
						);
				}
			}
		}

	}

	function updateFilesInfo()
	{
		$this -> loadClass( 'Valid' );

        $files_js = serialize( (array)$GLOBALS['files_js'] );
		$files_css = serialize( (array)$GLOBALS['files_css']  );
		
        $files_mobile_js = serialize( $this -> files_js );
		$files_mobile_css = serialize( $this -> files_css );

		$hook_code = <<< JC
\$files_js = unserialize('$files_js');
\$GLOBALS['files_js'] = \$files_js;

\$files_css = unserialize('$files_css');
\$GLOBALS[\'files_css\'] = \$files_css;

\$files_mobile_js = unserialize('$files_mobile_js');
\$GLOBALS['files_mobile_js'] = \$files_mobile_js;

\$files_mobile_css = unserialize('$files_mobile_css');
\$GLOBALS[\'files_mobile_css\'] = \$files_mobile_css;
JC;
		if ( !empty( $this -> files_js ) && !empty( $this -> files_css ) )
		{
			$sql = "UPDATE `" . RL_DBPREFIX . "hooks` SET `Code` = '" . $GLOBALS['rlValid'] -> xsql( $hook_code ) . "' WHERE `Name` = 'init' AND `Plugin` = 'compressionJsCss' LIMIT 1";
			$this -> query( $sql );
		}
	}

	function getDateModifyFile( $file = false, $type = false )
	{
		$this -> loadClass( 'Valid' );

		if ( !$file || !$type )
		{
			return false;
		}

		if ( substr_count( $file, $this -> domain ) > 0 )
		{
			$file = str_replace( RL_URL_HOME , RL_ROOT, $file );   
			$time = filemtime( $file );

			if ( $time > 0 )
			{
				return $time;
			}
		}
		else
		{
			switch($type)
			{
				case 'js' :
					$time = !empty( $this -> files_js[md5( $file )] ) ? $this -> files_js[md5( $file )] : time();
					break;
				case 'css' :
					$time = !empty( $this -> files_css[md5( $file )] ) ? $this -> files_css[md5( $file )] : time();
					break;
			}

			return $time;
		}

		return false;
	}

	function check( $type = false )
	{
		$modify = false;

		switch( $type )
		{
			case 'js' :
				foreach ( $this -> files_js as $k => $v )
				{
					if ( substr_count( $v['link'], $this -> domain ) > 0 )
					{
						$file_local = str_replace( RL_URL_HOME , RL_ROOT, $v['link'] );  

						if ( file_exists( $file_local ) )
						{
							if ( $this -> is_file_modify( $v['link'], 'js' ) )
							{
								$modify = true;
							}
						}
						else
						{
							$modify = true;
						}

						unset( $file_local );
					}
				}
				break;
			case 'css' :
				foreach ( $this -> files_css as $k => $v )
				{
					if( substr_count( $v['link'], $this -> domain ) > 0 )
					{
						$file_local = str_replace( RL_URL_HOME , RL_ROOT, $v['link'] );

						if ( file_exists( $file_local ) )
						{

							if ( $this -> is_file_modify( $v['link'], 'css' ) )
							{
								$modify = true;
							}
						}
						else
						{
							$modify = true;
						}

						unset( $file_local );
					}
				}
				break;
		}

		return $modify;
	}

	function getLinksFromHooks()
	{
		if ($this -> isUnderConstructions )
		{
			return false;
		}

		$tmp_hooks = $GLOBALS['hooks'];

		/* get css */		
		foreach ( $tmp_hooks as $key => $val )
		{
			if ( is_array( $val ) )
			{
				foreach ( $val as $k => $v )
				{
					if ( substr_count( $v, 'RL_MOBILE' ) <= 0 )
					{
						if ( preg_match_all( "/<link href=\"(.*\.css)\".*>/i", $v, $matches ) )
						{
							/* get links */
							foreach( $matches[1] as $i => $link ) 
							{
								$link = str_replace( " ", "", $link );
								$link = str_replace( array( "'.RL_PLUGINS_URL.'", "'.RL_LIBS_URL.'" ), array( RL_PLUGINS_URL, RL_LIBS_URL ), $link );

								if ( !$this -> isException( $link ) )
								{
									$this -> links_plugin_css[] = $link;
								
									/* remove css */
									$GLOBALS['hooks'][$key][$k] = str_replace( $matches[0][$i], "", $GLOBALS['hooks'][$key][$k] );
								}
							}
						}
					}
				}
			}
			else             
			{                	
				if ( substr_count( $val, 'RL_MOBILE' ) <= 0 )
				{
					if ( preg_match_all( "/<link href=\"(.*\.css)\".*>/i", $val, $matches ) )
					{            
						/* get links */
						foreach ( $matches[1] as $i => $link )
						{
							$link = str_replace( " ", "", $link );
							$link = str_replace( array( "'.RL_PLUGINS_URL.'", "'.RL_LIBS_URL.'" ), array( RL_PLUGINS_URL, RL_LIBS_URL ), $link );
							
							if ( !$this -> isException( $link ) )
							{
								$this -> links_plugin_css[] = $link;
								
								/* remove css */
								$GLOBALS['hooks'][$key] = str_replace( $matches[0][$i], "", $GLOBALS['hooks'][$key] );
							}
						}
					}
				}
			}
		}

		/* get js */		
		foreach ( $tmp_hooks as $key => $val )
		{
			if ( is_array( $val ) )
			{
				foreach ( $val as $k => $v )
				{
					if ( substr_count( $v, 'RL_MOBILE' ) <= 0 )
					{
						if ( preg_match_all( "/<script .* src=\"(.*\.js)\"><\/script>/i", $v, $matches ) )
						{
							/* get links */
							foreach( $matches[1] as $i => $link )
							{
								$link = str_replace( " ", "", $link );
								$link = str_replace(array( "'.RL_PLUGINS_URL.'", "'.RL_LIBS_URL.'" ), array( RL_PLUGINS_URL, RL_LIBS_URL ), $link );
								
								if ( !$this -> isException( $link ) )
								{
									$this -> links_plugin_js[] = $link;
									
									/* remove js */
									if($this->doCompression)
									{
										$GLOBALS['hooks'][$key][$k] = str_replace( $matches[0][$i], "", $GLOBALS['hooks'][$key][$k] );
									}
								}
							}
						}
					}
				}
			}
			else
			{          
				if ( substr_count( $val, 'RL_MOBILE' ) <= 0 )
				{             
					if ( preg_match_all( "/<script .* src=\"(.*\.js)\"><\/script>/i", $val, $matches ) )
					{
						/* get links */              
						foreach( $matches[1] as $i => $link )
						{
							$link = str_replace( " ", "", $link );
							$link = str_replace(array( "'.RL_PLUGINS_URL.'", "'.RL_LIBS_URL.'" ), array( RL_PLUGINS_URL, RL_LIBS_URL ), $link );
							
							if ( !$this -> isException( $link ) )
							{
								$this -> links_plugin_js[] = $link;

								/* remove js */
								if($this->doCompression)
								{                                            
									$GLOBALS['hooks'][$key] = str_replace( $matches[0][$i], "", $GLOBALS['hooks'][$key] );
								}
							}
						}
					}
				}
			}
		}

		$this -> links_plugin_js = array_unique( $this -> links_plugin_js );
		$this -> links_plugin_css = array_unique( $this -> links_plugin_css );

		unset( $tmp_hooks );
	}

	function replaceURLImageInCss( &$content, $link = false )
	{
		if(preg_match_all("/plugins\/(.*)\/static/i", $link, $pmatches))
		{
			$plugin_name = trim($pmatches[1][0]);
		}

		if ( preg_match_all( "/\'.*\.(?:jpg|gif|png)\'/i", $content, $matches ) )
		{
            $matches[0] = array_unique($matches[0]);
			
			foreach ( $matches[0] as $k => $v )
			{
				$v = trim( $v, "'" );
				$img_link = '/../../../plugins/'.$plugin_name.'/static/' . $v;
				$content = str_replace( $v, $img_link, $content );

				unset( $img_link );
			}
		}

		unset( $link );
	}

	function replaceURLImageInLibsCss( &$content, $link = false )
	{
		if ( preg_match_all( "/libs\/(.*)\.css$/i", $link, $lmatches ) )
		{
			$lib_name = trim( $lmatches[1][0] ); 
			$lib_name = explode( "/", $lib_name );
			array_pop( $lib_name ); 
			$lib_name = implode( "/", $lib_name );

			if ( preg_match_all( "/\'.*\.(?:jpg|gif|png)\'/i", $content, $matches ) )
			{
            	$matches[0] = array_unique( $matches[0] );
			
				foreach ( $matches[0] as $k => $v )
				{
					$v = trim( $v, "'" );
					$img_link = '/../../../libs/' . $lib_name . '/' . str_replace( "../", "", $v );
					$content = str_replace( $v, $img_link, $content );

					unset( $img_link );
				}
			}                 
		}
	}

	function isException( $data = false )
	{
		if ( empty( $data ) )
		{
			return false;
		}

		$result = false;
           
		$data = str_replace( array( 
								'{$smarty.const.RL_LANG_CODE}',  
								'{$smarty.const.RL_PLUGINS_URL}', 
								'{$smarty.const.RL_LIBS_URL}', 
								'{$rlTplBase}', 
								'{$smarty.const.RL_LANG_CODE|lower}', 
								'{if $smarty.const.RL_LANG_CODE != \'\' && $smarty.const.RL_LANG_CODE != \'en\'}&amp;language={$smarty.const.RL_LANG_CODE}{/if}', 
								'{$config.google_map_key}' 
						   ),
						   array( 
								RL_LANG_CODE, 
								RL_PLUGINS_URL, 
								RL_LIBS_URL, 
								RL_URL_HOME . 'templates/' . $GLOBALS['config']['mobile_template'] . '/', 
								strtolower( RL_LANG_CODE ),
								'&amp;language=' . RL_LANG_CODE, 
								$GLOBALS['config']['google_map_key'] 
						  ), 
						  $data );

		/* check links */
		foreach( $this -> exceptions as $eKey => $eVal )
		{
 			if ( substr_count( $data, $eVal ) > 0 )
			{
				$result = true;
				break;
			}
		}

        /* check plugins */
		if ( preg_match_all( "/plugins\/(.*)\/static/i", $data, $pmatches ) )
		{
			$plugin_name = trim($pmatches[1][0]);
		}
		
		if ( in_array( $plugin_name, $this -> exception_plugins ) )
		{
			$result = true;
		}

		return $result;
	}

	function isExceptionPlugin( $link = false )
	{
		if ( !$link )
		{
			return false;
		}

		$result = false;

		foreach( $this -> exception_plugins as $eKey => $eVal )
		{
 			if ( substr_count( $link,  $eVal ) > 0 )
			{
				$result = true;
				break;
			}
		}

		return $result;
	}
	
 	function ajaxRebuild( $self = false )
	{
		global $_response;

		set_time_limit( 0 );

		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN . "/index.php";
			$redirect_url .= empty( $_SERVER['QUERY_STRING'] ) ? '?session_expired' : '?' . $_SERVER['QUERY_STRING'] . '&session_expired';
			$_response -> redirect( $redirect_url );
		}

		$sql = "SELECT * FROM `" . RL_DBPREFIX . "hooks` WHERE `Name` = 'init' AND `Plugin` = 'compressionJsCss' LIMIT 1";
		$hook_info = $this -> getRow( $sql );

		eval( $hook_info['Code'] );

		$this -> files_js = $files_mobile_js;
		$this -> files_css = $files_mobile_css; 		

		/* clear  header_common.tpl */
		$this -> saveJSCode( true );

		/* build css */
		$this -> reBuild( 'css' );

		/* build js */
		$this -> reBuild( 'js' );

		$_response -> script( "printMessage('notice', '{$GLOBALS['lang']['compression_rebuild_success']}')" );
		$_response -> script( "$('#{$self}').val('{$GLOBALS['lang']['rebuild']}');" );

		return $_response;
	}

	function saveJSCode( $clear = false )
	{
		$file = RL_PLUGINS . 'compressionJsCss' . RL_DS . 'header_common_mobile.tpl';
		$content = '';

		if ( file_exists( $file ) )
		{
			if ( !is_writable( $file ) )
			{
				chmod( $file, 0777 );
			}

			if ( is_writable( $file ) )
			{
				if( !$clear )
				{
					$handle_source = fopen( $file, 'r' ); 

					if ( $handle_source )
					{
						while ( !feof( $handle_source ) ) 
						{
							$content .= fgets( $handle_source );
						}
						fclose( $handle_source );

						foreach( $this -> static_js as $sjKey => $sjVal )
						{
							$content .= $sjVal . "\n\n";
						}

						$handle_target = fopen( $file, 'w' ); 

						fwrite( $handle_target, $content );
						fclose( $handle_target );
					}
				}
				else
				{
					$handle = fopen( $file, 'w' );
					fclose( $handle );
				}
			}
            else
			{  
				$content = $GLOBALS['hooks']['tplCompressionJsCssStaticJSMobileMobile'];

				foreach( $this -> static_js as $sjKey => $sjVal )
				{
					$content .= $sjVal . "\n\n";
				}

				$content = $rlValid -> xSql( $content ); 

				$sql = "UPDATE `" . RL_DBPREFIX . "hooks` SET `Code` = '{$content}' WHERE `Name` = 'tplCompressionJsCssStaticJSMobile' AND `Plugin` = 'compressionJsCss' LIMIT 1";
				$this -> query( $sql );

				$GLOBALS['hooks']['tplCompressionJsCssStaticJSMobile'] = $content;
			}
		}

		unset( $content );
		$this -> static_js = array();
	}

	/* 
	@ajax_javascripts: string; The javascript code is generated by xajax library
	*/
	function adaptXajaxJS( &$ajax_javascripts )
	{
 		if ( preg_match_all( "/(<script type=\"text\/javascript\" charset=\"UTF-8\">.*<\/script>).*(<script type=\"text\/javascript\" src=\".*\" charset=\"UTF-8\"><\/script>)/isU", $ajax_javascripts, $matches ) )
		{
			$this -> xajax_pre_js = trim( $matches[1][0] );
			$js_file = trim( $matches[2][0] );
		}

		$ajax_javascripts = str_replace( array( $this -> xajax_pre_js, $js_file ), array( "", "" ), $ajax_javascripts );

		unset( $js_file );
	}

	/*
	When user change a template this method add a new hooks;
	*/
	function applyNewTemplate()
	{
		global $rlValid;

	 	if ( isset( $_POST['config']['template'] ) && ( $_POST['config']['template']['value'] != $GLOBALS['config']['template'] ) )
		{
			$new_template_name = trim( $_POST['config']['template']['value'] );

			/* add new hook */
		 	$this -> addSmartyHook( $new_template_name );
			
			/* remove links from hook */
			$empty_hook = "\$files_js = array(); \$GLOBALS['files_js'] = \$files_js; \$files_css = array(); \$GLOBALS['files_css'] = \$files_css;";
			$empty_hook = $rlValid -> xSql( $empty_hook ); 

			$sql = "UPDATE `" . RL_DBPREFIX . "hooks` SET `Code` = '{$empty_hook}' WHERE `Name` = 'init' AND `Plugin` = 'compressionJsCss' LIMIT 1";
			$this -> query( $sql );

			unset( $empty_hook );

			/* clear header_common.tpl */
			$this -> saveJSCode();

			/* remove files */ 
			$path_common_js = RL_ROOT . 'templates/' . $new_template_name . '/js/compressed.js';
			$path_common_css = RL_ROOT . 'templates/' . $new_template_name . '/css/compressed.css';

			if ( file_exists( $path_common_js ) )
			{
				unlink( $path_common_js );
			}
			if ( file_exists( $path_common_css ) )
			{
				unlink( $path_common_css );
			}
		}	
	}

	function compressCSS( &$content )
	{
		/* remove comments */
		$content = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content );
  		
		/* remove tabs, spaces ... */
		$content = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $content );
	}

	function isLocal( &$link )
	{
 		if ( substr_count( $link, $this -> domain ) > 0 
			|| substr_count( $link, 'RL_PLUGINS_URL' ) > 0 
			|| substr_count( $link, 'RL_LIBS_URL' ) > 0 
			|| substr_count( $link, '$rlTplBase' ) > 0 )
		{	
			return true;
		}

		return false;
	}

	function isExceptionPage( $key = false )
	{
		if ( !$key )
		{
			return false;
		}

		$result = false;

		if( in_array( $key, $this -> exception_pages ) )
		{
			$result = true;
		}

		return (bool)$result;
	}

	function imitateLoadHomePage()
	{
		global $_response;

		$_response -> script("setTimeout(function(){ $.get('" . RL_URL_HOME . "'); }, 2000);");

		return $_response;
	}

	function outCompressionHtml()
	{
		global $aHooks, $reefless, $rlSmarty;

		if ( $aHooks['compressionJsCss'] )   
		{
			if ( file_exists( $this -> path_common_css ) )
			{
				$path_common_css =  RL_URL_HOME . 'templates/' . $GLOBALS['config']['mobile_template'] . '/css/compressed.css';
				echo '<link href="'. $path_common_css .'" type="text/css" rel="stylesheet" />';
			}

			if ( file_exists( $this -> path_common_js ) && $this -> doCompression )
			{
				echo $this -> xajax_pre_js;
				
				if ( is_writable( RL_ROOT . 'plugins' . RL_DS . 'compressionJsCss' . RL_DS . 'header_common_mobile.tpl' ) )
				{
					$GLOBALS['rlSmarty'] -> display(RL_ROOT . 'plugins' . RL_DS . 'compressionJsCss' . RL_DS . 'header_common_mobile.tpl' );
				}
				else
				{
					echo $GLOBALS['hooks']['tplCompressionJsCssStaticJSMobile'];
				}

				$path_common_js = RL_URL_HOME . 'templates/' . $GLOBALS['config']['mobile_template'] . '/js/compressed.js';
				echo '<script type="text/javascript" src="' . $path_common_js . '"></script>';
			}
		}
	}

	function checkUnderConstructions()
	{

		if ( $GLOBALS['config']['under_constructions_module'] )
		{
			$ip = getenv('HTTP_X_REAL_IP') ? getenv('HTTP_X_REAL_IP') : getenv('REMOTE_ADDR');
			$ips = explode(';', $GLOBALS['config']['under_constructions_ip']);
			$date = $this -> getRow("SELECT UNIX_TIMESTAMP(`Default`) AS `Date` FROM `". RL_DBPREFIX ."config` WHERE `Key` = 'under_constructions_date' LIMIT 1");
			
			if ( !in_array($ip, $ips) && time() <= $date['Date'] )
			{
				$this -> isUnderConstructions = true;
			}
		}
	}
}
