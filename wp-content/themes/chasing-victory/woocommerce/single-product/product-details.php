<h1 class="product-title hidden-xs"><?php the_title(); ?></h1>
<div class="product-description">
	<br class="visible-xs">
	<?php the_content(); ?>	
	<div class="social-sharing visible-xs">
		<span>Share: </span>
		<a href="http://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>"><i class="fa fa-facebook"></i> Facebook</a>
		<a href="http://twitter.com/home?status=<?php the_title(); ?> - <?php the_permalink(); ?>"><i class="fa fa-twitter"></i> Twitter</a>
		<a href="http://www.pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>&media=<?php echo wp_get_attachment_url( get_post_thumbnail_id() ); ?>&description=<?php bloginfo('name'); ?> - <?php the_title(); ?>"><i class="fa fa-pinterest"></i> Pinterest</a>
	</div>
	<div class="clearfix visible-xs"></div>
</div>
<hr>
<div class="product-short-description">
	<?php woocommerce_get_template( 'single-product/short-description.php' ); ?>
</div>
<hr>