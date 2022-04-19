<?php

/**
 * Plugin Name: Woocommerce Cario Shipping
 * Plugin URI:        https://freelancer.com/u/projoomexperts
 * Description:       Woocommerce shipping plugin for cario including handling charge and uplift
 * Version:           1.10.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            ProJoomExperts
 * Author URI:        https://freelancer.com/u/projoomexperts
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cario
 */

/* 
 * Retrieve this value with:
 * $woocommerce_cario_shipping_options = get_option( 'woocommerce_cario_shipping_option_name' ); // Array of All Options
 * $usernameoremailaddress_0 = $woocommerce_cario_shipping_options['usernameoremailaddress_0']; // userNameOrEmailAddress
 * $password_1 = $woocommerce_cario_shipping_options['password_1']; // password
 * $tenantname_2 = $woocommerce_cario_shipping_options['tenantname_2']; // tenantName
 * $rememberclient_3 = $woocommerce_cario_shipping_options['rememberclient_3']; // rememberClient
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}



/*

// Scheduled Action Hook
function woo_cario_authentication_generate( ) {

	

	$woocommerce_cario_shipping_options = get_option('woocommerce_cario_settings');
	$apiUrl = $woocommerce_cario_shipping_options['apiUrl'];
	$usernameoremailaddress = $woocommerce_cario_shipping_options['userNameOrEmailAddress'];
	$password = $woocommerce_cario_shipping_options['password'];
	$tenantname = $woocommerce_cario_shipping_options['tenantName'];
	$rememberclient = true;

	$url = $apiUrl . '/api/Authentication/Login';

	$response = wp_remote_post( $url, array(
		'method'      => 'POST',
		'timeout'     => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => [
			'Content-Type' => 'application/json',
		],
		'body'        => json_encode(array(
			'userNameOrEmailAddress' => $usernameoremailaddress,
			'password' => $password,
			'tenantName' => $tenantname,
			'rememberClient' => $rememberclient
		)),
		'cookies'     => array()
		)
	);

	

	pj_var_dump($response);

	 
	if ( is_wp_error( $response ) ) {
		$error_message = $response->get_error_message();
	} else {
		$data = json_decode($response['body'] , true );
		update_option('woo_cario_auth_data', $data);
	}

}
add_action( 'woo_cario_authentication_generate', 'woo_cario_authentication_generate' );

// Schedule Cron Job Event
function woo_cario_authentication() {
	if ( ! wp_next_scheduled( 'woo_cario_authentication_generate' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'daily', 'woo_cario_authentication_generate' );
	}
}
add_action( 'wp', 'woo_cario_authentication' );


*/


// Add Shortcode
function fn_woo_cario_test() {

	woo_cario_authentication_generate();

}
add_shortcode( 'woo_cario_test', 'fn_woo_cario_test' );






