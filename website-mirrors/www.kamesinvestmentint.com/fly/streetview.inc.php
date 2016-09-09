<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: STREETVIEW.INC.PHP
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

/* send headers */
header("Content-Type: text/html; charset=utf-8" );
header("Cache-Control: store, no-cache, max-age=3600, must-revalidate" );

$Lat = $_POST['lat'];
$Lng = $_POST['lng'];

	$streetView = '
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
	<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=320,user-scalable=no" />
	<title>Street View</title>
	<link href="http://code.google.com/apis/maps/documentation/javascript/examples/default.css" rel="stylesheet" type="text/css" />
	<script src="http://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script>
	<script type="text/javascript">

	  function initialize() {
		var fenway = new google.maps.LatLng('. $Lat. ', '. $Lng .')

		checkStreet(fenway);

	    var mapOptions = {
	      center: fenway,
	      zoom: 13,
	      mapTypeId: google.maps.MapTypeId.ROADMAP
	    };
	    var map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);

	    var panoramaOptions = {
	      position: fenway,
	      pov: {
	        heading: 34,
	        pitch: 10,
	        zoom: 1
	      }
	    };
	    var panorama = new  google.maps.StreetViewPanorama(document.getElementById("pano"),panoramaOptions);
	    map.setStreetView(panorama);

		function checkStreet(pnt)
		{
			var svSer = new google.maps.StreetViewService;
			svSer.getPanoramaByLocation(pnt, 50, function(data, status){

				if (status != "OK") {
					alert("Street view not valid for this location");
				}
			});
		}
	  }
	</script>
	</head>
	<body onload="initialize()">
	  <div id="map_canvas" style="width: 320px; height: 100px"></div>
	  <div id="pano" style="position:absolute; left:0; top: 100px; width: 320px; height: 316px;"></div>
	</body>
	</html>';

$iPhone -> printAsText($streetView);