<?php get_header(); ?>

<?php if(is_page('home')): ?>
<?php get_template_part('jumbotron-carousel'); ?>
<?php endif; ?>

<div class="container">
	<div class="row">
		<div class="col-lg-12">

			<?php woocommerce_content(); ?>

			<h1><?php global $woocommerce; ?>
 
<a class="cart-contents" href="<?php echo $woocommerce->cart->get_cart_url(); ?>" title="<?php _e('View your shopping cart', 'woothemes'); ?>"><?php echo sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'woothemes'), $woocommerce->cart->cart_contents_count);?></a>
</h1>

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
	</div>

	<?php get_template_part('swatches'); ?>

</div>

<?php get_footer(); ?>
