<?php
/**
 * Checkout login form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( is_user_logged_in() ) return;

$info_message = apply_filters( 'woocommerce_checkout_login_message', __( 'Returning customer?', 'woocommerce' ) );
?>


	<div class="col-md-6">
		<h2><?php echo esc_html( $info_message ); ?></h2>
		<hr>
		<a href="#" class="showlogin"><?php _e( 'Login', 'woocommerce' ); ?></a>	

		<?php
			woocommerce_login_form(
				array(
					'message'  => __( 'If you have shopped with us before, please enter your details in the boxes below. If you are a new customer please proceed to the Billing &amp; Shipping section.', 'woocommerce' ),
					'redirect' => get_permalink( woocommerce_get_page_id( 'checkout') ),
					'hidden'   => false
				)
			);
		?>

</div>