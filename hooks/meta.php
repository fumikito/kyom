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
		$title[ 'category' ] = implode( ', ', array_map( function ( $cat ) {
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
	foreach (
		[
			'facebook'  => '',
			'twitter'   => '',
			'instagram' => '',
			'github'    => '',
			'pinterest' => '',
			'youtube'   => '',
			'wordpress' => 'WordPress',
			'linkedin'  => '',
			'dribbble'  => '',
			'behance'   => '',
			'google'    => '',
		
		] as $key => $label
	) {
		if ( ! $label ) {
			$label = ucfirst( $key );
		}
		$new_methods[ $key ] = $label . ' URL';
	}
	
	return $new_methods;
} );


/**
 * If redirect to is set, move permanently.
 */
add_action( 'template_redirect', function () {
	if ( is_singular() && ( $redirect_to = get_post_meta( get_queried_object_id(), 'redirect_to', true ) ) ) {
		wp_redirect( $redirect_to, 301 );
		exit;
	}
} );

/**
 * Get title separated data.
 *
 * @param null|int|WP_Post $post
 *
 * @return string[]
 */
function kyom_get_title_data( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return [];
	}
	
	return array_filter( (array) get_post_meta( $post->ID, '_kyom_title_data', true ) );
}

/**
 * Save post title data
 *
 * @param null|int|WP_Post $post
 * @reutrn string[]
 */
function kyom_save_title_data( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return [];
	}
	$saved = [];
	try {
		// Check if already saved.
		$data = kyom_get_title_data( $post );
		if ( $data ) {
			list( $original, $parsed ) = $data;
			if ( $original === $post->post_title ) {
				// No change.
				$saved = $parsed;
				throw new Exception( 'No Change.', 200 );
			}
		}
		// Parse via API.
		$response = kyom_parse_string( $post->post_title );
		if ( is_wp_error( $response ) ) {
			delete_post_meta( $post->ID, '_kyom_title_data' );
			throw new Exception( $response->get_error_message(), 500 );
		}
		// Save parsed data.
		update_post_meta( $post->ID, '_kyom_title_data', [
			$post->post_title,
			$response,
		] );
		$saved = $response;
	} catch ( \Exception $e ) {
		// Do nothing.
	} finally {
		update_post_meta( $post->ID, '_kyom_title_updated', current_time( 'timestamp' ) );
	}
	return $saved;
}

/**
 * Save separated title.
 *
 * @param int $post_id
 * @param WP_Post $post
 */
add_action( 'save_post', function ( $post_id, $post ) {
	$post_type_object = get_post_type_object( $post->post_type );
	if ( $post_type_object->public ) {
		// If this is public post type, save.
		kyom_save_title_data( $post );
	}
} );

/**
 * Filter single post title in single page.
 */
add_action( 'wp_body_open', function () {
	if ( ! is_singular() ) {
		return;
	}
	add_filter( 'single_post_title', 'kyom_title_separator', 10, 2 );
}, 9999 );

/**
 * Separate title
 *
 * @param string $title
 * @param null|int|WP_Post $post
 *
 * @return string
 */
function kyom_title_separator( $title, $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return $title;
	}
	// Get old data.
	$data     = kyom_get_title_data( $post );
	if ( $data ) {
		// Data exists.
		list( $original, $parsed ) = $data;
	} else {
		// Data not exits.
		// If no data, try to save if data is old.
		$last_updated = (int) get_post_meta( $post->ID, '_kyom_title_updated', true );
		if ( current_time( 'timestamp' ) > ( $last_updated + 60 * 60 * 24 ) ) {
			// Get latest parsed string and update.
			$parsed = kyom_save_title_data( $post );
		} else {
			// Within 1 day, so just return original title.
			return $title;
		}
	}
	if ( ! $parsed ) {
		return $title;
	}
	return implode( '', array_map( function ( $token ) {
		$classes = preg_match( '#[ぁ-んァ-ヶー一-龠]#u', $token ) ? 'jp' : 'ascii';
		
		return sprintf( '<span class="budou %s">%s</span>', $classes, esc_html( $token ) );
	}, $parsed ) );
}
