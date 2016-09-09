<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: PDFEXPORT.INC.PHP
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

require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');

if ( $_GET['listingID'] )
{
	$reefless -> loadClass( 'Listings' );

	$listing_id = (int)$_GET['listingID'];

	/* get listing info */
	$sql = "SELECT `T1`.*, `T2`.`Path`, `T2`.`Type` AS `Listing_type`, `T2`.`Key` AS `Cat_key`, `T2`.`Type` AS `Cat_type`, ";
	$sql .= "`T3`.`Image`, `T3`.`Image_unlim`, `T3`.`Video`, `T3`.`Video_unlim`, CONCAT('categories+name+', `T2`.`Key`) AS `Category_pName` ";
	$sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
	$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T5` ON `T1`.`Account_ID` = `T5`.`ID` ";
	$sql .= "WHERE `T1`.`ID` = '{$listing_id}' AND `T5`.`Status` = 'active' LIMIT 1";

	$listing_data = $rlDb -> getRow( $sql );

	/* define listing type */
	$listing_type = $rlListingTypes -> types[$listing_data['Listing_type']];

	/* build listing structure */
	$category_id = $listing_data['Category_ID'];
	$listing = array_values($rlListings -> getListingDetails($category_id, $listing_data, $listing_type));	

	/* get listing title */
	$listing_title = $rlListings -> getListingTitle($category_id, $listing_data, $listing_type['Key']);
	$listing_url = SEO_BASE . $pages[$listing_type['Page_key']] .'/'. $listing_data['Path'] .'/'. $rlSmarty -> str2path($listing_title) .'-l'. $listing_data['ID'] .'.html';

	/* get listing photo */
	$photo = $rlDb -> getOne('Photo', "`Listing_ID` = {$listing_id} AND `Status` = 'active' AND `Photo` <> ''", 'listing_photos');
	if ( !empty($photo) && file_exists(RL_FILES . $photo) )
	{
		$photo = RL_FILES . $photo;
	}
	else
	{
		$photo = RL_PLUGINS . 'PdfExport/no-photo.jpg';
	}

	/* QR Code integration */
	$qrCode_image = 'qrcode/user_'. $listing_data['Account_ID'] .'/listing_'. $listing_data['ID'] .'.png';
	
	if ( is_readable(RL_FILES . $qrCode_image) ) {
		$qrCode_html = '<img width="130px" height: 130px; style="border: 1px black solid;" src="'. RL_FILES_URL . $qrCode_image .'" />';
	}
	
	/* get seller information */
	$seller_info = $rlAccount -> getProfile((int)$listing_data['Account_ID']);
	$additional_fields = $seller_info["Fields"];

	$seller_name = $seller_info['First_name'] || $seller_info['Last_name'] ? $seller_info['First_name'] .' '. $seller_info['Last_name'] : $seller_info['Username'];

	/* create new PDF document */
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf -> SetCreator($lang['pages+title+home'] .'PDF Export Plugin');
	$pdf -> SetAuthor($seller_name);
	$pdf -> SetTitle($listing_title);
	$pdf -> SetSubject('PDF Listing Export by '. $lang['pages+title+home']);
	$pdf -> SetKeywords($lang['pages+title+home'] .', PDF, export, PDF Export');

	// set default header data
	$pdf -> SetHeaderData('../../templates'. RL_DS . $config['template'] . RL_DS .'img'. RL_DS .'logo.png' , 35, $lang['pages+title+home'], SEO_BASE);

	// set header and footer fonts
	$pdf -> setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf -> setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf -> SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	//set margins
	$pdf -> SetMargins(PDF_MARGIN_LEFT, 30, PDF_MARGIN_RIGHT);
	$pdf -> SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf -> SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf -> SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	//set image scale factor
	$pdf -> setImageScale(PDF_IMAGE_SCALE_RATIO);

	//set some language-dependent strings
	$pdf -> setLanguageArray($l);

	// set font
	$pdf -> SetFont('freeserif', '', 12);

	// add a page
	$pdf -> AddPage();

	// set color for text
	$pdf -> SetTextColor(39, 39, 39);

	$html = '
	<table width="100%">
	<tr>
		<td colspan="2" align="left" height="30px">
			<a style="color: #444444; font-size: 56px;" href="'. $listing_url .'">' . $listing_title . '</a>
		</td>
	</tr>
	<tr>
		<td width="250px">
			<img src="' . $photo . '" alt="'. $listing_title .'" width="230px" border="0" />';
	
	$html .= '
		</td>
		<td width="auto">';

	$html .= '<table width="100%">
				<tr>
					<td colspan="2" style="background-color: #e5e5e5;">'. $lang['seller_info'] .'</td>
				</tr>
				<tr>
					<td width="100px" style="color: #676766;height: 20px;">' . $lang["name"] . ':</td>
					<td>' . $seller_name . '</td>
				</tr>';
	
	if ($seller_info["Display_email"])
	{
		$html .= '<tr>
					<td style="color: #676766;height: 20px;">' . $lang["mail"] . ':</td>
					<td>' . $seller_info["Mail"] . '</td>
				</tr>';	
	}

	foreach ( $additional_fields as $key => $additional_value )
	{
		if ( substr_count($additional_value["value"], "http") )
		{
				$additional_value["value"] = str_replace(RL_URL_HOME, "", $additional_value["value"]);
		}
		$html .= '<tr>
					<td style="color: #676766;height: 20px;">' . $additional_value["name"] . ':</td>
					<td>' . $additional_value["value"] . '</td>
				</tr>';
	}

	$html .= '</table>';
	
	$html .= '</td></tr>
	</table>';

	$html .= '<div style="margin: 20px 0;background-color: #e5e5e5;">'. $lang['listing_details'] .'</div>';

	$qrCode_lines = 6;
	$qrCode_group = count($listing);
	$qrCode_merge = 0;
	
	/* validate data */
	foreach ( array_reverse($listing, true) as $item ) {
		if ( !count(array_filter($item['Fields'])) ) {
			$qrCode_group--;
			continue;
		}

		foreach ( $item['Fields'] as $field ) {
			if ( $field['Type'] == 'textarea' ) {
				$qrCode_group--;
				break;
			}
		}
		
		if ( count($item['Fields']) < $qrCode_lines ) {
			$qrCode_lines -= count($item['Fields']);
			$qrCode_merge++;
		}
	}
	
	/* print listing details */
	foreach ( $listing as $index => $value )
	{
		if ( !count(array_filter($value['Fields'])) ) 
			continue;

		if ( ($index == $qrCode_group - $qrCode_merge) && $qrCode_html ) {
			$html .= '<table width="100%"><tr><td valign="top">';
		}
		
		$html .= '
			<table>
			<tr>
				<td colspan="2" height="20">
					<font size="16" color="#000000"><b>' . $value["name"] . '</b></font>
				</td>
			</tr>';

		foreach ( $value['Fields'] as $field )
		{
			$html .= '
			<tr>
				<td width="140" style="color: #676766;">' . $field["name"] . ':</td><td>' . $field["value"] . '</td>
			</tr>';
		}
		$html .= '
			</table><br />';
		
		if ( ($index == $qrCode_group) && $qrCode_html ) {
			$html .= '</td><td valign="bottom" align="right">'. $qrCode_html .'</td></tr></table>';
		}
	}
	
	if ( RL_LANG_DIR == 'rtl' )
	{
		$pdf -> setRTL(true);
	}
	
	// output the HTML content
	$pdf -> writeHTML($html, true, false, true, false, 'left');

	// close and output PDF document
	$pdf -> Output('pdfExport_listing'. $listing_data['ID'] .'.pdf', 'I');
}
else
{
	$sError = true;
}