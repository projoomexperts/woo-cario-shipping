<?php

/**
 * Plugin Name: Woocommerce Cario Shipping
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



// Scheduled Action Hook
function woo_cario_authentication_generate( ) {

	$url = 'https://integrate.cario.com.au/api/Authentication/Login';

	$woocommerce_cario_shipping_options = get_option('woocommerce_cario_settings');
	$usernameoremailaddress = $woocommerce_cario_shipping_options['userNameOrEmailAddress'];
	$password = $woocommerce_cario_shipping_options['password'];
	$tenantname = $woocommerce_cario_shipping_options['tenantName'];
	$rememberclient = true;

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


// Add Shortcode
function fn_woo_cario_test() {

	$data = get_option('woocommerce_cario_settings');
	echo '<pre>';
	var_dump($data);
	echo '</pre>';

	// echo print_r( get_option('woo_cario_auth_data')['accessToken']);

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
				$this->userNameOrEmailAddress = isset( $this->settings['userNameOrEmailAddress'] ) ? $this->settings['userNameOrEmailAddress'] : __( 'APIUser', 'cario' );
				$this->password = isset( $this->settings['password'] ) ? $this->settings['password'] : __( 'CarioAPItest', 'cario' );
				$this->tenantName = isset( $this->settings['tenantName'] ) ? $this->settings['tenantName'] : __( 'TEST', 'cario' );
				$this->uplift = isset( $this->settings['uplift'] ) ? $this->settings['uplift'] : __( '0', 'cario' );



				$this->uplift = isset( $this->settings['uplift'] ) ? $this->settings['uplift'] : __( '5', 'cario' );
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
			}

			/**
			 * Define settings field for this shipping
			 * @return void 
			 */
			function init_form_fields() { 

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

					'handlingCharge' => array(
						'title' => __( '<a target="_blank" href="'. site_url('/wp-admin/admin.php?page=cario_shipping_rules') .'">Handling Charge</a>', 'cario' ),
						'type' => 'title',
						'description' => __( '', 'cario' ),
						'default' => ''
					),

					'uplift' => array(
						'title' => __( 'Uplift Amount', 'cario' ),
						'type' => 'number',
						'description' => __( 'percentage ( % )', 'cario' ),
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

				foreach ( $package['contents'] as $item_id => $values ) 
				{ 
					$_product = $values['data']; 
					if( $_product->get_weight() ){
						$weight = $weight + $_product->get_weight() * $values['quantity'];
					}
					$qty += $values['quantity'];
					if( $_product->get_length() && $_product->get_width() && $_product->get_height()){
						$prod_m3 = $_product->get_length() * $_product->get_width() * $_product->get_height();
						$prod_m3 = ($prod_m3 * $values['quantity']) / 1000000;
						array_push($cart_prods_m3, $prod_m3);
					}
				}

				$volume = array_sum($cart_prods_m3);

				$weight = wc_get_weight( $weight, 'kg' );


				$iso3=array('BD'=>'BGD','BE'=>'BEL','BF'=>'BFA','BG'=>'BGR','BA'=>'BIH','BB'=>'BRB','WF'=>'WLF','BL'=>'BLM','BM'=>'BMU','BN'=>'BRN','BO'=>'BOL','BH'=>'BHR','BI'=>'BDI','BJ'=>'BEN','BT'=>'BTN','JM'=>'JAM','BV'=>'BVT','BW'=>'BWA','WS'=>'WSM','BQ'=>'BES','BR'=>'BRA','BS'=>'BHS','JE'=>'JEY','BY'=>'BLR','BZ'=>'BLZ','RU'=>'RUS','RW'=>'RWA','RS'=>'SRB','TL'=>'TLS','RE'=>'REU','TM'=>'TKM','TJ'=>'TJK','RO'=>'ROU','TK'=>'TKL','GW'=>'GNB','GU'=>'GUM','GT'=>'GTM','GS'=>'SGS','GR'=>'GRC','GQ'=>'GNQ','GP'=>'GLP','JP'=>'JPN','GY'=>'GUY','GG'=>'GGY','GF'=>'GUF','GE'=>'GEO','GD'=>'GRD','GB'=>'GBR','GA'=>'GAB','SV'=>'SLV','GN'=>'GIN','GM'=>'GMB','GL'=>'GRL','GI'=>'GIB','GH'=>'GHA','OM'=>'OMN','TN'=>'TUN','JO'=>'JOR','HR'=>'HRV','HT'=>'HTI','HU'=>'HUN','HK'=>'HKG','HN'=>'HND','HM'=>'HMD','VE'=>'VEN','PR'=>'PRI','PS'=>'PSE','PW'=>'PLW','PT'=>'PRT','SJ'=>'SJM','PY'=>'PRY','IQ'=>'IRQ','PA'=>'PAN','PF'=>'PYF','PG'=>'PNG','PE'=>'PER','PK'=>'PAK','PH'=>'PHL','PN'=>'PCN','PL'=>'POL','PM'=>'SPM','ZM'=>'ZMB','EH'=>'ESH','EE'=>'EST','EG'=>'EGY','ZA'=>'ZAF','EC'=>'ECU','IT'=>'ITA','VN'=>'VNM','SB'=>'SLB','ET'=>'ETH','SO'=>'SOM','ZW'=>'ZWE','SA'=>'SAU','ES'=>'ESP','ER'=>'ERI','ME'=>'MNE','MD'=>'MDA','MG'=>'MDG','MF'=>'MAF','MA'=>'MAR','MC'=>'MCO','UZ'=>'UZB','MM'=>'MMR','ML'=>'MLI','MO'=>'MAC','MN'=>'MNG','MH'=>'MHL','MK'=>'MKD','MU'=>'MUS','MT'=>'MLT','MW'=>'MWI','MV'=>'MDV','MQ'=>'MTQ','MP'=>'MNP','MS'=>'MSR','MR'=>'MRT','IM'=>'IMN','UG'=>'UGA','TZ'=>'TZA','MY'=>'MYS','MX'=>'MEX','IL'=>'ISR','FR'=>'FRA','IO'=>'IOT','SH'=>'SHN','FI'=>'FIN','FJ'=>'FJI','FK'=>'FLK','FM'=>'FSM','FO'=>'FRO','NI'=>'NIC','NL'=>'NLD','NO'=>'NOR','NA'=>'NAM','VU'=>'VUT','NC'=>'NCL','NE'=>'NER','NF'=>'NFK','NG'=>'NGA','NZ'=>'NZL','NP'=>'NPL','NR'=>'NRU','NU'=>'NIU','CK'=>'COK','XK'=>'XKX','CI'=>'CIV','CH'=>'CHE','CO'=>'COL','CN'=>'CHN','CM'=>'CMR','CL'=>'CHL','CC'=>'CCK','CA'=>'CAN','CG'=>'COG','CF'=>'CAF','CD'=>'COD','CZ'=>'CZE','CY'=>'CYP','CX'=>'CXR','CR'=>'CRI','CW'=>'CUW','CV'=>'CPV','CU'=>'CUB','SZ'=>'SWZ','SY'=>'SYR','SX'=>'SXM','KG'=>'KGZ','KE'=>'KEN','SS'=>'SSD','SR'=>'SUR','KI'=>'KIR','KH'=>'KHM','KN'=>'KNA','KM'=>'COM','ST'=>'STP','SK'=>'SVK','KR'=>'KOR','SI'=>'SVN','KP'=>'PRK','KW'=>'KWT','SN'=>'SEN','SM'=>'SMR','SL'=>'SLE','SC'=>'SYC','KZ'=>'KAZ','KY'=>'CYM','SG'=>'SGP','SE'=>'SWE','SD'=>'SDN','DO'=>'DOM','DM'=>'DMA','DJ'=>'DJI','DK'=>'DNK','VG'=>'VGB','DE'=>'DEU','YE'=>'YEM','DZ'=>'DZA','US'=>'USA','UY'=>'URY','YT'=>'MYT','UM'=>'UMI','LB'=>'LBN','LC'=>'LCA','LA'=>'LAO','TV'=>'TUV','TW'=>'TWN','TT'=>'TTO','TR'=>'TUR','LK'=>'LKA','LI'=>'LIE','LV'=>'LVA','TO'=>'TON','LT'=>'LTU','LU'=>'LUX','LR'=>'LBR','LS'=>'LSO','TH'=>'THA','TF'=>'ATF','TG'=>'TGO','TD'=>'TCD','TC'=>'TCA','LY'=>'LBY','VA'=>'VAT','VC'=>'VCT','AE'=>'ARE','AD'=>'AND','AG'=>'ATG','AF'=>'AFG','AI'=>'AIA','VI'=>'VIR','IS'=>'ISL','IR'=>'IRN','AM'=>'ARM','AL'=>'ALB','AO'=>'AGO','AQ'=>'ATA','AS'=>'ASM','AR'=>'ARG','AU'=>'AUS','AT'=>'AUT','AW'=>'ABW','IN'=>'IND','AX'=>'ALA','AZ'=>'AZE','IE'=>'IRL','ID'=>'IDN','UA'=>'UKR','QA'=>'QAT','MZ'=>'MOZ',);
				$isofull=array('BD'=>'Bangladesh','BE'=>'Belgium','BF'=>'Burkina Faso','BG'=>'Bulgaria','BA'=>'Bosnia and Herzegovina','BB'=>'Barbados','WF'=>'Wallis and Futuna','BL'=>'Saint Barthelemy','BM'=>'Bermuda','BN'=>'Brunei','BO'=>'Bolivia','BH'=>'Bahrain','BI'=>'Burundi','BJ'=>'Benin','BT'=>'Bhutan','JM'=>'Jamaica','BV'=>'Bouvet Island','BW'=>'Botswana','WS'=>'Samoa','BQ'=>'Bonaire, Saint Eustatius and Saba ','BR'=>'Brazil','BS'=>'Bahamas','JE'=>'Jersey','BY'=>'Belarus','BZ'=>'Belize','RU'=>'Russia','RW'=>'Rwanda','RS'=>'Serbia','TL'=>'East Timor','RE'=>'Reunion','TM'=>'Turkmenistan','TJ'=>'Tajikistan','RO'=>'Romania','TK'=>'Tokelau','GW'=>'Guinea-Bissau','GU'=>'Guam','GT'=>'Guatemala','GS'=>'South Georgia and the South Sandwich Islands','GR'=>'Greece','GQ'=>'Equatorial Guinea','GP'=>'Guadeloupe','JP'=>'Japan','GY'=>'Guyana','GG'=>'Guernsey','GF'=>'French Guiana','GE'=>'Georgia','GD'=>'Grenada','GB'=>'United Kingdom','GA'=>'Gabon','SV'=>'El Salvador','GN'=>'Guinea','GM'=>'Gambia','GL'=>'Greenland','GI'=>'Gibraltar','GH'=>'Ghana','OM'=>'Oman','TN'=>'Tunisia','JO'=>'Jordan','HR'=>'Croatia','HT'=>'Haiti','HU'=>'Hungary','HK'=>'Hong Kong','HN'=>'Honduras','HM'=>'Heard Island and McDonald Islands','VE'=>'Venezuela','PR'=>'Puerto Rico','PS'=>'Palestinian Territory','PW'=>'Palau','PT'=>'Portugal','SJ'=>'Svalbard and Jan Mayen','PY'=>'Paraguay','IQ'=>'Iraq','PA'=>'Panama','PF'=>'French Polynesia','PG'=>'Papua New Guinea','PE'=>'Peru','PK'=>'Pakistan','PH'=>'Philippines','PN'=>'Pitcairn','PL'=>'Poland','PM'=>'Saint Pierre and Miquelon','ZM'=>'Zambia','EH'=>'Western Sahara','EE'=>'Estonia','EG'=>'Egypt','ZA'=>'South Africa','EC'=>'Ecuador','IT'=>'Italy','VN'=>'Vietnam','SB'=>'Solomon Islands','ET'=>'Ethiopia','SO'=>'Somalia','ZW'=>'Zimbabwe','SA'=>'Saudi Arabia','ES'=>'Spain','ER'=>'Eritrea','ME'=>'Montenegro','MD'=>'Moldova','MG'=>'Madagascar','MF'=>'Saint Martin','MA'=>'Morocco','MC'=>'Monaco','UZ'=>'Uzbekistan','MM'=>'Myanmar','ML'=>'Mali','MO'=>'Macao','MN'=>'Mongolia','MH'=>'Marshall Islands','MK'=>'Macedonia','MU'=>'Mauritius','MT'=>'Malta','MW'=>'Malawi','MV'=>'Maldives','MQ'=>'Martinique','MP'=>'Northern Mariana Islands','MS'=>'Montserrat','MR'=>'Mauritania','IM'=>'Isle of Man','UG'=>'Uganda','TZ'=>'Tanzania','MY'=>'Malaysia','MX'=>'Mexico','IL'=>'Israel','FR'=>'France','IO'=>'British Indian Ocean Territory','SH'=>'Saint Helena','FI'=>'Finland','FJ'=>'Fiji','FK'=>'Falkland Islands','FM'=>'Micronesia','FO'=>'Faroe Islands','NI'=>'Nicaragua','NL'=>'Netherlands','NO'=>'Norway','NA'=>'Namibia','VU'=>'Vanuatu','NC'=>'New Caledonia','NE'=>'Niger','NF'=>'Norfolk Island','NG'=>'Nigeria','NZ'=>'New Zealand','NP'=>'Nepal','NR'=>'Nauru','NU'=>'Niue','CK'=>'Cook Islands','XK'=>'Kosovo','CI'=>'Ivory Coast','CH'=>'Switzerland','CO'=>'Colombia','CN'=>'China','CM'=>'Cameroon','CL'=>'Chile','CC'=>'Cocos Islands','CA'=>'Canada','CG'=>'Republic of the Congo','CF'=>'Central African Republic','CD'=>'Democratic Republic of the Congo','CZ'=>'Czech Republic','CY'=>'Cyprus','CX'=>'Christmas Island','CR'=>'Costa Rica','CW'=>'Curacao','CV'=>'Cape Verde','CU'=>'Cuba','SZ'=>'Swaziland','SY'=>'Syria','SX'=>'Sint Maarten','KG'=>'Kyrgyzstan','KE'=>'Kenya','SS'=>'South Sudan','SR'=>'Suriname','KI'=>'Kiribati','KH'=>'Cambodia','KN'=>'Saint Kitts and Nevis','KM'=>'Comoros','ST'=>'Sao Tome and Principe','SK'=>'Slovakia','KR'=>'South Korea','SI'=>'Slovenia','KP'=>'North Korea','KW'=>'Kuwait','SN'=>'Senegal','SM'=>'San Marino','SL'=>'Sierra Leone','SC'=>'Seychelles','KZ'=>'Kazakhstan','KY'=>'Cayman Islands','SG'=>'Singapore','SE'=>'Sweden','SD'=>'Sudan','DO'=>'Dominican Republic','DM'=>'Dominica','DJ'=>'Djibouti','DK'=>'Denmark','VG'=>'British Virgin Islands','DE'=>'Germany','YE'=>'Yemen','DZ'=>'Algeria','US'=>'United States','UY'=>'Uruguay','YT'=>'Mayotte','UM'=>'United States Minor Outlying Islands','LB'=>'Lebanon','LC'=>'Saint Lucia','LA'=>'Laos','TV'=>'Tuvalu','TW'=>'Taiwan','TT'=>'Trinidad and Tobago','TR'=>'Turkey','LK'=>'Sri Lanka','LI'=>'Liechtenstein','LV'=>'Latvia','TO'=>'Tonga','LT'=>'Lithuania','LU'=>'Luxembourg','LR'=>'Liberia','LS'=>'Lesotho','TH'=>'Thailand','TF'=>'French Southern Territories','TG'=>'Togo','TD'=>'Chad','TC'=>'Turks and Caicos Islands','LY'=>'Libya','VA'=>'Vatican','VC'=>'Saint Vincent and the Grenadines','AE'=>'United Arab Emirates','AD'=>'Andorra','AG'=>'Antigua and Barbuda','AF'=>'Afghanistan','AI'=>'Anguilla','VI'=>'U.S. Virgin Islands','IS'=>'Iceland','IR'=>'Iran','AM'=>'Armenia','AL'=>'Albania','AO'=>'Angola','AQ'=>'Antarctica','AS'=>'American Samoa','AR'=>'Argentina','AU'=>'Australia','AT'=>'Austria','AW'=>'Aruba','IN'=>'India','AX'=>'Aland Islands','AZ'=>'Azerbaijan','IE'=>'Ireland','ID'=>'Indonesia','UA'=>'Ukraine','QA'=>'Qatar','MZ'=>'Mozambique',); 


				$store_address = WC()->countries->get_base_address();
				$store_address .= WC()->countries->get_base_address_2();
				$store_city = WC()->countries->get_base_city();
				$store_postcode = WC()->countries->get_base_postcode();
				$store_state = WC()->countries->get_base_state();
				$store_country = WC()->countries->get_base_country();
				$store_country_3 = $iso3[$store_country];
				$store_country_full = strtoupper($isofull[$store_country]);
				
				$destination = $package["destination"];
				$destination_country = $destination['country'];
				$destination_city = $destination['city'];
				$destination_state = $destination['state'];
				$destination_postcode = $destination['postcode'];
				$destination_country_3 = $iso3[$destination_country];
				$destination_country_full = strtoupper($isofull[$destination_country]);

				$pickup_date =  date('Y-m-d', strtotime('+1 day'));
				 


				$data = array (
					'customerId' => 4208,
					'pickupDate' => $pickup_date,
					'pickupAddress' => 
					array (
					  'name' => 'Quote',
					  'line1' => 'Quote',
					  'location' => 
					  array (
						'id' => 8162,
						'locality' => $store_city,
						'state' => $store_state,
						'postcode' => $store_postcode,
						'country' => 
						array (
						  'id' => 36,
						  'isO2' => $store_country,
						  'isO3' => $store_country_3,
						  'name' => $store_country_full,
						),
					  ),
					),
					'deliveryAddress' => 
					array (
					  'name' => 'Quote',
					  'line1' => 'Quote',
					  'location' => 
					  array (
						'id' => 2495,
						'locality' => $destination_city,
						'state' => $destination_state,
						'postcode' => $destination_postcode,
						'country' => 
						array (
						  'id' => 36,
						  'isO2' => $destination_country,
						  'isO3' => $destination_country_3,
						  'name' => $destination_country_full,
						),
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
						'length' => 20,
						'width' => 20,
						'height' => 20,
						'volume' => $volume,
						'weight' => $weight,
					  ),
					),
				);

				// pj_var_dump($package);



				$url = 'https://integrate.cario.com.au/api/Consignment/GetQuotes';

				$auth_data = get_option('woo_cario_auth_data');
				$auth_accesstoken = 'Bearer ' . $auth_data['accessToken'];
				$auth_tenantid = $auth_data['tenantId'];
				$auth_customerid = $auth_data['customerId'];
				$auth_userid = $auth_data['userId'];

				$response = wp_remote_post( $url, array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => [
						'Content-Type' => 'application/json',
						'Authorization' => $auth_accesstoken,
						'CustomerId' => $auth_customerid,
						'TenantId' => $auth_tenantid,
						'userId' => $auth_userid,
					],
					'body'        => json_encode($data),
					'cookies'     => array()
					)
				);

				// pj_var_dump($response);
				
				if ( is_wp_error( $response ) ) {
					$error_message = $response->get_error_message();
					var_dump($error_message);
				} else {
					$results = json_decode($response['body'] , true );
					if( $results){
						foreach($results as $result){

							$rate = array(
								'id' => $result['carrierCode'],
								'label' => $result['carrierName'],
								'cost' => $result['total'],
							);
			
							$this->add_rate( $rate );

						}
					}
				}
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

		<hr/>

		<?php $auth_data = get_option('woo_cario_auth_data');
			echo '<table>';
			foreach ( $auth_data as $key => $value ){
				echo '<tr>';
				echo '<td>'.$key.'</td>';
				echo '<td><input type="text" readonly value="'.$value.'"/></td>';
				echo '</tr>';
			}
			echo '</table>';

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