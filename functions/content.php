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