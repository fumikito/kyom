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
	wp_register_style( 'uikit', get_template_directory_uri(). '/assets/css/style.css', [  ], kyom_version() );

	// icons
	$uikit_version = '3.3.6';
	wp_register_script( 'uikit', get_template_directory_uri() . '/assets/js/uikit.min.js', [], $uikit_version, true );
	wp_register_script( 'uikit-icon', get_template_directory_uri() . '/assets/js/uikit-icons.min.js', [ 'uikit' ], $uikit_version, true );

	// Fit height
	wp_register_script( 'kyom-fit-height', get_template_directory_uri() . '/assets/js/fit-height.js', [ 'jquery' ], kyom_version(), true );

	// Fitie
	wp_register_script( 'fitie', get_template_directory_uri() . '/assets/js/fitie.js', [], '1.0.0', true );
	wp_add_inline_script( 'fitie', 'window.fitie = {};', 'before' );

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

} );

/**
 * Enqueue scripts
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'uikit' );
	wp_enqueue_script( 'kyom' );
	wp_enqueue_script( 'fitie' );
} );

/**
 * Enqueue admin style.
 */
add_action( 'admin_enqueue_scripts', function() {
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

add_filter( 'script_loader_tag', function( $tag, $handle ) {
	$deferrable = [ 'kyom-fit-height', 'kyom', 'kyom-netabare', 'fitie' ];
	if ( in_array( $handle, $deferrable ) ) {
		$tag = str_replace( '<script', '<script defer', $tag );
	}
	return $tag;
}, 10, 2 );

/**
 * Move jQuery to footer
 */
add_action( 'init', function() {
	// Avoid admin and login screen.
	if ( is_admin() || ( isset( $_SERVER['SCRIPT_FILENAME'] ) && 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) ) ) {
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
 * Detect if style is "critical"
 *
 * @see https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery
 * @param string $handle
 * @return bool
 */
function kyom_is_critical_css( $handle ) {
	return apply_filters( 'kyom_is_critical_css', in_array( $handle, [ 'uikit' ] ), $handle );
}

/**
 * Check if preloader exits.
 */
function kyom_preloader_exists() {
	return file_exists( get_template_directory() . '/assets/js/cssrelpreload.min.js' );
}

/**
 * Rewrite style tags for preload.
 */
add_filter( 'style_loader_tag', function( $tag, $handle, $href, $media ) {
	if ( kyom_is_critical_css( $handle ) || ! kyom_preloader_exists() ) {
		return $tag;
	}
	$html = <<<'HTML'
<link rel="preload" href="%1$s" as="style" onload="this.onload=null;this.rel='stylesheet'" data-handle="%3$s" />
<noscript>
	%2$s
</noscript>
HTML;
	return sprintf( $html, $href, $tag, $handle );
}, 10, 4 );

/**
 * Add preloader helper.
 */
add_action( 'wp_head', function() {
	if ( ! kyom_preloader_exists() ) {
		return;
	}
	$preloader = file_get_contents( get_template_directory() . '/assets/js/cssrelpreload.min.js' );
	?>
	<!-- CSS Preloader Polyfill -->
	<script><?php echo $preloader ?></script>
	<?php
}, 11 );

/**
 * Add post class.
 */
add_filter( 'post_class', function( $classes, $additional_class, $post_id ) {
	$classes[] = kyom_is_cjk( $post_id ) ? 'post-in-cjk' : 'post-not-in-cjk';
	return $classes;
}, 10, 3 );

/**
 * Remove not serif jp to be loaded.
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
