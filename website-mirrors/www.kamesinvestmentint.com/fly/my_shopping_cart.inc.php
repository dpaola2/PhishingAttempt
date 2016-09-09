<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: MY_SHOPPING_CART.INC.PHP
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
$reefless -> loadClass( 'DHL', null, 'shoppingCart' );
$reefless -> loadClass( 'UPS', null, 'shoppingCart' );

$shc_steps = $rlShoppingCart -> getSteps();

$rlHook -> load( 'shoppingCartProcessOrderTop' );

/* init currency rates */
$rlHook -> load( 'shoppingCartCurrencyRates' );

if ( defined( 'IS_LOGIN' ) )
{
	unset( $shc_steps['auth'] );
}

$rlSmarty -> assign_by_ref( 'shc_steps', $shc_steps );
$show_step_caption = false;
$rlSmarty -> assign_by_ref( 'show_step_caption', $show_step_caption );

/* define step */
$request = explode( '/', $_GET['rlVareables'] );
$requestStep = array_pop( $request );

$step = $requestStep ? $requestStep : $_GET['step'];
$cur_step = !empty( $step ) ? $step : 'cart'; 

if ( !empty( $cur_step ) )
{
	if ( $cur_step != 'cart' )
	{
		$bread_crumbs[] = array(
			'name' => $GLOBALS['lang']['shc_step_' . $cur_step]
		);
	}

	$rlSmarty -> assign( 'cur_step', $cur_step );

	$page_info['name'] = $GLOBALS['lang']['shc_step_' . $cur_step];
}

