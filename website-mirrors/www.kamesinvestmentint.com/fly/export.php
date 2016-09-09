<?php

/* system config */
require_once( '../../includes/config.inc.php' );

$filename = RL_CACHE."xml_".md5( serialize($_GET) );
if( is_file($filename) )
{
	$fmtime = filemtime( $filename );
}

$fmtime = false;
$expiration_time = 600; //seconds

if ( !$fmtime || $fmtime + $expiration_time > time() )
{	
	set_time_limit(0);
	require_once( 'control.inc.php' );

	$lang = $rlLang -> getLangBySide('frontEnd', $config['lang']);	
	
	$format = $rlValid -> xSql($_GET['format']);
	$format_info = $rlDb -> fetch(array("Xpath"), array("Key" => $format ), null, null, "xml_formats", "row" );

	if ( !$format )
		exit;

	$reefless -> loadClass("XmlImport", null, "xmlFeeds");

	$total_limit = (int)$_GET['limit'] != 0 ? (int)$_GET['limit'] : 1000;
	$order['field'] = !empty($_GET['order_by']) ? $_GET['order_by'] : 'Date' ;
	$order['type'] = !empty($_GET['order_type']) ? strtoupper($_GET['order_type']) : 'DESC' ;
	$type = !empty($_GET['listing_type']) ? $_GET['listing_type'] : false ;

	$where = array();
	$structure_tmp = $rlDb -> getAll("SHOW FIELDS FROM `".RL_DBPREFIX."listings`");

	foreach($structure_tmp as $k => $v)
	{
		if(!in_array($v['Field'], $disabled_fields))
		{
			$structure[] = strtolower($v['Field']);
		}
	}

	unset( $_GET['format'] );

	foreach( $_GET as $k => $v )
	{
		$k = strtolower($k);

		if( $k == 'category' )
		{
			$v = $rlDb -> getOne('ID', "`Key` ='".$v."'", 'categories');
		}

		if(in_array($k, $structure) && $v)
		{
			$where[$k] = $v;
		}
	}

	$rlXmlImport -> loadFormat( 'flMap');
	$flMap -> fields_mapping = $rlXmlImport -> getMapping( $format, 'export' );
	$flMap -> xpath = $format_info['Xpath'];

	$fp = fopen($filename, 'w+');
	$flMap -> export( $fp, $where, $order, $total_limit, $type );
	fclose($fp);
}

$fp = fopen($filename, 'rb');
header('Content-Type: text/xml; charset=utf-8');
header("Content-Length: " . filesize($filename));

fpassthru($fp);
fclose($fp);
exit;
