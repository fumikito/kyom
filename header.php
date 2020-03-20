<?php get_header( 'meta' ); ?>

<header class="site-header uk-sticky uk-sticky-fixed" uk-sticky>

	<nav class="uk-navbar-container uk-navbar" uk-navbar="boundary-align: true; align: center;">

		<div class="uk-navbar-left">

			<a class="uk-navbar-toggle menu-toggle" href="#uk-offcanvas-main" uk-toggle>
				<span uk-navbar-toggle-icon></span>
				<span class="uk-margin-small-left uk-visible@s"><?php esc_html_e( 'Menu', 'kyom' ) ?></span>
			</a>

			<form class="site-header-form" method="get" action="<?php echo home_url( '/' ) ?>">
				<label for="site-header-search">
					<span class="site-header-form-icon" uk-icon="icon: search"></span>
				</label>
				<input id="site-header-search" class="site-header-form-search" name="s" type="search" value="<?php the_search_query(); ?>"
					   placeholder="<?php echo esc_attr( _x( 'Search...', 'Header Search Form', 'kyom' ) ) ?>" />
			</form>

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
				<?php if ( is_user_logged_in() ) : ?>
					<li class="logged-in">
						<a href="<?= admin_url() ?>" rel="nofollow">
							<span uk-icon="icon: settings" class="uk-margin-small-right"></span>
							<span class="uk-visible@s"><?php esc_html_e( 'Dashboard', 'kyom' ) ?></span>
						</a>
					</li>
				<?php else : ?>
				<?php if ( get_option( 'users_can_register' ) ) : ?>
				<li class="uk-visible@s not-logged-in border">
					<a href="<?= wp_registration_url() ?>" rel="nofollow">
						<?php esc_html_e( 'Sign Up', 'kyom' ) ?>
					</a>
				</li>
				<?php endif; ?>
				<li class="uk-visible@s not-logged-in border">
					<a href="<?= wp_login_url( $_SERVER['REQUEST_URI'] ) ?>" rel="nofollow">
						<?php esc_html_e( 'Sign In', 'kyom' ) ?>
					</a>
				</li>
				<li class="uk-hidden@s not-logged-in">
					<a href="<?= wp_login_url( $_SERVER['REQUEST_URI'] ) ?>" rel="nofollow">
						<span><?php esc_html_e( 'Log in', 'kyom' ) ?></span>
					</a>
				</li>
				<?php endif; ?>
			</ul>
		</div>
	</nav>

	<?php if ( has_nav_menu( 'top-pages-pc' ) ) : ?>
		<div class="kyom-navigations-wrapper">
			<div class="uk-container">
				<?php Kunoichi\SetMenu::nav_menu( [
					'theme_location'  => 'top-pages-pc',
					'depth'           => 2,
					'menu_class'      => 'kyom-navigations-list',
					'container'       => 'nav',
					'container_class' => 'kyom-navigations'
				] ); ?>
			</div>
		</div>
	<?php endif; ?>

</header>

<div id="uk-offcanvas-main" uk-offcanvas="overlay:true" class="kyom-off-canvas">
	<div class="uk-offcanvas-bar">

		<button class="uk-offcanvas-close" type="button" uk-close></button>

		<h3 class="kyom-off-canvas-title"><span uk-icon="menu"></span> <?php esc_html_e( 'Menu', 'kyom' ) ?></h3>

		<?php if ( has_nav_menu( 'top-pages' ) ) {
			Kunoichi\SetMenu::nav_menu( [
				'theme_location'  => 'top-pages',
				'depth'           => 2,
				'menu_class'      => 'kyom-menu-parent',
				'container'       => 'nav',
				'container_class' => 'kyom-menu'
			] );
		} ?>

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
