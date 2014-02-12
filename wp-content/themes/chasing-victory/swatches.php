<div class="modal fade" id="swatchModal" tabindex="-1" role="dialog" aria-labelledby="swatchModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    	<!-- Nav tabs -->
				<ul class="nav nav-tabs">
					<button type="button" class="close visible-xs" data-dismiss="modal" aria-hidden="true">&times;</button>
				<?php $terms = get_terms('material_type');
					$count = count($terms);
					if ( $count > 0 ): ?>
						<?php foreach ( $terms as $term ): ?>
							<li class="<?php echo $term->slug; ?>"><a href="#<?php echo $term->slug; ?>" data-toggle="tab"><?php echo $term->name; ?></a></li>
						<?php endforeach; ?>
				<?php endif; ?>
				<button type="button" class="close hidden-xs" data-dismiss="modal" aria-hidden="true">&times;</button>
				</ul>
				
      <div class="modal-body">
				
				<!-- Tab panes -->
				<div class="tab-content">
					<?php $terms = get_terms('material_type');
					$count = count($terms);
					if ( $count > 0 ): ?>
						<?php foreach ( $terms as $term ): ?>

					<div class="tab-pane" id="<?php echo $term->slug; ?>">
						<?php $args = array( 'post_type' => 'material_swatches', 'material_type' => $term->slug); ?>
						<?php $swatches = new WP_Query( $args ); ?>
						<?php while ( $swatches->have_posts() ) : $swatches->the_post() ; ?>
							<div class="swatch-thumb">
							<?php $attachment_id = get_field('swatch_photo'); $size = "material-thumbs"; ?>
							<?php echo wp_get_attachment_image( $attachment_id, $size ); ?>
								<div class="swatch-title">
									<?php the_title(); ?>
								</div>
								<div class="clearfix"></div>
							</div>
						<?php wp_reset_postdata(); ?>
						<?php endwhile; ?>
						<div class="clearfix"></div>
					</div>
				<?php endforeach; ?>
				<?php endif; ?>
				</div>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->