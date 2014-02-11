<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override this template by copying it to yourtheme/woocommerce/content-single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="container">
	<div class="row">
		<div class="col-md-4">
			<div class="row">
				<div class="col-md-12">
					Product Photos
					<?php woocommerce_get_template( 'single-product/product-image.php' ); ?>
				</div>
				<div class="col-md-12">
					<a href="http://www.pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>&media=<?php echo wp_get_attachment_url( get_post_thumbnail_id() ); ?>&description=<?php bloginfo('name'); ?> - <?php the_title(); ?>">Pin it</a> <br>
					<a href="http://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>">Share on Facebook</a> <br>
					<a href="http://twitter.com/home?status=<?php the_title(); ?> - <?php the_permalink(); ?>">Share on Twitter</a>
				</div>
			</div>
		</div>
		<div class="col-md-5">
			<h1 class="h2"><?php the_title(); ?></h1>
			<?php the_content(); ?>
			<hr>
			<?php woocommerce_get_template( 'single-product/short-description.php' ); ?>
			<hr>
			<h3>Custom Options</h3>

		</div>
		<div class="col-md-3">
			
		<div class="price"></div>

		<?php woocommerce_get_template( 'single-product/price.php' ); ?>
		
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

			<?php do_action('add_to_cart' ); ?>

		</div>
	</div>
</div>

<div class="container">

<?php
	/**
	 * woocommerce_before_single_product hook
	 *
	 * @hooked woocommerce_show_messages - 10
	 */
	 do_action( 'woocommerce_before_single_product' );
?>

<div itemscope itemtype="http://schema.org/Product" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php
		/**
		 * woocommerce_show_product_images hook
		 *
		 * @hooked woocommerce_show_product_sale_flash - 10
		 * @hooked woocommerce_show_product_images - 20
		 */
		//do_action( 'woocommerce_before_single_product_summary' );
	?>

	<div class="summary entry-summary">

		<?php
			/**
			 * woocommerce_single_product_summary hook
			 *
			 * @hooked woocommerce_template_single_title - 5
			 * @hooked woocommerce_template_single_price - 10
			 * @hooked woocommerce_template_single_excerpt - 20
			 * @hooked woocommerce_template_single_add_to_cart - 30
			 * @hooked woocommerce_template_single_meta - 40
			 * @hooked woocommerce_template_single_sharing - 50
			 */
			 // do_action( 'woocommerce_single_product_summary' );
		?>



	</div><!-- .summary -->

	<?php
		/**
		 * woocommerce_after_single_product_summary hook
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_output_related_products - 20
		 */
		do_action( 'woocommerce_after_single_product_summary' );
	?>



	<?php get_template_part('swatches'); ?>
<?php do_action( 'woocommerce_after_single_product' ); ?>
</div>  <!-- Extra div...not sure why it's there -->
</div><!-- #product-<?php the_ID(); ?> -->
</div><!-- END: .container -->

<div class="container">
	<!-- Button trigger modal -->
			<button class="btn btn-champagne woods-active" data-toggle="modal" data-target="#swatchModal">
				Woods
			</button>
			<button class="btn btn-default metals-active" data-toggle="modal" data-target="#swatchModal">
				Metals
			</button>
			<button class="btn btn-gray-light btn-sm gemstones-active" data-toggle="modal" data-target="#swatchModal">
				Gemstones
			</button>
</div>