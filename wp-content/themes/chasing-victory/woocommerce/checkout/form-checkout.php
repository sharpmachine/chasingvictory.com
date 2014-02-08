<?php
/**
 * Checkout Form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;

$woocommerce->show_messages(); ?>
<div class="container">
	<div class="row checkout-before">
		<div class="col-md-8 col-md-offset-2">
			<div class="row">
				<?php do_action( 'woocommerce_before_checkout_form', $checkout );

				// If checkout registration is disabled and not logged in, the user cannot checkout
				if ( ! $checkout->enable_signup && ! $checkout->enable_guest_checkout && ! is_user_logged_in() ) {
					echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) );
					return;
				}

				// filter hook for include new pages inside the payment method
				$get_checkout_url = apply_filters( 'woocommerce_get_checkout_url', $woocommerce->cart->get_checkout_url() ); ?>
						</div>
		</div>
	</div>
</div>

<form name="checkout" method="post" class="checkout" action="<?php echo esc_url( $get_checkout_url ); ?>">

	<?php if ( sizeof( $checkout->checkout_fields ) > 0 ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>


	<div class="billing-details">
		<div class="container">
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
					<h2>Billing &amp; Shipping Details</h2>
					<hr>
					<div class="row" id="customer_details">
						<div class="col-md-6 billing-addy">
							<?php do_action( 'woocommerce_checkout_billing' ); ?>
						</div>
						<div class="col-md-6">
							<?php do_action( 'woocommerce_checkout_shipping' ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>

	<?php do_action( 'woocommerce_checkout_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>