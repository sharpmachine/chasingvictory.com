<?php

/**
 * WC_Mailchimp_Integration class.
 *
 * http://apidocs.mailchimp.com/api/rtfm/campaignecommorderadd.func.php#campaignecommorderadd-v13
 */
class WC_Mailchimp_Integration {

	private $api_key;
	private $list;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $api_key, $list = false ) {

		if ( ! class_exists( 'MCAPI' ) )
			include_once('api/mailchimp/MCAPI.class.php');

		$this->api_key = $api_key;
		$this->list    = $list;

		add_action( 'init', array( $this, 'ecommerce360_set_cookies' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'ecommerce360_tracking' ) );
	}

	/**
	 * set_cookies function.
	 *
	 * @access public
	 * @return void
	 */
	public function ecommerce360_set_cookies() {

		$thirty_days = time() + 60 * 60 * 24 * 30;

		if ( isset( $_REQUEST['mc_cid'] ) ) {
			@setcookie( 'mailchimp_campaign_id', trim( $_REQUEST['mc_cid'] ), $thirty_days, '/' );
		}
		if ( isset( $_REQUEST['mc_eid'] ) ) {
			@setcookie( 'mailchimp_email_id', trim( $_REQUEST['mc_eid'] ), $thirty_days, '/' );
		}
	}

	/**
	 * ecommerce360_tracking function.
	 *
	 * @access public
	 * @param mixed $order_id
	 * @return void
	 */
	public function ecommerce360_tracking( $order_id ) {

		if ( empty( $_COOKIE['mailchimp_campaign_id'] ) || empty( $_COOKIE['mailchimp_email_id'] ) )
			return;

		// Get the order and output tracking code
		$order = new WC_Order( $order_id );

		$items = array();

		if ( $order->get_items() ) {
			foreach ( $order->get_items() as $item ) {
				$_product = $order->get_product_from_item( $item );

				$cats = wp_get_post_terms( $_product->id, 'product_cat', array( "fields" => "all" ) );

				$category_id = $category_name = '';

				if ( $cats ) {
					foreach ( $cats as $cat ) {
						$category_id   = $cat->term_id;
						$category_name = $cat->name;
						break;
					}
				}

				$items[] = array(
					'product_id'    => $_product->id,
					'sku'           => $_product->get_sku(),
					'product_name'  => $_product->get_title(),
					'category_id'   => $category_id,
					'category_name' => $category_name,
					'qty'           => $item['qty'],
					'cost'          => $order->get_item_total( $item )
				);
			}
		}

		$tracked_order = array(
			'id'          => $order_id,
			'campaign_id' => $_COOKIE['mailchimp_campaign_id'],
			'email_id'    => $_COOKIE['mailchimp_email_id'],
			'total'       => $order->get_total(),
			'shipping'    => $order->get_shipping(),
			'tax'         => $order->get_total_tax(),
			'store_id'    => substr( md5( site_url() ), 0, 20 ),
			'store_name'  => site_url(),
			'items'       => $items
		);

		$mailchimp = new MCAPI( $this->api_key );

		$mailchimp->campaignEcommOrderAdd( $tracked_order );
	}

	/**
	 * has_list function.
	 *
	 * @access public
	 * @return void
	 */
	public function has_list() {
		if ( $this->list )
			return true;
	}

	/**
	 * has_api_key function.
	 *
	 * @access public
	 * @return void
	 */
	public function has_api_key() {
		if ( $this->api_key )
			return true;
	}

	/**
	 * get_lists function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_lists() {
		if ( ! $mailchimp_lists = get_transient( 'wc_mc_list_' . md5( $this->api_key ) ) ) {

			$mailchimp_lists = array();
			$mailchimp       = new MCAPI( $this->api_key );
			$retval          = $mailchimp->lists();

			if ( $mailchimp->errorCode ) {

				echo '<div class="error"><p>' . sprintf( __( 'Unable to load lists() from MailChimp: (%s) %s', 'wc_subscribe_to_newsletter' ), $mailchimp->errorCode, $mailchimp->errorMessage ) . '</p></div>';

				return false;

			} else {
				foreach ( $retval['data'] as $list )
					$mailchimp_lists[ $list['id'] ] = $list['name'];

				if ( sizeof( $mailchimp_lists ) > 0 )
					set_transient( 'wc_mc_list_' . md5( $this->api_key ), $mailchimp_lists, 60*60*1 );
			}
		}

		return $mailchimp_lists;
	}

	/**
	 * show_stats function.
	 *
	 * @access public
	 * @return void
	 */
	public function show_stats() {

		if ( ! $stats = get_transient( 'woocommerce_mailchimp_stats' ) ) {

			$api = new MCAPI( $this->api_key );

			$retval = $api->lists();

			if ( $api->errorCode ) {

				echo '<div class="error inline"><p>' . __( 'Unable to load stats from MailChimp', 'wc_subscribe_to_newsletter' ) . '</p></div>';

			} else {

				foreach ( $retval['data'] as $list ) {

					if ( $list['id'] !== $this->list )
						continue;

					$stats  = '<ul class="woocommerce_stats">';
					$stats .= '<li><strong>' . $list['stats']['member_count'] . '</strong> ' . __( 'Total subscribers', 'wc_subscribe_to_newsletter' ) . '</li>';
					$stats .= '<li><strong>' . $list['stats']['unsubscribe_count'] . '</strong> ' . __( 'Unsubscribes', 'wc_subscribe_to_newsletter' ) . '</li>';
					$stats .= '<li><strong>' . $list['stats']['member_count_since_send'] . '</strong> ' . __( 'Subscribers since last newsletter', 'wc_subscribe_to_newsletter' ) . '</li>';
					$stats .= '<li><strong>' . $list['stats']['unsubscribe_count_since_send'] . '</strong> ' . __( 'Unsubscribes since last newsletter', 'wc_subscribe_to_newsletter' ) . '</li>';
					$stats .= '</ul>';

					break;
				}

				set_transient('woocommerce_mailchimp_stats', $stats, 60*60*1);
			}

		}

		echo $stats;
	}

	/**
	 * subscribe function.
	 *
	 * @access public
	 * @param mixed $first_name
	 * @param mixed $last_name
	 * @param mixed $email
	 * @param string $listid (default: 'false')
	 * @return void
	 */
	public function subscribe( $first_name, $last_name, $email, $listid = 'false' ) {

		if ( ! $email )
			return; // Email is required

		if ( $listid == 'false' )
			$listid = $this->list;

		$api = new MCAPI( $this->api_key );

		$vars = apply_filters( 'wc_mailchimp_subscribe_vars', array( 'FNAME' => $first_name, 'LNAME' => $last_name ) );

		$retval = $api->listSubscribe( $listid, $email, $vars, 'html', ( get_option( 'woocommerce_mailchimp_double_opt_in' ) == 'no' ? false : true ) );

		if ( $api->errorCode && $api->errorCode != 214 ) {
			do_action( 'wc_subscribed_to_newsletter', $email );

			// Email admin
			wp_mail( get_option('admin_email'), __( 'Email subscription failed (Mailchimp)', 'wc_subscribe_to_newsletter' ), '(' . $api->errorCode . ') ' . $api->errorMessage );
		}
	}

}