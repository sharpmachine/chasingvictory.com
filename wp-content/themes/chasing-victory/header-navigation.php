<?php global $woocommerce; ?>
<header id="header">
	<div class="navbar navbar-inverse navbar-fixed-top">
		
			<div class="navbar-header">
			
				<a class="navbar-brand" href="<?php bloginfo('url') ?>"><img src="<?php bloginfo('template_directory'); ?>/img/logo.png" alt="Chasing Victory Logo" class="img-responsive" width="169" height="84"></a>
				<?php if (!is_page('checkout')): ?>
				<div class="menu-btn visible-xs">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>
			<?php endif; ?>
			
		</div>
		<?php if (!is_page('checkout')): ?>
		<div class="collapse navbar-collapse">
	
				<ul class="nav navbar-nav">
					<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'nav', 'items_wrap' => '%3$s', 'walker' => new Bootstrap_Menu_Walker ) ); ?>
					<li><a class="cart-contents" href="<?php echo $woocommerce->cart->get_cart_url(); ?>" title="<?php _e('View your shopping cart', 'woothemes'); ?>"> Cart (<?php echo sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'woothemes'), $woocommerce->cart->cart_contents_count);?>)</a></li>
				</ul>
	
		</div><!--/.nav-collapse -->
		<?php endif; ?>
	</div>
</header>