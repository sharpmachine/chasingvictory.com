<div class="row">
	<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3">
		<?php if ( is_front_page() ) { ?>
		<h2 class="entry-title"><?php the_title(); ?></h2>
		<?php } else { ?>
		<h1 class="entry-title"><?php the_title(); ?></h1>
		<?php } ?>
	</div>
</div>

