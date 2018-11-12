<?php
/**
 * Cache related functions.
 *
 * @package kyom
 */


/**
 * Add Cache header
 *
 * @filter nocache_headers
 *
 * @param array $headers
 *
 * @return array
 */
add_filter( 'nocache_headers', function ( $headers ) {
	// Only cache singular and front page.
	$should_cache = is_front_page() || is_single();
	$should_cache = apply_filters( 'kyom_should_cache', $should_cache );
	if ( $should_cache ) {
		unset( $headers['Expires'] );
		unset( $headers['Cache-Control'] );
		unset( $headers['Pragma'] );
	} else {
		$headers['X-Accel-Expires'] = 0;
	}
	
	return $headers;
}, 1 );


/**
 * Add CloudFlare headers.
 *
 * @action template_redirect
 */
add_action( 'template_redirect', function () {
	// Add CF tags.
	$tags = '';
	if ( is_front_page() ) {
		$tags = 'front';
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$cat  = get_queried_object();
		$tags = $cat->taxonomy . '-' . $cat->slug;
	} elseif ( is_single() || is_page() || is_singular() ) {
		$tags = get_post_type() . '-' . get_the_ID();
	}
	if ( $tags ) {
		header( 'Cache-Tag: ' . $tags );
	}
} );


/**
 * Purge CloudFlare cache when posts are saved.
 */
add_action( 'save_post', function ( $post_id, $post ) {
	if ( 'post' == $post->post_type && 'publish' == $post->post_status ) {
		kyom_purge_cf_cache( $post );
	}
}, 10, 2 );

/**
 * Purge CloudFlare cache when post is published.
 */
add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {
	if ( 'publish' === $new_status && 'future' === $old_status ) {
		kyom_purge_cf_cache( $post );
	}
}, 10, 3 );