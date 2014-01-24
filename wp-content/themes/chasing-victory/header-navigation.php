<header id="header">
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<a class="navbar-brand" href="<?php bloginfo('url') ?>"><img src="<?php bloginfo('template_directory'); ?>/img/logo.png" alt="Chasing Victory Logo" class="img-responsive" width="169" height="84"></a>
				<div class="menu-btn visible-xs">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>
			</div>
		</div>
		<div class="collapse navbar-collapse">
			<div class="container">
				<ul class="nav navbar-nav">
					<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'nav', 'items_wrap' => '%3$s', 'walker' => new Bootstrap_Menu_Walker ) ); ?>
				</ul>
			</div>
		</div><!--/.nav-collapse -->
	</div>
</header>