<?php get_header(); ?>

<?php if(is_page('home')): ?>
	<?php get_template_part('jumbotron-carousel'); ?>

	<div class="container visible-xs">
		<div class="row">
			<div class="col-xs-12">
				<h1 class="site-title text-center"><?php bloginfo('description'); ?></h1>
				<hr class="champagn bottom">
			</div>
		</div>
	</div>

<?php endif; ?>

<?php if(is_page('home') || is_tax('product_cat')): ?>
<div class="container">
	<div class="row filters text-center">
		<div class="col-xs-12">
			<div class="hidden-xs">
				<?php get_template_part('filters'); ?>
			</div>
			<div class="visible-xs">
				<?php the_widget('WC_Widget_Product_Categories', 'dropdown=1&hierarchical=true'); ?>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

	

			<?php if(is_page('home') || is_archive()): ?>
			<div class="container">
				<div class="row catalog">
				<?php if ( have_posts() ) : ?>
					
						<?php while ( have_posts() ) : the_post(); ?>

							<?php woocommerce_get_template_part( 'content', 'product' ); ?>

						<?php endwhile; // end of the loop. ?>
					
				<?php else : ?>

					<?php if ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

						<p><?php _e( 'No products found which match your selection.', 'woocommerce' ); ?></p>

					<?php endif; ?>
						
				<?php endif; ?>
					</div>
					<hr class="champagn">
					<blockquote class="call-to-action">
						<p class="text-center"><span>Looking for custom?</span><a href="<?php bloginfo('url'); ?>/quote-request" class="btn btn-default btn-champagne">Get a quote request</a></p>
					</blockquote>
				</div>
			<?php else: ?>

				<?php woocommerce_content(); ?>

			<?php endif; ?>

<?php get_footer(); ?>
