<?php
/**
 * Assets related hooks.
 *
 * @package kyom
 */

/**
 * Register scripts and styles.
 */
add_action( 'init', function() {

	// Main style.
	wp_register_style( 'uikit', get_template_directory_uri(). '/assets/css/style.css', [ 'dashicons' ], kyom_version() );

	// icons
	$uikit_version = '3.0.22';
	wp_register_script( 'uikit', get_template_directory_uri() . '/assets/js/uikit.min.js', [], $uikit_version, true );
	wp_register_script( 'uikit-icon', get_template_directory_uri() . '/assets/js/uikit-icons.min.js', [ 'uikit' ], $uikit_version, true );

	// Fit height
	wp_register_script( 'kyom-fit-height', get_template_directory_uri() . '/assets/js/fit-height.js', [ 'jquery' ], kyom_version(), true );

	// Netabare
	wp_register_script( 'kyom-netabare', get_template_directory_uri() . '/assets/js/netabare.js', [ 'jquery' ], kyom_version(), true );
	wp_localize_script( 'kyom-netabare', 'Netabare', [
		'label' => __( 'Click to open spoiler', 'kyom' ),
	] );

	// Theme
	wp_register_script( 'kyom', get_template_directory_uri() . '/assets/js/app.js', [
		'kyom-netabare',
		'kyom-fit-height',
		'uikit-icon',
	], kyom_version(), true );

	// Admin theme.
	list( $url, $version ) = kyom_asset_url_and_version( 'css/kyom-admin.css' );
	wp_register_style( 'kyom-admin', $url, [], $version );

	// Add image size.
	$sizes = [
		'face-rectangle' => [ 360, 360, true ],
	];
	foreach ( $sizes as $key => list( $width, $height, $crop ) ) {
		add_image_size( $key, $width, $height, $crop );
	}
} );



/**
 * Enqueue scripts
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'uikit' );
	wp_enqueue_script( 'kyom' );
} );

/**
 * Enqueue admin style.
 */
add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_style( 'kyom-admin' );
} );

/**
 * Remove unwanted assets
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
add_action( 'init', function() {
	// Avoid admin screen.
	if ( is_admin() ) {
		return;
	}
	// Store current version and url.
	global $wp_scripts;
	$jquery = $wp_scripts->registered['jquery-core'];
	$jquery_ver = $jquery->ver;
	$jquery_src = $jquery->src;
	// Remove current version.
	wp_deregister_script( 'jquery' );
	wp_deregister_script( 'jquery-core' );
	// Register again.
	wp_register_script( 'jquery', false, ['jquery-core'], $jquery_ver, true );
	wp_register_script( 'jquery-core', $jquery_src, [], $jquery_ver, true );
}, 1 );

/**
 * Add post class.
 */
add_filter( 'post_class', function( $classes, $additional_class, $post_id ) {
	$classes[] = kyom_is_cjk( $post_id ) ? 'post-in-cjk' : 'post-not-in-cjk';
	return $classes;
}, 10, 3 );

/**
 * Remove not serif jp
 */
add_filter( 'gettext_with_context', function( $translation, $text, $context, $domain ) {
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

/**
 * Remove Yarpp CSS
 */
add_action( 'wp_footer', function () {
}, 1 );

/**
 * Start buffer for replacing img tag.
 */
add_action( 'wp_head', function() {
	ob_start();
}, 9999 );

/**
 * Replace img tag.
 */
add_action( 'wp_footer', function() {
	$body     = ob_get_contents();
	$replaced = preg_replace_callback( '#<img([^>]+)>#u', function( $matches ) {
		list( $match, $attr ) = $matches;
		foreach ( [
			'loading'  => 'lazy',
			// 'decoding' => 'async', // Meaningless?
		] as $key => $val ) {
			if ( false !== strpos( $attr, $key . '=' ) ) {
				continue;
			}
			$attr = sprintf( ' %s="%s"%s', $key, $val, $attr );
		}
		return sprintf( '<img%s>', $attr );
	}, $body );
	ob_end_clean();
	echo $replaced;
}, 9999 );
