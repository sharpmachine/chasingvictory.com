<?php
/**
 * Simple product add to cart
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.15
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $product;
// global $woocommerce, $product, $post;

if ( ! $product->is_purchasable() ) return;
?>

<hr class="visible-xs visible-sm">
<div class="purchase-price text-center center-block">
	<div class="single_variation"><?php echo $product->get_price_html(); ?></div>
	<div class="lifetime-guarantee">Lifetime Guarantee</div>
</div>

<table class="table">
	<tr>
		<td><small>Ask a question</small></td>
		<td class="text-right">
			<a href="#" data-toggle="modal" data-target="#productQuestion">
				<i class="fa fa-caret-right"></i>
			</a>
		</td>
	</tr>
	<tr>
		<?php get_template_part('layaway-popover'); ?>
	</tr>
	<tr>
		<td class="availability-status">
			<small>
				<?php if(has_term('made-to-order', 'product_cat')): ?>
				<i class="fa fa-clock-o"></i> 
				<span><em>Made to order</em></span>
				<p>Please Allow <?php the_field('turnaround_time', 'option'); ?> weeks for delivery</p>
			<?php else: ?>
			<i class="fa fa-check-square-o"></i> 
			<span><em>Ready to ship</em></span>
			<p class="text-muted">Ships within 2 business days</p>
		<?php endif; ?>
			</small>
		</td>
		<td>&nbsp;</td>
	</tr>
</table>