<!-- Modal -->
<div class="modal fade" id="swatchModal" tabindex="-1" role="dialog" aria-labelledby="swatchModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="swatchModalLabel">Swatches</h4>
      </div>
      <div class="modal-body">
				<!-- Nav tabs -->
				<ul class="nav nav-tabs">
				<?php $terms = get_terms('material_type');
					$count = count($terms);
					if ( $count > 0 ): ?>
						<?php foreach ( $terms as $term ): ?>
							<li class="<?php echo $term->slug; ?>"><a href="#<?php echo $term->slug; ?>" data-toggle="tab"><?php echo $term->name; ?></a></li>
						<?php endforeach; ?>
				<?php endif; ?>
				</ul>
        
				<!-- Tab panes -->
				<div class="tab-content">
					<?php $terms = get_terms('material_type');
					$count = count($terms);
					if ( $count > 0 ): ?>
						<?php foreach ( $terms as $term ): ?>

					<div class="tab-pane row" id="<?php echo $term->slug; ?>">
						<?php $args = array( 'post_type' => 'material_swatches', 'material_type' => $term->slug); ?>
						<?php $swatches = new WP_Query( $args ); ?>
						<?php while ( $swatches->have_posts() ) : $swatches->the_post() ; ?>
							<div class="col-md-3">
							<?php $attachment_id = get_field('swatch_photo'); $size = "material-thumbs"; ?>
							<?php echo wp_get_attachment_image( $attachment_id, $size ); ?>
							<?php the_title(); ?>
							</div>
						<?php wp_reset_postdata(); ?>
						<?php endwhile; ?>
					</div>
				<?php endforeach; ?>
				<?php endif; ?>
				</div>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->