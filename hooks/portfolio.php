<?php

/**
 *
 */


/**
 * If portfolio, ad shouldn't be displayed.
 *
 * @param bool $should_display
 * @return bool
 */
function kyom_is_not_portfolio( $should_display ) {
	if ( is_singular( 'jetpack-portfolio' ) ) {
		return false;
	} else {
		return $should_display;
	}
}

add_filter( 'kyom_should_display_before_content_ad', 'kyom_is_not_portfolio' );
add_filter( 'kyom_should_display_footer_ad', 'kyom_is_not_portfolio' );
add_filter( 'kyom_should_display_in_article_ad', 'kyom_is_not_portfolio' );
