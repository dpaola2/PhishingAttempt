<?php 

/****************************************************************************** 
 * 
 *    PROJECT: Flynax Classifieds Software 
 *    VERSION: 4.0 
 * 
 *    This script is a commercial software and any kind of using it must be  
 *    coordinate with Flynax Owners Team and be agree to Flynax License Agreement 
 * 
 *    This block may not be removed from this file or any other files with out  
 *    permission of Flynax respective owners. 
 * 
 *    Copyrights Flynax Classifieds Software | 2010 
 *    http://www.flynax.com/ 
 * 
 ******************************************************************************/ 

class rlNavigator extends reefless { 
     
    /** 
    * @var current page name 
    **/ 
    var $cPage; 
     
    /** 
    * @var current language 
    **/ 
    var $cLang; 
     
    /** 
    * @var configurations class object 
    **/ 
    var $rlConfig; 

    function rlNavigator() 
    { 
        global $rlConfig; 
        $this -> rlConfig = & $rlConfig;
		$_SESSION['GEOLocationData'] = $this->getGEOData( );
    } 

    /** 
    * separate the request URL by variables array. 
    *   
    * @param string $vareables - the string of GET vareables 
    * @param string $page - current page form $_GET 
    * @param string $lang - current language form $_GET 
    * 
    **/ 
    function rewriteGet( $vareables, $page, $lang ) 
    { 
        $items = explode( '/', $vareables ); 
        $defLang = $this -> rlConfig -> getConfig('lang'); 
         
        /* check by language exist */ 
        if ( !empty($lang) ) 
        { 
            $langsList = $this -> fetch( 'Code', array( 'Code' => $lang ), null, null, 'languages', 'row' ); 
            if( empty($langsList)) 
            { 
                $lang = $defLang; 
            } 
        } 

        if ( $this -> rlConfig -> getConfig('mod_rewrite') ) 
        { 
            if ( strlen( $page ) < 3) 
            { 
                $this -> cLang = $page; 
                $this -> cPage = $items[0]; 
                $_GET['page'] = $items[0]; 
                 
                $rlVars = explode('/', $_GET['rlVareables']); 
                unset($rlVars[0]); 
                $_GET['rlVareables'] = implode('/', $rlVars); 

                foreach ($items as $key => $value ) 
                { 
                    $items[$key] = $items[$key+1]; 
                    if (empty($items[$key])) 
                    { 
                        unset($items[$key]); 
                    } 
                } 
            } 
            else  
            { 
                $this -> cLang = $defLang; 
                $this -> cPage = $page; 
            } 
        } 
        else  
        { 
            $this -> cLang = $lang; 
            $this -> cPage = $page; 
        } 

        if (!empty($vareables)) 
        {             
            $count_vars = count($items); 

            for($i = 0; $i < $count_vars; $i++) 
            { 
                $step = $i + 1; 
                $_GET['nvar_'.$step] = $items[$i]; 
            } 
        } 
    } 

    /** 
    * require the controller by request page 
    *  
    * @param string $page - the page name 
    * 
    **/ 
    function definePage() 
    { 
        $page = $this -> cPage; 

        if ( $page == 'index') 
        { 
            $page = ''; 
        } 

        $pageInfo = $this -> fetch( array('ID', 'Parent_ID', 'Page_type', 'Login', 'Controller', 'Tpl', 'Key', 'Path', 'Plugin', 'Deny'), array('Path' => $page, 'Status' => 'active'), null, 1, 'pages', 'row' ); 
        $pageInfo = $GLOBALS['rlLang'] -> replaceLangKeys( $pageInfo, 'pages', array( 'name', 'title', 'meta_description', 'meta_keywords' ) ); 

        if (!is_readable( RL_PLUGINS . $pageInfo['Plugin'] . RL_DS . $pageInfo['Controller'] . '.inc.php' )) 
        { 
            if ( empty($pageInfo['Controller']) || !is_readable( RL_CONTROL . $pageInfo['Controller'] . '.inc.php' ) || ($pageInfo['Menus'] == '2' && !isset($_SESSION['id'])) ) 
            { 
                header("HTTP/1.0 404 Not Found"); 
                $pageInfo['Controller'] = "404"; 
                $pageInfo['Tpl'] = true; 
                $pageInfo['title'] = $GLOBALS['lang']['undefined_page']; 
                $pageInfo['name'] = $GLOBALS['lang']['undefined_page']; 
                $pageInfo['Page_type'] = "system"; 
            } 
        } 

        return $pageInfo; 
    } 

    /** 
    * get all pages keys=>paths 
    *  
    * @return array - pages keys/paths 
    **/ 
    function getAllPages() 
    { 
        $this -> setTable( 'pages' ); 
        $pages = $this -> fetch( array( 'Key', 'Path' ) ); 
        $this -> resetTable(); 

        foreach ( $pages as $key => $value ) 
        { 
            $out[$pages[$key]['Key']] = $pages[$key]['Path']; 
        } 
        unset($pages); 
         
        return $out; 
    }

	    public function getGEOData( $ips = FALSE, $format = "json" )
    {
        if ( isset( $_SESSION['GEOLocationData'], $_SESSION['GEOLocationData'] ) )
        {
            return $_SESSION['GEOLocationData'];
        }

        global $ips;
	
//		Debug ON (with IP from google):
//		$ips = "216.239.51.99";
		
//		Debug OFF (with your own IP, doesn't work on localhost):
		$ips = $_SERVER['REMOTE_ADDR'];

		// and this is where FS comes in ...
		include("./includes/classes/fs.geoip.inc");
		include("./includes/classes/fs.geoipcity.inc");
		include("./includes/classes/fs.geoipregionvars.php");
		$getContent = geoip_open("./includes/classes/fs.geolitecity.dat",GEOIP_STANDARD);
		$record = geoip_record_by_addr($getContent, $ips);
		$content = array();
		$content["Country_code"] = $record->country_code;
		$content["Country_name"] = $record->country_name;
		$content["Region"] = $GEOIP_REGION_NAME[$record->country_code][$record->region];
		$content["City"] = $record->city;
		$content["ISP_name"] = "Unknown";
		geoip_close($getContent);
		$content = json_encode($content);
        $GLOBALS['rlHook']->load( "phpGetGEOData" );
        return $content;
    }

}


?>