<?php
/**
 * Cart Page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;

$woocommerce->show_messages();

$available_methods = $woocommerce->shipping->get_available_shipping_methods();
?>

<?php //do_action( 'woocommerce_before_cart' ); ?>
<form action="<?php echo esc_url( $woocommerce->cart->get_cart_url() ); ?>" method="post">
		<?php do_action( 'woocommerce_before_cart_contents' ); ?>

		<?php
		if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
			foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
				$_product = $values['data'];
				if ( $_product->exists() && $values['quantity'] > 0 ) {
					?>
	<div class="row">
		<div class="col-md-8">
			<div class="row">
				<div class="col-md-12">
					<h2>My Cart</h2>
					<hr>

					<?php do_action( 'woocommerce_before_cart_table' ); ?>
					<div class="table-responsive">
						<table class="shop_table cart table table-striped">
							<thead>
								<tr>
									<th class="product-remove text-center">&nbsp;</th>
									<th class="product-thumbnail text-center">&nbsp;</th>
									<th class="product-name text-center"><?php _e( 'Item', 'woocommerce' ); ?></th>
									<th class="product-quantity text-center"><?php _e( 'Qty', 'woocommerce' ); ?></th>
									<th class="product-price text-center"><?php _e( 'Price', 'woocommerce' ); ?></th>
									<th class="product-subtotal text-center"><?php _e( 'Total', 'woocommerce' ); ?></th>
								</tr>
							</thead>
							<tbody>

							<tr class = "<?php echo esc_attr( apply_filters('woocommerce_cart_table_item_class', 'cart_table_item', $values, $cart_item_key ) ); ?>">
								<!-- Remove from cart link -->
								<td class="product-remove">
									<?php
										echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf('<a href="%s" class="remove" title="%s">&times;</a>', esc_url( $woocommerce->cart->get_remove_url( $cart_item_key ) ), __( 'Remove this item', 'woocommerce' ) ), $cart_item_key );
									?>
								</td>

								<!-- The thumbnail -->
								<td class="product-thumbnail">
									<?php
										$thumbnail = apply_filters( 'woocommerce_in_cart_product_thumbnail', $_product->get_image(), $values, $cart_item_key );

										if ( ! $_product->is_visible() || ( ! empty( $_product->variation_id ) && ! $_product->parent_is_visible() ) )
											echo $thumbnail;
										else
											printf('<a href="%s">%s</a>', esc_url( get_permalink( apply_filters('woocommerce_in_cart_product_id', $values['product_id'] ) ) ), $thumbnail );
									?>
								</td>

								<!-- Product Name -->
								<td class="product-name">
									<?php
										if ( ! $_product->is_visible() || ( ! empty( $_product->variation_id ) && ! $_product->parent_is_visible() ) )
											echo apply_filters( 'woocommerce_in_cart_product_title', $_product->get_title(), $values, $cart_item_key );

										else
											printf('<a href="%s">%s</a>', esc_url( get_permalink( apply_filters('woocommerce_in_cart_product_id', $values['product_id'] ) ) ), apply_filters('woocommerce_in_cart_product_title', $_product->get_title(), $values, $cart_item_key ) );

										// Meta data
										echo $woocommerce->cart->get_item_data( $values );

		                   				// Backorder notification
		                   				if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $values['quantity'] ) )
		                   					echo '<p class="backorder_notification">' . __( 'Available on backorder', 'woocommerce' ) . '</p>';
									?>
								</td>

								<!-- Quantity inputs -->
								<td class="product-quantity">
									<?php
										if ( $_product->is_sold_individually() ) {
											$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
										} else {

											$step	= apply_filters( 'woocommerce_quantity_input_step', '1', $_product );
											$min 	= apply_filters( 'woocommerce_quantity_input_min', '', $_product );
											$max 	= apply_filters( 'woocommerce_quantity_input_max', $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(), $_product );

											$product_quantity = sprintf( '<div class="quantddity input-group input-group-sm"><input type="number" name="cart[%s][qty]" step="%s" min="%s" max="%s" value="%s" title="' . _x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ) . '" class="input-text qty text form-control" maxlength="2" />', $cart_item_key, $step, $min, $max, esc_attr( $values['quantity'] ) );
										}

										echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key );
									?>

										<span class="input-group-btn">
											<input type="submit" class="button btn btn-default btn-gray-light" name="update_cart" value="<?php _e( 'Update', 'woocommerce' ); ?>" />
										</span>
									</div>
								</td>

								<!-- Product price -->
								<td class="product-price">
									<?php
										$product_price = get_option('woocommerce_tax_display_cart') == 'excl' ? $_product->get_price_excluding_tax() : $_product->get_price_including_tax();

										echo apply_filters('woocommerce_cart_item_price_html', woocommerce_price( $product_price ), $values, $cart_item_key );
									?>
								</td>

								<!-- Product subtotal -->
								<td class="product-subtotal">
									<?php
										echo apply_filters( 'woocommerce_cart_item_subtotal', $woocommerce->cart->get_product_subtotal( $_product, $values['quantity'] ), $values, $cart_item_key );
									?>
								</td>
							</tr>
							<?php
						}
					}
				}

									do_action( 'woocommerce_cart_contents' ); ?>
								<?php do_action( 'woocommerce_after_cart_contents' ); ?>
							</tbody>
						</table>
					</div><!-- END: .table-responsive -->
					<div class="alert alert-info alert-dismissable visible-xs">
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
						<small><i class="fa fa-info-circle"></i> You can swipe left above to see prices and change quantities.</small>
					</div>
					<?php do_action( 'woocommerce_after_cart_table' ); ?>	

					<a href="<?php bloginfo('url'); ?>" class="btn btn-default btn-lg visible-xs">Continue Shopping</a>

			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<h2>Shipping</h2>
				<hr>
				<?php do_action('woocommerce_cart_collaterals'); ?>
				<div class="row">
					<div class="col-sm-6 col-md-4">
						<h3>Estimate Shipping</h3>
						<hr>
						<?php woocommerce_shipping_calculator(); ?>
					</div>
					<div class="col-sm-6 col-md-8">
						<h3>Rates &amp; Insurance</h3>
						<hr>
						<?php woocommerce_get_template( 'cart/shipping-methods.php', array('available_methods' => $available_methods )); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<!-- <div data-spy="affix" data-offset-top="40" data-offset-bottom="300"> </div> -->
		<h2>Summary</h2>
		<hr>
			<?php woocommerce_cart_totals(); ?>

				<?php if ( $woocommerce->cart->coupons_enabled() ) { ?>
				<div class="coupon">
					<label for="coupon_code"><?php _e( 'Promo Code', 'woocommerce' ); ?>:</label> 
					<div class="input-group">
						<input name="coupon_code" class="input-text form-control" id="coupon_code" value="" />
						<span class="input-group-btn">
							<input type="submit" class="button btn btn-default btn-gray-light" name="apply_coupon" value="<?php _e( 'Apply', 'woocommerce' ); ?>" />
						</span>
						<?php do_action('woocommerce_cart_coupon'); ?>
					</div>
				</div>
				<?php } ?>
				<br>
				<input type="submit" class="checkout-button button alt btn btn-default btn-block" name="proceed" value="<?php _e( 'Checkout', 'woocommerce' ); ?>" />
				<?php do_action('woocommerce_proceed_to_checkout'); ?>

				<?php $woocommerce->nonce_field('cart') ?>
		</div>
	</div>
</form>

<?php do_action( 'woocommerce_after_cart' ); ?>