<?php
/**
 * Get meta
 *
 * @package kyom
 */

/**
 * Change title
 *
 * @param array $title
 * @return array
 */
add_filter( 'document_title_parts', function( $title ) {
	if ( is_single() ) {
		$title['category'] = implode( ', ', array_map( function( $cat ) {
			return $cat->name;
		}, get_the_category( get_queried_object_id() ) ) );
		
	}
	return $title;
} );

/**
 * Change title separator.
 *
 * @return string
 */
add_filter( 'document_title_separator', function () {
	return '|';
} );

/**
 * Remove admin bar
 */
add_filter( 'show_admin_bar', '__return_false' );

/**
 * Add social contact methods.
 */
add_filter( 'user_contactmethods', function( $methods ) {
	$new_methods = [];
	foreach ( $methods as $key => $label ) {
		switch ( $key ) {
			case 'aim':
			case 'yim':
			case 'jabber':
				// Do nothing.
				break;
			default:
				$new_methods[ $key ] = $label;
				break;
		}
	}
	foreach ( [
		'facebook' => '',
		'twitter'  => '',
		'instagram' => '',
		'github'    => '',
		'pinterest' => '',
		'youtube'   => '',
		'wordpress' => 'WordPress',
		'linkedin'  => '',
		'dribbble'   => '',
		'behance'   => '',
		'google'    => '',
		
	] as $key => $label ) {
		if ( ! $label ) {
			$label = ucfirst( $key );
		}
		$new_methods[ $key ] = $label .' URL';
	}
	return $new_methods;
} );


/**
 * If redirect to is set, move permanently.
 */
add_action( 'template_redirect', function(){
	if ( is_singular() && ( $redirect_to = get_post_meta( get_queried_object_id(), 'redirect_to', true ) ) ) {
		wp_redirect( $redirect_to, 301 );
		exit;
	}
} );

/**
 * Separate title
 *
 * @param string $title
 * @return string
 */
function kyom_title_separator( $title ) {
	$cache = wp_cache_get( $title, 'kyom_title' );
	if ( false === $cache ) {
		$response = kyom_parse_string( $title );
		if ( is_wp_error( $response ) ) {
			return $title;
		}
		$parsed = implode( '', array_map( function( $token ) {
			$classes = preg_match( '#[ぁ-んァ-ヶー一-龠]#u', $token ) ? 'jp' : 'ascii';
			return sprintf( '<span class="budou %s">%s</span>', $classes, esc_html( $token ) );
		}, $response ) );
		if ( empty( $parsed ) ) {
			return $title;
		} else {
			wp_cache_set( $title, $parsed, 'kyom_title', 60 * 30 );
			$cache = $parsed;
		}
	}
	return $cache;
}
add_action( 'wp_head', function(){
	add_filter( 'single_post_title', 'kyom_title_separator' );
	add_filter( 'kyom_archive_title', 'kyom_title_separator' );
}, 9999 );
