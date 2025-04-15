<?php
/**
 * Date functions
 *
 * @package kyom
 */


/**
 * Detect if post is outdated.
 *
 * @param int|object $post
 *
 * @return boolean
 */
function kyom_is_expired_post( $post = null ) {
	$outdated = apply_filters( 'kyom_outdated_days', 365, get_post( $post ) );
	return kyom_get_outdated_days( $post ) > $outdated;
}

/**
 * Get outdated days
 *
 * @param int|object $post
 *
 * @return int
 */
function kyom_get_outdated_days( $post ) {
	$post = get_post( $post );

	return floor( ( current_time( 'timestamp' ) - strtotime( $post->post_date ) ) / 60 / 60 / 24 );
}

/**
 * Get oldest date of this blog.
 *
 * @param string $format
 * @return string
 */
function kyom_oldest_date( $format = 'Y' ) {
	$query = new WP_Query( [
		'post_type'      => [ 'page', 'post' ],
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
		'orderby'        => 'date',
		'order'          => 'ASC',
	] );
	if ( ! $query->have_posts() ) {
		return date_i18n( $format );
	}
	$date = $query->posts[0]->post_date;
	$year = mysql2date( $format, $date );

	/**
	 * kyom_copyright_year
	 *
	 * The copyright year of this blog.
	 *
	 * @param string $year
	 * @param string $date
	 */
	return apply_filters( 'kyom_copyright_year', $year, $date );
}

/**
 * Get outdated days
 *
 * @param int|object|WP_Post $post
 *
 * @return string
 */
function kyom_get_outdated_string( $post = null ) {
	$post      = get_post( $post );
	$date_diff = floor( ( current_time( 'timestamp', true ) - strtotime( $post->post_date_gmt ) ) / 60 / 60 / 24 );
	$year      = floor( $date_diff / 365 );
	$string    = sprintf( _n( '%s year', '%s years', $year, 'kyom' ), number_format( $year ) );
	if ( ( $date_diff / 365 ) - $year > 0.5 ) {
		$string .= _x( ' and half', 'date', 'kyom' );
	}
	return apply_filters( 'kyom_outdated_days_string', $string, $date_diff, $post );
}

/**
 * Get date diff string.
 *
 * @param $date          Date string.
 * @param string $now    Starting point for calculating diff.
 * @param bool   $gmt    Default false.
 *
 * @return string
 */
function kyom_date_diff( $date, $now = 'now', $gmt = false ) {
	if ( $gmt ) {
		$time_zone = new DateTimeZone( 'UTC' );
	} else {
		$time_zone = wp_timezone();
	}
	$date = new DateTime( $date, $time_zone );
	$now  = new DateTime( $now, $time_zone );
	$diff = $now->diff( $date );
	if ( $diff->y ) {
		$label = sprintf( _n( '%s year ago', '%s years ago', $diff->y, 'kyom' ), $diff->y );
	} elseif ( $diff->m ) {
		$label = sprintf( _n( '%s month ago', '%s months ago', $diff->m, 'kyom' ), $diff->m );
	} elseif ( $diff->d ) {
		$label = ( 1 === $diff->d ) ? __( 'Yesterday' ) : sprintf( __( '%s days ago', 'kyom' ), $diff->d );
	} elseif ( $diff->h ) {
		$label = sprintf( _n( '%s hour ago', '%s hours ago', $diff->h, 'kyom' ), $diff->h );
	} elseif ( 1 < $diff->i ) {
		$label = sprintf( __( '%d minutes ago', 'kyom' ), $diff->i );
	} else {
		$label = __( 'Just Now', 'kyom' );
	}
	return $label;
}

/**
 * Detect if post is new.
 *
 * @param null|int|WP_Post $post
 *
 * @return bool
 */
function kyom_is_new( $post = null ) {
	$post = get_post( $post );

	/**
	 * Days to decide the post is new or not.
	 */
	$kyom_new_limit = apply_filters( 'kyom', 7, $post );

	return ( current_time( 'timestamp', true ) - strtotime( $post->post_date_gmt ) ) / 60 / 60 / 24 < $kyom_new_limit;
}
