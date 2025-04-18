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
 *
 * @return array
 */
add_filter( 'document_title_parts', function ( $title ) {
	if ( is_single() ) {
		$title['category'] = implode( ', ', array_map( function ( $cat ) {
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
add_filter( 'user_contactmethods', function ( $methods ) {
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
	foreach ( kyom_social_keys() as $key ) {
		switch ( $key ) {
			case 'WordPress':
				$label = 'WordPress';
				break;
			default:
				$label = ucfirst( $key );
				break;
		}
		$label               = apply_filters( 'kyom_contact_method_label', $label . ' URL', $key );
		$new_methods[ $key ] = $label;
	}

	return $new_methods;
} );


/**
 * If redirect to is set, move permanently.
 */
add_action( 'template_redirect', function () {
	if ( ! is_singular() ) {
		return;
	}
	$redirect_to = get_post_meta( get_queried_object_id(), 'redirect_to', true );
	if ( $redirect_to ) {
		wp_redirect( $redirect_to, 301 );
		exit;
	}
} );
