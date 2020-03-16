<?php
/**
 * Image related functions
 *
 * @package kyom
 */


/**
 * Get post thumbnail
 *
 *
 * @param null|int|WP_Post $post
 * @param string           $size
 *
 * @return false|string
 */
function kyom_thumbnail_bg( $post = null, $size = 'full' ) {
	$post = get_post( $post );
	if ( has_post_thumbnail( $post ) ) {
		return sprintf( 'background-image: url(\'%s\')', get_the_post_thumbnail_url( $post, $size ) );
	} else {
		return '';
	}
}


/**
 * Get not found image.
 *
 * @return array
 */
function kyom_not_found_image() {
	$image = [
		'url'     => get_template_directory_uri() . '/assets/img/not-found.jpg',
		'version' => filemtime( get_template_directory() . '/assets/img/not-found.jpg' ),
		'credit'  => '(C) Marcelo Jaboo https://www.pexels.com/photo/brown-wooden-armchair-on-brown-wooden-floor-696407/',
	];

	/**
	 * kyom_not_found_image
	 *
	 * Filter for not found photo.
	 *
	 * @param array $image Associative array consist with 'url', 'version', and 'credit'.
	 */
	return apply_filters( 'kyom_not_found_image', $image );
}

/**
 * @param WP_Term $term
 * @param string  $size
 * @return string
 */
function kyom_term_image( $term, $size = 'post-thumbnail' ) {
	$url = '';
	$image_id = get_term_meta( $term->term_id, 'image', true );
	if ( $image_id ) {
		$url = wp_get_attachment_image_url( $image_id, $size );
	}

	/**
	 * kyom_term_image_url
	 *
	 * Get term image if set.
	 *
	 * @param string $url
	 */
	return apply_filters( 'kyom_term_image_url', $url, $term );
}
