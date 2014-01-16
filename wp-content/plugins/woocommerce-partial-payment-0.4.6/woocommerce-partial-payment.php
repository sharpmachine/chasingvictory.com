<?php
/**
* Plugin Name: Woo Deposits
* Plugin URI: http://webatix.com/supporturl
* Description: Add a partial payment function to WooCommerce Shop
* Version: 0.4.6
* Author: Webatix
* Author URI: http://webatix.com
* Text Domain: woocommerce-partial-payment
* Domain Path: /lang/
* License: GPL2
*/

class WoocommercePartialPayment {
	
	/**
	 * Used to save cart before processing deposits to restore it afterwards
	 * 
	 * @var object
	 */
	private $cart_holder;

	
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 * 
	 * @return void
	 */
	public function __construct() {

		// Register hooks that are fired when the plugin is activated and deactivated respectively.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		
		// Load plugin text domain
		add_action( 'plugins_loaded', array( $this, 'plugin_textdomain' ) );
		
		//show user instructions on plugin activation
		if ( true == get_option( 'wpp_plugin_activated' ) ) {
			
			delete_option( 'wpp_plugin_activated' );
			
			add_action( 'admin_footer', array( $this, 'wpp_display_user_instructions' ) );
			
		}
		
		//register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
		
		//settings page
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_menu_item' ) );
		
		add_action( 'woocommerce_settings_tabs_partial_payment', array( $this, 'settings_page' ) );
		
		add_action( 'woocommerce_update_options_partial_payment', array( $this, 'save_settings' ) );
		
		//handling auto updates
		add_action( 'init', array( 'WoocommercePartialPayment', 'wpp_activate_auto_updates' ) );
		
		add_action( 'admin_notices', array( $this, 'wpp_show_user_message' ) );
		
		//add custom links to plugin meta data 
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		
		//load classes
		add_action( 'init', array( $this, 'load_classes' ) );
		
		//if plugin license is not active, only plugin settings page is available
		if ( $this->is_license_active() ) {
	
			add_action( 'init', array( $this, 'plugin_logic' ) );

		}
		
		//calculate deposit values for the cart
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'wpp_recalculate_deposits_on_totals_update' ), 20 );
		
	}
	
	
	/**
	 * Checks plugin license status
	 * 
	 * @return bool
	 */
	private function is_license_active() {
	
		if ( md5('active') === get_option( md5('wpp_is_license_active') ) ) {
	
			return true;
	
		} else {
	
			return false;
	
		}
	
	}
	
	
	/**
	 * Performs license check
	 * 
	 * @return bool | license validity
	 */
	private function check_license( $license_email_address = '', $license_key = '', $old_license_key = '' ) {
	
		//first, make sure we have something to check
		if ( empty( $license_email_address ) )
			$license_email_address = get_option('wpp_license_email_address');
	
		if ( empty( $license_key ) )
			$license_key = get_option('wpp_license_key');
	
		if ( ! empty( $license_email_address ) && ! empty( $license_key ) ) {
	
			$check = wp_remote_get( 'http://webatix.com/woocommerce/?wc-api=software-api&request=activation&email=' . $license_email_address . '&licence_key=' . $license_key . '&product_id=woocommerce-partial-payment&secret=OWwdpkUQsptHV6bzhN5b&instance=' . urlencode( get_bloginfo('url') ) );
	
			if ( ! is_wp_error( $check ) && ( 200 == $check['response']['code'] ) ) {
	
				$response_body = json_decode($check['body']);
	
				if (  isset($response_body->activated) && ( true === $response_body->activated )  ) {
	
					update_option(md5('wpp_is_license_active'), md5('active'));
	
					update_option('wpp_show_user_message', array('value' => 'license_activated', 'message' => __('Plugin license was activated successfully!', 'woocommerce-partial-payment')));
	
					return true;
	
				} else {
	
					//show user "Invalid license" message with details, received from server
					update_option('wpp_show_user_message', array('value' => 'invalid_license', 'message' => $response_body->error));
	
				}
					
			} else {
	
				error_log('License check returned error: ' . maybe_serialize( $check ));
	
			}
	
		}
	
		//check if license was active before and deactivate it if so
		if ( true === $this->is_license_active() && ! empty( $old_license_key ) ) {
	
			$deactivate = wp_remote_get( 'http://webatix.com/woocommerce/?wc-api=software-api&request=deactivation&email=' . $license_email_address . '&licence_key=' . $old_license_key . '&product_id=woocommerce-partial-payment&instance=' . urlencode( get_bloginfo('url') ) );
	
			if ( ! is_wp_error( $deactivate ) && ( 200 == $deactivate['response']['code'] ) ) {
	
				$response_body = json_decode($deactivate['body']);
	
				if (  isset($response_body->reset) && ( true === $response_body->reset )  ) {
	
					update_option('wpp_show_user_message', array('value' => 'license_deactivated', 'message' => __('Plugin license was dectivated!', 'woocommerce-partial-payment')));
	
				}
	
			}
	
		}
	
		update_option(md5('wpp_is_license_active'), md5('inactive'));
			
		return false;
	
	}


	/**
	 * Fired when the plugin is activated.
	 * 
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * 
	 * @return void
	 */
	public function activate( $network_wide ) {

		add_option('wpp_plugin_activated', true);
		
		//create table in DB for keeping deposit and payment records 
		global $wpdb;
		
		$wpdb->query( '
			CREATE TABLE IF NOT EXISTS '. $wpdb->prefix . 'wpp_deposit_records (
				ID BIGINT(20) NOT NULL AUTO_INCREMENT,
				order_id BIGINT(20),
				sum DECIMAL(15,2),
				payment_method VARCHAR(255),
				payment_status VARCHAR(255),
				payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (ID)
			)
		' );

	}
	
	
	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * 
	 * @return void
	 */
	public function deactivate( $network_wide ) {
	
		//TODO:	Define deactivation functionality here
	
	}
	
	
	/**
	 * Loads the plugin text domain for translation
	 * 
	 * @return void
	 */
	public function plugin_textdomain() {
	
		load_plugin_textdomain( 'woocommerce-partial-payment', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	
	}

	
	/**
	 * Displays user instructions after plugin activation
	 * 
	 * @return void
	 */
	public function wpp_display_user_instructions() {
		
		wp_enqueue_script('jquery-ui-dialog');
			
		wp_enqueue_style('jquery-ui-external', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');
		
		?><div id="wpp-user-instructions" style="display: none"><?php _e('Thank you for installing the WooCommerce Partial Payment and Deposit Plugin by Webatix. There is now a "Partial Payment" tab under WooCommerce --> Settings that you can use to set site-wide defaults. You can also change settings for each product in the Product Data settings of each product.', 'woocommerce-partial-payment'); ?></div><script type="text/javascript">jQuery(function($) { $('#wpp-user-instructions').dialog({title: '<?php _e('Plugin activated!', 'woocommerce-partial-payment'); ?>', width: 830}); });</script><?php 
		
	}

	
	/**
	 * Registers and enqueues admin-specific styles
	 * 
	 * @return void
	 */
	public function register_admin_styles() {

		wp_enqueue_style( 'woocommerce-partial-payment-admin-styles', plugins_url( 'css/admin.css', __FILE__ ) );

	}


	/**
	 * Registers and enqueues admin-specific JavaScript
	 * 
	 * @return void
	 */	
	public function register_admin_scripts() {

		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'woocommerce-partial-payment-admin-script', plugins_url( 'js/admin.js', __FILE__ ) );

	}

	
	/**
	 * Returns plugin text labels that can be edited on "Settings" page
	 *
	 * @param bool $defaults true if default values are requested
	 *
	 * @return array $labels
	 */
	public function get_text_labels( $defaults = false ) {
	
		$labels = get_option( '_wpp_text_labels', '' );
		
		$empty = true;
		
		if ( ! empty( $labels ) ) {
			
			//if at least one of the labels is not empty, we do not return default values
			foreach ( $labels as &$label ) {
				
				if ( ! empty( $label ) ) {
					
					$empty = false;
					
					$label = stripslashes( htmlspecialchars_decode( $label, ENT_QUOTES ) );
				
				}
				
			}	
			
		}
		
		if ( ! isset( $labels['pay_deposit_price_text'] ) ) {
			
			$labels['pay_deposit_now_text'] = __('Pay Deposit Now', 'woocommerce-partial-payment');
			
		}
		
		if ( ! isset( $labels['view_order_pending_payments'] ) ) {
			
			$labels['view_order_pending_payments'] = __('Your payment details contain pending payments. Goods and services might not be delivered until these payments are confirmed. If you still have a balance you can make a payment below.', 'woocommerce-partial-payment');
			
		}
		
		if ( ( true === $empty ) || ( true === $defaults ) ) {
	
			//if there are no labels saved yet or defaults are requested
			$labels = array(
					'single_deposit_required_text' => __('Deposit Only: [deposit_calculation_rule_text] is due today. The rest will be billed later. [min_and_max_deposit_option_text]', 'woocommerce-partial-payment'),
					'single_deposit_optional_text' => __('Deposit Option Available: Pay [deposit_calculation_rule_text] today and the rest later. [min_and_max_deposit_option_text]', 'woocommerce-partial-payment'),
					'cart_deposit_required_text' => __('For some items, you can only pay a deposit today. The rest will be billed later. [min_and_max_deposit_option_text]', 'woocommerce-partial-payment'),
					'cart_deposit_optional_text' => __('Some items may only have a deposit due today. [min_and_max_deposit_option_text]', 'woocommerce-partial-payment'),
					'cart_per_product_text' => __('Deposit: [deposit_calculation_rule_text]', 'woocommerce-partial-payment'),
					'no_deposit_text' => __('Full price (no deposit option).', 'woocommerce-partial-payment'),
					'min_and_max_deposit_option_text' => __('Based on your order total, minimum or maximum deposit amounts may apply.', 'woocommerce-partial-payment'),
					'pay_full_price_text' => __('Pay Full Price', 'woocommerce-partial-payment'),
					'pay_deposit_price_text' => __('Pay Deposit Price', 'woocommerce-partial-payment'),
					'calculate_deposit_text' => __('Calculate Deposit', 'woocommerce-partial-payment'),
					'pay_deposit_now_text' => __('Pay Deposit Now', 'woocommerce-partial-payment'),
					'view_order_pending_payments' => __('Your payment details contain pending payments. Goods and services might not be delivered until these payments are confirmed. If you still have a balance you can make a payment below.', 'woocommerce-partial-payment')
			);
	
		}
	
		return $labels;
	
	}
	
	
	/**
	 * Adds "Partial Payment" tab to Woocommerce Settings page
	 * 
	 * @param array $tabs
	 * 
	 * @return array $tabs
	 */
	public function add_menu_item( $tabs ) {
	
		$tabs['partial_payment'] = __( 'Woo Deposits', 'woocommerce-partial-payment' );

		return $tabs;

	}
	
	
	/**
	 * Settings page view
	 * 
	 * @return void
	 */
	public function settings_page() {
	
		global $woocommerce_settings, $woocommerce;
	
		$title = array(
	
				'name' => __('Woo Deposits Plugin Options', 'woocommerce-partial-payment'),
	
				'type' => 'title',
	
				'desc' => '',
	
				'id'   => 'partial_payment_options',
	
		);
	
		$license_email_address = array(
	
				'name' => __('Email Address', 'woocommerce-partial-payment'),
	
				'type' => 'text',
	
				'desc' => __('Email Address you used when purchasing the license.', 'woocommerce-partial-payment'),
	
				'id'   => 'wpp_license_email_address',
	
				'class' => 'email-address',
	
				'css'  => 'min-width:300px;',
	
				'desc_tip' => true,
	
				'std'  => ''
	
		);
	
		$license_key = array(
	
				'name' => __('Product License Key', 'woocommerce-partial-payment'),
	
				'type' => 'text',
	
				'desc' => __('License Key received in email when you purchased.', 'woocommerce-partial-payment'),
	
				'id'   => 'wpp_license_key',
	
				'class' => 'license-key',
	
				'css'  => 'min-width:300px;',
	
				'desc_tip' => true,
	
				'std'  => ''
	
		);
	
		$field_price_option = array(
	
				'name' => __('Deposit Payment Options', 'woocommerce-partial-payment'),
	
				'type' => 'select',
	
				'desc' => __('Select plugin behavior.', 'woocommerce-partial-payment'),
	
				'id'   => 'deposit_price_option',
	
				'css'  => 'min-width:300px;',
	
				'class' => 'chosen_select',
	
				'desc_tip' => true,
	
				'std'  => 'full',
	
				'options' => array(
	
						'full' => __('Let customer choose to pay full or deposit price', 'woocommerce-partial-payment'),
	
						'depo' => __('Customer must pay deposit price', 'woocommerce-partial-payment'),
	
				)
	
		);
	
		$site_wide_deposits = array(
	
				'name' => __('Enable site-wide deposit options', 'woocommerce-partial-payment'),
	
				'type' => 'select',
	
				'id'   => 'site_wide_deposit_option',
	
				'class' => 'chosen_select',
	
				'std'  => 'full',
	
				'css'  => 'min-width:300px;',
	
				'desc' => __('Individual product deposit options remain anyway.', 'woocommerce-partial-payment'),
	
				'desc_tip' => true,
	
				'options' => array(
	
						'yes' => __('Yes', 'woocommerce-partial-payment'),
	
						'no' => __('No', 'woocommerce-partial-payment'),
	
				)
	
		);
	
		$field_deposit_base = array(
	
				'name' => __('Calculate Deposit By', 'woocommerce-partial-payment'),
	
				'type' => 'select',
	
				'desc' => __('Select plugin behavior.', 'woocommerce-partial-payment'),
	
				'id'   => 'deposit_price_base',
	
				'css'  => 'width:300px;',
	
				'class' => 'chosen_select',
	
				'desc_tip' => true,
	
				'std'  => 'percent',
	
				'options' => array(
	
						'percent' => __('Percentage', 'woocommerce-partial-payment'),
	
						'float_flat' => __('Flat Amount per Item', 'woocommerce-partial-payment')
	
				)
	
		);
	
		$field_deposit_value = array(
	
				'name' => __('Deposit Value', 'woocommerce-partial-payment'),
	
				'type' => 'text',
	
				'id'   => 'deposit_price_value',
	
				'css'  => 'width:50px;',
	
				'std'  => '',
	
				'desc' => '<span class="depo-value-percent-sign">%</span><span class="depo-value-currency-sign">' . get_woocommerce_currency_symbol() . '</span>',
	
				'desc_tip' => __('This is a site-wide default. You can override it on the individual product settings.', 'woocommerce-partial-payment')
	
		);
	
		//hide site-wide deposit options if it is set to do so
		if ( 'no' === get_option('site_wide_deposit_option') ) {
			 
			$field_deposit_base['class'] .= ' hidden-option';
			 
			$field_deposit_value['class'] = 'hidden-option';
			 
		}
	
		$field_deposit_min_value = array(
	
				'name' => __('Minimum Deposit Value', 'woocommerce-partial-payment') . ' (' . get_woocommerce_currency_symbol() . ')',
	
				'type' => 'text',
	
				'id'   => 'deposit_price_min_value',
	
				'css'  => 'width:50px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'desc_tip' => __('Regardless of the actual deposit calculation, each order will have this value as a minimum required deposit. It applies to the entire cart.', 'woocommerce-partial-payment')
	
		);
	
		$field_deposit_max_value = array(
	
				'name' => __('Maximum Deposit Value', 'woocommerce-partial-payment') . ' (' . get_woocommerce_currency_symbol() . ')',
	
				'type' => 'text',
	
				'id'   => 'deposit_price_max_value',
	
				'css'  => 'width:50px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'desc_tip' => __('Regardless of the actual deposit calculation, no deposit will exceed this amount. It applies to the entire cart.', 'woocommerce-partial-payment')
	
		);
	
		$section_end = array(
	
				'type' => 'sectionend',
	
				'id' => 'partial_payment_options',
	
		);
	
		//labels part
		$labels = $this->get_text_labels();
		
		$labels_title = array(
	
				'name' => __('Text Templates', 'woocommerce-partial-payment'),
	
				'type' => 'title',
	
				'desc' => '',
	
				'id'   => 'partial_payment_labels',
	
		);
	
		$single_deposit_required_text = array(
	
				'name' => __('Product Page: Deposit Required Text', 'woocommerce-partial-payment'),
	
				'type' => 'textarea',
	
				'id'   => 'single_deposit_required_text',
	
				'css'  => 'width:350px;height:100px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'default' => $labels['single_deposit_required_text'],
	
				'desc_tip' => __('Used if Woo Deposits “Deposit Payment Options” setting is "customer must pay deposit price".', 'woocommerce-partial-payment')
	
		);
	
		$single_deposit_optional_text = array(
	
				'name' => __('Product Page: Deposit Optional Text', 'woocommerce-partial-payment'),
	
				'type' => 'textarea',
	
				'id'   => 'single_deposit_optional_text',
	
				'css'  => 'width:350px;height:100px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'default' => $labels['single_deposit_optional_text'],
	
				'desc_tip' => __('Used if Woo Deposits “Deposit Payment Options” setting is "let customer choose…".', 'woocommerce-partial-payment')
	
		);
	
		$cart_deposit_required_text = array(
	
				'name' => __('Cart Page: Deposit Required Text', 'woocommerce-partial-payment'),
	
				'type' => 'textarea',
	
				'id'   => 'cart_deposit_required_text',
	
				'css'  => 'width:350px;height:100px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'default' => $labels['cart_deposit_required_text'],
	
				'desc_tip' => __('Used if Woo Deposits “Deposit Payment Options” setting is "customer must pay deposit price".', 'woocommerce-partial-payment')
	
		);
	
		$cart_deposit_optional_text = array(
	
				'name' => __('Cart Page: Deposit Optional Text', 'woocommerce-partial-payment'),
	
				'type' => 'textarea',
	
				'id'   => 'cart_deposit_optional_text',
	
				'css'  => 'width:350px;height:100px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'default' => $labels['cart_deposit_optional_text'],
	
				'desc_tip' => __('Used if Woo Deposits “Deposit Payment Options” setting is "let customer choose…".', 'woocommerce-partial-payment')
	
		);
	
		$cart_per_product_text = array(
	
				'name' => __('Cart Page: Per Product Text', 'woocommerce-partial-payment'),
	
				'type' => 'textarea',
	
				'id'   => 'cart_per_product_text',
	
				'css'  => 'width:350px;height:100px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'default' => $labels['cart_per_product_text'],
	
				'desc_tip' => __('Shown under each item in the cart.', 'woocommerce-partial-payment')
	
		);
	
		$no_deposit_text = array(
	
				'name' => __('Deposit Is Not Allowed Text', 'woocommerce-partial-payment'),
	
				'type' => 'textarea',
	
				'id'   => 'no_deposit_text',
	
				'css'  => 'width:350px;height:100px;',

				'std'  => '',
	
				'desc' => '',
	
				'default' => $labels['no_deposit_text'],
	
				'desc_tip' => __('Shown under item in the cart and on single product page if deposits are disabled for this product.', 'woocommerce-partial-payment')
	
		);
	
		$min_and_max_deposit_option_text = array(
	
				'name' => __('Min and Max Deposit Option Text', 'woocommerce-partial-payment'),
	
				'type' => 'textarea',
	
				'id'   => 'min_and_max_deposit_option_text',
	
				'css'  => 'width:350px;height:100px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'default' => $labels['min_and_max_deposit_option_text'],
	
				'desc_tip' => __('Text for the shortcode used above. Only shows if min / max are not null.', 'woocommerce-partial-payment')
	
		);
	
		$pay_full_price_text = array(
	
				'name' => __('"Pay Full Price" Option Text', 'woocommerce-partial-payment'),
	
				'type' => 'text',
	
				'id'   => 'pay_full_price_text',
	
				'css'  => 'min-width:300px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'default' => $labels['pay_full_price_text'],
	
		);
	
		$pay_deposit_price_text = array(
	
				'name' => __('"Pay Deposit Price" Option Text', 'woocommerce-partial-payment'),
	
				'type' => 'text',
	
				'id'   => 'pay_deposit_price_text',
	
				'css'  => 'min-width:300px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'default' => $labels['pay_deposit_price_text'],
	
		);
	
		$calculate_deposit_text = array(
	
				'name' => __('"Calculate Deposit" Button Text', 'woocommerce-partial-payment'),
	
				'type' => 'text',
	
				'id'   => 'calculate_deposit_text',
	
				'css'  => 'min-width:300px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'default' => $labels['calculate_deposit_text'],
	
		);
	
		$pay_deposit_now_text = array(
	
				'name' => __('"Pay Deposit Now" Button Text', 'woocommerce-partial-payment'),
	
				'type' => 'text',
	
				'id'   => 'pay_deposit_now_text',
	
				'css'  => 'min-width:300px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'default' => $labels['pay_deposit_now_text'],
	
		);
	
		$field_deposit_payment_note = array(
	
				'name' => __('Instructions to customer for paying the balance of their bill', 'woocommerce-partial-payment'),
	
				'type' => 'textarea',
	
				'id'   => 'deposit_price_payment_note',
	
				'css'  => 'width:350px;height:100px;',
	
				'std'  => '',
	
				'desc' => '',
	
				'desc_tip' => __('i.e. "Remaining payments are due when you arrive for your appointment", etc. This appears on the checkout page when the deposit option is required or selected by the user. It also appears in order confirmation e-mails.', 'woocommerce-partial-payment')
	
		);
		
		$view_order_pending_payments = array(
				
				'name' => __('Pending payments note', 'woocommerce-partial-payment'),
				
				'type' => 'textarea',
				
				'id'   => 'view_order_pending_payments',
				
				'css'  => 'width:350px;height:100px;',
				
				'std'  => '',
				
				'desc' => '',
				
				'desc_tip' => __('Displayed on "View Order" page if customer has pending deposit payments.', 'woocommerce-partial-payment'),
				
				'default' => $labels['view_order_pending_payments']
				
		);
	
		$labels_section_end = array(
	
				'type' => 'sectionend',
	
				'id' => 'partial_payment_labels',
	
		);
	
		//show plugin settings only if license was checked and activated
		if ( $this->is_license_active() ) {
	
			$woocommerce_settings['partial_payment'] = array($title, $license_email_address, $license_key, $field_price_option, $site_wide_deposits, $field_deposit_base, $field_deposit_value, $field_deposit_min_value, $field_deposit_max_value, $section_end, $labels_title, $single_deposit_required_text, $single_deposit_optional_text, $cart_deposit_required_text, $cart_deposit_optional_text, $cart_per_product_text, $no_deposit_text, $min_and_max_deposit_option_text, $pay_full_price_text, $pay_deposit_price_text, $calculate_deposit_text, $pay_deposit_now_text, $field_deposit_payment_note, $view_order_pending_payments, $labels_section_end);
			 
		} else {
			 
			$woocommerce_settings['partial_payment'] = array($title, $license_email_address, $license_key, $section_end);
			 
		}
	
		woocommerce_admin_fields( $woocommerce_settings['partial_payment'] );
	
		if ( $this->is_license_active() ) {
		
			?>
			
				<div id="wpp-shortcodes-desc">
				
					<h3><?php _e('Available shortcodes', 'woocommerce-partial-payment'); ?></h3>
					
					<ul>
						
						<li><strong>[deposit_calculation_rule_text]</strong> - <?php _e('e.g. “10%” or “$50.00” or “$50” or “5,000 ¥”, based on deposit settings and currency display settings in WooCommerce', 'woocommerce-partial-payment'); ?></li>
						<li><strong>[min_and_max_deposit_option_text]</strong> - <?php _e('shortcode for the text, defined below. Shows only if min / max are not null', 'woocommerce-partial-payment'); ?></li>
					</ul>
				
				</div>
			
				<a href="#" id="wpp_reset_labels_to_defaults"><?php _e('Reset To Defaults', 'woocommerce-partial-payment'); ?></a>
			
			<?php 
			
		}	
	
	}
	
	
	/**
	 * Saves plugin settings
	 * 
	 * @return void
	 */
	public function save_settings() {
	
		//save currents to skip the check if license data wasn't changed
		$old_license_email_address = get_option('wpp_license_email_address');
		 
		$old_license_key = get_option('wpp_license_key');
	
		$license_email_address = '';
		 
		if ( isset( $_POST['wpp_license_email_address'] ) ) {
			 
			$license_email_address = sanitize_email( $_POST['wpp_license_email_address'] );
	
			update_option('wpp_license_email_address', $license_email_address);
			 
		}
		 
		$license_key = '';
		 
		if ( isset( $_POST['wpp_license_key'] ) ) {
	
			$license_key = sanitize_text_field( $_POST['wpp_license_key'] );
			 
			update_option('wpp_license_key', $license_key);
	
		}
		 
		if ( ( $old_license_email_address !== $license_email_address ) || ( $old_license_key !== $license_key ) ) {
	
			//perform license check on email address or license key update
			$this->check_license($license_email_address, $license_key, $old_license_key);
	
		}
		 
		if ( isset( $_POST['deposit_price_option'] ) && ( strlen( $_POST['deposit_price_option'] ) > 0 ) ) {
	
			update_option('deposit_price_option', $_POST['deposit_price_option']);
	
		}
	
		if ( isset( $_POST['site_wide_deposit_option'] ) && ( 'no' === $_POST['site_wide_deposit_option'] ) ) {
	
			update_option('site_wide_deposit_option', 'no');
	
		}  else {
	
			update_option('site_wide_deposit_option', 'yes');
	
		}
	
		if ( isset( $_POST['deposit_price_base'] ) && ( strlen( $_POST['deposit_price_base'] ) > 0 ) ) {
	
			update_option('deposit_price_base', $_POST['deposit_price_base']);
	
		}
	
		if ( isset( $_POST['deposit_price_value'] ) ) {
	
			update_option('deposit_price_value', floatval($_POST['deposit_price_value']));
	
		}
	
		if (isset($_POST['deposit_price_payment_note']) ) {
	
			update_option('deposit_price_payment_note', $_POST['deposit_price_payment_note']);
	
		}
		
		if ( isset( $_POST['deposit_price_min_value'] ) ) {
	
			$min_price = ( empty( $_POST['deposit_price_min_value'] ) ) ? '' : floatval( $_POST['deposit_price_min_value'] );
			 
			update_option('deposit_price_min_value', $min_price);
	
		}
	
		if ( isset( $_POST['deposit_price_max_value'] ) ) {
	
			if ( empty( $_POST['deposit_price_max_value'] ) || ( floatval( $_POST['deposit_price_max_value'] ) < floatval( $_POST['deposit_price_min_value'] ) ) ) {
	
				$max_price = '';
	
			} else {
	
				$max_price = floatval( $_POST['deposit_price_max_value'] );
	
			}
			 
			update_option('deposit_price_max_value', $max_price);
	
		}
	
		//save labels
		$labels = $this->get_text_labels( true );
	
		//if reset to defaults is set, use default labels, otherwise, use ones entered by user
		if ( empty( $_POST['wpp_reset_to_defaults'] ) ) {
			 
			foreach ( $labels as $key => &$value ) {

				$value = htmlspecialchars( $_POST[ $key ], ENT_QUOTES );

			}
			 
		}
	
		update_option( '_wpp_text_labels', $labels );
	
	}
	
	
	/**
	 * Handles auto updates from our server
	 * 
	 * @return void
	 */
	public static function wpp_activate_auto_updates() {
	
		require_once(dirname(__FILE__) . '/wp_autoupdate.php');
	
		$wpp_plugin_current_version = '0.4.6';
	
		$wpp_plugin_remote_path = 'http://webatix.com/plugins_repository/update.php';
	
		$wpp_package = 'http://webatix.com/plugins_repository/woocommerce-partial-payment.zip';
	
		$wpp_plugin_slug = plugin_basename( __FILE__ );
	
		$license_email_address = get_option( 'wpp_license_email_address', 'false' );
	
		$license_key = get_option( 'wpp_license_key', 'false' );
	
		new wp_auto_update( $wpp_plugin_current_version, $wpp_plugin_remote_path, $wpp_plugin_slug, $wpp_package, $license_email_address, $license_key );
	
	}
	
	
	/**
	 * Shows user message if there is any
	 * 
	 * @return void
	 */
	public function wpp_show_user_message() {
	
		$user_message = get_option('wpp_show_user_message');
	
		if ( ! empty( $user_message ) && is_array($user_message) ) {
	
			?><div class="<?php if ( 'license_activated' === $user_message['value'] ) { echo 'updated'; } else { echo 'error'; } ?>"><p><?php echo esc_html($user_message['message']); ?></p></div><?php
				
			//message is only displayed once
			delete_option('wpp_show_user_message');
				
		}	
			
	}
	
	
	/**
	 * Adds "Settings" link to plugin row on plugins page
	 * 
	 * @param array $links
	 * 
	 * @return array $links
	 */
	public function action_links( $links ) {
	
		$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=woocommerce_settings&tab=partial_payment' ) . '">' . __( 'Settings', 'woocommerce-partial-payment' ) . '</a>',
		);
	
		return array_merge( $plugin_links, $links );
	
	}
	
	
	/**
	 * Loads additional classes
	 *
	 * @return void
	 */
	public function load_classes() {
	
		include_once( dirname(__FILE__).'/classes/wc_cart_extended.php' );
	
	}
	
	
	/**
	 * Contains main action and filter hooks
	 * 
	 * @return void
	 */
	public function plugin_logic() {
		
		//this filter can be used to deactivate plugin functionality by other plugins or theme
		if ( false === apply_filters( 'wpp_plugin_is_enabled', true ) )
			return;
		
		//register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );
			
			
		/* single product page */
		//display single product deposit text label
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'deposit_price' ), 11 );
			
			
		/* cart page */
		//display cart-wide text
		add_action( 'woocommerce_before_cart', array( $this, 'display_cart_wide_text' ) );
			
		//show individual cart item deposit text
		add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'display_individual_cart_item_deposit_text' ), 11, 3 );
			
			
		/* checkout page */
		//adds "Pay Full Price"/"Pay Deposit Price" option above "Place Order" button
		add_action( 'woocommerce_review_order_before_submit', array( $this, 'payment_options_form' ) );
			
		//adds "Calculate Deposit" button
		add_filter( 'woocommerce_order_button_html', array( $this, 'add_calc_deposit_button' ), 10, 1 );
			
		//AJAX call for "Calculate Deposit" button click
		add_action( 'wp_ajax_wpp_calculate_total_deposit', array( $this, 'wpp_calculate_total_deposit' ) );
		add_action( 'wp_ajax_nopriv_wpp_calculate_total_deposit', array( $this, 'wpp_calculate_total_deposit' ) );
		
			
		/* "Order Received" page */
		//adds "order note"
		add_action( 'woocommerce_thankyou', array( $this, 'order_note_on_thankyou_page' ), 10, 1 );
			
		//add deposit-related rows to "Review Order" table
		add_filter( 'woocommerce_get_order_item_totals', array( $this, 'add_deposit_to_order_item_totals' ), 11, 2 );
		
		//make sure follow up payments show original product prices instead of remaining ones on "Order Received" page and in emails
		add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'show_full_item_price' ), 10, 3 );
		
			
		/* internal logic */
			
		//recalculate deposits for cart items after totals for the cart are calculated
		add_action( 'woocommerce_cart_updated', array( $this, 'wpp_recalculate_deposits_on_totals_update' ) );
			
		//on order creation replace full product price with deposit value, save $woocommerce->cart as $this->cart_holder for future use
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_single_product_price_to_partial' ) );
		
		//substitutes regular product price with composite price when add composite product to $this->cart_holder
		add_filter( 'woocommerce_add_cart_item', array( $this, 'apply_composite_price' ), 10, 2 );
			
		//saves deposit in order_items after order creation
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_deposit_in_order_items' ), 11 );
			
		//define when exaclty totals should be recalculated depending on chosen payment method
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'wpp_trigger_recalculate_totals_after_deposit' ), 10, 2 );
		
		//recalculate order totals after "Thank you" page ( if deposit was paid ) OR restore original order ( if remaining balance was paid )
		add_action( 'woocommerce_thankyou', array( $this, 'wpp_recalculate_totals_after_thankyou_page' ), 10, 1 );
		
		//change deposit record payment_status for instant payment methods such as paypal
		add_action( 'woocommerce_payment_complete', array( $this, 'add_deposit_record_for_instant_payment' ), 10, 1 );
			
			
		/* "edit product" page */
		//show individial product deposit options on "edit post" screen
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'form_deposit_price' ) );
		
		//save individual product deposit options
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_deposit_price' ) );
		
		 
		/* "edit order" page */
		//save _deposit_paid value, when editing order from WP admin
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'wpp_save_deposit_value' ), 20 );
		 
		//add "deposits" section to "edit order" screen
		add_action( 'woocommerce_admin_order_totals_after_shipping', array( $this, 'deposit_admin_order_totals_after_shipping' ) );
		 
		//ajax create new deposit record
		add_action( 'wp_ajax_wpp_add_new_deposit_record', array( $this, 'wpp_ajax_add_new_deposit_record' ) );
		
		//remove deposit records when order post is deleted
		add_action( 'before_delete_post', array( $this, 'wpp_remove_deposits_on_order_removal' ), 9 );
		
		//change order deposits statuses to "completed" when order status is changed to "completed"
		add_action( 'woocommerce_order_status_completed', array( $this, 'wpp_mark_all_order_deposits_as_completed' ), 10, 1 );
		 
		 
		/* other */
		//adds order_id to orders list in wp_options table TODO: this is not currently in use
		//add_action( 'woocommerce_checkout_order_processed', array( $this, 'order_to_list' ) );
		
		//add remaining payment details to the beginning of notification email
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_remaining_payment_details' ), 11, 2 );
		
		//add deposit rows to notification email
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_total_table' ), 11, 2 );
		
		/* Follow-up payments */
		
		//force show payment buttons for orders with only deposit paid (My Account page)
		add_filter( 'woocommerce_valid_order_statuses_for_payment', array( $this, 'wpp_show_payment_buttons_on_my_account' ), 10, 2 );
		
		//change order status to "Pending" as it is required by woocommerce
		$this->prepare_follow_up_payment();
		
		//add "Pay Now" button to "View Order" page
		add_action( 'woocommerce_view_order', array( $this, 'wpp_add_pay_button_to_view_order' ), 9, 1 ); 
		
	}
	
	
	/**
	 * Registers and enqueues front-end styles
	 * 
	 * @return void
	 */
	public function register_plugin_styles() {

		wp_enqueue_style( 'woocommerce-partial-payment-plugin-styles', plugins_url( 'css/display.css', __FILE__ ) );

	}

	
	/**
	 * Registers and enqueues front-end scripts
	 * 
	 * @return void
	 */
	public function register_plugin_scripts() {
		
		if ( is_checkout() ) {

			wp_enqueue_script('jquery-ui-dialog');
			
			wp_enqueue_script( 'woocommerce-partial-payment-plugin-script', plugins_url( 'js/display.js', __FILE__ ), array('jquery', 'jquery-ui-dialog') );
			
			wp_enqueue_style('jqueryui', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');
			
		}

	}

	
	/**
	 * Displays single product deposit text label
	 *
	 * @return mixed ( boolean | void )
	 */
	public function deposit_price() {
	
		global $post;
	
		$labels = $this->get_text_labels();
	
		if ( ! empty( $labels ) ) {
	
			$deposits_enabled = get_post_meta( $post->ID, '_enable_deposit_options', true );
	
			if ( 'no' === $deposits_enabled ) {
	
				$label = $labels['no_deposit_text'];
	
			} else {
	
				$required_or_optional = $this->is_deposit_optional_or_required();
	
				$label = $labels[ 'single_deposit_' . $required_or_optional . '_text' ];
	
				$label = $this->parse_label_shortcodes( $label, $post->ID );
	
			}
	
			echo '<p class="wpp_single_product_deposit_label">', $label, '</p>';
	
		}
	
	}
	
	
	/**
	 * Displays cart-wide text
	 *
	 * @return void
	 */
	public function display_cart_wide_text() {
		 
		global $woocommerce;
		 
		if ( ! empty( $woocommerce->cart->cart_contents ) ) {
	
			$labels = $this->get_text_labels();
	
			//define whether to show optional or required label
			$required_or_optional = $this->is_deposit_optional_or_required();
				
			//check if cart contains items with individual deposit options that are different from site-wide deposit options
			$individual = $this->compare_cart_items_depo_options_with_site_wide_options( $woocommerce->cart );
			
			//show "deposit required" or "deposit optional" text if product deposit options vary
			if ( true === $individual ) {

				$cart_wide_text = $labels[ 'cart_deposit_' . $required_or_optional . '_text' ];

			} else {

				$cart_wide_text = $labels[ 'single_deposit_' . $required_or_optional . '_text' ];

			}

			$cart_wide_text = $this->parse_label_shortcodes( $cart_wide_text );
	
			if ( ! empty( $cart_wide_text ) ) {
			
				?>
			    	
					<div class="woocommerce-message">
	
						<?php echo $cart_wide_text; ?>
	
					</div>
	
				<?php 
				
			}	

		}

	}
	    
	
	/**
	 * Shows individual deposit text for cart items if cart contains items with individual deposit options that are different from site-wide deposit options
	 * 
	 * @param string $formatted_price
	 * @param array $value
	 * @param string $cart_item_key
	 * 
	 * @return string
	 */
	public function display_individual_cart_item_deposit_text( $formatted_price, $value, $cart_item_key ) {
	
		global $woocommerce;
	
		//for composite products deposit options are defined by parent product
		if ( ! empty( $value['composite_parent'] ) )
			return $formatted_price;
		
		//check if cart contains items with individual deposit options that are different from site-wide deposit options
		$individual = $this->compare_cart_items_depo_options_with_site_wide_options( $woocommerce->cart );
	
		if ( true === $individual ) {
	
			$labels = $this->get_text_labels();
				
			$deposits_enabled = get_post_meta( $value['product_id'], '_enable_deposit_options', true );
				
			if ( 'no' === $deposits_enabled ) {
				
				$label = $labels['no_deposit_text'];
				
			} else {
				
				$label = $this->parse_label_shortcodes( $labels['cart_per_product_text'], $value['product_id'], $value );
				
			}
				
			$formatted_price .= ' <div class="depo-label">' . $label . '</div>';
	
		}
	
		return $formatted_price;
	
	}

	
	/**
	 * Adds "Pay Full Price"/"Pay Deposit Price" option above "Place Order" button. Displayed on checkout page when deposit option is available
	 *
	 * @return void
	 */
	public function payment_options_form() {
	
		global $woocommerce;
	
		$cart_contents = $woocommerce->cart->get_cart();
		
		$has_deposit = false;
	
		if ( ! empty( $cart_contents ) ) {
	
			foreach ( $cart_contents as $cart_item_key => $values ) {
	
				$_product = $values['data'];
	
				$cpost = $_product->get_post_data();
	
				$deposits_enabled = get_post_meta( $cpost->ID, '_enable_deposit_options', true );
	
				if ( 'no' !== $deposits_enabled ) {
	
					$label = get_post_meta($cpost->ID, '_deposit_price', true);
	
					if ( ! is_numeric( $label ) ) {
	
						$label = get_option('deposit_price_value', '');
	
					}
	
					if ( is_numeric( $label ) ) {
	
						$has_deposit = true;
	
					}
					 
				}
	
			}
	
		}
	
		$req = get_option('deposit_price_option', 'full');
	
		if ( $has_deposit ) :
	
			$labels = $this->get_text_labels();
	
			?>
	
				<div id="partial-payment">
				
					<h3><?php _e('Choose Payment Options', 'woocommerce-partial-payment'); ?></h3>
				
								<ul class="payment_methods methods">
				
				                <?php if ( 'depo' !== $req ) : ?>
				
								    <li>
				
										<input id="partial_payment_full" class="input-radio" name="partial_payment" value="full" checked="checked" type="radio">
				
										<label for="partial_payment_full"><?php echo $labels['pay_full_price_text']; ?></label>
				
				                    </li>
				
				                <?php endif; ?>
				
								    <li>
				
										<input id="partial_payment_deposit" class="input-radio" name="partial_payment" value="deposit" <?php if ( 'depo' === $req ) echo 'checked="checked"'; ?> type="radio">
				
										<label for="partial_payment_deposit"><?php echo $labels['pay_deposit_price_text']; ?></label>
				
								    </li>
				
								</ul>
				
						<div class="clear"></div>
						
				</div>
	
			<?php
	
		endif;
	
	}
	    	
	
	/**
	 * Adds "Calculate Deposit" button to checkout page
	 * 
	 * @param string $button_html
	 * 
	 * @return string
	 */
	public function add_calc_deposit_button( $button_html ) {
		
		$labels = $this->get_text_labels();
		
		$button_html .= '<a href="#" class="button alt" id="wpp-calculate-deposit">' . $labels['calculate_deposit_text'] . '</a>';
		
		return $button_html;
		
	}
		
	
	/**
	 * Handles AJAX call on "Calculate Deposit" button click
	 * 
	 * @return void
	 */
	public function wpp_calculate_total_deposit() {
			
		global $woocommerce;
			
		$woocommerce->verify_nonce( 'process_checkout' );
		
		$labels = $this->get_text_labels();
		
		$deposit_cart = new Woocommerce_Cart_Extended();
		
		//first calculate totals to get correct discounted prices (if coupons were used)
		$woocommerce->cart->calculate_totals();
		
		//add products
		if ( ! empty( $woocommerce->cart->cart_contents ) ) {
		
			foreach ( $woocommerce->cart->cart_contents as $product ) {
		
				$data = array( 'deposit_value' => $product['deposit_value'] );
				
				//Woocommerce Composite Products plugin compatibility
				if ( ! empty( $product['composite_parent'] ) ) {
					
					$product['deposit_value'] = 0;
						
				}
					
				$deposit_cart->add_to_cart( $product['product_id'], $product['quantity'], $product['variation_id'], '', $data );
				
			}
			
		}

		//replace regular prices with deposit values for deposit total calculations
		if ( ! empty( $deposit_cart->cart_contents ) ) {
		
			foreach ( $deposit_cart->cart_contents as $item ) {
				
				$item['data']->price = $item['data']->deposit_price = ( empty( $item['deposit_value'] ) ) ? 0 : $item['deposit_value'];
				
				//compatibility with Composite pricing plugin ver version 1.6.2+
				if ( 'yes' === $item['data']->per_product_pricing )
					$item['data']->base_price = $item['data']->price;
				
			}
			
		}	
		
		//for shipping to be calculated correctly
		define('WOOCOMMERCE_CHECKOUT', true);
		
		$deposit_cart->calculate_totals();
			
		//recalculate cart totals to get correct "Remaining Balance" value
		$woocommerce->cart->calculate_totals();
		
		ob_clean();
		
		?>
			
			<table id="wpp_calculated_deposit">
				
				<tr>
					
					<td><?php _e('Due Today:', 'woocommerce-partial-payment'); ?></td>
					<td><?php echo woocommerce_price( $deposit_cart->total ); ?></td>
					
				</tr>
					
				<tr>
					
					<td><?php _e('Remaining Balance:', 'woocommerce-partial-payment'); ?></td>
					<td><?php echo woocommerce_price( $woocommerce->cart->total - $deposit_cart->total ); ?></td>
				
				</tr>
					
				<tr>
					
					<td colspan="2"><?php echo get_option('deposit_price_payment_note'); ?></td>
					
				</tr>
					
				<tr id="content">
					
					<td colspan="2"><a href="#" class="button alt" id="wpp-pay-deposit"><?php echo $labels['pay_deposit_now_text']; ?></a> <a href="#" class="button alt" id="wpp-close-deposit-window"><?php _e('Cancel', 'woocommerce-partial-payment'); ?></a></td>
					
				</tr>
				
			</table>
			
		<?php 
			
		die;
			
	}
    

	/**
	 * Shows Order Note on "thankyou" page
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function order_note_on_thankyou_page( $order_id ) {
	
		global $woocommerce;
	
		if ( isset( $woocommerce->session->partial_payment ) && ( 'deposit' === $woocommerce->session->partial_payment ) ) {
	
			if ( is_numeric( $order_id ) ) {
	
				$out = '<div id="wpp-instructions-for-paying">' . get_option('deposit_price_payment_note', '') . '</div>';
	
			}
	
			unset( $woocommerce->session->partial_payment );
	
		}
	
	}

	
	/**
	 * Shows deposit related rows on "Order Received" page
	 *
	 * @param array $total_rows
	 * @param object $order
	 *
	 * @return array $total_rows
	 */
	public function add_deposit_to_order_item_totals( $total_rows, $order ) {
		
		$order_remaining_total = get_post_meta($order->id, '_order_remaining_total', true);
		
		$deposit = get_post_meta( $order->id, '_deposit_paid', true );
		
		if ( $order_remaining_total > 0 ) {
			
			global $post, $pagenow;
			
			$total_cost = floatval( $order_remaining_total ) + floatval( $deposit );
			
			//if orig_order is set, this means that remaining_balance has just been paid. Update _deposit_paid if we're on "Order Received" page
			if ( ! empty( $order->order_custom_fields['_orig_order'][0] ) && ( ! empty( $post ) && ( intval( get_option('woocommerce_thanks_page_id') ) === $post->ID ) ) ) {
			
				update_post_meta( $order->id, '_deposit_paid', $deposit + $order->order_total );
				
			}
			
			//labels depend on payment method, page user is currently on: if it's "thankyou" page, we show "Amount Due Today", otherwise, "Amount Paid" OR email template: if it's a customer invoice, show "Amount Paid", otherwise, "Amount Due Today"  
			if ( ( ( 'bacs' === $order->payment_method || 'cheque' === $order->payment_method ) && ( ! empty( $post ) && ( intval( get_option('woocommerce_thanks_page_id') ) === $post->ID ) ) ) || ( is_admin() && isset( $pagenow ) && ( 'post.php' !== $pagenow ) ) ) {
				 
				$total_rows['order_total']['label'] = __('Amount Due Today:', 'woocommerce-partial-payment');
				
				if ( ! empty( $order->order_custom_fields['_orig_order'][0] ) ) {
					
					$deposit = $order->order_total;
				
				}
				 
			} else {
	
				$total_rows['order_total']['label'] = __('Amount Paid:', 'woocommerce-partial-payment');
				
				if ( ! empty( $order->order_custom_fields['_orig_order'][0] ) ) {
				
					//in this case all the money is paid already
					$deposit += $order->order_total;
				
				}
	
			}
			
			//if orig_order is set, this means that remaining_balance has just been paid. Update _order_remaining_total
			if ( ! empty( $order->order_custom_fields['_orig_order'][0] ) ) {
				
				$order_remaining_total -= $order->order_total;
				
				//only update this value when "Order Received" page is shown
				if ( ! empty( $post ) && ( intval( get_option('woocommerce_thanks_page_id') ) === $post->ID ) ) {
					
					update_post_meta( $order->id, '_order_remaining_total', $order_remaining_total );
					
				}	
				
			}	
			
			if ( ( $deposit > 0 ) && ( ! empty( $post ) && ( intval( get_option('woocommerce_pay_page_id') ) === $post->ID ) ) ) {
				
				add_filter( 'woocommerce_available_payment_gateways', array( $this, 'wpp_disable_cod' ) );
				
			}
			
			$total_rows['order_total']['value'] = '<span class="amount">' . woocommerce_price( $deposit ) . '</span>';

			unset( $total_rows['cart_subtotal'] );
			
			if ( $order_remaining_total > 0 )
				$total_rows['deposit'] = array( 'label' => __('Remaining Balance:', 'woocommerce-partial-payment'), 'value' => woocommerce_price( $order_remaining_total ) );
	
			$total_rows['total_cost'] = array( 'label' => __('Total Cost:', 'woocommerce-partial-payment'), 'value' => woocommerce_price( $total_cost ) );
	
		}
	
		return $total_rows;
	
	}
	
	
	/**
	 * Shows full item price instead of remaining one for follow up payments ("Order Received" page + email templates)
	 * 
	 * @param unknown_type $subtotal
	 * @param unknown_type $item
	 * @param unknown_type $order
	 */
	public function show_full_item_price( $subtotal, $item, $order ) {
		
		if ( ! empty( $order->order_custom_fields['_orig_order'][0] ) ) {
			
			$tax_display = $order->tax_display_cart;
			
			$subtotal = 0;
			
			//TODO: consider restoring original values from $order->order_custom_fields['_orig_order_items'][0]
			$product = $order->get_product_from_item( $item );
			
			if ( $tax_display == 'excl' ) {
				
				if ( $order->prices_include_tax ) $ex_tax_label = 1; else $ex_tax_label = 0;
				
				$subtotal = woocommerce_price( $product->get_price_excluding_tax( $item['qty'] ), array( 'ex_tax_label' => $ex_tax_label ) );
				
			} else {
				
				$subtotal = woocommerce_price( $product->get_price_including_tax( $item['qty'] ) );
				
			}
			
		}
		
		return $subtotal;
		
	}
	
	
	/**
	 * Disables "Cash on Delivery" payment method for follow up payments
	 * 
	 * @param array $payment_gateways
	 * 
	 * @return array $payment_gateways
	 */
	public function wpp_disable_cod( $payment_gateways ) {
		
		if ( isset( $payment_gateways['cod'] ) )
			unset( $payment_gateways['cod'] );
		
		return $payment_gateways;
		
	}
	
	
	/**
	 * Recalculates deposits on totals update and on cart being loaded from session
	 *
	 * @param obj $cart
	 *
	 * @return void
	 */
	public static function wpp_recalculate_deposits_on_totals_update( $cart = false ) {
	
		global $woocommerce;
	
		$woocommerce->cart->cart_contents_total_deposit = 0;
		
		//if $cart is supplied as an argument, method was triggered by 'woocommerce_cart_loaded_from_session' action. This means, we need to calculate totals before proceeding with deposit calculations  
		if ( is_object( $cart ) ) {
			
			$woocommerce->cart->calculate_totals();
			
		}
		
		if ( ! empty( $woocommerce->cart->cart_contents ) ) {
			
			foreach ( $woocommerce->cart->cart_contents as $cart_item_key => $values ) {
	
				$post_id = ( empty( $values['variation_id'] ) ) ? $values['product_id'] : $values['variation_id'];
	
				$woocommerce->cart->cart_contents[ $cart_item_key ] = WoocommercePartialPayment::wpp_save_deposit_in_the_cart($values, $values['product_id'], $values['variation_id']);
	
				$woocommerce->cart->cart_contents_total_deposit += floatval( $woocommerce->cart->cart_contents[ $cart_item_key ]['deposit_value'] ) * $woocommerce->cart->cart_contents[ $cart_item_key ]['quantity'];
	
			}
				
		}
	
	}
	

	/**
	 * Replaces full product price with deposit value on order creation, saves $woocommerce->cart as $this->cart_holder for future use
	 *
	 * @param obj $cart_object
	 *
	 * @return void
	 */
	public function set_single_product_price_to_partial( $cart_object ) {
	
		if ( isset( $_POST['partial_payment'] ) && ( 'deposit' === $_POST['partial_payment'] ) ) {
	
			global $woocommerce;
	
			//clone cart to restore it after deposit is processed
			if ( empty( $this->cart_holder->cart_contents ) ) {
	
				$this->cart_holder = new Woocommerce_Cart_Extended();
				
				foreach ( $woocommerce->cart->cart_contents as $product ) {
					
					$additional_data = array();
					
					if ( ! empty( $product['composite_parent'] ) && ( 'yes' === $woocommerce->cart->cart_contents[ $product['composite_parent'] ]['data']->per_product_pricing ) ) {
						
						$additional_data['_composite_price'] = $product['composite_data'][ $product['composite_item'] ]['price'];
						
					}

					$this->cart_holder->add_to_cart( $product['product_id'], $product['quantity'], $product['variation_id'], '', $additional_data );

				}
				
				//apply coupons
				$this->cart_holder->applied_coupons = array();

				if ( ! empty( $woocommerce->cart->applied_coupons ) ) {
				
					foreach ( $woocommerce->cart->applied_coupons as $code )
						$this->cart_holder->add_discount( $code );
					
				}	
				
				//unset discounts as they're already included in deposit value calculations
				$woocommerce->cart->applied_coupons = array();
				
			}
				
			if ( sizeof( $woocommerce->cart->cart_contents ) > 0 ) {
	
				foreach ( $woocommerce->cart->cart_contents as $cart_item_key => &$value ) {
	
					//Woocommerce Composite Products plugin compatibility
					if ( ! empty( $value['composite_parent'] ) && ( 'no' === $woocommerce->cart->cart_contents[ $value['composite_parent'] ]['data']->per_product_pricing ) ) {

						$value['deposit_value'] = 0;

					}
						
					$value['data']->price = $value['data']->deposit_price = ( empty( $value['deposit_value'] ) ) ? 0 : $value['deposit_value'];
					
					//compatibility with Composite pricing plugin ver version 1.6.2+
					if ( 'yes' === $value['data']->per_product_pricing )
						$value['data']->base_price = $value['data']->price;
	
				}
	
			}
	
			if ( isset( $woocommerce->session ) ) {
	
				// WC 2.0
				$name = 'partial_payment';
				$woocommerce->session->$name = 'deposit';
	
			}
	
		}
	
	}
	
	
	/**
	 * Replaces regular product price with composite price when adding products to $this->cart_holder
	 * 
	 * @param array $cart_item_data
	 * @param string $cart_item_key
	 * 
	 * @return void
	 */
	public function apply_composite_price( $cart_item_data, $cart_item_key ) {
		
		if ( ! empty( $cart_item_data['_composite_price'] ) )
			$cart_item_data['data']->price = $cart_item_data['_composite_price'];
		
		return $cart_item_data;
		
	}


	/**
	 * Saves deposit value for each order item right after order creation
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function save_deposit_in_order_items( $order_id ) {
	
		global $woocommerce;
	
		$order = new WC_Order( $order_id );
	
		$order_items = $order->get_items();

		if ( isset( $woocommerce->session->partial_payment ) && ( 'deposit' === $woocommerce->session->partial_payment ) ) {
	
			foreach ( $order_items as $key => $item ) {
	
				woocommerce_add_order_item_meta( $key, '_deposit_value', $item['line_total'] );
	
			}
	
		}
	
		update_post_meta( $order_id, '_deposit_paid', $order->order_total );
	
	}	

	
	/**
	 * Defines when exaclty totals should be recalculated depending on chosen payment method
	 *
	 * @param int $order_id
	 * @param array $posted
	 *
	 * @return void
	 */
	public function wpp_trigger_recalculate_totals_after_deposit( $order_id, $posted ) {
		
		if ( isset( $posted['payment_method'] ) && ( 'paypal' === $posted['payment_method'] ) ) {
	
			//set order_item price back to full price after deposit was made
			add_filter('woocommerce_payment_successful_result', array($this, 'wpp_recalculate_totals_after_deposit'));
				
		} else {	//recalculate totals straight away
				
			$order = new WC_Order( $order_id );
				
			$result = array(
					'result' 	=> 'success',
					'redirect'	=> add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order->id, get_permalink( woocommerce_get_page_id( 'thanks' ) ) ) )
			);
				
			$this->wpp_recalculate_totals_after_deposit( $result );
				
		}
	
	}


	/**
	 * Recalculates order totals after "Thank you" page ( if deposit was paid ) OR restores original order ( if remaining balance was paid )
	 *
	 * @param obj $order
	 *
	 * @return void
	 */
	public function wpp_recalculate_totals_after_thankyou_page( $order_id ) {
		
		$order = new WC_Order( $order_id );
		
		$order_items = $order->get_items();
		
		//if orig_order is set, this means that remaining_balance has just been paid. Restore original order totals
		if ( ! empty( $order->order_custom_fields['_orig_order'][0] ) ) {
			
			$payment_status = 'pending';
			
			//only for instant payment methods deposit record status is changed to "completed"
			if ( ( 'bacs' !== $order->payment_method ) && ( 'cheque' !== $order->payment_method ) ) {
				
				$payment_status = 'completed';
				
			}
			
			//first let's add new deposit record
			$this->update_deposit_record( array( 'order_id' => $order->id, 'sum' => floatval( $order->order_total ), 'payment_method' => $order->payment_method_title, 'payment_status' => $payment_status ) );
			
			$orig_order = maybe_unserialize( $order->order_custom_fields['_orig_order'][0] );
			
			$orig_order_items = maybe_unserialize( $order->order_custom_fields['_orig_order_items'][0] );
			
			if ( ! empty( $order_items ) ) {
			
				foreach ( $order_items as $id => $item ) {
					
					//update _deposit_value for the item
					woocommerce_update_order_item_meta( $id, '_deposit_value', $item['item_meta']['_deposit_value'][0] + $orig_order_items[ $id ][ 'line_total' ] );
					
					//restore original values
					woocommerce_update_order_item_meta( $id, '_line_total', $orig_order_items[ $id ][ 'line_total' ] );
					woocommerce_update_order_item_meta( $id, '_line_tax', $orig_order_items[ $id ][ 'line_tax' ] );
					woocommerce_update_order_item_meta( $id, '_line_subtotal', $orig_order_items[ $id ][ 'line_subtotal' ] );
					woocommerce_update_order_item_meta( $id, '_line_subtotal_tax', $orig_order_items[ $id ][ 'line_subtotal_tax' ] );
					 
				}
				
			}
			
			update_post_meta( $order->id, '_order_total', $orig_order->order_total );
			
			update_post_meta( $order->id, '_order_tax', $orig_order->order_tax );
			
			update_post_meta( $order->id, '_order_shipping', $orig_order->order_shipping );
			
			update_post_meta( $order->id, '_order_shipping_tax', $orig_order->order_shipping_tax );
			
			//remove _orig_order and _orig_order_items as they're meant to be used only once
			delete_post_meta( $order->id, '_orig_order' );
			
			delete_post_meta( $order->id, '_orig_order_items' );
			
		} elseif ( ! empty( $order->order_custom_fields['_order_remaining_total'] ) && ( $order->order_custom_fields['_order_remaining_total'] > 0 ) ) {
		
			//recalculate order_total
			$order_total = 0;
				
			if ( ! empty( $order_items ) ) {
		
				foreach ( $order_items as $id => $item ) {
		
					$order_total += $item['line_total'];
		
				}
		
			}
		
			//TODO: make sure following line is correct OR maybe replace it with $order_total = $deposit_paid + $order_remaining_total, so above lines are not required too
			$order_total += floatval( get_post_meta( $order->id, '_order_tax', true ) ) + floatval( get_post_meta( $order->id, '_order_shipping', true ) ) + floatval( get_post_meta( $order->id, '_order_shipping_tax', true ) - floatval( get_post_meta( $order->id, '_order_discount', true ) ) );
		
			if ( $order_total !== $order->order_total )
				update_post_meta( $order->id, '_order_total', woocommerce_format_total( $order_total ) );
			
		}	
	
	}	
	
	
	/**
	 * Changes deposit record payment_status for instant payment methods such as paypal
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function add_deposit_record_for_instant_payment( $order_id ) {
	
		$order = new WC_Order( $order_id );
	
		//only for instant payment methods deposit record status is changed to "completed"
		if ( ( 'bacs' !== $order->payment_method ) && ( 'cheque' !== $order->payment_method ) ) {
	
			$deposit_record_ids = $this->get_deposit_records( $order_id, true );
			
			$deposit_id = array_pop( $deposit_record_ids );
			
			$this->update_deposit_record( array( 'order_id' => $order_id, 'payment_status' => 'completed', 'deposit_id' => $deposit_id ) );
				
		}
	
	}	
	
	
	/**
	 * Recalculates Order Totals even if user is not returning to "thank you" page (on successful payment), saves deposit record in DB
	 *
	 * @param array $result
	 *
	 * @return array payment results
	 */
	public function wpp_recalculate_totals_after_deposit( $result ) {
	
		$args = parse_url( $result['redirect'] );
	
		parse_str( urldecode( $args['query'] ) );
	
		if ( ( ! isset( $order ) || empty( $order ) || ! is_numeric( $order ) ) && isset( $return ) ) {
				
			$args = parse_url( $return );
			
			parse_str( urldecode( $args['query'] ) );
				
		}

		if ( isset( $order ) && ! empty( $order ) && is_numeric( $order ) ) {
				
			global $woocommerce;
				
			$order = new WC_Order( $order );
				
			//recalculate _deposit_paid value
			update_post_meta( $order->id, '_deposit_paid', $order->order_total );
			
			//save deposit in DB
			$this->update_deposit_record( array( 'order_id' => $order->id, 'sum' => floatval( $order->order_total ), 'payment_method' => $order->payment_method_title, 'payment_status' => 'pending' ) );
				
			//only applies if deposit option was selected ( or user is forced to pay deposit )
			if ( 'deposit' === $_POST['partial_payment'] ) {
	
				$order_items = $order->get_items();
				
				//loop through order items and restore line_totals, line_subtotals and line_taxes from $this->cart_holder
				if ( ! empty( $order_items ) ) {
						
					foreach ( $order_items as $id => &$item ) {
	
						foreach ( $this->cart_holder->cart_contents as $key => $cart_item ) {
								
							//find this item in cloned cart and restore original values
							if ( ( $item['product_id'] == $cart_item['product_id'] ) && ( $item['variation_id'] == $cart_item['variation_id'] ) ) {
	
								//Woocommerce Composite Products plugin compatibility
								if ( ! empty( $item['composite_parent'] )  && ( 'no' === $woocommerce->cart->cart_contents[ $item['composite_parent'] ]['data']->per_product_pricing ) ) {
										
									$item['line_total'] = $item['line_tax'] = $item['line_subtotal'] = $item['line_subtotal_tax'] = 0;
	
									woocommerce_update_order_item_meta( $id, '_line_total', 0 );
									woocommerce_update_order_item_meta( $id, '_line_tax', 0 );
									woocommerce_update_order_item_meta( $id, '_line_subtotal', 0 );
									woocommerce_update_order_item_meta( $id, '_line_subtotal_tax', 0 );
									
									unset( $this->cart_holder->cart_contents[ $key ] );
										
								} else	{
	
									$item['line_total'] = $cart_item['line_total'];
									$item['line_tax'] = $cart_item['line_tax'];
									$item['line_subtotal'] = $cart_item['line_subtotal'];
									$item['line_subtotal_tax'] = $cart_item['line_subtotal_tax'];
										
									woocommerce_update_order_item_meta( $id, '_line_total', $item['line_total'] );
									woocommerce_update_order_item_meta( $id, '_line_tax', $item['line_tax'] );
									woocommerce_update_order_item_meta( $id, '_line_subtotal', $item['line_subtotal'] );
									woocommerce_update_order_item_meta( $id, '_line_subtotal_tax', $item['line_subtotal_tax'] );
										
								}
	
							}
								
						}
	
					}
	
				}
	
				$this->cart_holder->calculate_totals();
	
				//restore order tax rows
				$order_tax_items = $order->get_items( 'tax' );
	
				if ( ! empty( $order_tax_items ) ) {
						
					foreach ( $order_tax_items as $id => &$tax_item ) {
	
						$tax_item['tax_amount'] = $this->cart_holder->taxes[ $tax_item['rate_id'] ];
	
						woocommerce_update_order_item_meta($id, 'tax_amount', $this->cart_holder->taxes[ $tax_item['rate_id'] ]);
							
					}
						
				}
	
				$order->order_tax = $this->cart_holder->tax_total;
				update_post_meta($order->id, '_order_tax', $this->cart_holder->tax_total);
	
				$order->order_tax = $this->cart_holder->tax_total;
				update_post_meta($order->id, '_order_tax', $this->cart_holder->tax_total);

				//restore discount values
				if ( $applied_coupons = $this->cart_holder->get_applied_coupons() ) {
					
					foreach ( $applied_coupons as $code ) {
				
						$item_id = woocommerce_add_order_item( $order->id, array(
								'order_item_name' 		=> $code,
								'order_item_type' 		=> 'coupon'
						) );
				
						//add discount to order
						if ( $item_id ) {
							
							woocommerce_add_order_item_meta( $item_id, 'discount_amount', isset( $this->cart_holder->coupon_discount_amounts[ $code ] ) ? $this->cart_holder->coupon_discount_amounts[ $code ] : 0 );
							
						}
						
					}
					
				}
	
				$order->cart_discount = $this->cart_holder->discount_cart;
				update_post_meta($order->id, '_cart_discount', $this->cart_holder->discount_cart);
	
				$order->order_discount = $this->cart_holder->discount_total;
				update_post_meta($order->id, '_order_discount', $this->cart_holder->discount_total);
	
			}
			
			if ( isset( $_POST['partial_payment'] ) && ( 'deposit' === $_POST['partial_payment'] ) && ! empty( $this->cart_holder->total ) ) {
			
				$order_remaining_total = $this->cart_holder->total - $order->order_total;
				
				//TODO: this line is added to avoid wrong shipping tax calculation
				$order_remaining_total = $order_remaining_total - $this->cart_holder->shipping_tax_total + $order->order_shipping_tax;
				
			} else {
				
				$order_remaining_total = 0;
				
			}	
			
			update_post_meta($order->id, '_order_remaining_total', $order_remaining_total);
				
		} else {
				
			error_log('Something is wrong with payment results array.');
				
		}
	
		return $result;
	
	}	
	
	
	/**
	 * Shows individial product deposit options on "edit post" screen
	 *
	 * @return void
	 */
	public function form_deposit_price() {
	
		global $post;
	
		$label = get_post_meta($post->ID, '_deposit_price', true);
	
		$global_dpb = get_option('deposit_price_base', 'percent');
	
		if ( is_numeric( $label ) && intval( $label ) > 0 ) {
	
			if ( ! $deposit_price_base = get_post_meta($post->ID, '_deposit_type', true) )
				$deposit_price_base = get_option('deposit_price_base', 'percent');
	
		} else {
	
			$deposit_price_base = get_option('deposit_price_base', 'percent');
	
		}
	
		$deposit_price_value = get_option('deposit_price_value', 0);
	
		$global_dpb = get_option('deposit_price_base', 'percent');
	
		if ( 'percent' === $global_dpb ) {
	
			$descr = __('Leave blank for default value(' . $deposit_price_value . '%)', 'woocommerce-partial-payment');
	
		} else {
	
			$descr = __('Leave blank for default value(' . get_woocommerce_currency_symbol() . $deposit_price_value . ')', 'woocommerce-partial-payment');
	
		}
	
		echo '<div class="wpp-deposit-options-wrapper">';
	
		woocommerce_wp_select( array( 'id' => '_enable_deposit_options', 'label' => __('Enable Deposit Options', 'woocommerce-partial-payment'), 'description' => __('Overrides site-wide deposit options', 'woocommerce-partial-payment'), 'options' => array('yes' => __('Yes', 'woocommerce-partial-payment'), 'no' => __('No', 'woocommerce-partial-payment')) ) );
	
		if ( 'percent' === $deposit_price_base ) {
	
			woocommerce_wp_text_input( array( 'id' => '_deposit_price', 'label' => __('Product Deposit Value <span class="prcnt">(%)</span><span class="curs" style="display: none;">('.get_woocommerce_currency_symbol().')</span>', 'woocommerce-partial-payment'), 'placeholder' => __('0', 'woocommerce-partial-payment'), 'description' => $descr ) );
	
		} else {
	
			woocommerce_wp_text_input( array( 'id' => '_deposit_price', 'label' => __('Product Deposit Value <span class="prcnt" style="display: none;">(%)</span><span class="curs">('.get_woocommerce_currency_symbol().')</span>', 'woocommerce-partial-payment'), 'placeholder' => __('0', 'woocommerce-partial-payment'), 'description' => $descr ) );
	
		}
		
		woocommerce_wp_select( array( 'id' => '_deposit_type', 'label' => __('Deposit percent or flat', 'woocommerce-partial-payment'), 'options' => array('percent' => 'Percentage of each quantity', 'float_flat' => 'Flat amount per product * quantity'), 'value' => $deposit_price_base ) );
	
		echo '</div>';
	
	}	
	
	
	/**
	 * Saves individual product deposit options
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function save_deposit_price( $post_id ) {
	
		if ( isset( $_POST['_enable_deposit_options'] ) && ( 'no' === $_POST['_enable_deposit_options'] ) ) {
	
			update_post_meta($post_id, '_enable_deposit_options', 'no');
	
		} else {
	
			update_post_meta($post_id, '_enable_deposit_options', 'yes');
	
		}
	
		if ( isset( $_POST['_deposit_price'] ) ) {
	
			update_post_meta( $post_id, '_deposit_price', sanitize_text_field( $_POST['_deposit_price'] ) );
	
		}

		if ( isset( $_POST['_deposit_type'] ) )
			update_post_meta( $post_id, '_deposit_type', sanitize_text_field( $_POST['_deposit_type'] ) );
	
		if ( isset( $_POST['_deposit_price_percent'] ) ) {
	
			update_post_meta( $post_id, '_deposit_price_percent', sanitize_text_field( $_POST['_deposit_price_percent'] ) );
	
		} else {
	
			update_post_meta( $post_id, '_deposit_price_percent', false);
	
		}
	
	}

	
	/**
	 * Saves _deposit_paid value, deposit records, when editing order from WP admin
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function wpp_save_deposit_value( $order_id ) {
		 
		//make sure it's not an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		 
		//verify that data was received from our form, not from somewhere else
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) || !wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) )
			return;
		 
		//check permissions
		if ( !current_user_can( 'edit_post', $order_id ) )
			return;
		 
		$deposit_paid = 0;
		 
		//process deposit records
		$deposit_ids = array();
		 
		if ( ! empty( $_POST['deposit-ID'] ) )
			$deposit_ids = $_POST['deposit-ID'];
		 
		//remove extra deposit records
		$existing_deposit_ids = $this->get_deposit_records( $order_id, true );
		 
		$to_remove = array_diff( $existing_deposit_ids, $deposit_ids );
		 
		if ( ! empty( $to_remove ) )
			$this->remove_deposit_records( $to_remove );
		 
		//save deposit records for this order
		if ( ! empty( $deposit_ids ) ) {
	
			foreach ( $deposit_ids as $deposit_id ) {
	
				$this->update_deposit_record( array( 'order_id' => $order_id, 'sum' => floatval( $_POST[ 'deposit-sum' ][ $deposit_id ] ), 'payment_method' => $_POST[ 'payment-method' ][ $deposit_id ], 'payment_status' => $_POST[ 'payment-status' ][ $deposit_id ], 'deposit_id' => $deposit_id ) );
				 
				//if ( 'completed' === $_POST[ 'payment-status' ][ $deposit_id ] )	//TODO: this line was commented out as we treat "pending" payments as "completed" when calculate _order_remaining_total value 
					$deposit_paid += floatval( $_POST[ 'deposit-sum' ][ $deposit_id ] );
				 
			}
	
		}
		 
		update_post_meta( $order_id, '_deposit_paid', $deposit_paid );
		 
		$order = new WC_Order( $order_id );
		 
		//recalculate _order_remaining_total
		update_post_meta( $order_id, '_order_remaining_total', $order->get_total() - $deposit_paid );
		 
	}

	
	/**
	 * Adds "Deposits" section to "edit order" page
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function deposit_admin_order_totals_after_shipping( $order_id ) {
	
		?>
	
				<div class="clear"></div>
	
			</div>
	
			<div class="totals_group">
	
				<h4><?php printf( __( 'Deposit (%s)', 'woocommerce-partial-payment'), get_woocommerce_currency_symbol() ); ?></h4>
	    	
				<ul class="deposit-paid">
	    		
					<li style="font-weight:bold;color:green;"><?php
							
						$deposit_paid = get_post_meta( $order_id, '_deposit_paid', true );
						
						echo woocommerce_price( $deposit_paid );
						
					?></li>
	    		
				</ul>
	    
	    		<?php 
	    		
	    			$order_remaining_total = get_post_meta($order_id, '_order_remaining_total', true);
	    		
	    		?>
	    
				<h4><?php _e('Remaining Payments', 'woocommerce-partial-payment'); ?></h4>
	
				<ul class="totals">
	
					<li style="font-weight:bold;color:<?php echo ( $order_remaining_total > 0 ) ? 'red' : 'green'; ?>;"><?php
						
							echo woocommerce_price( $order_remaining_total );
	
					?></li>
	
				</ul>
	
				<div class="clear"></div>
	
			<?php
	        
				//show deposits that are already paid
				$deposit_records = $this->get_deposit_records( $order_id );
					
			?>
					
				<h4><?php _e('Deposits Paid', 'woocommerce-partial-payment'); ?></h4>
					
				<ul id="paid-deposits">
						
					<?php
							
						if ( ! empty( $deposit_records ) ) {
								
							foreach ( $deposit_records as $deposit ) {
										
					?>

								<li id="deposit-<?php echo $deposit->ID; ?>">

									<input type="hidden" name="deposit-ID[]" value="<?php echo $deposit->ID; ?>" />
											
									<span class="deposit-sum"><?php echo __('Amount: ', 'woocommerce-partial-payment'), woocommerce_price( $deposit->sum ); ?></span>
									<input type="hidden" name="deposit-sum[<?php echo $deposit->ID; ?>]" value="<?php echo $deposit->sum; ?>" />

									<select class="payment-status" name="payment-status[<?php echo $deposit->ID; ?>]">
										<option<?php if ( 'pending' === $deposit->payment_status ) echo ' selected="selected"' ?> value="pending"><?php _e('Pending', 'woocommerce-partial-payment'); ?></option>
										<option<?php if ( 'completed' === $deposit->payment_status ) echo ' selected="selected"' ?> value="completed"><?php _e('Completed', 'woocommerce-partial-payment'); ?></option>
									</select>
												
									<a href="#" class="remove-deposit">x</a>
												
									<div class="clear"></div>
												
									<span class="deposit-date"><?php echo date_i18n( 'd M, Y @ H:i', strtotime( $deposit->payment_date ) ); ?></span> via <span class="deposit-payment-method"><?php echo $deposit->payment_method; ?></span>
		
									<input type="hidden" name="payment-method[<?php echo $deposit->ID; ?>]" value="<?php echo $deposit->payment_method; ?>" />
												
								</li>
										
					<?php 
								
							}
									
						}
							
					?>
						
				</ul>
						
				<a href="#" id="new-deposit"><?php _e('Add deposit record', 'woocommerce-partial-payment'); ?></a>
						
				<div id="new-deposit-wrapper">
						
					<p>
							
						<label for="new-deposit-amount"><?php _e('Amount: ', 'woocommerce-partial-payment'); ?></label>
						<input type="number" id="new-deposit-amount" value="0" step="any" />
								
					</p>	
							
					<p>
							
						<label for="new-deposit-status"><?php _e('Status: ', 'woocommerce-partial-payment'); ?></label>
						<select id="new-deposit-status">
							<option value="pending"><?php _e('Pending', 'woocommerce-partial-payment'); ?></option>
							<option value="completed"><?php _e('Completed', 'woocommerce-partial-payment'); ?></option>
						</select>
								
					</p>	
							
					<p>
							
						<label for="new-deposit-payment-method"><?php _e('Payment Method: ', 'woocommerce-partial-payment'); ?></label>
						<select id="new-deposit-payment-method">
							<?php
		
								global $woocommerce;
										
								$payment_methods = $woocommerce->payment_gateways->get_available_payment_gateways();
									
								if ( ! empty( $payment_methods ) ) {
											
									foreach ( $payment_methods as $method ) {
										
										?>
									
											<option value="<?php echo $method->title; ?>"><?php echo $method->title; ?></option>
													
										<?php
												
									}
		
								}	
												
							?>
						</select>
								
					</p>
							
					<p><a href="#" id="add-new-deposit"><?php _e('Add new deposit', 'woocommerce-partial-payment'); ?></a></p>
						
					<input type="hidden" id="add-new-deposit-security" name="add-new-deposit-security" value="<?php echo wp_create_nonce( 'new-deposit' ); ?>" />
						
				</div>
						
		<?php
	
	}
		
	
	/**
	 * Handles ajax request to create new deposit record
	 *
	 * @return void
	 */
	public function wpp_ajax_add_new_deposit_record() {
	
		check_ajax_referer( 'new-deposit', 'security' );
	
		$payment_status = ( isset( $_REQUEST['new_deposit_status'] ) && 'pending' === $_REQUEST['new_deposit_status'] ) ? 'pending': 'completed';
	
		$new_deposit_id = $this->update_deposit_record( array( 'order_id' => intval( $_REQUEST['order_id'] ), 'sum' => floatval( $_REQUEST['new_deposit_sum'] ), 'payment_method' => sanitize_text_field( $_REQUEST['new_deposit_payment_method'] ), 'payment_status' => $payment_status ) );
	
		if ( ! empty( $new_deposit_id ) ) {
				
			$deposit = $this->get_deposit_record_by_id( $new_deposit_id );
	
			?>
		
				<li id="deposit-<?php echo $deposit->ID; ?>">
												
					<input type="hidden" name="deposit-ID[]" value="<?php echo $deposit->ID; ?>" />
												
					<span class="deposit-sum"><?php echo __('Amount: ', 'woocommerce-partial-payment'), woocommerce_price( $deposit->sum ); ?></span>
					<input type="hidden" name="deposit-sum[<?php echo $deposit->ID; ?>]" value="<?php echo $deposit->sum; ?>" />
												
					<select class="payment-status" name="payment-status[<?php echo $deposit->ID; ?>]">
						<option<?php if ( 'pending' === $deposit->payment_status ) echo ' selected="selected"' ?> value="pending"><?php _e('Pending', 'woocommerce-partial-payment'); ?></option>
						<option<?php if ( 'completed' === $deposit->payment_status ) echo ' selected="selected"' ?> value="completed"><?php _e('Completed', 'woocommerce-partial-payment'); ?></option>
					</select>
													
					<a href="#" class="remove-deposit">x</a>
													
					<div class="clear"></div>
													
					<span class="deposit-date"><?php echo date_i18n( 'd M, Y @ H:i', strtotime( $deposit->payment_date ) ); ?></span> via <span class="deposit-payment-method"><?php echo $deposit->payment_method; ?></span>
			
					<input type="hidden" name="payment-method[<?php echo $deposit->ID; ?>]" value="<?php echo $deposit->payment_method; ?>" />
													
				</li>
				
			<?php 
			
		} else {
				
			_e('Error saving deposit to database!', 'woocommerce-partial-payment');
				
		}	
				
		die;
			
	}
	
	
	/**
	 * Removes deposit records on order removal
	 * 
	 * @param int $post_id
	 * 
	 * @return void
	 */
	public function wpp_remove_deposits_on_order_removal( $post_id ) {
		
		if ( 'shop_order' == get_post_type( $post_id ) ) {

			$this->remove_deposit_records_by_order_id( $post_id );
		
		}
		
	}
	
	
	/**
	 * Changes all of the order deposits statuses to "Completed" if order was marked as completed.
	 * 
	 * @param int $order_id
	 * 
	 * @return void
	 */
	public function wpp_mark_all_order_deposits_as_completed( $order_id ) {
		
		global $wpdb;
		
		$result = $wpdb->update($wpdb->prefix . 'wpp_deposit_records',
				array( 'payment_status' => 'completed' ),
				array( 'order_id' => intval( $order_id ) ),
				array( '%s' ),
				array( '%d' )
		);
		
		//change previous payment statuses in case order status was changed on "edit order" page
		if ( isset( $_POST['payment-status'] ) )
			foreach ( $_POST['payment-status'] as &$status )
				$status = 'completed';
		
	}


	/**
	 * Saves or updates (if record already exists) deposit record to DB
	 *
	 * @param array $args. $defaults = array( 'order_id' => 0, 'sum' => 0, 'payment_method' => '', 'payment_status' => 'pending', 'deposit_id' => false)
	 *
	 * @return int deposit record ID on successful insert/update | bool false on error
	 */
	private function update_deposit_record( $args = array() ) {
	
		global $wpdb;
		
		$return = false;
	
		//defaults
		$defaults = array( 'order_id' => 0, 'sum' => 0, 'payment_method' => '', 'payment_status' => 'pending', 'deposit_id' => false);
	
		extract( array_merge( $defaults, $args ) );
	
		//if deposit record ID is not provided or provided record ID does not exist, let's create new record
		$exists = false;
		
		if ( ! empty( $deposit_id ) ) {
	
			$exists =$wpdb->get_var('SELECT ID from ' . $wpdb->prefix . 'wpp_deposit_records WHERE ID = ' . $deposit_id );
				
		}
		
		//if $deposit_id wasn't set or wrong $deposit_id was provided, let's create new deposit record
		if ( empty( $deposit_id ) || empty( $exists ) ) {
				
			if ( ! empty( $order_id ) && ! empty( $sum ) ) {
					
				$result = $wpdb->insert($wpdb->prefix . 'wpp_deposit_records',
						array(
								'order_id' => $order_id,
								'sum' => floatval($sum),
								'payment_method' => $payment_method,
								'payment_status' => $payment_status
						),
						array( '%d', '%f', '%s', '%s' )
				);
	
				if ( ! empty( $result ) ) {
	
					$return = $wpdb->get_var('SELECT ID FROM ' . $wpdb->prefix . 'wpp_deposit_records' . ' WHERE order_id = ' . $order_id . ' AND sum = "' . floatval($sum) . '" AND payment_method = "' . $payment_method . '" AND payment_status = "' . $payment_status . '" ORDER BY ID ASC LIMIT 0,1');
		
				}
	
			} else {
	
				error_log( 'Not enough arguments supplied to a method!' );
		
			}
					
		} else {	//if record already exists, let's update it
		
			$result = $wpdb->update($wpdb->prefix . 'wpp_deposit_records',
					array( 'payment_status' => $payment_status ),
					array( 'ID' => $deposit_id ),
					array( '%s' ),
					array( '%d' )
			);
				
			if ( ! empty( $result ) ) {
					
				$return = $deposit_id;
						
			}
					
		}
	
		return $return;
		
	}

	
	/**
	 * Returns deposit records or deposit records ids for given order_id
	 *
	 * @param int $order_id
	 * @param bool $ids_only
	 *
	 * @return array $deposit_records OR array $deposit_records_ids
	 */
	private function get_deposit_records( $order_id, $ids_only = false ) {
	
		global $wpdb;
	
		$deposit_records = $wpdb->get_results( 'SELECT * from ' . $wpdb->prefix . 'wpp_deposit_records WHERE order_id = ' . intval( $order_id ) );
	
		if ( true === $ids_only ) {
				
			$deposit_records_ids = array();
				
			if ( ! empty( $deposit_records ) ) {
	
				foreach ( $deposit_records as $deposit_record ) {
						
					$deposit_records_ids[] = $deposit_record->ID;
						
				}
	
			}
				
			return $deposit_records_ids;
				
		}
	
		return $deposit_records;
	
	}

	
	/**
	 * Returns deposit record from DB
	 *
	 * @param int $deposit_id
	 *
	 * @return obj $deposit_record | NULL
	 */
	private function get_deposit_record_by_id( $deposit_id ) {
	
		global $wpdb;
	
		return $wpdb->get_row( 'SELECT * from ' . $wpdb->prefix . 'wpp_deposit_records WHERE ID = ' . intval( $deposit_id ), OBJECT );
	
	}

	
	/**
	 * Removes deposit records for provided deposit ids
	 *
	 * @param array|int $deposit_ids
	 *
	 * @return void
	 */
	private function remove_deposit_records( $deposit_ids = array() ) {
	
		if ( ! empty( $deposit_ids ) ) {
				
			//security data type check
			foreach ( $deposit_ids as $id ) {
	
				if ( $id != intval( $id ) )
					return false;
					
			}
	
			global $wpdb;
					
			$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'wpp_deposit_records WHERE ID IN (' . join(',', $deposit_ids) . ')' );
					
		}
		
	}
		
	
	/**
	 * Removes deposit records by given order id
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	private function remove_deposit_records_by_order_id( $order_id ) {
	
		if ( ! empty( $order_id ) && ( intval( $order_id ) == $order_id ) ) {
	
			global $wpdb;
	
			$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'wpp_deposit_records WHERE order_id = ' . $order_id );
	
		}
	
	}


	/**
	 * Calculates deposit value for the case when deposit is set to be % of original price
	 * 
	 * @param unknown_type $num_amount
	 * @param unknown_type $num_total
	 * 
	 * @return float deposit_price
	 */
	public static function percent($num_amount, $num_total) {

		if ($num_amount < 1)
			$num_amount = 1;

		if ($num_amount > 100)
			$num_amount = 100;

		$count1 = $num_total/100;

		$count2 = $count1 * $num_amount;
        
		//if ( ( $count2 / 1 ) != intval( $count2 / 1 ) )
			//$count2 = number_format($count2, 2);

		return $count2;

	}

    /**
     * Checks if deposit is optional or required
     * 
     * @return string ( 'required' | 'optional' )
     */
    private function is_deposit_optional_or_required() {
    	
    	$req = get_option('deposit_price_option', 'full');
    	
    	if ( 'depo' === $req ) {
    	
    		return 'required';
    	
    	} else {
    	
    		return 'optional';
    	
    	}
    	
    }
    
    /**
     * Returns deposit_calculation_rule_text value
     * 
     * @param int $post_id optional
     * @param array $product_data optional
     * 
     * @return string | bool false if deposit can't be calculated
     */
    private function get_deposit_calculation_rule_text( $post_id = false, $product_data = array() ) {
    	
    	//if $post_id is not supplied, use site-wide deposit options
    	$value = get_post_meta($post_id, '_deposit_price', true);

		if ( is_numeric( $value ) && ( $value > 0 ) ) {

			$percent = get_post_meta($post_id, '_deposit_type', true);

		} elseif ( 'yes' === get_option('site_wide_deposit_option') ) {

			$percent = get_option('deposit_price_base', 'percent');

			$value = get_option('deposit_price_value', '');

		} else {

			return false;

		}
    	
    	if ( 'percent' === $percent ) {
    	
    		$deposit_calculation_rule_text = $value . '%';
    	
    	} else {
    	
    		$qty = 1;
    		
    		if ( ! empty( $product_data['quantity'] ) )
    			$qty = $product_data['quantity'];
    		
    		$deposit_calculation_rule_text = woocommerce_price( $value * $qty );
    	
    	}
    	
    	return $deposit_calculation_rule_text;
    	
    }

    /**
     * Replaces label shortcodes with values
     * 
     * @param string $label
     * @param int $post_id optional
     * @param array $product_data optional
     * 
     * @return string
     */
    private function parse_label_shortcodes( $label, $post_id = false, $product_data = array() ) {
    	
    	$deposit_calculation_rule_text = $this->get_deposit_calculation_rule_text( $post_id, $product_data );
    	
    	if ( ! empty( $deposit_calculation_rule_text ) ) {
    		
    		$label = str_replace( '[deposit_calculation_rule_text]', $deposit_calculation_rule_text, $label );
    		
    	}
    	
    	$dep_max_min_value = get_option('deposit_price_max_value');
    	
    	if ( empty( $dep_max_min_value ) )
    		$dep_max_min_value = get_option('deposit_price_min_value');
    	
    	if ( ! empty( $dep_max_min_value ) ) {
    		
    		$labels = $this->get_text_labels();
    	
    		$label = str_replace( '[min_and_max_deposit_option_text]', $labels['min_and_max_deposit_option_text'], $label );
    	
    	} else {
    	
    		$label = str_replace( '[min_and_max_deposit_option_text]', '', $label );
    		 
    	}
    	
    	return $label;
    	
    }
    
    /**
     * Checks whether cart contains items with individual deposit options that are different from site-wide deposit options
     * 
     * @param obj $cart_object
     * 
     * @return bool $individual
     */
    private function compare_cart_items_depo_options_with_site_wide_options( $cart_object ) {
    	
    	$individual = false;
    	
    	if ( ! empty( $cart_object->cart_contents ) ) {
    	
	    	$site_wide_deposit_option = get_option('site_wide_deposit_option');
	    	
	    	$site_wide_deposit_type = get_option('deposit_price_base', 'percent');
	    	 
	    	$site_wide_deposit_amount = get_option('deposit_price_value', '');
	    	
	    	//check if none of the products in the cart have overrides for deposit type, deposit amount or have ‘enable deposit options’ turned off
	    	foreach ( $cart_object->cart_contents as $item ) {
	    		
	    		//for composite products deposit options are defined by parent product
	    		if ( ! empty( $item['composite_parent'] ) )
	    			continue;
	    		 
	    		$deposits_enabled = get_post_meta( $item['product_id'], '_enable_deposit_options', true );
	    		 
	    		if ( 'no' === $deposits_enabled ) {
	    	
	    			$individual = true;
	    	
	    			break;
	    	
	    		} else {
	    	
	    			$product_deposit_amount = get_post_meta( $item['product_id'], '_deposit_price', true );

	    			if ( ! empty( $product_deposit_amount ) ) {
	    					
	    				//still there is a chance that individual deposit options match site-wide deposit options
	    				if ( 'yes' === $site_wide_deposit_option ) {
	    	
	    					$product_deposit_type = get_post_meta( $item['product_id'], '_deposit_type', true );
	    	
	    					if ( ( $product_deposit_amount == $site_wide_deposit_amount ) && ( $product_deposit_type == $site_wide_deposit_type ) )
	    						continue;
	    						
	    				}
	    					
	    				$individual = true;
	    					
	    				break;
	    					
	    			}
	    	
	    		}
	    		 
	    	}
	    	
    	}
    	
    	return $individual;
    	
    }
	
    /**
     * Compares deposit value of the product + deposits for items that are already in the cart to min and max deposit values from plugin settings 
     * 
     * @param int $product_id
     * @param float $deposit_value
     * @param float $full_product_price
     * @param int $quantity
     * 
     * @return float $deposit_value
     */
    public static function wpp_compare_to_min_and_max_deposit_options( $product_id, $deposit_value, $full_product_price, $quantity = 1 ) {
    	
    	global $woocommerce;
    	
    	//get the deposit value for all the cart items
    	$total_deposit = isset( $woocommerce->cart->cart_contents_total_deposit ) ? $woocommerce->cart->cart_contents_total_deposit : 0;
    	
    	$min_deposit = floatval( get_option('deposit_price_min_value') );
    	
    	$max_deposit = floatval( get_option('deposit_price_max_value') );
    	
    	$deposit_price = $deposit_value * $quantity;
    	
    	if ( $total_deposit + $deposit_price < $min_deposit ) {
    		
    		$deposit_value = ( $min_deposit - $total_deposit ) / $quantity;
    	
    	}
    	
    	if ( ! empty( $max_deposit ) && ( $total_deposit + $deposit_price > $max_deposit ) ) {
    		
    		$deposit_value = ( $max_deposit - $total_deposit ) / $quantity;
    	
    	}
    	
    	//make sure that deposit_value is not bigger then full product price
    	if ( $deposit_value > $full_product_price )
    		$deposit_value = $full_product_price;
    	
    	$_prod = get_product( $product_id );
    	 
    	$_prod->set_price( $deposit_value * $quantity );
    	
    	//extract taxes from deposit_value as tax will be added later and this will change order total, so it might become > than max. deposit option
    	if ( $_prod->is_taxable() && ( 'no' === get_option('woocommerce_prices_include_tax') ) ) {
    		 
    		$_tax = new WC_Tax();
    		 
    		$tax_rates = $_tax->get_shop_base_rate( $_prod->tax_class );
    		 
    		$taxes = $_tax->calc_tax( $deposit_value * $quantity, $tax_rates, true );
    		 
    		$tax_amount = $_tax->get_tax_total( $taxes );
    		
    		$decimal_sep = wp_specialchars_decode( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ), ENT_QUOTES );
    		
    		$thousands_sep = wp_specialchars_decode( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ), ENT_QUOTES );
    			
    		$tax_amount = floatval( str_replace( array( $thousands_sep, $decimal_sep ), array( '', '.' ), $tax_amount ) );
    		
    		$deposit_value = round( ( $deposit_value * $quantity - $tax_amount ) / $quantity, 2 );

    	}
    	
    	//and not less than 0
    	if ( $deposit_value < 0 )
    		$deposit_value = 0;
    	
    	return $deposit_value;
    	
    }
    
    /**
     * Saves deposit value in the cart item data
     * 
     * @param array $cart_item_data
     * @param int $product_id
     * @param int|string $variation_id (empty string if it's not a product variation)
     * 
     * @return array $cart_item_data
     */
	public static function wpp_save_deposit_in_the_cart( $cart_item_data, $product_id, $variation_id ) {
		
		global $woocommerce;
		
		$post_id = empty( $variation_id ) ? $product_id : $variation_id;
		
		$_prod = get_product( $post_id );
		
		//composite products plugin compatibility
		if ( ! empty( $cart_item_data['composite_children'] ) && ( 'yes' === $cart_item_data['data']->per_product_pricing ) ) {	//deposit is calculated only for parent product
			
			$reg_price = 0;
			
			//let's calculate composite product price
			foreach ( $cart_item_data['composite_data'] as $child ) {
				
				$child_product = get_product( $child['product_id'] );
				
				$child_product->set_price( $child['price'] );
				
				$reg_price += $child_product->get_price_including_tax( $child['quantity'] );
				
			}	
			
		} elseif ( ! empty( $cart_item_data['composite_parent'] ) ) {		//for composite children deposit shouldn't be paid at all
			
			$cart_item_data['deposit_value'] = 0;
        
			return $cart_item_data;
			
		} elseif ( ! empty( $cart_item_data['line_total'] ) ) {	//if item is already in the cart, let's get current price, in case discount coupons were used
			
			$reg_price = $cart_item_data['line_total'];
			
			$decimal_sep = wp_specialchars_decode( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ), ENT_QUOTES );
				
			$thousands_sep = wp_specialchars_decode( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ), ENT_QUOTES );
			
			$reg_price = floatval( str_replace( array( $thousands_sep, $decimal_sep ), array( '', '.' ), $reg_price ) );
			
			//add taxes ( if any ) to get final product price for calculating deposit values
			$tax = 0;
			
			if ( ! empty( $cart_item_data['line_tax'] ) ) {
				
				$tax = $cart_item_data['line_tax'];
				
				$tax = floatval( str_replace( array( $thousands_sep, $decimal_sep ), array( '', '.' ), $tax ) );
				
			}
			
			$reg_price += $tax;
			
			$reg_price = $reg_price / $cart_item_data['quantity'];
			
		} else {
			
			$reg_price = $_prod->get_price_including_tax( $cart_item_data['quantity'] );
			
		}
		
		//if deposits are switched off for that product, let's charge full price
		$deposits_enabled = get_post_meta( $product_id, '_enable_deposit_options', true );
		
		if ( 'no' === $deposits_enabled ) {

			$deposit_value = $reg_price;
			
		} else {
		
			$deposit_value = get_post_meta($product_id, '_deposit_price', true);
	
	        if ( is_numeric( $deposit_value ) && $deposit_value > 0 ) {
	        	
	            if ( ! $percent = get_post_meta($product_id, '_deposit_type', true) )
	                $percent = get_option('deposit_price_base', 'percent');
	            
	        } elseif ( 'yes' === get_option('site_wide_deposit_option') ) {
	        	
	            $percent = get_option('deposit_price_base', 'percent');
	            
	            $deposit_value = get_option('deposit_price_value', '');
	            
	        } else {
	        	
	        	$percent = false;
	        	
	        	$deposit_value = $reg_price;
	        	
	        }
	        
	        if ( 'percent' === $percent ) {
	            
	            $deposit_value = WoocommercePartialPayment::percent( $deposit_value, $reg_price );
	
	        }
	        
	        //compare deposit value + deposits for items already in the cart to min and max deposit values from plugin settings 
	        $deposit_value = WoocommercePartialPayment::wpp_compare_to_min_and_max_deposit_options( $product_id, $deposit_value, $reg_price, $cart_item_data['quantity'] );
	        
		}
        
        $cart_item_data['deposit_value'] = $deposit_value;
        
		return $cart_item_data;
		
	}
	

	/**
	 * Adds deposit order_id to orders list
	 *  
	 * @param int $order_id
	 * @param bool $par default false
	 * 
	 * @return void
	 */
    public function order_to_list( $order_id, $par = false ){
    	
        global $woocommerce;
        
        if ( isset( $woocommerce->session->partial_payment ) && ( 'deposit' === $woocommerce->session->partial_payment ) ) {

	        if ( is_numeric( $order_id ) ) {
	
	            if ( $orders = get_option('woocommerce_partial_payments_list') ) {
	
	                $orders[] = $order_id;
	
	            } else {
	
	                $orders = array( $order_id );
	                
	            }
	
	            update_option('woocommerce_partial_payments_list', $orders);
	
	        }

        }

    }
	
	
    /**
     * Adds remaining payment data to email template
     *
     * @param obj $order
     * @param bool $for_admin
     *
     * @return void
     */
    public function email_remaining_payment_details(  $order, $for_admin = false ) {
    	
    	if ( ! empty( $order->order_custom_fields['_orig_order'][0] ) ) {
    	
    		?>
    		
    			<h4 style="color:red;"><?php _e('Follow Up Payment', 'woocommerce-partial-payment'); ?></h4>
    		
    		<?php 
    	
    	}
    	
    }
    
    
    /**
     * Adds deposit data to email template
     * 
     * @param obj $order
     * @param bool $for_admin
     * 
     * @return void
     */
    public function email_total_table( $order, $for_admin = false ) {
    	
        $order_remaining_total = get_post_meta($order->id, '_order_remaining_total', true);
        
        if ( empty( $order_remaining_total ) ) {
        	
        	$order_remaining_total = $order->order_total - $order->order_custom_fields['_deposit_paid'][0];
        	
        }
        
        if ( ! empty( $order->order_custom_fields['_orig_order'][0] ) ) {
        
        	$order_remaining_total -= $order->order_total;
        
        }
        
	?>

			<div class="clear"></div>

		</div>

		<div class="totals_group">

			<?php if ( intval( $order_remaining_total ) > 0 ) { ?>

			<h4><?php _e('Remaining Payments', 'woocommerce-partial-payment'); ?></h4>

			<?php } ?>
			
			<ul class="totals">

				<li style="font-weight:bold;color:<?php if ( intval( $order_remaining_total ) > 0 ) { echo 'red'; } else { echo 'green'; } ?>;">

					<?php 

						if ( ( intval( $order_remaining_total ) > 0 ) || ( ! empty( $order->order_custom_fields['_orig_order'][0] ) ) ) {

					?>

							<label><?php _e('Remaining amount:', 'woocommerce-partial-payment'); ?></label>

					<?php
			
							echo woocommerce_price( $order_remaining_total );
						
						}

					?>

					<p>

						<?php
	
							//admins get only link to "edit order" page
							if ( true === $for_admin ) {

								$view_order_url = add_query_arg( array('post' => $order->id, 'action' => 'edit'), admin_url( 'post.php' ) );
				                		
							} else {

								$view_order_url = add_query_arg( 'order', $order->id, get_permalink( woocommerce_get_page_id( 'view_order' ) ) );

							}

							if ( ( true === $for_admin ) || ( intval( $order_remaining_total ) > 0 ) ) {
							
								?>
		
									<a href="<?php echo $view_order_url; ?>" target="_blank" style="color: green;"><?php _e('View order', 'woocommerce-partial-payment'); ?></a>
		
								<?php
						
							}

							//"Pay Now" link is only for customers
							if ( ( false === $for_admin ) && ( intval( $order_remaining_total ) > 0 ) ) {

						?>

							<span style="color: #000;">|</span> <a href="<?php echo $order->get_checkout_payment_url(); ?>" target="_blank" style="color: red;"><?php _e('Pay Now', 'woocommerce-partial-payment'); ?></a>

						<?php

							}
			                	
						?>	

					</p>	

				</li>
			
			</ul>
			
			<div class="clear"></div>

			<?php if ( intval( $order_remaining_total ) > 0 ) { ?>
			
				<div id="wpp-instructions-for-paying"><?php echo get_option('deposit_price_payment_note', ''); ?></div>
			
			<?php } ?>

	<?php

	}
    

	/**
	 * Forces to show payment buttons for orders with only deposit paid (My Account page)
	 * 
	 * @param array $statuses
	 * @param object $order
	 * 
	 * @return array $statuses
	 */
	public function wpp_show_payment_buttons_on_my_account( $statuses, $order ) {

		if ( isset( $order->order_custom_fields['_order_remaining_total'] ) && ( $order->order_custom_fields['_order_remaining_total'][0] > 0 ) ) {

			//orders with only deposit paid have "processing" or "on-hold" status
			$statuses[] = 'processing';
			$statuses[] = 'on-hold';
	
		}
	
		return $statuses;

	}


	/**
	 * Changes order status to "Pending" when user tries to pay the rest after deposit, deducts _deposit_value from item prices
	 * 
	 * @return void
	 */
	private function prepare_follow_up_payment() {

		global $woocommerce;

		if ( isset( $_POST['woocommerce_pay'] ) && $woocommerce->verify_nonce( 'pay' ) ) {

			ob_start();
    	
			// Pay for existing order
			$order_key 	= urldecode( $_GET['order'] );
			$order_id 	= absint( $_GET['order_id'] );
			$order 		= new WC_Order( $order_id );
    		
			//only applies to orders where only deposit was paid
			if ( isset( $order->order_custom_fields['_order_remaining_total'] ) && ( $order->order_custom_fields['_order_remaining_total'] > 0 ) ) {

				$order->update_status( 'pending' );

				$order_items = $order->get_items();

				if ( ! empty( $order_items ) ) {

					//create Woocommerce_Cart_Extended object to place there all the products from the order
					$remaining_balance_cart = new Woocommerce_Cart_Extended();

					//save original order to be restored after remaining balance is paid
					update_post_meta( $order->id, '_orig_order', $order );

					update_post_meta( $order->id, '_orig_order_items', $order_items );

					//loop through order items and add products to $remaining_balance_cart, preserving original order_item values
					foreach ( $order_items as $id => $item ) {

						$product = $order->get_product_from_item( $item );

						$variation_id = ( isset( $product->variation_id ) ) ? $product->variation_id : '';

						//remaining price equals full price - deposit price
						$remaining_price = floatval( $item['line_total'] ) - floatval( $item['item_meta']['_deposit_value'][0] );

						$remaining_balance_cart->add_to_cart( $product->id, $item['qty'], $variation_id, '', array( 'deposit_value' => $remaining_price, 'item_id' => $id ) );

					}

					//replace regular prices with deposit values for deposit total calculations
					if ( ! empty( $remaining_balance_cart->cart_contents ) ) {

						foreach ( $remaining_balance_cart->cart_contents as $item ) {

							$item['data']->price = $item['data']->deposit_price = ( empty( $item['deposit_value'] ) ) ? 0 : $item['deposit_value'];

						}

					}	

					$remaining_balance_cart->calculate_totals();

					define('WOOCOMMERCE_CHECKOUT', true);

					//exclude shipping from calculations as if it's required, it was paid already anyway
					add_filter('woocommerce_cart_needs_shipping', array('WoocommercePartialPayment', 'wpp_exclude_shipping'), 10, 1);

					//calculate totals, based on remaining prices
					$remaining_balance_cart->calculate_totals();

					remove_filter('woocommerce_cart_needs_shipping', array('WoocommercePartialPayment', 'wpp_exclude_shipping'));

					//apply calculated values to current order
					if ( ! empty( $remaining_balance_cart->cart_contents ) ) {

						foreach ( $remaining_balance_cart->cart_contents as $product ) {

							woocommerce_update_order_item_meta( $product['item_id'], '_line_total', $product['line_total'] );
							woocommerce_update_order_item_meta( $product['item_id'], '_line_tax', $product['line_tax'] );
							woocommerce_update_order_item_meta( $product['item_id'], '_line_subtotal', $product['line_subtotal'] );
							woocommerce_update_order_item_meta( $product['item_id'], '_line_subtotal_tax', $product['line_subtotal_tax'] );

						}

					}

					update_post_meta( $order->id, '_order_total', $remaining_balance_cart->total );

					update_post_meta( $order->id, '_order_tax', $remaining_balance_cart->tax_total );

					update_post_meta( $order->id, '_order_shipping', 0 );

					update_post_meta( $order->id, '_order_shipping_tax', 0 );

				}

			}

		}	

	}


	/**
	 * Excludes shipping from cart totals calculation
	 * 
	 * @param bool $needs_shipping
	 * 
	 * @return bool false
	 */
	public static function wpp_exclude_shipping( $needs_shipping ) {

		return false;

	}

	/**
	 * Adds "Pay Now" button to "View Order" page if only deposit was paid
	 * 
	 * @param int $order_id
	 * 
	 * @return void
	 */
	public function wpp_add_pay_button_to_view_order( $order_id ) {

		$order = new WC_Order( $order_id );

		$deposit_records = $this->get_deposit_records( $order_id );
			
		$amount_paid = 0;
			
		$amount_pending = 0;
		
		$amount_due = ( empty( $order->order_custom_fields['_order_remaining_total'][0] ) ) ? 0 : $order->order_custom_fields['_order_remaining_total'][0];
			
		if ( ! empty( $deposit_records ) ) {
			
		?>
				
			<h2><?php _e( 'Payment Details', 'woocommerce-partial-payment' ); ?></h2>
			
			<table class="wpp-payment-details">
				
				<thead>
					
					<tr>
						
						<td><?php _e( 'Date', 'woocommerce-partial-payment' ); ?></td>
							
						<td><?php _e( 'Amount', 'woocommerce-partial-payment' ); ?></td>
							
						<td><?php _e( 'Status', 'woocommerce-partial-payment' ); ?></td>
							
					</tr>
						
				</thead>
				
				<tbody>
					<?php
						
						$deposits_count = count( $deposit_records );
						
						for ( $i=0; $i < $deposits_count; ++$i ) {
								
							if ( 'pending' === $deposit_records[$i]->payment_status ) {
									
								$amount_pending += $deposit_records[$i]->sum;
									
							} else if ( 'completed' === $deposit_records[$i]->payment_status ) {
									
								$amount_paid += $deposit_records[$i]->sum;
									
							}
							
					?>
						
						<tr class="<?php echo ( $i % 2 ) ? 'even' : 'odd'; ?>">
							
							<td><?php echo date_i18n( 'M. d, Y', strtotime( $deposit_records[$i]->payment_date ) ); ?></td>
								
							<td><?php echo woocommerce_price( $deposit_records[$i]->sum ); ?></td>
								
							<td><?php echo ( 'pending' === $deposit_records[$i]->payment_status ) ? __( 'Pending', 'woocommerce-partial-payment' ) : __( 'Confirmed', 'woocommerce-partial-payment' ); ?></td>
								
						</tr>
							
					<?php
							
						}
							
					?>
				</tbody>
					
			</table>

		<?php
			
		}
			
		?>	
				
		<h2><?php _e( 'Payment Totals', 'woocommerce-partial-payment' ); ?></h2>
			
		<table class="wpp-payment-totals">
			
			<tr class="odd">
				
				<td class="row-title"><?php _e( 'Amount Paid', 'woocommerce-partial-payment' ); ?></td>
					
				<td><?php echo woocommerce_price( $amount_paid ); ?></td>
					
			</tr>
				
			<tr class="even">
				
				<td class="row-title wpp-amount-pending"><?php _e( 'Amount Pending', 'woocommerce-partial-payment' ); ?></td>
					
				<td><?php echo woocommerce_price( $amount_pending ); ?></td>
					
			</tr>
				
			<tr class="odd">
				
				<td class="row-title"><?php _e( 'Amount Due', 'woocommerce-partial-payment' ); ?></td>
					
				<td><?php echo woocommerce_price( $amount_due ); ?></td>
					
			</tr>
				
		</table>
		
	<?php
		
		if ( $amount_pending > 0 ) {
			
			$labels = $this->get_text_labels();
			
	?>
		
			<p class="wpp-pending-payments-note"><?php echo esc_html( $labels['view_order_pending_payments'] ); ?></p>
		
	<?php
			
		}
			
		if ( isset( $order->order_custom_fields['_order_remaining_total'] ) && ( $order->order_custom_fields['_order_remaining_total'][0] > 0 ) ) {
		
	?>
			
			<p><a href="<?php echo $order->get_checkout_payment_url(); ?>" class="button alt"><?php _e('Make a Payment', 'woocommerce-partial-payment'); ?></a></p>

			<?php 

		}

	}

} //end of WoocommercePartialPayment class

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	$woocommerce_partial_payment = new WoocommercePartialPayment();

}

