<?php get_header(); ?>

<?php if(is_page('home')): ?>
<?php get_template_part('jumbotron-carousel'); ?>
<?php endif; ?>

<div class="container">
	<div class="row">
		<div class="col-lg-12">

			<?php woocommerce_content(); ?>


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
		<div class="col-md-12">
			<a href="<?php bloginfo('url'); ?>/quote-request">Get a Quote on a custom ring</a>
		</div>
	</div>

	<?php get_template_part('swatches'); ?>

</div>

<?php get_footer(); ?>
