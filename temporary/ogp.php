<?php
/**
 * OGP related functions.
 *
 * @package kyom
 */


/**
 * Remove Jetpack OGP
 */
add_action( 'wp_head', function() {
	remove_action('wp_head','jetpack_og_tags');
}, 1 );



/**
 * Build OGP
 */
add_action('wp_head', function(){
	if ( is_front_page() || is_singular() ) {
		$title = wp_get_document_title();
		$url   = is_front_page() ? home_url() : get_permalink();
		$image = false;
		if ( has_site_icon() ) {
			$image = get_site_icon_url( 1024 );
		}
		$post = get_queried_object();
		if ( has_post_thumbnail( $post ) ) {
			$image = get_the_post_thumbnail_url( $post, 'full' );
		}
		$type = is_front_page() ? 'website' : 'article';
		setup_postdata( $post );
		$desc = explode( '<div class="sharedaddy sd-sharing-enabled">', apply_filters( 'the_excerpt', get_the_excerpt( $post ) ) );
		$desc = str_replace( "\n", '', strip_tags( current( $desc ) ) );
		wp_reset_postdata();
		$dir = get_stylesheet_directory_uri();
		$properties = [
			'name'     => [
				[ 'description', $desc ],
				[ 'copyright', sprintf( 'copyright %s %s', kyom_oldest_date(), get_bloginfo( 'name' ) ) ],
				[ 'twitter:card', 'summary' ],
				[ 'twitter:site', '@takahashifumiki' ],
				[ 'twitter:url', $url ],
				[ 'twitter:title', $title ],
				[ 'twitter:description', $desc ],
				[ 'twitter:image', $image ],
			],
			'property' => [
				[ 'og:locale', 'ja_JP' ],
				[ 'og:title', $title ],
				[ 'og:url', $url ],
				[ 'og:image', $image ],
				[ 'og:description', $desc ],
				[ 'og:type', $type ],
				[ 'og:site_name', get_bloginfo( 'name' ) ],
				[ 'fb:admins', '1034317368' ],
				[ 'fb:pages', '240120469352376' ],
				[ 'fb:app_id', '264573556888294' ],
				[ 'fb:profile_id', '240120469352376' ],
				[ 'article:author', 'https://www.facebook.com/TakahashiFumiki.Page/' ],
				[ 'article:publisher', '240120469352376' ],
			],
		];
		if ( ! is_front_page() ) {
			$terms = [];
			if ( $categories = get_the_category( get_queried_object_id() ) ) {
				$terms += $categories;
				foreach ( $categories as $term ) {
					$properties['property'][] = [ 'article:section', $term->name ];
				}
			}
			if ( $tags = get_the_tags( get_queried_object_id() ) ) {
				$terms += $tags;
				foreach ( $tags as $term ) {
					$properties['property'][] = [ 'article:tag', $term->name ];
				}
			}
			if ( $terms ) {
				$properties['name'][] = [ 'keywords', implode( ',', array_map( function( $term ) {
					return $term->name;
				}, $terms ) ) ];
			}
			$properties['property'][] = [ 'article:published_time', mysql2date( DateTime::ATOM, $post->post_date ) ];
			$properties['property'][] = [ 'article:modified_time', mysql2date( DateTime::ATOM, $post->post_modified ) ];
			// Add related
			if ( function_exists( 'yarpp_get_related' ) ) {
				foreach ( yarpp_get_related( [], $post ) as $p ) {
					$properties['property'][] = [ 'og:see_olso', get_permalink( $p ) ];
				}
			}
		} else {
			$properties['name'][] = [ 'p:domain_verify', 'd41b6fbe34cc94d28d077985fdc1fe7a' ];
		}
		foreach ( $properties as $property => $vals ) {
			foreach ( $vals as list( $key, $val ) ) {
				printf( '<meta %s="%s" content="%s" />'."\n", $property, esc_attr( $key ), esc_attr( $val ) );
			}
		}
	}
}, 0 );
