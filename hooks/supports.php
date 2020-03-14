<?php
/**
 * Add theme supports.
 *
 * @package kyom
 */


/**
 * Register theme support
 */
add_action( 'after_setup_theme', function() {
	// Title tag.
	add_theme_support( 'title-tag' );

	// Align wide.
	add_theme_support( 'align-wide' );

	// Logo setting.
	$defaults = apply_filters( 'kyom_logo_setting', [
		'height'      => 60,
		'width'       => 360,
		'flex-height' => false,
		'flex-width'  => false,
	] );
	add_theme_support( 'custom-logo', $defaults );

	// Thumbnail.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1200, 600, true );

	// Comic.
	// add_theme_support( 'jetpack-comic' );

	// html5
	add_theme_support( 'html5', [
		'comment-list',
		'comment-form',
		'search-form',
		'gallery',
		'caption',
	] );


	// Feed links.
	if ( apply_filters( 'kyom_automatic_feed_links', true ) ) {
		add_theme_support( 'automatic-feed-links' );
	}

} );

/**
 * Add comment reply link
 */
add_action( 'wp_enqueue_scripts', function() {
	if ( ! is_singular() ) {
		return;
	}
	if ( ! post_type_supports( get_post_type( get_queried_object() ), 'comments' ) ) {
		return;
	}
	wp_enqueue_script( 'comment-reply' );
} );
