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

	<div class="col-md-5 product-details">
		<h1 class="product-title"><?php the_title(); ?></h1>
		<div class="product-description">
			<?php the_content(); ?>	
		</div>
		<hr>
		<div class="product-short-description">
			<?php woocommerce_get_template( 'single-product/short-description.php' ); ?>
		</div>
		<hr>
		<h3>Custom Options <a href="#" class="custom-options pull-right"><i class="fa fa-caret-down"></i></a></h3>
		<div class="product-custom-options variations row">

			<?php $loop = 0; foreach ( $attributes as $name => $options ) : $loop++; ?>
			<?php if ($name != 'pa_sizes'): ?>
			<div class="col-md-4">
				<?php woocommerce_get_template('single-product/add-to-cart/attribute-loop.php',
					array(
						'name' => $name, 
						'options' => $options,
						'selected_attributes' => $selected_attributes,
						)); ?>
					</div>
				<?php endif; ?>
			<?php endforeach;?>
			<div class="clearfix"></div>
		</div>
		<hr>
		<p><small><em>All our rings are delivered in an elegant gift box and include wax and care guide.</em></small></p>
	</div>

	<div class="col-md-3">

		<div class="price"><div class="single_variation"></div></div>

		
		<?php if(has_term('made-to-order', 'product_cat')): ?>
		Made to order <br>
		please allow....
	<?php else: ?>
	Ready to ship
<?php endif; ?> 
<br>
<br>
<table class="table">
	<tr>
		ASk a questions
	</tr>
	<tr>
		<?php get_template_part('layaway-popover'); ?>
	</tr>
</table>





<?php //do_action('woocommerce_before_add_to_cart_button'); ?>

<!-- Sizes attribute should show up here -->

<?php woocommerce_get_template('single-product/add-to-cart/attribute-loop.php', array(
	'name' => 'pa_sizes', 
	'options' => $attributes['pa_sizes'],
	'selected_attributes' => $selected_attributes,
	)); ?>


	<div class="single_variation_wrap" style="display:none;">
		<div class="variations_button">
			<input type="hidden" name="variation_id" value="" />
			<?php //woocommerce_quantity_input(); ?>
			<button type="submit" class="single_add_to_cart_button button alt"><?php echo apply_filters('single_add_to_cart_text', __( 'Add to cart', 'woocommerce' ), $product->product_type); ?></button>
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
