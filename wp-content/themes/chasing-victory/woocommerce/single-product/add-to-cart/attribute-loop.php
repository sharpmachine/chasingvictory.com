

	<label for="<?php echo sanitize_title($name); ?>"><?php echo $woocommerce->attribute_label( $name ); ?></label>
	<div class="value select-styled champagn-border">
		<select id="<?php echo esc_attr( sanitize_title($name) ); ?>" name="attribute_<?php echo sanitize_title($name); ?>" class="form-control">
			<option value=""><?php echo __( 'Choose an option', 'woocommerce' ) ?>&hellip;</option>
			<?php
			if ( is_array( $options ) ) {

				if ( empty( $_POST ) )
					$selected_value = ( isset( $selected_attributes[ sanitize_title( $name ) ] ) ) ? $selected_attributes[ sanitize_title( $name ) ] : '';
				else
					$selected_value = isset( $_POST[ 'attribute_' . sanitize_title( $name ) ] ) ? $_POST[ 'attribute_' . sanitize_title( $name ) ] : '';

											// Get terms if this is a taxonomy - ordered
				if ( taxonomy_exists( $name ) ) {

					$orderby = $woocommerce->attribute_orderby( $name );

					switch ( $orderby ) {
						case 'name' :
						$args = array( 'orderby' => 'name', 'hide_empty' => false, 'menu_order' => false );
						break;
						case 'id' :
						$args = array( 'orderby' => 'id', 'order' => 'ASC', 'menu_order' => false );
						break;
						case 'menu_order' :
						$args = array( 'menu_order' => 'ASC' );
						break;
					}

					$terms = get_terms( $name, $args );

					foreach ( $terms as $term ) {
						if ( ! in_array( $term->slug, $options ) )
							continue;

						echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $selected_value, $term->slug, false ) . '>' . apply_filters( 'woocommerce_variation_option_name', $term->name ) . '</option>';
					}
				} else {

					foreach ( $options as $option ) {
						echo '<option value="' . esc_attr( sanitize_title( $option ) ) . '" ' . selected( sanitize_title( $selected_value ), sanitize_title( $option ), false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
					}

				}
			}
			?>
		</select>
	</div><!-- END: .select-styled -->
	<div class="view-swatch">
		<a href="#">View Swatches</a>
	</div>