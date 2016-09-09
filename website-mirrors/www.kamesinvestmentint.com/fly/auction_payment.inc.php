<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: AUCTION_PAYMENT.INC.PHP
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

$reefless -> loadClass( 'Actions' );
$reefless -> loadClass( 'Categories' );
$reefless -> loadClass( 'Mail' );
$reefless -> loadClass( 'ShoppingCart', null, 'shoppingCart' );
$reefless -> loadClass( 'Auction', null, 'shoppingCart' );
$reefless -> loadClass( 'DHL', null, 'shoppingCart' );
$reefless -> loadClass( 'UPS', null, 'shoppingCart' );

$rlHook -> load( 'shoppingCartAuctionProcessOrderTop' );

/* init currency rates */
$rlHook -> load( 'shoppingCartCurrencyRates' );

$item_id = (int)$_REQUEST['item'];

if ( $item_id )
{
	/* move H1 for responsive template */
	if ( $tpl_settings['type'] == 'responsive_42' ) {
		$rlSmarty -> assign('no_h1', true);
	}
	
	$shc_steps = $rlAuction -> getSteps();
	$rlSmarty -> assign_by_ref( 'shc_auction_steps', $shc_steps );

	$show_step_caption = false;
	$rlSmarty -> assign_by_ref( 'show_step_caption', $show_step_caption );

	/* define step */
	$request = explode( '/', $_GET['rlVareables'] );
	$requestStep = array_pop( $request );

	$step = $requestStep ? $requestStep : $_GET['step'];
	$cur_step = !empty( $step ) ? $step : 'cart'; 

	/* replace bread crumbs */
	unset($bread_crumbs[count($bread_crumbs) - 1]);

	$bread_crumbs[] = array(
		'name' => $GLOBALS['lang']['pages+name+shc_auctions'],
		'title' => $GLOBALS['lang']['pages+name+shc_auctions'],
		'path' => $pages['shc_auctions']
	);

	if ( !empty( $cur_step ) )
	{
		$bread_crumbs[] = array(
			'name' => $GLOBALS['lang']['pages+name+shc_auction_payment']
		);

		$rlSmarty -> assign( 'cur_step', $cur_step );
	
		$page_info['name'] = $GLOBALS['lang']['shc_step_' . $cur_step];
	}

	/* get prev/next step */
	$tmp_steps = $shc_steps;
	foreach( $tmp_steps as $t_key => $t_step )
	{
		if ( $t_key != $cur_step )
		{
			next($shc_steps);
		}
		else
		{
			break;
		}
	}
	unset( $tmp_steps );

	$nextStep = next( $shc_steps ); prev( $shc_steps );
	$prevStep = prev( $shc_steps );

	$rlSmarty -> assign( 'next_step', $nextStep );
	$rlSmarty -> assign( 'prev_step', $prevStep );
	
	/* get auction info */
	$auction_info = $rlAuction -> getAuctionInfo( $item_id );

	if( !$auction_info )
	{
		$errors[] = $GLOBALS['lang']['shc_auction_failed'];
	}

	if ( $auction_info['pStatus'] != 'unpaid' )
	{
		$errors[] = $GLOBALS['lang']['shc_auction_already_paid'];
	}	

	if ( !$errors )
	{
		$rlSmarty -> assign_by_ref( 'auction_info', $auction_info );

		switch( $cur_step )
		{
			case 'cart' :
				/* base step */
				break;

			case 'shipping' :
				$errors = array();

		        $rlShoppingCart -> getShippingMethods( true, $auction_info['Dealer_ID'] );
				$rlUPS -> outputStaticData();

				/* get dealer info */
				$sql = "SELECT * FROM `" . RL_DBPREFIX . "accounts` WHERE `ID` = '{$dealer}' LIMIT 1";
				$dealer_info = $rlDb -> getRow( $sql );

				$rlSmarty -> assign_by_ref( 'dealer_info', $dealer_info );

				/* adapt account phone */
				$sql = "SELECT * FROM `" . RL_DBPREFIX . "account_fields` WHERE `Key` = 'phone' LIMIT 1";
				$phone_field = $rlDb -> getRow( $sql );

				if($phone_field)
				{
					$account_info['phone'] = $reefless->parsePhone($account_info['phone'], $phone_field);
				}

				$rlSmarty -> assign_by_ref( 'shc_countries', $rlCategories -> getDF( 'countries' ) );

				/* get countries */
				$rlSmarty -> assign_by_ref( 'shc_countries', $rlCategories -> getDF( 'countries' ) );
				$rlShoppingCart -> getStatesUS( true );

				$update = false;

				/* simulate post data */
				if ( !$_POST['form'] )
				{
					$_POST['shipping']['method'] = $auction_info['Shipping_method'];
					$_POST['shipping']['package_type'] = $order_info['Package_type'];
					$_POST['shipping']['pickup_method'] = $order_info['Pickup_method'];
					$_POST['shipping']['ups_service'] = $order_info['UPSService'];
					$_POST['shipping']['country'] = $auction_info['Country'];
					$_POST['shipping']['state'] = $order_info['State'];

					if($order_info['Country'] != 'United States')
					{
						$_POST['shipping']['region'] = $order_info['State'];
					}
					$_POST['shipping']['city'] = $auction_info['City'];
					$_POST['shipping']['zip'] = $auction_info['Zip_code'];
					$_POST['shipping']['address'] = $auction_info['Address'];
					$_POST['shipping']['email'] = $auction_info['Mail'];
					$_POST['shipping']['phone'] = $auction_info['Phone'];
					$_POST['shipping']['comment'] = $auction_info['Comment'];
					$_POST['shipping']['name'] = $auction_info['Name'];
					$_POST['shipping']['vat_no'] = $auction_info['Vat_no'];
				}

				if ( !empty( $_POST['shipping'] ) && $_POST['form'] )
				{
					$shipping_details = $_POST['shipping'];

					/* check shipping fields */
					if ( empty($shipping_details['method']) )
					{
						array_push( $errors, str_replace( '{field}', "<b>\"{$lang['shc_shipping_method']}\"</b>", $lang['notice_select_empty'] ) );
						array_push( $error_fields, 'shipping[method]' );
					}
					else
					{
						if($shipping_details['method'] != 'pickup')
						{
		                    if ( $shipping_details['method'] == 'ups' )
							{
								if ( empty( $shipping_details['ups_service'] ) )
								{
									array_push( $errors, str_replace( '{field}', "<b>\"{$lang['shc_ups_service']}\"</b>", $lang['notice_select_empty'] ) );
									array_push( $error_fields, 'shipping[ups_service]' );
								}

								if($shipping_details['country'] != 'United States')
								{
									$shipping_details['state'] = $shipping_details['region'];
								}
							}

							if ( empty( $shipping_details['country'] ) )
							{
								array_push( $errors, str_replace( '{field}', "<b>\"{$lang['shc_country']}\"</b>", $lang['notice_select_empty'] ) );
								array_push( $error_fields, 'shipping[country]' );
							}
							if ( empty( $shipping_details['zip'] ) )
							{
								array_push( $errors, str_replace( '{field}', "<b>\"{$lang['shc_zip']}\"</b>", $lang['notice_field_empty'] ) );
								array_push( $error_fields, 'shipping[zip]' );
							}
							if ( empty( $shipping_details['city'] ) )
							{
								array_push( $errors, str_replace( '{field}', "<b>\"{$lang['shc_city']}\"</b>", $lang['notice_field_empty'] ) );
								array_push( $error_fields, 'shipping[city]' );
							}
							if ( empty( $shipping_details['address'] ) )
							{
								array_push( $errors, str_replace( '{field}', "<b>\"{$lang['shc_address']}\"</b>", $lang['notice_field_empty'] ) );
								array_push( $error_fields, 'shipping[address]' );
							}
						}

						if ( empty( $shipping_details['name'] ) )
						{
							array_push( $errors, str_replace( '{field}', "<b>\"{$lang['your_name']}\"</b>", $lang['notice_field_empty'] ) );
							array_push( $error_fields, 'shipping[name]' );
						}
						if ( empty( $shipping_details['email'] ) )
						{
							array_push( $errors, str_replace( '{field}', "<b>\"{$lang['your_email']}\"</b>", $lang['notice_field_empty'] ) );
							array_push( $error_fields, 'shipping[email]' );
						}
						if ( empty( $shipping_details['phone'] ) )
						{
							array_push( $errors, str_replace( '{field}', "<b>\"{$lang['shc_phone']}\"</b>", $lang['notice_field_empty'] ) );
							array_push( $error_fields, 'shipping[phone]' );
						}
					}

					$error_fields = implode( ',', $error_fields );

					if ( !$errors )
					{
						if ( $rlAuction -> addShippingInfo( $shipping_details, $auction_info ) )
						{
							/* redirect to related controller */
							$redirect = SEO_BASE;
							$redirect .= $config['mod_rewrite'] ? $pages['shc_auction_payment'] . ( $nextStep['path'] ? '/' . $nextStep['path'] : '' ) . '.html?item=' . $item_id : '?page=' . $pages['shc_auction_payment'] . '&step=' . $nextStep['path'] . '&item=' . $item_id;
							$reefless -> redirect( null, $redirect );
						}
						else
						{
							$errors[] = $GLOBALS['lang']['shc_create_order_error'];
						}
					}
				}
				break;

			case 'confirmation' :

				if ( $auction_info )
				{
					$quote = false;

					if($_POST['form'])
					{   
						$update = array(
								'fields' => array(
										'Shipping_price' => $_POST['shipping_price'],
										'Total' => $auction_info['Total'] + $_POST['shipping_price']
									),
								'where' => array('ID' => $auction_info['ID'])
							);

							$action = $rlActions -> updateOne($update, 'shc_orders');

							/* redirect to related controller */
							if($action)
							{
								/* update session */
		                        $_SESSION['order_info']['Total'] = $update['fields']['Total'];
		                        $_SESSION['order_info']['Shipping_price'] = $_POST['shipping_price'];

								$redirect = SEO_BASE;
								$redirect .= $config['mod_rewrite'] ? $pages['shc_auction_payment'] . ( $nextStep['path'] ? '/' . $nextStep['path'] : '' ) . '.html?item=' . $item_id  : '?page=' . $pages['shc_auction_payment'] . '&step=' . $nextStep['path'] . '&item=' . $item_id;
								$reefless -> redirect( null, $redirect );
							}
							else
							{
								$errors[] = $GLOBALS['lang']['shc_update_order_error'];
							}
					}

					if ( $auction_info['Shipping_method'] == 'dhl' )
					{
						$sql = "SELECT * FROM `" . RL_DBPREFIX . "accounts` WHERE `ID` = '{$auction_info['Dealer_ID']}' LIMIT 1";
						$dealer_info = $rlDb -> getRow( $sql );

						$search = array(
								'{message_time}',
								'{reference}',
								'{siteid}',
								'{password}',
								'{from_country_code}',
								'{from_postal_code}',
								'{from_city}',
								'{vat_no}',
								'{payment_country_code}',
								'{bkg_date}',
								'{bkg_ready_time}',
								'{bkg_weight}',
								'{to_country_code}',
								'{to_postal_code}',
								'{to_city}',
								'{vat_no}'
							);

						$ts = time();
						$reference = $ts . $ts . $ts . $ts . $ts . $ts . $ts . $ts . $ts . $ts;
						$reference = substr( $reference, 0, 30 );
						$from_country_code = $rlShoppingCart -> getCountryCode( $dealer_info['country'] );

						$replacement = array(
								date( "c" ),
								$reference,
								$GLOBALS['config']['shc_dhl_site_id'],
								$GLOBALS['config']['shc_dhl_password'],
								$from_country_code,
								$dealer_info['zip_code'],
								$dealer_info['city'] ? $dealer_info['city'] : '',
								$dealer_info['vat_no'] ? $dealer_info['vat_no'] : '',
								$from_country_code,
								date( 'Y-m-d' ),
								'PT10H21M',
								(float)$auction_info['Weight'],
								$rlShoppingCart -> getCountryCode( $auction_info['Country'] ),
								$auction_info['Zip_code'],		
								$auction_info['City'] ? $auction_info['City'] : '',
								$auction_info['Vat_no'] ? $auction_info['Vat_no'] : ''
							);

						$xml = str_replace( $search, $replacement, $rlDHL -> xml_schema );
						$response = $rlDHL -> parse( $rlDHL -> post( $xml ) );

						$quote = array(
								'code' => 'DHL',
								'title' =>  $response['PRODUCTSHORTNAME'][0],
								'days' => $response['TOTALTRANSITDAYS'][0],
								'quote' => round( $response['SHIPPINGCHARGE'][0] * $response['EXCHANGERATE'][0], 2 ),
								'error' => ""
							);

						if ( $quote['quote'] <= 0 )
						{
							$errors[] = $GLOBALS['lang']['shc_shipping_method_failed'];
						}
					}
					elseif( $order_info['Shipping_method'] == 'ups' )
					{
		                $rlUPS -> request = array(
								'ups_pickup' => $GLOBALS['config']['shc_method'] == 'single' ? $GLOBALS['config']['shc_ups_pickup_methods'] : $dealer_info['shc_ups_pickup_methods'],
								'ups_classification' => $GLOBALS['config']['shc_method'] == 'single' ? $GLOBALS['config']['shc_ups_classification'] : $dealer_info['shc_ups_classification'],
								'ups_country' => $GLOBALS['config']['shc_method'] == 'single' ? $rlShoppingCart -> getCountryCode( $GLOBALS['config']['shc_ups_country'] ) : $rlShoppingCart -> getCountryCode( $dealer_info['country'] ),
								'ups_city' => $GLOBALS['config']['shc_method'] == 'single' ? $GLOBALS['config']['shc_ups_city'] : $dealer_info['city'],
								'ups_state' =>  $GLOBALS['config']['shc_method'] == 'single' ? $GLOBALS['config']['shc_ups_state'] : $dealer_info['ups_state'],
								'ups_postcode' => $GLOBALS['config']['shc_method'] == 'single' ? $GLOBALS['config']['shc_ups_zip'] : $dealer_info['zip_code'],
								'country' => $rlShoppingCart -> getCountryCode( $order_info['Country'] ),
								'city' => $order_info['City'],
								'postcode' => $order_info['Zip_code'],
								'ups_quote_type' => $GLOBALS['config']['shc_ups_quote_type'],
								'ups_packaging' => $GLOBALS['config']['shc_method'] == 'single' ? $GLOBALS['config']['shc_ups_package_types'] : $dealer_info['shc_ups_package_type'],
								'length_code' => $GLOBALS['config']['shc_ups_length_type'],
								'weight_code' => $GLOBALS['config']['shc_ups_weight_type'],
								'length' => $GLOBALS['config']['shc_ups_length'],
								'width' => $GLOBALS['config']['shc_ups_width'],
								'height' => $GLOBALS['config']['shc_ups_height'],
								'weight' => (float)$_SESSION['order_info']['Weight'],
							);

						$quote = $rlUPS -> post( $rlUPS -> build_xml_schema());

						if ( $quote['quote'] <= 0 )
						{
							$errors[] = $GLOBALS['lang']['shc_shipping_method_failed'];
						}
					}
					elseif( $auction_info['Shipping_method'] == 'courier' )
					{
						$quote = array(
								'code' => $GLOBALS['lang']['shc_shipping_courier'],
								'title' =>  '',
								'days' => ' - ',
								'quote' => 0.00,
								'error' => ""
							);
					}
					elseif( $auction_info['Shipping_method'] == 'pickup' )
					{
						$quote = array(
								'code' => $GLOBALS['lang']['shc_shipping_pickup'],
								'title' =>  '',
								'days' => ' - ',
								'quote' => 0.00,
								'error' => ""
							);
					}

					$auction_info['total_price'] = $auction_info['Total'] + $quote['quote'];

					$rlSmarty -> assign_by_ref( 'auction_info', $auction_info );
					$rlSmarty -> assign_by_ref( 'quote', $quote );

				}
				else
				{
					$sError = true;
				}
				break;

			case 'checkout' :
				$rlShoppingCart -> getPaymentGateways( true, $auction_info['Dealer_ID'] );

				if ( $GLOBALS['config']['shc_method'] == 'multi' )
				{
					$sql = "SELECT * FROM `" . RL_DBPREFIX . "accounts` WHERE `ID` = '{$auction_info['Dealer_ID']}' LIMIT 1";
					$dealer_info = $rlDb -> getRow( $sql );

					$rlShoppingCart -> adaptPaymentDetails( $dealer_info );
					unset( $dealer_info );
				}
				  
				if ( !empty( $auction_info ) )
				{
					$plan_info = array(
						'Price' => $auction_info['Total']
					);

					$rlSmarty -> assign_by_ref( 'plan_info', $plan_info );

					if ( $_POST['form'] )
					{
						$gateway = $_POST['gateway'];

						/* check availble payment gateway */
						if ( empty( $gateway ) )
						{
							$errors[] = $lang['notice_payment_gateway_does_not_chose'];
						}

						if ( !$errors )
						{
							$cancel_url = SEO_BASE;
							$cancel_url .= $GLOBALS['config']['mod_rewrite'] ? $pages['shc_auctions'] . '/' . $shc_steps['checkout']['path'] . '.html?item='.$auction_info['ID'].'&canceled' : 'index.php?page=' . $pages['shc_auctions'] . '&ampstep=' . $shc_steps['checkout']['path'] . '&amp;item='.$auction_info['ID'].'&amp;canceled';

							$success_url = SEO_BASE;
							$success_url .= $GLOBALS['config']['mod_rewrite'] ? $pages['shc_auctions'] . '/' . $shc_steps['done']['path'] . '.html?item='.$auction_info['ID'].'&completed' : 'index.php?page=' . $pages['shc_auctions'] . '&ampstep=' . $shc_steps['done']['path'] . '&amp;item='.$auction_info['ID'].'&amp;completed';

							$complete_payment_info = array(
								'item_name' => $auction_info['title'],
								'gateway' => $gateway,
								'service' => 'auction',
								'item_id' => $auction_info['ID'],
								'plan_info' => $plan_info,
								'account_id' => $account_info['ID'],
								'callback' => array(
									'class' => 'rlAuction',
									'method' => 'completeTransaction',
									'cancel_url' => $cancel_url,
									'success_url' => $success_url,
									'plugin' => "shoppingCart"
								)
							);

							$_SESSION['complete_payment'] = $complete_payment_info;

							$rlHook -> load( 'addShoppingCartCheckoutPreRedirect' );

							/* redirect to checkout */
							$redirect = SEO_BASE;
							$redirect .= $config['mod_rewrite'] ? $pages['payment'] . '.html' : 'index.php?page=' . $pages['payment'];
							$reefless -> redirect( null, $redirect );
							exit;
						}
					}
				}
				else
				{
					$sError = true;
				}
				break;

			case 'done' :
		        $item_id = (int)$_GET['item'];

				$is_paid = $rlAuction -> isPaid( $item_id );
				$rlSmarty -> assign_by_ref( 'shcIsPaid', $is_paid );

				if ( $is_paid )
				{
					unset( $_SESSION['order_info'] );
				}
				else
				{ 
					$sError = true;
				}

				break;

			default :
				$sError = true;
				break;
		}
	}
}
else
{
	$sError = true;
}
