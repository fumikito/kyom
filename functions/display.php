<?php
/**
 * Display related functions
 *
 * @package kyom
 */


/**
 * Get parenthesis
 *
 * @param string $string
 *
 * @return string
 */
function kyom_paren( $string ) {
	return sprintf( _x( '(%s)', 'parenthesis', 'kyom' ), $string );
}



/**
 * Get archive slug.
 *
 * @return string
 */
function kyom_archive_slug() {
	$slug = '';
	
	/**
	 * kyom_archive_slug
	 *
	 * Return archive slug.
	 *
	 * @param string $slug
	 */
	return apply_filters( 'kyom_archive_slug', $slug );
}

/**
 * Display archive title.
 *
 *
 */
function kyom_archive_title() {
	if ( is_search() ) {
		$title = __( 'Search Results', 'kyom' );
	} elseif ( 'all' == get_query_var( 'quote' ) ) {
		$title = __( 'Quotes Collection', 'kyom' );
	} elseif ( is_post_type_archive() ) {
		$post_type        = get_query_var( 'post_type' );
		$post_type_object = get_post_type_object( $post_type );
		$title            = $post_type_object ? $post_type_object->label : get_the_archive_title();
	} else {
		$title = get_the_archive_title();
	}
	return apply_filters( 'kyom_archive_title', $title );
}

/**
 * Get archive description
 *
 * @return string
 */
function kyom_archive_description() {
	$description = '';
	$object = get_queried_object();
	if ( is_a( $object, 'WP_Term' ) ) {
		$description = $object->description;
	} elseif ( is_post_type_archive() ) {
		$post_type_object = get_post_type_object( get_query_var( 'post_type' ) );
		$description = $post_type_object ? $post_type_object->description : '';
	} elseif ( is_search() ) {
		$description = sprintf( esc_html( __( 'Search results of "%s".', 'kyom' ) ), get_search_query() );
	}
	return $description;
}

/**
 * Get searchable taxonomies.
 *
 * @return array
 */
function kyom_searchable_taxonomies() {
	$default = [
		'category' => 'cat',
		'post_tag' => 'tag',
	];
	
	return apply_filters( 'kyom_searchable_taxonomies', $default );
}
