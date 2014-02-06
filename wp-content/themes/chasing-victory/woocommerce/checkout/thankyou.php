<?php
/**
 * Thankyou page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;

if ( $order ) : ?>

	<?php if ( in_array( $order->status, array( 'failed' ) ) ) : ?>

		<p class="lead"><?php _e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction.', 'woocommerce' ); ?></p>

		<p><?php
			if ( is_user_logged_in() )
				_e( 'Please attempt your purchase again or go to your account page.', 'woocommerce' );
			else
				_e( 'Please attempt your purchase again.', 'woocommerce' );
		?></p>

		<p>
			<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e( 'Pay', 'woocommerce' ) ?></a>
			<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( get_permalink( woocommerce_get_page_id( 'myaccount' ) ) ); ?>" class="button pay"><?php _e( 'My Account', 'woocommerce' ); ?></a>
			<?php endif; ?>
		</p>

	<?php else : ?>

		<p class="lead"><?php _e( 'Thank you. Your order has been received.', 'woocommerce' ); ?> | <span class="text-uppercase"><?php _e( 'Order', 'woocommerce' ); ?> <?php echo $order->get_order_number(); ?> | <?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ); ?> | <?php echo $order->get_formatted_order_total(); ?></span></p>

		<p><small>Currently the turnaround time is <strong><?php the_field('turnaround_time', 'option'); ?></strong> weeks.  We thank you ahead of time for your patience.  You will not be disappointed.</small>
</p>

		<p><small>Once your order ships you'll receive an email that has your orders tracking number.  Also be aware that you will have to sign 
for the delivery.</small></p>
		<hr>

	<?php endif; ?>

	<?php do_action( 'woocommerce_thankyou_' . $order->payment_method, $order->id ); ?>
	<?php do_action( 'woocommerce_thankyou', $order->id ); ?>

<?php else : ?>

	<p class="lead"><?php _e( 'Thank you. Your order has been received.', 'woocommerce' ); ?></p>
	<hr>
<?php endif; ?>
