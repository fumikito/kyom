<?php get_header( 'meta' ); ?>

<header class="site-header uk-sticky uk-sticky-fixed" uk-sticky>

	<nav class="uk-navbar-container uk-navbar" uk-navbar="boundary-align: true; align: center;">

		<div class="uk-navbar-left">
			<a class="uk-navbar-toggle" href="#uk-offcanvas-main" uk-toggle>
				<span uk-navbar-toggle-icon></span>
				<span class="uk-margin-small-left uk-visible@s"><?php esc_html_e( 'Menu', 'kyom' ) ?></span>
			</a>
		</div>

		<div class="uk-navbar-center">
			<a href="<?php echo home_url() ?>" class="custom-logo-link">
				<?php if ( has_custom_logo() ) : ?>
					<?php echo strip_tags( get_custom_logo(), '<img>' ); ?>
				<?php else : ?>
					<span class="site-header-title"><?php bloginfo( 'name' ) ?></span>
				<?php endif; ?>
			</a>
		</div>
		
		<div class="uk-navbar-right">
			<ul class="uk-navbar-nav">
				<li class="uk-visible@s not-logged-in">
					<a href="<?= wp_registration_url() ?>">
						<?php esc_html_e( 'Sign Up', 'kyom' ) ?>
					</a>
				</li>
				<li class="uk-visible@s not-logged-in">
					<a href="<?= wp_login_url( $_SERVER['REQUEST_URI'] ) ?>">
						<?php esc_html_e( 'Sign In', 'kyom' ) ?>
					</a>
				</li>
				<li class="uk-hidden@s not-logged-in">
					<a href="<?= wp_login_url( $_SERVER['REQUEST_URI'] ) ?>">
						<?php esc_html_e( 'Log in', 'kyom' ) ?>
					</a>
				</li>
			</ul>
		</div>
	</nav>
	
	
	
	
</header>

<div id="uk-offcanvas-main" uk-offcanvas="overlay:true" class="kyom-off-canvas">
	<div class="uk-offcanvas-bar">

		<button class="uk-offcanvas-close" type="button" uk-close></button>
		
		<h3 class="kyom-off-canvas-title"><span uk-icon="menu"></span> <?php esc_html_e( 'Menu', 'kyom' ) ?></h3>
		
		<?php
		wp_nav_menu( [
			'theme_location'  => 'top-pages',
			'depth'           => 2,
			'menu_class'      => 'kyom-menu-parent',
			'container'       => 'nav',
			'container_class' => 'kyom-menu'
		] );
		?>
		
		<?php if ( apply_filters( 'kyom_should_show_search', true ) ) : ?>
		<form action="<?= home_url() ?>">
			<div class="uk-inline">
				<span class="uk-form-icon uk-form-icon-flip" uk-icon="search"></span>
				<input name="s" class="uk-input" type="search" placeholder="<?php esc_attr_e( 'Input keywords...', 'kyom' ) ?>" value="<?php the_search_query() ?>" />
			</div>
		</form>
		<?php endif; ?>
		
	</div>
</div>