<?php
/**
 * Link related functions.
 *
 * @package kyom
 */


/**
 * Display breadcrumbs
 */
function kyom_breadcrumb() {
	if ( ! function_exists( 'bcn_display' ) ) {
		return;
	}
	$option = get_option( 'bcn_options', [] );
	$delimiter = $option['hseparator'] ?? ' &gt; ';
	$bcn = explode( $delimiter, bcn_display( true ) );
	$items = array_map( function( $link ) {
		if ( 0 === strpos( $link, '<a ' ) ) {
			// This is link.
			$markup = $link;
		} else {
			$markup = sprintf( '<span>%s</span>', $link );
		}
		return sprintf( '<li>%s</li>', $markup );
	}, $bcn );
	printf( '<ul class="uk-breadcrumb">%s</ul>', implode( '', $items ) );
}

/**
 * Get archive top.
 *
 * @param null|int|WP_Post $post
 * @return string
 */
function kyom_archive_top( $post = null ) {
	$post = get_post( $post );
	$link = '';
	if ( ! $post ) {
		// no archive.
	} else {
		switch ( $post->post_type ) {
			case 'page':
				$link = home_url();
				break;
			case 'post':
				if ( $front_id = get_option( 'page_for_posts' ) ) {
					$link = get_permalink( $front_id );
				} else {
					$link = home_url();
				}
				break;
			case 'quote':
				$link = home_url( 'quotes' );
				break;
			default:
				// This is custom post type.
				$post_type_object = get_post_type_object( $post->post_type );
				if ( $post_type_object->has_archive ) {
					$link = get_post_type_archive_link( $post->post_type );
				}
				break;
		}
	}
	
	/**
	 * kyom_archive_top
	 *
	 * Archive URL for specified post.
	 *
	 * @param string  $link
	 * @param WP_Post $post
	 */
	return apply_filters( 'kyom_archive_top', $link, $post );
}

/**
 * Get todo strings.
 *
 * @return array
 */
function kyom_todo_when_404() {
	$todo = [
		'wrong_link'      => __( 'If you are from an external site, URL might be wrong. Please check it is correct.' ),
		'deleted_content' => __( 'Content on this URL might be deleted in some reason.' ),
		'try_search'      => __( 'Please try search form below to find what you are looking for.', 'kyom' ),
		'simply_back'     => __( 'Are you lost yourself? Simply back to former page by clicking browser\'s back button.', 'kyom' ),
	];
	
	/**
	 * kyom_todo_when_404
	 *
	 * Todo list on 404 page.
	 *
	 * @param array $todo
	 */
	return apply_filters( 'kyom_todo_when_404', $todo );
}

/**
 * Get primary term for post.
 *
 * @param null|int|WP_Post $post
 *
 * @return WP_Term
 */
function kyom_get_primary_term( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	switch ( $post->post_type ) {
		case 'post':
			$primary_taxonomy = 'category';
			break;
		case 'product':
			$primary_taxonomy = 'product_cat';
			break;
		case 'podcast':
			$primary_taxonomy = 'post_tag';
			break;
		case 'jetpack-portfolio':
			$primary_taxonomy = 'jetpack-portfolio-type';
			break;
		default:
			$primary_taxonomy = '';
			break;
	}
	
	/**
	 * kyom_primary_taxonomy
	 *
	 * Get primary taxonomy name
	 *
	 * @param string  $primary_taxonomy
	 * @param WP_Post $post
	 */
	$primary_taxonomy = apply_filters( 'kyom_primary_taxonomy', $primary_taxonomy, $post );
	if ( ! $primary_taxonomy ) {
		return null;
	}
	$terms = get_the_terms( $post, $primary_taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}
	return current( $terms );
}

/**
 * Get primary term link.
 *
 * @param null|int|WP_Post $post
 *
 * @return string
 */
function kyom_primary_term_link( $post = null ) {
	$term = kyom_get_primary_term( $post );
	return $term ? sprintf( '<a class="primary-term-link" href="%s">%s</a>', get_term_link( $term ), esc_html( $term->name ) ) : '';
}

/**
 * Get related pages.
 *
 * @param null|int|WP_Post $post
 *
 * @return array
 */
function kyom_get_related_pages( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return [];
	}
	$pages = [];
	// Get parents.
	if ( $post->post_parent ) {
		$parent = get_post( $post->post_parent );
		if ( 'publish' === $parent->post_status ) {
			$pages[] = $parent;
		}
		// Get siblings.
		foreach (
			get_posts( [
				'post_type'      => 'page',
				'post_parent'    => $post->post_parent,
				'post__not_in'   => [ $post->ID ],
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
			] ) as $page
		) {
			$pages[] = $page;
		}
	}
	// Get children.
	foreach ( get_posts( [
		'post_type'      => 'page',
		'post_parent'    => $post->ID,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	] ) as $page ) {
		$pages[] = $page;
	}
	return $pages;
}
