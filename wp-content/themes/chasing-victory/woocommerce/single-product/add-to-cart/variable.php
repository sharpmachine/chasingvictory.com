<?php
/**
 * Variable product add to cart
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.15
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $product, $post;
?>

<?php do_action('woocommerce_before_add_to_cart_form'); ?>

<form class="variations_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo $post->ID; ?>" data-product_variations="<?php echo esc_attr( json_encode( $available_variations ) ) ?>">

	<div class="col-sm-8 col-md-5 product-details">
		<?php woocommerce_get_template( 'single-product/product-details.php' ); ?>
		<h3>Custom Options <a href="#" class="custom-options pull-right"><i class="fa fa-caret-down"></i></a></h3>
		<div class="product-custom-options variations">
			<p class="text-muted"><small><em>Some custom options cost extra.</em></small></p>
			<div class="row">
				<?php $loop = 0; foreach ( $attributes as $name => $options ) : $loop++; ?>
				<?php if ($name != 'pa_sizes'): ?>
				<div class="col-md-6">
					<?php woocommerce_get_template('single-product/add-to-cart/attribute-loop.php',
						array(
							'name' => $name, 
							'options' => $options,
							'selected_attributes' => $selected_attributes,
							)); ?>
						</div>
					<?php endif; ?>
				<?php endforeach;?>
			</div>
			<div class="clearfix"></div>
			<div class="view-swatch">
				<a href="#" data-toggle="modal" data-target="#swatchModal">View Swatches</a>
			</div>
		</div>
		<hr>
		<p class="text-muted"><small><em>All our rings are delivered in an elegant gift box and include wax and care guide.</em></small></p>
	</div>
	<div class="clear visible-sm"></div>

	<div class="col-sm-12 col-md-3 product-add-to-cart">
		<?php woocommerce_get_template( 'single-product/product-cart-summary.php' ); ?>

<?php woocommerce_get_template('single-product/add-to-cart/attribute-loop.php', array(
	'name' => 'pa_sizes', 
	'options' => $attributes['pa_sizes'],
	'selected_attributes' => $selected_attributes,
	)); ?>

	<?php //do_action('woocommerce_before_add_to_cart_button'); ?>
	<div class="single_variation_wrap" style="display:none;">
		<div class="variations_button">
			<input type="hidden" name="variation_id" value="" />
			<?php //woocommerce_quantity_input(); ?>
			<button type="submit" class="single_add_to_cart_button button alt btn btn-default btn-block"><?php echo apply_filters('single_add_to_cart_text', __( 'Add to cart', 'woocommerce' ), $product->product_type); ?></button>
		</div>
	</div>
	<div>
		<input type="hidden" name="add-to-cart" value="<?php echo $product->id; ?>" />
		<input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
	</div>
	<?php do_action('woocommerce_after_add_to_cart_button'); ?>
	</div>
</form>

<?php do_action('woocommerce_after_add_to_cart_form'); ?>
