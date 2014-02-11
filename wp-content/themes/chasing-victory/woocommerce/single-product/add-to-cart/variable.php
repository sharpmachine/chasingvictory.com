<?php
/**
 * Variable product add to cart
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.15
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $product, $post;
?>

<?php do_action('woocommerce_before_add_to_cart_form'); ?>

<form class="variations_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo $post->ID; ?>" data-product_variations="<?php echo esc_attr( json_encode( $available_variations ) ) ?>">

	<div class="col-md-5 product-details">
			<h1 class="product-title"><?php the_title(); ?></h1>
			<div class="product-description">
				<?php the_content(); ?>	
			</div>
			<hr>
			<div class="product-short-description">
				<?php woocommerce_get_template( 'single-product/short-description.php' ); ?>
			</div>
			<hr>
			<h3>Custom Options <a href="#" class="custom-options pull-right"><i class="fa fa-caret-down"></i></a></h3>
			<div class="product-custom-options variations row">

						<?php $loop = 0; foreach ( $attributes as $name => $options ) : $loop++; ?>
								<div class="col-md-4 att-<?php echo esc_attr( sanitize_title($name) ); ?>">
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
						</div><!-- END: .col-md-4 -->
				        <?php endforeach;?>
				<div class="clearfix"></div>
			</div>
			<hr>
			<p><small><em>All our rings are delivered in an elegant gift box and include wax and care guide.</em></small></p>
		</div>

		<div class="col-md-3">
			
			<div class="price"><div class="single_variation"></div></div>

		
		<?php if(has_term('made-to-order', 'product_cat')): ?>
				Made to order <br>
					please allow....
			<?php else: ?>
				Ready to ship
			<?php endif; ?> 
			<br>
			<br>
			<table class="table">
				<tr>
					ASk a questions
				</tr>
				<tr>
					<?php get_template_part('layaway-popover'); ?>
				</tr>
			</table>
		</div>


	

	<?php //do_action('woocommerce_before_add_to_cart_button'); ?>

	<table class="variations" cellspacing="0">
		<tbody>
			
			<tr>
				<td class="label"><label for="pa_sizes">Sizes</label></td>
				<td class="value">
					<select id="pa_sizes" name="attribute_pa_sizes">
					<option value="">Choose an option&hellip;</option>
					<option value="3"  selected='selected'>3</option><option value="3-5" >3.5</option>					
				</select>
				</tr>
			</tbody>
		</table>

	<div class="single_variation_wrap" style="display:none;">
		
		<div class="variations_button">
			<input type="hidden" name="variation_id" value="" />
			<?php //woocommerce_quantity_input(); ?>
			<button type="submit" class="single_add_to_cart_button button alt"><?php echo apply_filters('single_add_to_cart_text', __( 'Add to cart', 'woocommerce' ), $product->product_type); ?></button>
		</div>
	</div>
	<div>
		<input type="hidden" name="add-to-cart" value="<?php echo $product->id; ?>" />
		<input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
	</div>

	<?php do_action('woocommerce_after_add_to_cart_button'); ?>

</form>

<?php do_action('woocommerce_after_add_to_cart_form'); ?>
