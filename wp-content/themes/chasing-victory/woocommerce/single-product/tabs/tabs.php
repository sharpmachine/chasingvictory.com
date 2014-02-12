<?php
/**
 * Single Product tabs
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Filter tabs and allow third parties to add their own
 *
 * Each tab is an array containing title, callback and priority.
 * @see woocommerce_default_product_tabs()
 */
$tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $tabs ) ) : ?>

<div class="product-tabs">
	<div class="container">
		<ul class="tabs nav nav-tabs">
			<li class="active"><a href="#1" data-toggle="tab">Customer Reviews</a></li>
			<li><a href="#2" data-toggle="tab">FAQ</a></li>
		</ul>
	</div>
	<div class="tab-content">
		<div class="tab-pane active" id="1">
			<div class="container">
				<div class="row revs">
					<?php foreach ( $tabs as $key => $tab ) : ?>
						<?php if ($key == 'reviews'): ?>
						<?php call_user_func( $tab['callback'], $key, $tab ) ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<p class="add_review text-center"><a href="#review_form" class="inline show_review_form button btn btn-default btn-sm btn-champagne" title="Add Your Review">Add Review</a></p>
			</div>
		</div>
		<div class="tab-pane" id="2">
			<div class="container">
				<div class="two-col">
					<?php
					$my_id = 37;
					$post_id_37 = get_post($my_id);
					$content = $post_id_37->post_content;
					$content = apply_filters('the_content', $content);
					$content = str_replace(']]>', ']]>', $content);
					echo $content;
					?>
				</div>
			</div>
		</div>
		
		<div class="container">
			<hr class="champagn">
			<blockquote class="call-to-action">
				<p class="text-center"><span>Looking for custom?</span><a href="<?php bloginfo('url'); ?>/quote-request" class="btn btn-default">Get a quote request</a></p>
			</blockquote>
		</div>
	</div><!-- END: .tab-content -->
</div><!-- END: .product-tabs -->

<?php endif; ?>