<?php
/**
 * Content related functions.
 *
 * @package kyom
 */

/**
 * Get content locale
 *
 * @return string
 */
function kyom_get_content_locale( $post = null ) {
	$post = get_post( $post );
	$locale = get_post_meta( $post->ID, '_locale', true );
	if ( ! $locale ) {
		$locale = get_locale();
	}

	/**
	 * kyom_locale
	 *
	 * Filter for content locale.
	 *
	 * @param string  $locale
	 * @param WP_Post $post
	 */
	return apply_filters( 'kyom_locale', $locale, $post );
}

/**
 * Get top level category
 *
 * @param null|int|WP_Post $post
 *
 * @return mixed|WP_Term|null
 */
function kyom_get_top_category( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	switch ( $post->post_type ) {
		case 'post':
			$taxonomy = 'category';
			break;
		case 'product':
			$taxonomy = 'product_cat';
			break;
		default:
			$taxonomy = '';
			foreach ( get_post_taxonomies( $post ) as $post_tax ) {
				$taxonomy = $post_tax;
				break;
			}
			break;
	}
	$taxonomy = apply_filters( 'kyom_top_category_taxonomy', $taxonomy, $post );
	if ( $taxonomy ) {
		$terms = get_the_terms( $post, $taxonomy );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0];
		}
	}
	return null;
}

/**
 * Detect if post is written in CJK.
 *
 * @param null|int|WP_Post $post
 *
 * @return bool
 */
function kyom_is_cjk( $post = null, $global = false ) {
	$locale = kyom_get_content_locale( $post );
	$lang_code = explode( '_', $locale );
	switch ( $lang_code[0] ) {
		case 'ja':
		case 'zh':
		case 'ko':
			return true;
		default:
			return false;
	}
}

/**
 * Get content length.
 *
 * @param null $post
 * @return int
 */
function kyom_get_content_length( $post = null ) {
	$post    = get_post( $post );
	$content = strip_tags( strip_shortcodes( $post->post_content ) );
	if ( kyom_is_cjk( $post ) ) {
		// Count character length.
		return mb_strlen( $content, 'utf-8' );
	} else {
		// Count words.
		return str_word_count( $content );
	}
}

/**
 * Is post updated after published?
 *
 * @param null|int|WP_Post $post Post object.
 * @return bool
 */
function kyom_is_updated( $post = null ) {
	$post      = get_post( $post );
	if ( 'publish' !== $post->post_status ) {
		return false;
	}
	$updated   = strtotime( $post->post_modified );
	$published = strtotime( $post->post_date );
	$diff      = apply_filters( 'kyom_updated_post_time', 86400 * 7, $post );
	return $updated > $published + $diff;
}

/**
 * Get formatted content length.
 *
 * @param null|int|WP_Post $post
 * @return string
 */
function kyom_content_length_formatted( $post = null ) {
	$length = kyom_get_content_length( $post );
	if ( kyom_is_cjk( $post ) ) {
		return sprintf( _n( '%s letter', '%s letters', $length, 'kyom' ), number_format( $length ) );
	} else {
		return sprintf( _n( '%s word', '%s words', $length, 'kyom' ), number_format( $length ) );
	}
}

/**
 * Detect reading time
 *
 * @param null|int|WP_Post $post
 * @return int
 */
function kyom_reading_minutes( $post = null ) {
	$length = kyom_get_content_length( $post );
	if ( kyom_is_cjk( $post ) ) {
		// 800 letters per minutes.
		// https://www.sokunousokudoku.net/hakarukun/
		$minutes = ceil( $length / 800 );
	} else {
		// 200 words per minutes.
		// http://www.readingsoft.com/
		$minutes = ceil( $length / 200 );
	}

	/**
	 * kyom_reading_time
	 *
	 * Minutes to read full content.
	 *
	 * @param int     $minutes
	 * @param WP_Post $post
	 * @param int     $length
	 */
	return apply_filters( 'kyom_reading_time', $minutes, $post, $length );
}

/**
 * Get formatted reading time.
 *
 * @param null|int|WP_Post $post
 *
 * @return string
 */
function kyom_reading_time( $post = null ) {
	$minutes = kyom_reading_minutes( $post );
	return sprintf( _n(  '%s minute to read', '%s minutes to read', $minutes, 'kyom' ), number_format( $minutes ) );
}

/**
 * Detect if posts are children.
 *
 * @param null|int|WP_Post $post
 *
 * @return bool
 */
function kyom_is_parent( $post = null ) {
	$post = get_post( $post );
	$global = get_queried_object();
	if ( ! is_a( $global, 'WP_Post' ) ) {
		return false;
	}
	return $global->post_parent === $post->ID;
}

/**
 * Parse String via API
 *
 * @param string $string
 *
 * @return array|WP_Error
 */
function kyom_parse_string( $string ) {
	$endpoint = 'https://punctuate.space/json?q='.rawurlencode( $string );
	$response = wp_remote_get( $endpoint );
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	return json_decode( $response['body'], true );
}

/**
 * Get attachment size
 *
 * @param bool             $raw  If true, return int.
 * @param null|int|WP_Post $post Post object.
 *
 * @return string|int
 */
function kyom_attachment_size( $raw = false, $post = null ) {
	$attachment = get_post( $post );
	if ( ! is_attachment( $post ) ) {
		return $raw ? 0 : 'N/A';
	}
	$file = get_attached_file( $attachment->ID );
	if ( ! $file ) {
		return $raw ? 0 : 'N/A';
	}
	$size = filesize( $file );
	if ( false === $size ) {
		return $raw ? 0 : 'N/A';
	}
	if ( $raw ) {
		return $size;
	}
	$suffix = 'B';
	foreach ( [
		'TB' => 4,
		'GB' => 3,
		'MB' => 2,
		'KB' => 1,
	] as $unit => $pow ) {
		$min = pow( 1024, $pow );
		if ( $size > $min ) {
			$suffix = $unit;
			$size   = round( $size / $min, 2 );
			break;
		}
	}
	return $size . $suffix;
}