if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
function cario_shipping_method() {
	if ( ! class_exists( 'Cario_Shipping_Method' ) ) {
		class Cario_Shipping_Method extends WC_Shipping_Method {
			/**
			 * Constructor for your shipping class
			 *
			 * @access public
			 * @return void
			 */
			public function __construct() {
				$this->id                 = 'cario'; 
				$this->method_title       = __( 'Cario Shipping', 'cario' );  
				$this->method_description = __( 'Custom Shipping Method for Cario', 'cario' ); 

				// Availability & Countries
				$this->availability = 'including';
				$this->countries = array(
					'AU'
					);

				$this->init();

				$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
				$this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Cario Shipping', 'cario' );
				$this->accessToken = isset( $this->settings['accessToken'] ) ? $this->settings['accessToken'] : __( '', 'cario' );
				$this->customerId = isset( $this->settings['customerId'] ) ? $this->settings['customerId'] : __( '', 'cario' );
				$this->tenantId = isset( $this->settings['tenantId'] ) ? $this->settings['tenantId'] : __( '', 'cario' );

				$this->uplift = isset( $this->settings['uplift'] ) ? $this->settings['uplift'] : __( '5', 'cario' );
				$this->handlingCharge = isset( $this->settings['handlingCharge'] ) ? $this->settings['handlingCharge'] : __( '20', 'cario' );

				$this->storeCompany = isset( $this->settings['storeCompany'] ) ? $this->settings['storeCompany'] : __( '', 'cario' );
				$this->storeAddress = isset( $this->settings['storeAddress'] ) ? $this->settings['storeAddress'] : __( '', 'cario' );
				$this->storeCity = isset( $this->settings['storeCity'] ) ? $this->settings['storeCity'] : __( '', 'cario' );
				$this->storeState = isset( $this->settings['storeState'] ) ? $this->settings['storeState'] : __( '', 'cario' );
				$this->storePostCode = isset( $this->settings['storePostCode'] ) ? $this->settings['storePostCode'] : __( '', 'cario' );
				$this->storeCountry = isset( $this->settings['storeCountry'] ) ? $this->settings['storeCountry'] : __( '', 'cario' );




			}

			/**
			 * Init your settings
			 *
			 * @access public
			 * @return void
			 */
			function init() {
				// Load the settings API
				$this->init_form_fields(); 
				$this->init_settings(); 

				// Save settings in admin if you have any defined
				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
				//add_action('woocommerce_update_options_shipping_' . $this->id, 'woo_cario_authentication_generate', 99);
			}

			/**
			 * Define settings field for this shipping
			 * @return void 
			 */
			function init_form_fields() { 

				$symbol = get_woocommerce_currency();

				$this->form_fields = array(

					'enabled' => array(
						'title' => __( 'Enable', 'cario' ),
						'type' => 'checkbox',
						'description' => __( 'Enable this shipping.', 'cario' ),
						'default' => 'yes'
					),

					'title' => array(
						'title' => __( 'Title', 'cario' ),
						'type' => 'text',
						'description' => __( '', 'cario' ),
						'default' => __( 'Cario Shipping', 'cario' )
					),


					/*

					'apiUrl' => array(
						'title' => __( 'API URL', 'cario' ),
						'type' => 'text',
						'description' => __( 'Only Domain Name With HTTPS:// ,  No extra / at the end', 'cario' ),
						'default' => __( 'https://integrate.cario.com.au', 'cario' )
					),
					
					
					'userNameOrEmailAddress' => array(
						'title' => __( 'Username / Email Address', 'cario' ),
						'type' => 'text',
						'description' => __( '', 'cario' ),
						'default' => __( 'APIUser', 'cario' )
					),

					'password' => array(
						'title' => __( 'Password', 'cario' ),
						'type' => 'password',
						'description' => __( '', 'cario' ),
						'default' => __( 'CarioAPItest', 'cario' )
					),

					'tenantName' => array(
						'title' => __( 'Tenant Name', 'cario' ),
						'type' => 'text',
						'description' => __( '', 'cario' ),
						'default' => __( 'TEST', 'cario' )
					),

					*/

					'accessToken' => array(
						'title' => __( 'Access Token', 'cario' ),
						'type' => 'textarea',
						'description' => __( 'Production token that you get from cario support', 'cario' ),
						'default' => __( '', 'cario' )
					),

					'tenantId' => array(
						'title' => __( 'Tenant ID', 'cario' ),
						'type' => 'text',
						'description' => __( '', 'cario' ),
						'default' => __( '', 'cario' )
					),

					'customerId' => array(
						'title' => __( 'Customer ID', 'cario' ),
						'type' => 'text',
						'description' => __( '', 'cario' ),
						'default' => __( '', 'cario' )
					),

					'handlingCharge' => array(
						'title' => __( 'Default Handling Charge', 'cario' ),
						'type' => 'number',
						'description' => __( 'In '. $symbol .' - Check <a target="_blank" href="'. site_url('/wp-admin/admin.php?page=cario_shipping_rules') .'">Weight Based Handling Charges</a>', 'cario' ),
						'default' => '20'
					),

					'uplift' => array(
						'title' => __( 'Uplift (%)', 'cario' ),
						'type' => 'number',
						'description' => __( 'In Percentage', 'cario' ),
						'default' => __( '5', 'cario' )
					),

					'storeDetails' => array(
						'title' => __( 'Store Details', 'cario' ),
						'type' => 'title',
						'description' => __( '', 'cario' ),
						'default' => ''
					),

					'storeCompany' => array(
						'title' => __( 'Company Name', 'cario' ),
						'type' => 'text',
						'description' => __( '', 'cario' ),
						'default' => __( '', 'cario' )
					),

					'storeAddress' => array(
						'title' => __( 'Address', 'cario' ),
						'type' => 'text',
						'description' => __( '', 'cario' ),
						'default' => __( '', 'cario' )
					),

					'storeCity' => array(
						'title' => __( 'City', 'cario' ),
						'type' => 'text',
						'description' => __( '', 'cario' ),
						'default' => __( '', 'cario' )
					),

					'storeState' => array(
						'title' => __( 'State', 'cario' ),
						'type' => 'text',
						'description' => __( 'Example : NSW', 'cario' ),
						'default' => __( 'NSW', 'cario' )
					),

					'storePostCode' => array(
						'title' => __( 'Post Code', 'cario' ),
						'type' => 'text',
						'description' => __( '', 'cario' ),
						'default' => __( '', 'cario' )
					),

					'storeCountry' => array(
						'title' => __( 'Country', 'cario' ),
						'type' => 'text',
						'description' => __( 'Example : AU', 'cario' ),
						'default' => __( 'AU', 'cario' )
					),

				);

			}

			/**
			 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
			 *
			 * @access public
			 * @param mixed $package
			 * @return void
			 */
			public function calculate_shipping( $package = array() ) {
				
				$weight = 0;
				$cost = 0;
				$qty = 0;
				$cart_prods_m3 = array();
				$cart_prod_id_m3 = array();

				foreach ( $package['contents'] as $item_id => $values ) 
				{ 
					$_product = $values['data'];
					if( $_product){
						if( $_product->get_weight() ){
							$weight = $weight + $_product->get_weight() * $values['quantity'];
						}
						$qty += $values['quantity'];
						if( $_product->get_length() !== null && $_product->get_width() !== null && $_product->get_height() !== null ){
							$prod_m3 = $_product->get_length() * $_product->get_width() * $_product->get_height();
							$prod_m3 = ( $prod_m3 / 1000000 ) * $values['quantity'] ;
							$cart_prod_id_m3[$prod_m3] = $item_id ;
							array_push($cart_prods_m3, $prod_m3);
						}
					}
				}

				$volume = array_sum($cart_prods_m3);

				$largest_key = max(array_keys($cart_prod_id_m3));
				$largest_product_key = $cart_prod_id_m3[$largest_key];
				$largest_product = $package['contents'][$largest_product_key]['data'];
				$largest_product_length = $largest_product->get_length();
				$largest_product_width = $largest_product->get_width();
				$largest_product_height = $largest_product->get_height();

				$weight = wc_get_weight( $weight, 'kg' );

				$store_company = $this->storeCompany;
				$store_address = $this->storeAddress;
				$store_city = $this->storeCity;
				$store_postcode = $this->storePostCode;
				$store_state = $this->storeState;
				$store_country = $this->storeCountry;
				
				$store_country_arr = $this->cario_get_country_id($store_country);

				$store_country_id = $store_country_arr['id'];

				$store_location_id = $this->cario_get_location_id($store_country_id, $store_postcode, $store_city);

				$customerId = $this->customerId;

				//pj_var_dump($store_location_id);

				
				
				$destination = $package["destination"];
				$destination_address = $destination['address_1'] . ' ' . $destination['address_2'];
				$destination_country = $destination['country'];
				$destination_city = $destination['city'];
				$destination_state = $destination['state'];
				$destination_postcode = $destination['postcode'];

				$destination_country_arr = $this->cario_get_country_id($destination_country);

				$destination_country_id = $destination_country_arr['id'];

				$destination_location_id = $this->cario_get_location_id( $destination_country_id , $destination_postcode, $destination_city);


				

				$pickup_date =  date('Y-m-d', strtotime('+2 day'));

				//pj_var_dump($cart_prod_id_m3);

				//pj_var_dump($package['contents']);
				 


				$data = array (
					'customerId' => intval($customerId),
					'pickupDate' => $pickup_date,
					'pickupAddress' => 
					array (
					  'name' => $store_company,
					  'line1' => $store_address,
					  'location' => 
					  array (
						'id' => $store_location_id,
						'locality' => $store_city,
						'state' => $store_state,
						'postcode' => $store_postcode,
						'country' => $store_country_arr,
					  ),
					),
					'deliveryAddress' => 
					array (
					  'name' => 'Quote',
					  'line1' => $destination_address,
					  'location' => 
					  array (
						'id' => $destination_location_id,
						'locality' => $destination_city,
						'state' => $destination_state,
						'postcode' => $destination_postcode,
						'country' => $destination_country_arr,
					  ),
					),
					'totalItems' => $qty,
					'totalWeight' => $weight,
					'totalVolume' => $volume,
					'transportUnits' => 
					array (
					  0 => 
					  array (
						'transportUnitType' => 'Carton',
						'quantity' => 1,
						//'length' => $largest_product_length,
						//'width' => $largest_product_width,
						//'height' => $largest_product_height,
						'volume' => $volume,
						'weight' => $weight,
					  ),
					),
				);

				//pj_var_dump($data);

				//pj_var_dump( json_encode($data));



				$url = 'https://integrate.cario.com.au/api/Consignment/GetQuotes';

	
				$auth_accesstoken = 'Bearer ' .  $this->accessToken; 
				$auth_tenantid = $this->tenantId;
				$auth_customerid = $this->customerId;

				$response = wp_remote_post( $url, array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => [
						'Content-Type' => 'application/json',
						'Authorization' => $auth_accesstoken,
						'TenantId' => $auth_tenantid
					],
					'body'        => json_encode($data),
					'cookies'     => array()
					)
				);

				//pj_var_dump($response);
				
				if ( is_wp_error( $response ) ) {
					$error_message = $response->get_error_message();
					var_dump($error_message);
				} else {
					$results = json_decode($response['body'] , true );

					$handling_charges = get_option( 'cario_price_rules' );

					$handling_cost = '20';

					foreach( $handling_charges as $handling_charge){
						if( $weight >= $handling_charge['weight_start'] && $weight <= $handling_charge['weight_end'] ){
							$handling_cost = $handling_charge['cost'];
						}
					}

					if( $results){
						foreach($results as $result){

							$total_calculated = ( ( $handling_cost + $result['total'] ) + ( ( ( $handling_cost + $result['total'] ) / 100 ) * $this->uplift ) );



							$rate = array(
								'id' => $result['carrierCode'],
								'label' => $result['carrierName'],
								'cost' => $total_calculated,
							);
			
							$this->add_rate( $rate );

						}
					}
				}
			}


			public function cario_get_country_id( $country_code ) {

				$url = 'https://integrate.cario.com.au/api/Location/GetCountryByCode/'. $country_code;


				$auth_accesstoken = 'Bearer ' . $this->accessToken;
				$auth_tenantid = $this->tenantId;
				$auth_customerid = $this->customerId;

				$response = wp_remote_get( $url, array(
					'method'      => 'GET',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => [
						'Content-Type' => 'application/json',
						'Authorization' => $auth_accesstoken,
						'CustomerId' => $auth_customerid,
						'TenantId' => $auth_tenantid,
					],
					'cookies'     => array()
					)
				);

				
				
				if ( is_wp_error( $response ) ) {
					$error_message = $response->get_error_message();
					//var_dump($error_message);
				} else {
					$results = json_decode($response['body'] , true );
				}

				return $results;

			}

			public function cario_get_location_id( $country_id , $post_code , $city ) {

				$url = 'https://integrate.cario.com.au/api/Location/FindByCountryId/'. $country_id . '/' . $post_code;

				$location_id = '';

				$auth_accesstoken = 'Bearer ' . $this->accessToken;
				$auth_tenantid = $this->tenantId;
				$auth_customerid = $this->customerId;

				$response = wp_remote_get( $url, array(
					'method'      => 'GET',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => [
						'Content-Type' => 'application/json',
						'Authorization' => $auth_accesstoken,
						'CustomerId' => $auth_customerid,
						'TenantId' => $auth_tenantid
					],
					'cookies'     => array()
					)
				);

				
				
				if ( is_wp_error( $response ) ) {
					$error_message = $response->get_error_message();
					//var_dump($error_message);
				} else {
					$results = json_decode($response['body'] , true );
					$city_up = strtoupper($city);
					$location_id = $results['0']['id'];
					foreach($results as $result){
						if (strpos($result['description'], $city_up) !== false) {
							$location_id = $result['id'];
						}
					}
				}

				return $location_id;

			}


		}
	}
}

