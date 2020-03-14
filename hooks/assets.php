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
 * Remove Yarpp CSS
 */
add_action( 'wp_footer', function () {
	wp_dequeue_style( 'yarppRelatedCss' );
}, 1 );


