<?php
/**
 * Checkout coupon form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;

if ( ! $woocommerce->cart->coupons_enabled() )
	return; 

$info_message = apply_filters('woocommerce_checkout_coupon_message', __( 'Have a promo code?', 'woocommerce' ));
?>
<div class="col-md-6">
	<h2><?php echo $info_message; ?></h2>
	<hr>
	<a href="#" class="showcoupon"><?php _e( 'Enter your code', 'woocommerce' ); ?></a>
	<form class="checkout_coupon" method="post" style="display:none">

		<p class="form-row form-row-first">
			<input type="text" name="coupon_code" class="input-text" placeholder="<?php _e( 'Promo code', 'woocommerce' ); ?>" id="coupon_code" value="" />
		</p>

		<p>
			<input type="submit" class="button btn btn-default" name="apply_coupon" value="<?php _e( 'Apply', 'woocommerce' ); ?>" />
		</p>

		<div class="clear"></div>
	</form>
</div>