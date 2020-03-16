<?php
/**
 * Override function for WP Pagenavi
 *
 * @package kyom
 */

/**
 * Change WP-Pagenavi's output
 *
 * @param string $html
 * @return string
 */
add_filter( 'wp_pagenavi', function ( $html ) {
	// Remove div.
	$html = trim( preg_replace( '/<\/?div([^>]*)?>/u', '', $html ) );
	// Wrap links with li.
	$html = preg_replace( '/(<a[^>]*?>[^<]*<\/a>)/u', '<li>$1</li>', $html );
	// Wrap links with span considering class name.
	$html = preg_replace_callback( '/<span([^>]*?)>[^<]*<\/span>/u', function ( $matches ) {
		if ( false !== strpos( $matches[1], 'current' ) ) {
			// This is current page.
			$class_name = 'uk-active';
		} elseif ( false !== strpos( $matches[1], 'pages' ) ) {
			// This is page number.
			return '';
		} elseif ( false !== strpos( $matches[1], 'extend' ) ) {
			// This is ellipsis.
			$class_name = 'uk-disabled';
		} else {
			// No class.
			$class_name = '';
		}
		return "<li class=\"{$class_name}\">{$matches[0]}</li>";
	}, $html );
	
	// Wrap with ul as you like.
	return <<<HTML
<div class="wp-paginavi">
    <ul class="uk-pagination uk-flex-center">{$html}</ul>
</div>
HTML;
}, 10, 2 );