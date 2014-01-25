<?php if (get_field('jumbotron_mode', 'option') == "cinematic"): ?>

	<?php $args = array( 'post_type' => 'home_jumbotron', 'posts_per_page' => 4); ?>
	<?php $jumbotron_standard = new WP_Query( $args ); ?>


	<div id="jumbotron-cinematic" class="carousel slide" data-interval="false">
		<div class="carousel-inner">
			<?php while ( $jumbotron_standard->have_posts() ) : $jumbotron_standard->the_post() ; ?>
			
			<!-- Wrapper for slides -->
			<div class="item">
				<?php $attachment_id = get_field('jumbotron_photo'); $size = "jumbotron-cinematic"; ?>
				<?php echo wp_get_attachment_image( $attachment_id, $size ); ?>
				<div class="carousel-caption">
					<?php the_field('jumbotron_customer_testimony'); ?> <br>
					<?php the_field('jumbotron_customer_name'); ?>
				</div>
			</div>

			<?php wp_reset_postdata(); ?>
		<?php endwhile; ?>
	</div>

	<?php
	$count_posts = wp_count_posts('home_jumbotron');
	$jumbo_count = $count_posts->publish;

	if (($jumbo_count) > 1): ?>

	<!-- Controls -->
	<a class="left carousel-control" href="#jumbotron-cinematic" data-slide="prev">
		<span class="glyphicon glyphicon-chevron-left"></span>
	</a>
	<a class="right carousel-control" href="#jumbotron-cinematic" data-slide="next">
		<span class="glyphicon glyphicon-chevron-right"></span>
	</a>
<?php endif; ?>
</div>

<?php else: ?>

	<?php $args = array( 'post_type' => 'home_jumbotron', 'posts_per_page' => 4); ?>
	<?php $jumbotron_standard = new WP_Query( $args ); ?>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">

				<div id="jumbotron-standard" class="carousel slide" data-interval="false">
					
					<div class="carousel-inner">
						<?php while ( $jumbotron_standard->have_posts() ) : $jumbotron_standard->the_post() ; ?>
						
						<!-- Wrapper for slides -->
						<div class="item">
							<?php $attachment_id = get_field('jumbotron_photo'); $size = "jumbotron-standard"; ?>
							<?php echo wp_get_attachment_image( $attachment_id, $size ); ?>
							<div class="carousel-caption">
								<?php the_field('jumbotron_customer_testimony'); ?> <br>
								<?php the_field('jumbotron_customer_name'); ?>
							</div>
						</div>
						
						<?php wp_reset_postdata(); ?>
					<?php endwhile; ?>
				</div>

				<?php
				$count_posts = wp_count_posts('home_jumbotron');
				$jumbo_count = $count_posts->publish;

				if (($jumbo_count) > 1): ?>

				<!-- Controls -->
				<a class="left carousel-control" href="#jumbotron-standard" data-slide="prev">
					<span class="glyphicon glyphicon-chevron-left"></span>
				</a>
				<a class="right carousel-control" href="#jumbotron-standard" data-slide="next">
					<span class="glyphicon glyphicon-chevron-right"></span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
</div>
<?php endif; ?>