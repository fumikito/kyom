<?php
/**
 * Oembed related hooks.
 *
 *
 * @package kyom
 */

/**
 * Remove default styles.
 */
remove_action( 'embed_head', 'print_embed_styles' );

/**
 * Remove excerpt more.
 */
remove_filter( 'excerpt_more', 'wp_embed_excerpt_more', 20 );

/**
 * Remove buttons.
 */
remove_action( 'embed_content_meta', 'print_embed_comments_button' );
remove_action( 'embed_content_meta', 'print_embed_sharing_button' );
remove_action( 'embed_footer', 'print_embed_sharing_dialog' );

/**
 * Change image size.
 */
add_filter( 'embed_thumbnail_image_size', function () {
	return 'medium';
} );

/**
 * Render CSS
 */
add_action( 'embed_head', function () {
	$oembed_stylesheet = apply_filters( 'kyom_oembed_css_path', get_template_directory() . '/assets/css/kyom-oembed.css' );
	if ( ! file_exists( $oembed_stylesheet ) ) {
		return;
	}
	$style = file_get_contents( $oembed_stylesheet );
	$style = str_replace( 'sourceMappingURL=', 'sourceMappingURL=' . get_template_directory_uri() . '/assets/css/', $style ); // Remove source map.
	printf( "<style>\n%s\n</style>", $style );
} );
