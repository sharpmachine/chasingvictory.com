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


<form class="variations_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo $post->ID; ?>" data-product_variations="<?php echo esc_attr( json_encode( $available_variations ) ) ?>">
	<table class="variations" cellspacing="0">
		<tbody>
			
			<tr>
				<td class="label"><label for="pa_sizes">Sizes</label></td>
				<td class="value"><select id="pa_sizes" name="attribute_pa_sizes">
					<option value="">Choose an option&hellip;</option>
					<option value="3"  selected='selected'>3</option><option value="3-5" >3.5</option>					</select> <a class="reset_variations" href="#reset">Clear selection</a></td>

				</tr>
			</tbody>
		</table>

		<?php //do_action('woocommerce_before_add_to_cart_button'); ?>

		<div class="single_variation_wrap" style="display:none;">
			<div class="single_variation"></div>
			<div class="variations_button">
				<input type="hidden" name="variation_id" value="" />
				<?php woocommerce_quantity_input(); ?>
				<button type="submit" class="single_add_to_cart_button button alt"><?php echo apply_filters('single_add_to_cart_text', __( 'Add to cart', 'woocommerce' ), $product->product_type); ?></button>
			</div>
		</div>
		<div>
			<input type="hidden" name="add-to-cart" value="<?php echo $product->id; ?>" />
			<input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
		</div>

		<?php do_action('woocommerce_after_add_to_cart_button'); ?>

	</form>

	<?php do_action('woocommerce_after_add_to_cart_form'); ?>
