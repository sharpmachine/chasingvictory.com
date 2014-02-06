<?php
/**
 * Empty cart page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>

<h2 class="text-center"><?php _e( 'Your cart is currently empty.', 'woocommerce' ) ?></h2>

<?php do_action('woocommerce_cart_is_empty'); ?>
<br>
<p class="text-center">
	<a class="button btn btn-default btn-lg" href="<?php echo get_permalink(woocommerce_get_page_id('shop')); ?>"><?php _e( '&larr; Return To Shop', 'woocommerce' ) ?></a>
</p>