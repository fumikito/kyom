<footer class="site-footer">
	
	<div class="uk-container">
		<?php if ( $methods = kyom_get_social_links() ) : ?>
		<div class="site-footer-social">
			<ul class="uk-iconnav">
				<?php foreach ( $methods as $key => $url ) :
					if ( ! is_array( $url ) ) {
						$url = [
							'url' => $url,
						];
					}
					?>
				<li><a href="<?= esc_urL( $url['url'] ); ?>" uk-icon="<?= esc_attr( $key ) ?>"></a></li>
				<?php endforeach; ?>
			</ul>
			
		</div>
		<?php endif; ?>

		<?php if ( is_active_sidebar( 'footer-sidebar' ) ) : ?>
		<div class="site-footer-widgets uk-grid-divider uk-grid" uk-grid>
			<?php dynamic_sidebar( 'footer-sidebar' ) ?>
		</div>
		<?php endif ?>
		
		<?php
		if ( has_nav_menu( 'bottom-pages' ) ) {
			wp_nav_menu( [
				'theme_location'  => 'bottom-pages',
				'depth'           => 1,
				'menu_class'      => 'uk-subnav uk-subnav-divider',
				'container'       => 'nav',
				'container_class' => 'kyom-bottom-nav',
			] );
		}
		?>

		<p class="site-footer-copy">
			&copy; <?= kyom_oldest_date() ?> <a href="<?= home_url() ?>" rel="home"><?php bloginfo( 'name' ) ?></a>
			| Powered by <a href="https://wordpress.org">WordPress</a> and <a href="https://github.com/fumikito/kyom">Kyom</a>.
		</p>
	</div>
</footer>
<?php do_action( 'wp_footer' ); ?>
</body>
</html>