add_action( 'woocommerce_shipping_init', 'cario_shipping_method' );

function add_cario_shipping_method( $methods ) {
	$methods[] = 'Cario_Shipping_Method';
	return $methods;
}

add_filter( 'woocommerce_shipping_methods', 'add_cario_shipping_method' );

function cario_validate_order( $posted )   {

	$packages = WC()->shipping->get_packages();

	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
		
	if( is_array( $chosen_methods ) && in_array( 'cario', $chosen_methods ) ) {
			
		foreach ( $packages as $i => $package ) {

			if ( $chosen_methods[ $i ] != "cario" ) {
							
				continue;
							
			}

			$Cario_Shipping_Method = new Cario_Shipping_Method();
			$weightLimit = (int) $Cario_Shipping_Method->settings['weight'];
			$weight = 0;

			foreach ( $package['contents'] as $item_id => $values ) 
			{ 
				$_product = $values['data']; 
				$weight = $weight + $_product->get_weight() * $values['quantity']; 
			}

			$weight = wc_get_weight( $weight, 'kg' );
			
			if( $weight > $weightLimit ) {

					$message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', 'cario' ), $weight, $weightLimit, $Cario_Shipping_Method->title );
							
					$messageType = "error";

					if( ! wc_has_notice( $message, $messageType ) ) {
						
						wc_add_notice( $message, $messageType );
					
					}
			}
		}       
	} 
}

