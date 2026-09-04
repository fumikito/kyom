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
		$label               = kyom_social_label( $key );
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

/**
 * Keep attachment pages out of the search index.
 *
 * They exist as a browsing aid reached from the parent article
 * （template-parts/singular-main-attachment.php）, not as search landing pages.
 * Google was ranking them for queries that belong to the parent post,
 * so noindex them while keeping `follow` so link equity reaches the parent.
 *
 * @param array $robots Robots directives.
 *
 * @return array
 */
add_filter( 'wp_robots', function ( $robots ) {
	if ( is_attachment() ) {
		$robots = wp_robots_no_robots( $robots );
	}

	return $robots;
} );


/**
 * Keep crawlers out of internal search results.
 *
 * A 2019 link-spam campaign hammered `/?s=<korean spam with URLs>` and those
 * URLs are still sitting in third-party link indexes. Moz's DotBot re-crawls
 * them to this day: of the 2,417 `?s=` requests this site served in the 31
 * days to 2026-09-03, 2,160 were DotBot working through that list and 208
 * were bingbot. Only about 15 came from a browser.
 *
 * Search results are the one template WP Super Cache never caches, so every
 * one of those is a full render plus a LIKE query at the origin. Both bots
 * honour robots.txt, so this removes 98% of it without touching PHP.
 *
 * Core already sends `noindex, follow` on these pages, so nothing of value
 * is lost by keeping compliant crawlers away from them entirely.
 *
 * The rules must land inside the `User-agent: *` group and ahead of the
 * `Sitemap:` line that core appends at priority 0 — simple parsers treat a
 * blank line as the end of a group — hence the negative priority.
 *
 * @filter robots_txt
 *
 * @param string $output Robots.txt content.
 * @param bool   $public Whether the site is public.
 *
 * @return string
 */
add_filter( 'robots_txt', function ( $output, $public ) {
	if ( ! $public ) {
		return $output;
	}
	// `/*?s=` covers search under sub-paths, `/?s=` the literal prefix for
	// parsers with no wildcard support, `/*&s=` search combined with filters.
	foreach ( [ '/?s=', '/*?s=', '/*&s=' ] as $pattern ) {
		$output .= 'Disallow: ' . $pattern . "\n";
	}

	return $output;
}, -1, 2 );
