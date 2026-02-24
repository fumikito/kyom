<?php
/**
 * Assets related hooks.
 *
 * @package kyom
 */

/**
 * Register scripts and styles.
 */
add_action( 'init', function () {

	// Main style.
	wp_register_style( 'uikit', get_template_directory_uri() . '/assets/css/style.css', [], kyom_version() );

	// icons
	$uikit_version = '3.3.6';
	wp_register_script( 'uikit', get_template_directory_uri() . '/assets/js/uikit.min.js', [], $uikit_version, true );
	wp_register_script( 'uikit-icon', get_template_directory_uri() . '/assets/js/uikit-icons.min.js', [ 'uikit' ], $uikit_version, true );

	// Fit height
	wp_register_script( 'kyom-fit-height', get_template_directory_uri() . '/assets/js/fit-height.js', [ 'jquery' ], kyom_version(), true );

	// Fitie
	wp_register_script( 'fitie', get_template_directory_uri() . '/assets/js/fitie.js', [], '1.0.0', true );
	wp_add_inline_script( 'fitie', 'window.fitie = {};', 'before' );

	// Particle.js
	wp_register_script( 'particle-js', 'https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js', [], '2.0.0', true );
	wp_register_script( 'kyom-particle', get_template_directory_uri() . '/assets/js/particle.js', [ 'particle-js' ], kyom_version(), true );

	// Netabare
	wp_register_script( 'kyom-netabare', get_template_directory_uri() . '/assets/js/netabare.js', [ 'jquery' ], kyom_version(), true );
	wp_localize_script( 'kyom-netabare', 'Netabare', [
		'label' => __( 'Click to open spoiler', 'kyom' ),
	] );

	// Google Platform
	wp_register_script( 'google-api-platform', 'https://apis.google.com/js/platform.js', [], null, true );

	// Theme
	wp_register_script( 'kyom', get_template_directory_uri() . '/assets/js/app.js', [
		'kyom-netabare',
		'kyom-fit-height',
		'uikit-icon',
	], kyom_version(), true );

	// Admin theme.
	list( $url, $version ) = kyom_asset_url_and_version( 'css/kyom-admin.css' );
	wp_register_style( 'kyom-admin', $url, [], $version );

	list( $url, $version ) = kyom_asset_url_and_version( 'css/kyom-oembed.css' );
	wp_register_style( 'kyom-oembed', $url, [], $version );
} );

/**
 * Enqueue scripts
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'uikit' );
	wp_enqueue_script( 'kyom' );
	wp_enqueue_script( 'fitie' );
} );

/**
 * Enqueue admin style.
 */
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style( 'kyom-admin' );
} );

/**
 * Remove unwanted assets
 *
 * @todo This should be done by plugin.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! ( is_singular() && has_shortcode( get_queried_object()->post_content, 'contact-form-7' ) ) ) {
		wp_deregister_script( 'google-recaptcha' );
		wp_dequeue_style( 'contact-form-7' );
		wp_dequeue_script( 'contact-form-7' );
	}
	// Yarpp
	wp_dequeue_style( 'yarppRelatedCss' );
	wp_dequeue_style( 'yarppWidgetCss' );
}, 11 );

add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	$deferrable = [ 'kyom-fit-height', 'kyom', 'kyom-netabare', 'fitie', 'particle-js', 'kyom-particle' ];
	if ( in_array( $handle, $deferrable, true ) ) {
		$tag = str_replace( '<script', '<script defer', $tag );
	}
	return $tag;
}, 10, 2 );

/**
 * Move jQuery to footer
 */
add_action( 'wp_default_scripts', function ( WP_Scripts $wp_scripts ) {
	// Avoid admin and login screen.
	if ( is_admin() || ( isset( $_SERVER['SCRIPT_FILENAME'] ) && 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) ) ) {
		return;
	}
	// If this is CLI, skip.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return;
	}
	// Store current version and url.
	if ( ! isset( $wp_scripts->registered['jquery-core'] ) ) {
		return;
	}
	$jquery     = $wp_scripts->registered['jquery-core'];
	$jquery_ver = $jquery->ver;
	$jquery_src = $jquery->src;
	// Remove current version.
	$wp_scripts->remove( 'jquery' );
	$wp_scripts->remove( 'jquery-core' );
	// Register again.
	$wp_scripts->add( 'jquery', false, [ 'jquery-core' ], $jquery_ver, 1 );
	$wp_scripts->add( 'jquery-core', $jquery_src, [], $jquery_ver, 1 );
}, 11 );

/**
 * Detect if style is "critical"
 *
 * @see https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery
 * @param string $handle
 * @return bool
 */
function kyom_is_critical_css( $handle ) {
	return apply_filters( 'kyom_is_critical_css', in_array( $handle, [ 'uikit', 'login' ], true ), $handle );
}

/**
 * Rewrite style tags for preload.
 */
add_filter( 'style_loader_tag', function ( $tag, $handle, $href, $media ) {
	if ( kyom_is_critical_css( $handle ) || is_admin() || 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
		return $tag;
	}
	if ( 'print' === $media || str_contains( $tag, 'preload' ) ) {
		// Skip print and preload.
		return;
	}
	$html = <<<'HTML'
<link rel="stylesheet" href="%1$s" onload="this.onload=null;this.media='%2$s'" id="%3$s-css" media="print" />
HTML;
	return sprintf( $html, $href, esc_attr( $media ), esc_attr( $handle ) );
}, 10, 4 );

/**
 * Add post class.
 */
add_filter( 'post_class', function ( $classes, $additional_class, $post_id ) {
	$classes[] = kyom_is_cjk( $post_id ) ? 'post-in-cjk' : 'post-not-in-cjk';
	return $classes;
}, 10, 3 );

/**
 * Remove not serif jp to be loaded.
 */
add_filter( 'gettext_with_context', function ( $translation, $text, $context, $domain ) {
	switch ( $context ) {
		case 'Google Font Name and Variants':
			$translation = 'off';
			break;
		case 'CSS Font Family for Editor Font':
			$translation = 'YuMincho';
			break;
	}
	return $translation;
}, 10, 4 );

// Stop contain-intrinsic-size: 3000px 1500px
add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );
