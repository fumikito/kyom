<?php
/**
 * Assets related hooks.
 *
 * @package kyom
 */

/**
 * Register scripts and styles from wp-dependencies.json and vendor files.
 */
add_action( 'init', function () {
	// 1. Register assets from wp-dependencies.json.
	$json_path = get_template_directory() . '/wp-dependencies.json';
	if ( file_exists( $json_path ) ) {
		$deps     = json_decode( file_get_contents( $json_path ), true );
		$base_url = get_template_directory_uri();
		foreach ( $deps as $dep ) {
			if ( empty( $dep['path'] ) ) {
				continue;
			}
			$url = $base_url . '/' . $dep['path'];
			switch ( $dep['ext'] ) {
				case 'css':
					wp_register_style( $dep['handle'], $url, $dep['deps'], $dep['hash'], $dep['media'] );
					break;
				case 'js':
					$args = [ 'in_footer' => $dep['footer'] ];
					if ( in_array( $dep['strategy'], [ 'defer', 'async' ], true ) ) {
						$args['strategy'] = $dep['strategy'];
					}
					wp_register_script( $dep['handle'], $url, $dep['deps'], $dep['hash'], $args );
					break;
			}
		}
	}

	// 2. Vendor scripts (not managed by wp-dependencies.json).
	$uikit_version = '3.3.6';
	wp_register_script( 'uikit', get_template_directory_uri() . '/assets/vendor/uikit.min.js', [], $uikit_version, true );
	wp_register_script( 'uikit-icon', get_template_directory_uri() . '/assets/vendor/uikit-icons.min.js', [ 'uikit' ], $uikit_version, true );

	// CDN scripts.
	wp_register_script( 'particle-js', 'https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js', [], '2.0.0', true );
	wp_register_script( 'kyom-particle', get_template_directory_uri() . '/assets/js/particle.js', [ 'particle-js' ], kyom_version(), true );
	wp_register_script( 'google-api-platform', 'https://apis.google.com/js/platform.js', [], null, true );

	// 3. Localized data.
	wp_localize_script( 'kyom-netabare', 'Netabare', [
		'label' => __( 'Click to open spoiler', 'kyom' ),
	] );
} );

/**
 * Enqueue scripts
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'uikit' );
	wp_enqueue_script( 'kyom' );
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
