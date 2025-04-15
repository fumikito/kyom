<?php
/**
 * Login related functions.
 */

/**
 * Change login header url.
 *
 * @return string
 */
add_filter( 'login_headerurl', function () {
	return home_url();
} );

/**
 * Change login header title.
 *
 * @return string
 */
add_filter( 'login_headertext', function () {
	return get_bloginfo( 'name' );
} );

/**
 * If site icon is set, change background.
 */
add_action( 'login_head', function () {
	$site_icon = get_site_icon_url();
	if ( $site_icon ) {
		$site_icon = esc_url( $site_icon );
		echo <<<HTML
			<style>
				.login h1 a{
					background-image: url("{$site_icon}");
				}
			</style>
HTML;
	}
} );
