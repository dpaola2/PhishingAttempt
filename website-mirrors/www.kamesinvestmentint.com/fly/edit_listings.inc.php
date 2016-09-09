<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: EDIT_LISTINGS.INC.PHP
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

$listing_id = (int)$_POST['id'];

$reefless -> loadClass('Listings');

// get listing info
$sql  = "SELECT `T1`.*, `T2`.`Cross` AS `Plan_crossed` FROM `". RL_DBPREFIX ."listings` AS `T1` ";
$sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
$sql .= "WHERE `T1`.`ID` = '{$listing_id}' LIMIT 1";
$listing = $rlDb -> getRow( $sql );

if ( $_SESSION['type'] == 'buyer' || 
	!isset( $_SESSION['type'] ) || 
	empty( $listing_id ) || 
	empty( $listing ) || 
	$listing['Account_ID'] != $_SESSION['id'] )
{
	echo 'error';
}
else
{
	// get listing form 
	if ( isset( $listing_id ) )
	{
		$reefless -> loadClass( "Categories" );

		// get current listing kind information 
		$category = $rlCategories -> getCategory( $listing['Kind_ID'] );

		if ( $category !== false ) 
		{
			$form = $rlCategories -> buildListingForm( $category['ID'] );

			if ( !empty( $form ) )
			{
				$editListingsXML .= "
				<dict>
					<key>sections</key>
					<array>";

				foreach( $form as $key => $value ) 
				{
					$editListingsXML .= "
							<dict>
								<key>name</key>
								<string>". $iPhone -> pValid( $value['name'] ) ."</string>
								<key>fields</key>
								<array>";

					foreach( $value['Fields'] as $fKey => $fVal )
					{
						$fieldName = $iPhone -> pValid( $fVal['name'] );
						$fieldValue = $iPhone -> pValid( $fVal['value'] );

						$editListingsXML .= "
									<dict>
										<key>name</key>
										<string>{$fieldName}</string>
										<key>value</key>
										<string>{$fieldValue}</string>
									</dict>";
					}

					$editListingsXML .= "
								</array>
							</dict>";
				}

				$editListingsXML .= "
						</array>
					</dict>";

				$iPhone -> pListCreate( 'edit_listings', $editListingsXML );
			}
		}
	}
}