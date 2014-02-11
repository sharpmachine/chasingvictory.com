<?php
/**
 * Description tab
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $post;

$heading = esc_html( apply_filters('woocommerce_product_description_heading', __( 'FAQ', 'woocommerce' ) ) );
?>

<h2>FAQ</h2>

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