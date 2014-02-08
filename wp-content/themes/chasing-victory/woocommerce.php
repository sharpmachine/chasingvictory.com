<?php get_header(); ?>

<?php if(is_page('home')): ?>
	<?php get_template_part('jumbotron-carousel'); ?>
<?php endif; ?>

<div class="container visible-xs">
	<div class="row">
		<div class="col-xs-12">
			<h1 class="site-title text-center"><?php bloginfo('description'); ?></h1>
			<hr class="champagn bottom">
		</div>
	</div>
</div>


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
	<div class="row catalog">
		<!-- <div class="col-lg-12">  -->

			<?php if(is_page('home')): ?>
				<?php woocommerce_get_template_part( 'loop', 'shop' ); ?>
			<?php else: ?>
				<?php woocommerce_content(); ?>
			<?php endif; ?>

		<!-- </div> -->
		<!-- <div class="col-sm-4 col-md-3 catalog-item quote-request">
			<a href="<?php bloginfo('url'); ?>/quote-request" class="product-thumb">
				<img src="<?php bloginfo('template_directory'); ?>/img/custom.jpg" class="img-responsive" alt="">
				<div class="view-product">
					<div class="view-product-inner">
						<div class="view-product-title text-center">
							Looking for custom?
						</div>
					</div>
				</div>
			</a>
			<h3 class="product-title">
				<a href="<?php bloginfo('url'); ?>/quote-request">Get Quote Request</a>
			</h3>
			<div class="price">$399</div>
		</div> -->
		
	<!-- 		<div class="col-sm-4 col-md-3 product-catalog">
			<a href="#">
				<img src="holder.js/273x180/auto" class="img-responsive" alt="">
			</a>
			<h3 class="product-title"><a href="#">Product title</a></h3>
			<div class="price">$399</div>
		</div>

		<div class="col-sm-4 col-md-3 product-catalog">
			<a href="#">
				<img src="holder.js/273x180/auto" class="img-responsive" alt="">
			</a>
			<h3 class="product-title"><a href="#">Product title Product title Producttitle12356 sds sds</a></h3>
			<div class="price">$399</div>
		</div>

		<div class="col-sm-4 col-md-3 product-catalog">
			<a href="#">
				<img src="holder.js/273x180/auto" class="img-responsive" alt="">
			</a>
			<h3 class="product-title"><a href="#">Product title</a></h3>
			<div class="price">$399</div>
		</div>

		<div class="col-sm-4 col-md-3 product-catalog">
			<a href="#">
				<img src="holder.js/273x180/auto" class="img-responsive" alt="">
			</a>
			<h3 class="product-title"><a href="#">Product title</a></h3>
			<div class="price">$399</div>
		</div>

		<div class="col-sm-4 col-md-3 product-catalog">
			<a href="#">
				<img src="holder.js/273x180/auto" class="img-responsive" alt="">
			</a>
			<h3 class="product-title"><a href="#">Product title Product title Product title</a></h3>
			<div class="price">$399</div>
		</div>

		<div class="col-sm-4 col-md-3 product-catalog">
			<a href="#">
				<img src="holder.js/273x180/auto" class="img-responsive" alt="">
			</a>
			<h3 class="product-title"><a href="#">Product title</a></h3>
			<div class="price">$399</div>
		</div>

		<div class="col-sm-4 col-md-3 product-catalog">
			<a href="#">
				<img src="holder.js/273x180/auto" class="img-responsive" alt="">
			</a>
			<h3 class="product-title"><a href="#">Product title</a></h3>
			<div class="price">$399</div>
		</div> -->

	</div>
	
	<hr class="champagn">
	<blockquote class="call-to-action">
		<p class="text-right">Looking for custom? <a href="<?php bloginfo('url'); ?>/quote-request" class="btn btn-default">Get a quote request</a></p>
	</blockquote>
</div>

<?php get_footer(); ?>