add_action( 'woocommerce_review_order_before_cart_contents', 'cario_validate_order' , 10 );
add_action( 'woocommerce_after_checkout_validation', 'cario_validate_order' , 10 );
}
   
   
   


add_action( 'admin_menu', 'cario_add_admin_menu' );
add_action( 'admin_init', 'cario_settings_init' );
function cario_add_admin_menu(  ) { 
	add_submenu_page( 'woocommerce', 'Cario Shipping Rules', 'Cario Shipping Rules', 'manage_options', 'cario_shipping_rules', 'cario_shipping_rules_callback' );
}

function cario_settings_init(  ) { 
	register_setting( 'carioShippingPage', 'cario_price_rules' );
	add_settings_section(
		'cario_shipping_section', 
		__( 'Cario Shipping Rules', 'cario' ), 
		'cario_shipping_callback', 
		'carioShippingPage'
	);
	add_settings_field( 
		'cario_handling_charge', 
		__( 'Handling Charge', 'cario' ), 
		'cario_handling_charge_render', 
		'carioShippingPage', 
		'cario_shipping_section' 
	);
}


function cario_handling_charge_render(  ) { 
	$options = get_option( 'cario_price_rules' );
	$symbol = get_woocommerce_currency_symbol();
	$weight_unit = 'Weight (' . get_option('woocommerce_weight_unit') . ')';
	
	?>
	<div class="cario-list">
		<div data-repeater-list="cario_price_rules">
			<?php if ( $options == '' || empty($options)){ ?>
			<div data-repeater-item>
				<?php echo $weight_unit; ?> &nbsp; <input type='number' min='0' step='0.01' name='weight_start' value='' placeholder='From' required>
				
				<input type='number' min='0' step='0.01' name='weight_end' value='' placeholder='Up To' required>  &nbsp;  &nbsp;  &nbsp; 

				<?php echo $symbol; ?> &nbsp; <input type='number' min='0' step='0.01' name='cost' value='' placeholder='Cost' required> 
				
				<input data-repeater-delete type="button" value="Delete" class="button button-secondary"/>
			</div>
			<?php } else { 
				foreach( $options as $group){
				?>
			<div data-repeater-item>
				<?php echo $weight_unit; ?> &nbsp; <input type='number' min='0' step='0.01' name='weight_start' value='<?php echo $group['weight_start']; ?>' placeholder='From' required>
				
				<input type='number' min='0' step='0.01' name='weight_end' value='<?php echo $group['weight_end']; ?>' placeholder='Up To' required>   &nbsp;  &nbsp;  &nbsp; 
				
				<?php echo $symbol; ?> &nbsp; <input type='number' min='0' step='0.01' name='cost' value='<?php echo $group['cost']; ?>' placeholder='Cost' required>
				
				<input data-repeater-delete type="button" value="Delete" class="button button-secondary"/>

			</div>	
			<?php 
				}
			} ?>
		</div>
		<input data-repeater-create type="button" value="Add" class="button button-secondary"/>
	</div>
	<?php
}


