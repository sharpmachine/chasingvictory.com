<?php
/**
 * Cart errors page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>

<?php $woocommerce->show_messages(); ?>

<div class="alert alert-danger">
	<?php _e( 'There are some issues with the items in your cart (shown above). Please go back to the cart page and resolve these issues before checking out.', 'woocommerce' ) ?>
</div>

<?php do_action('woocommerce_cart_has_errors'); ?>

<p class="text-center">
	<a class="button btn btn-default btn-lg" href="<?php echo get_permalink(woocommerce_get_page_id('cart')); ?>"><?php _e( '&larr; Return To Cart', 'woocommerce' ) ?></a>
</p>