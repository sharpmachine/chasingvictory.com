<?php get_header(); ?>

<div id="error-four0four" class="page">
	<!-- <img src="<?php bloginfo('template_directory'); ?>/img/lost.jpg" alt="" class="img-responsive"> -->
	<div class="container">

		<div class="row fourofour-message">
			<div class="col-lg-12">
				<div id="post-0" class="post error404 not-found text-center">
					<h1 class="entry-title">You look a little lost...</h1>
					<div class="entry-content">
						<p>Whatever it is you're looking for isn't out here!  <br>Can you find your way <a href="<?php bloginfo('url'); ?>">home</a> from here?</p>
					</div><!-- .entry-content -->
					<h1 class="text-center"><strong>404</strong></h1>
				</div><!-- #post-0 -->
			</div>
		</div>
	</div>
</div><!-- #page -->
<script type="text/javascript">
	// focus on search field after it has loaded
	document.getElementById('s') && document.getElementById('s').focus();
</script>

<?php get_footer(); ?>