/* get prev/next step */
$tmp_steps = $shc_steps;
foreach( $tmp_steps as $t_key => $t_step )
{
	if ( $t_key != $cur_step )
	{
		next( $shc_steps );
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

/* check availability of step */
if( !defined( 'IS_LOGIN' ) && $cur_step != 'cart' && $cur_step != 'auth' )
{
	$sError = true;

	/* redirect to auth */
	$redirect = SEO_BASE;
	$redirect .= $config['mod_rewrite'] ? $pages['shc_my_shopping_cart'] . '/' . $shc_steps['auth']['path'] . '.html' : '?page=' . $pages['shc_my_shopping_cart'] . '&step=' . $shc_steps['auth']['path'];
	$reefless -> redirect( null, $redirect );
}

/* get items */
$items_tmp = $rlShoppingCart -> getItems();

$total = 0;
$weight = 0;

foreach( $items_tmp as $iKey => $iVal )
{
	$listing_type = $rlListingTypes -> types[$iVal['Listing_type']];

	$items_tmp[$iKey]['listing_link'] = SEO_BASE;
	$items_tmp[$iKey]['listing_link'] .= $GLOBALS['config']['mod_rewrite'] ? $pages[$listing_type['Page_key']] . '/' . $iVal['Path'] . '/' . $rlSmarty -> str2path( $iVal['Item'] ) . '-' . $iVal['Item_ID'] . '.html' : '?page=' . $pages[$listing_type['Page_key']] . '&amp;id=' . $iVal['Item_ID'];

	$items_tmp[$iKey]['total'] = round( ( $iVal['Quantity'] * $iVal['Price'] ), 2 );

	$total += $items_tmp[$iKey]['total'];
	$weight += $items_tmp[$iKey]['weight'];
}

$total = round( $total, 2 );
$weight = round( $weight, 2 );

$rlSmarty -> assign_by_ref( 'items_tmp', $items_tmp );

/* set dealer */
$dealer = $_SESSION['shc_dealer'] = !empty( $_POST['dealer'] ) ? (int)$_POST['dealer'] :  (int)$_SESSION['shc_dealer'];

$errors = $error_fields = array();

/* move H1 for responsive template */
if ( $tpl_settings['type'] == 'responsive_42' ) {
	$rlSmarty -> assign('no_h1', true);
}

switch( $cur_step )
{
	case 'cart'	: 
		if ( $_POST['form'] )
		{
			/* update quantity */
			if ( !empty( $_POST['quantity'] ) )
			{
				if ( $GLOBALS['config']['shc_method'] == 'multi' )
				{
					$rlShoppingCart -> updateItems( $items_tmp, $_POST['quantity'], $_POST['dealer'] );
				}
				else
				{
					$rlShoppingCart -> updateItems( $items_tmp, $_POST['quantity'] );
				}
			}

			/* redirect to related controller */
			$redirect = SEO_BASE;
			$redirect .= $config['mod_rewrite'] ? $pages['shc_my_shopping_cart'] . ( $nextStep['path'] ? '/' . $nextStep['path'] : '' ) . '.html' : '?page=' . $pages['shc_my_shopping_cart'] . '&step=' . $nextStep['path'];
			$reefless -> redirect( null, $redirect );
			exit;
		}

		if ( !empty ( $items_tmp ) )
		{
			if ( $GLOBALS['config']['shc_method'] == 'multi' )
			{
				$order_key = $rlValid -> xSql( $_COOKIE['shc_txn_id'] );
				$sql = "SELECT `T1`.`Dealer_ID`, ";
				$sql .= "IF(`T2`.`First_name` <> '' AND `T2`.`Last_name` <> '', CONCAT(`T2`.`First_name`, ' ', `T2`.`Last_name`), `T2`.`Username`) `Full_name` ";
				$sql .= " FROM `" . RL_DBPREFIX . "shc_order_details` AS `T1` ";
				$sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T2` ON `T1`.`Dealer_ID` = `T2`.`ID` ";
				$sql .= "WHERE `T1`.`Order_key` = '{$order_key}' AND `T1`.`Status` = 'active' ";
				$sql .= "GROUP BY `T1`.`Dealer_ID` ";

 				$dealers = $rlDb -> getAll( $sql );

				foreach ( $dealers as $k => $dVal )
				{
					$total_by_dealer = 0;

					foreach ( $items_tmp as $key => $item )
					{
						if ( $item['Dealer_ID'] == $dVal['Dealer_ID'] )
						{
							$items_by_dealer[] = $item;
							$dealers[$k]['total'] += (float)$item['Price'] * $item['Quantity'];
							$dealers[$k]['delivery'] += (float)$item['Delivery'];
						}
					}

					$dealers[$k]['items'] = $items_by_dealer;
					$dealers[$k]['total'] = round( ( $dealers[$k]['total'] + $dealers[$k]['delivery'] ), 2 );
					$dealers[$k]['delivery'] = round( $dealers[$k]['delivery'], 2 );

					unset( $items_by_dealer );
				}

				$items = $dealers;
				unset( $dealers );
			}
			else
			{
				$items = $items_tmp;
			}

			$rlSmarty -> assign_by_ref( 'items', $items );
			$rlSmarty -> assign( 'total', $total );
			$rlSmarty -> assign( 'delivery', $delivery );
		}
		break;

	case 'auth' :
		if ( !empty( $_POST['form'] ) )
		{
			$auth_try = false;

			$login_data = $_POST['login'];
			$register_data = $_POST['register'];

			/* login */
			if ( $login_data['username'] && $login_data['password'] )
			{
				$auth_try = true;

				if ( true === $res = $rlAccount -> login( $login_data['username'], $login_data['password'] ) )
				{
					$rlSmarty -> assign( 'isLogin', $_SESSION['username'] );
					define( 'IS_LOGIN', true );

					$account_info = $_SESSION['account'];
					$rlSmarty -> assign_by_ref( 'account_info', $account_info );
				}
				else
				{
					$errors = array_merge( $errors, $res );
				}
			}
			/* register */
			elseif ( $register_data['name'] && $register_data['email'] )
			{
				$auth_try = true;

				if ( $test =  $rlDb -> getOne( 'ID', "`Mail` = '{$register_data['email']}' AND `Status` <> 'trash'", 'accounts' ) )
				{
					$errors[] = str_replace( '{email}', '<span class="field_error">' . $register_data['email'] . '</span>', $lang['notice_account_email_exist'] );
				}
				if ( !$rlValid -> isEmail( $register_data['email'] ) )
				{
					$errors[] = $lang['notice_bad_email'];
				}

				if ( !$errors )
				{
					if ( $new_account = $rlAccount -> quickRegistration( $register_data['name'], $register_data['email'] ) )
					{
						$rlAccount -> login( $new_account[0], $new_account[1] );

						$_SESSION['add_listing']['account'] = $new_account;

						$rlSmarty -> assign( 'isLogin', $_SESSION['username'] );
						define( 'IS_LOGIN', true );

						$account_info = $_SESSION['account'];
						$rlSmarty -> assign_by_ref( 'account_info', $account_info );

						/* send login details to user */
						$mail_tpl = $rlMail -> getEmailTemplate( 'quick_account_created' );
						$find = array( '{username}', '{login}', '{password}' );
						$replace = array( $register_data['name'], $new_account[0], $new_account[1] );

						$mail_tpl['body'] = str_replace( $find, $replace, $mail_tpl['body'] );
						$rlMail -> send( $mail_tpl, $register_data['email'] );
					}
				}
			}

			if ( !$auth_try )
			{
				$errors[] = $lang['quick_signup_fail'];
			}

			if ( !$errors )
			{
				/* redirect to related controller */
				$redirect = SEO_BASE;
				$redirect .= $config['mod_rewrite'] ? $pages['shc_my_shopping_cart'] . '/' . $shc_steps['shipping']['path'] . '.html' : '?page=' . $pages['shc_my_shopping_cart'] . '&step=' . $shc_steps['shipping']['path'];
				$reefless -> redirect( null, $redirect );
			}
		}
		break;

	case 'shipping' :
        $rlShoppingCart -> getShippingMethods( true, $dealer );
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

		/* get countries */
		$rlSmarty -> assign_by_ref( 'shc_countries', $rlCategories -> getDF( 'countries' ) );
		$rlShoppingCart -> getStatesUS( true );

		$update = false;

		$order_key = $rlValid -> xSql( $_COOKIE['shc_txn_id'] );
		$sql = "SELECT * FROM `" . RL_DBPREFIX . "shc_orders` WHERE `Order_key` = '" . ( $order_key . '-D' . $dealer ) . "' AND `Dealer_ID` = '{$dealer}' LIMIT 1";
		$order_info = $rlDb -> getRow( $sql );

		/* simulate post data */
		if ( !$_POST['form'] )
		{
			$_POST['shipping']['method'] = $order_info['Shipping_method'];
			$_POST['shipping']['package_type'] = $order_info['Package_type'];
			$_POST['shipping']['pickup_method'] = $order_info['Pickup_method'];
			$_POST['shipping']['ups_service'] = $order_info['UPSService'];
			$_POST['shipping']['country'] = $order_info['Country'];
			$_POST['shipping']['city'] = $order_info['City'];
			$_POST['shipping']['state'] = $order_info['State'];
			
			if($order_info['Country'] != 'United States')
			{
				$_POST['shipping']['region'] = $order_info['State'];
			}
			$_POST['shipping']['zip'] = $order_info['Zip_code'];
			$_POST['shipping']['address'] = $order_info['Address'];
			$_POST['shipping']['email'] = $order_info['Mail'];
			$_POST['shipping']['phone'] = $order_info['Phone'];
			$_POST['shipping']['comment'] = $order_info['Comment'];
			$_POST['shipping']['name'] = $order_info['Name'];
			$_POST['shipping']['vat_no'] = $order_info['Vat_no'];
		}

		if ( !empty( $_POST['shipping'] ) && $_POST['form'] )
		{
			$shipping_details = $_POST['shipping'];

			/* check shipping fields */
			if(empty( $shipping_details['method'] ) )
			{
				array_push( $errors, str_replace( '{field}', "<b>\"{$lang['shc_shipping_method']}\"</b>", $lang['notice_select_empty'] ) );
				array_push( $error_fields, 'shipping[method]' );
			}
			else
			{
				if ( $shipping_details['method'] != 'pickup' )
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
				if ( $GLOBALS['config']['shc_method'] == 'multi' )
				{
					$total_price = 0;
					$weight = 0;

					foreach( $items_tmp as $iKey => $iVal )
					{
						if ( $iVal['Dealer_ID'] == $dealer )
						{                                  
							$total_price += ( $iVal['Quantity'] * $iVal['Price'] );	
							$weight += $iVal['weight'];
						}
					}
				}
				else
				{
					$total_price = $total;
				}

				$shipping_details['weight'] = round( $weight, 2 );
				$total_price = round( $total_price, 2 );

				/* get dealer info */
				$sql = "SELECT * FROM `" . RL_DBPREFIX . "accounts` WHERE `ID` = '{$dealer}' LIMIT 1";
				$dealer_info = $rlDb -> getRow( $sql );

				if ( $rlShoppingCart -> createOrder( $shipping_details, $total_price, $dealer, $order_info ) )
				{
					/* redirect to related controller */
					$redirect = SEO_BASE;
					$redirect .= $config['mod_rewrite'] ? $pages['shc_my_shopping_cart'] . ( $nextStep['path'] ? '/' . $nextStep['path'] : '' ) . '.html' : '?page=' . $pages['shc_my_shopping_cart'] . '&step=' . $nextStep['path'];
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

		$order_info = $_SESSION['order_info'];

		foreach( $items_tmp as $iKey => $iVal )
		{
			if ( $GLOBALS['config']['shc_method'] == 'multi' )
			{
				if ( $iVal['Dealer_ID'] == $dealer )
				{            
            		$total_price += ( $iVal['Quantity'] * $iVal['Price'] );	            
					$iVal['total'] = round( $iVal['Quantity'] * $iVal['Price'], 2 );	
					$order_info['items'][] = $iVal;  
				}
			}
			else
			{
            	$total_price += ( $iVal['Quantity'] * $iVal['Price'] );	            
				$iVal['total'] = round( $iVal['Quantity'] * $iVal['Price'], 2 );	
				$order_info['items'][] = $iVal;
			}
		}

		if ( $order_info )
		{
			$quote = false;

			if($_POST['form'])
			{   
				$update = array(
						'fields' => array(
								'Shipping_price' => (float)$_POST['shipping_price'],
								'Total' => $order_info['Total'] + (float)$_POST['shipping_price']
							),
						'where' => array('ID' => $order_info['ID'])
					);

					$action = $rlActions -> updateOne($update, 'shc_orders');

					/* redirect to related controller */
					if($action)
					{
						/* update session */
                        $_SESSION['order_info']['Total'] = $update['fields']['Total'];
                        $_SESSION['order_info']['Shipping_price'] = $_POST['shipping_price'];
						
						$redirect = SEO_BASE;
						$redirect .= $config['mod_rewrite'] ? $pages['shc_my_shopping_cart'] . ( $nextStep['path'] ? '/' . $nextStep['path'] : '' ) . '.html' : '?page=' . $pages['shc_my_shopping_cart'] . '&step=' . $nextStep['path'];
						$reefless -> redirect( null, $redirect );
					}
					else
					{
						$errors[] = $GLOBALS['lang']['shc_update_order_error'];
					}
			}
			
			/* get dealer info */
			$sql = "SELECT * FROM `" . RL_DBPREFIX . "accounts` WHERE `ID` = '{$dealer}' LIMIT 1";
			$dealer_info = $rlDb -> getRow( $sql );

			if ( $order_info['Shipping_method'] == 'dhl' )
			{
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
						(float)$_SESSION['order_info']['Weight'],
						$rlShoppingCart -> getCountryCode( $order_info['Country'] ),
						$order_info['Zip_code'],		
						$order_info['City'] ? $order_info['City'] : '',
						$order_info['Vat_no'] ? $order_info['Vat_no'] : ''
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
			elseif( $order_info['Shipping_method'] == 'courier' )
			{
				$quote = array(
						'code' => $GLOBALS['lang']['shc_shipping_courier'],
						'title' =>  '',
						'days' => ' - ',
						'quote' => 0.00,
						'error' => ""
					);
			}
			elseif( $order_info['Shipping_method'] == 'pickup' )
			{
				$quote = array(
						'code' => $GLOBALS['lang']['shc_shipping_pickup'],
						'title' =>  '',
						'days' => ' - ',
						'quote' => 0.00,
						'error' => ""
					);
			}

			$order_info['total_price'] = $total_price + $quote['quote'];

			$rlSmarty -> assign_by_ref( 'order_info', $order_info );
			$rlSmarty -> assign_by_ref( 'quote', $quote );
		}
		else
		{
			$sError = true;
		}

		break;

	case 'checkout' :
		$rlShoppingCart -> getPaymentGateways( true, $_SESSION['order_info']['Dealer_ID'] );

		if ( $GLOBALS['config']['shc_method'] == 'multi' )
		{
			$sql = "SELECT * FROM `" . RL_DBPREFIX . "accounts` WHERE `ID` = '{$dealer}' LIMIT 1";
			$dealer_info = $rlDb -> getRow( $sql );

			$rlShoppingCart -> adaptPaymentDetails( $dealer_info );

			if ( !empty( $dealer_info ) )
			{                  
				$dealer_info['shc_paypal_email'] ? $payment_plugins[] = 'paypal' : null;
				$dealer_info['shc_2co_id'] ? $payment_plugins[] = '2co' : null;
			}

			unset( $dealer_info );
		}
		  
		if ( !empty( $_SESSION['order_info'] ) )
		{
			$plan_info = array(
				'Price' => $_SESSION['order_info']['Total']
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
					$cancel_url .= $GLOBALS['config']['mod_rewrite'] ? $pages['shc_my_shopping_cart'] . '/' . $shc_steps['checkout']['path'] . '.html?canceled' : 'index.php?page=' . $page_info['Path'] . '&ampstep=' . $shc_steps['checkout']['path'] . '&amp;canceled';
					$cancel_url .= '&item=' . $_SESSION['order_info']['ID'];

					$success_url = SEO_BASE;
					$success_url .= $GLOBALS['config']['mod_rewrite'] ? $pages['shc_my_shopping_cart'] . '/' . $shc_steps['done']['path'] . '.html?completed' : 'index.php?page=' . $pages['shc_my_shopping_cart'] . '&ampstep=' . $shc_steps['done']['path'] . '&amp;completed';
					$success_url .= '&item=' . $_SESSION['order_info']['ID'];

					$complete_payment_info = array(
						'item_name' => $rlShoppingCart -> getOrderTitle( $_SESSION['order_info']['ID'] ) . ' (#' . $_SESSION['order_info']['Order_key'] . ')',
						'gateway' => $gateway,
						'service' => 'shoppingCart',
						'item_id' => $_SESSION['order_info']['ID'],
						'plan_info' => $plan_info,
						'account_id' => $account_info['ID'],
						'callback' => array(
							'class' => 'rlShoppingCart',
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

		$is_paid = $rlShoppingCart -> isPaid( $item_id );
		$rlSmarty -> assign_by_ref( 'shcIsPaid', $is_paid );

		if ( $is_paid )
		{
			$rlShoppingCart -> clearCookie();
			$GLOBALS['rlSmarty'] -> assign_by_ref( 'shcItems', $_SESSION['shc_items'] );
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

$rlHook -> load( 'shoppingCartProcessOrderBottom' );

if ( !empty( $errors ) )
{
	$rlSmarty -> assign_by_ref( 'errors', $errors );
}