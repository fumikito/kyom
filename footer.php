<?php
/**
 * Footer template.
 *
 * @package kyom
 */

do_action( 'kyom_before_site_footer' );
?>
<footer class="site-footer">

	<div class="uk-container">

		<?php if ( has_nav_menu( 'social-links' ) ) {
            wp_nav_menu( [
                'theme_location'  => 'social-links',
                'container'       => 'nav',
                'container_class' => 'site-footer-social',
                'menu_class'      => 'uk-iconnav',
            ] );
		} ?>

		<?php if ( is_active_sidebar( 'footer-sidebar' ) ) : ?>
		<div class="site-footer-widgets uk-grid-divider uk-grid" uk-grid>
			<?php dynamic_sidebar( 'footer-sidebar' ) ?>
		</div>
		<?php endif ?>

		<?php
		if ( has_nav_menu( 'bottom-pages' ) ) {
			Kunoichi\SetMenu::nav_menu( [
				'theme_location'  => 'bottom-pages',
				'depth'           => 1,
				'menu_class'      => 'uk-subnav uk-subnav-divider',
				'container'       => 'nav',
				'container_class' => 'kyom-bottom-nav',
			] );
		}
		?>

		<p class="site-footer-copy">
			&copy; <?php echo esc_html( kyom_oldest_date() ) ?> <a href="<?php echo home_url() ?>" rel="home"><?php bloginfo( 'name' ) ?></a>
			| Powered by <a href="https://wordpress.org">WordPress <?php echo esc_html( $GLOBALS['wp_version'] ) ?></a> and <a href="https://github.com/fumikito/kyom">Kyom <?php echo kyom_version( true ) ?></a>.
		</p>
	</div>
</footer>
<?php do_action( 'wp_footer' ); ?>
</body>
</html>