function cario_shipping_callback(  ) { 
	wp_enqueue_script( 'jquery_repeater_script', plugin_dir_url( __FILE__ ) . 'js/jquery.repeater.min.js' );
    wp_enqueue_script( 'jquery-ui-sortable' );
	?>
	<script>
		jQuery(document).ready(function(){
			
			$dragAndDrop = jQuery('.cario-list > div').sortable({placeholder: "ui-state-highlight",helper:'clone'});

			jQuery('.cario-list').repeater({
                ready: function (setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
				isFirstItemUndeletable: false
			});
		})
	</script>
	<style>
		.cario-list div[data-repeater-item] {
			margin-bottom: 10px;
            
		}
        .cario-list div[data-repeater-item]:before{
            content: "⇕";
            font-family: "Segoe UI Symbol";
            margin-right: 10px;
            font-size: 20px;
            cursor: move; /* fallback if grab cursor is unsupported */
            cursor: grab;
            cursor: -moz-grab;
            cursor: -webkit-grab;
        }
        .cario-list .ui-sortable-placeholder {
            height: 31px;
            width: 100%;
            border: 1px dashed #909090;
            margin-bottom: 10px;
            border-radius: 3px;
        }
	</style>
	<?php
}


function cario_shipping_rules_callback(  ) { 
	?>
	<form action='options.php' method='post'>
		<h1>Cario Shipping</h1>
		
		<div class="wrap">
		
		<?php
			settings_fields( 'carioShippingPage' );
			do_settings_sections( 'carioShippingPage' );
			submit_button();
		?>

		</div>
	</form>
	<?php
}

function pj_var_dump($str){
	if ( current_user_can( 'manage_options' ) ) {
		echo '<pre>';
		var_dump($str);
		echo '</pre>';
	}
}
