<?php get_header(); ?>

<div id="single-page" class="page">
	<div class="container">
		
		<?php get_template_part('page', 'title' ); ?>

		<div class="row">
			<div class="col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">

				<?php get_template_part( 'loop', 'page' ); ?>

			</div>
		</div>
	</div>
</div><!-- #page -->

<?php get_footer(); ?>
