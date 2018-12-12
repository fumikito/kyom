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

add_action( 'customize_register', function( WP_Customize_Manager $wp_customize ) {
	
	// Add ad section.
	$wp_customize->add_section( 'kyom_portfolio_section' , [
		'title'      => __( 'Portfolio', 'kyom' ),
		'priority'   => 10000,
	] );
	
	//
	$args = [
		'kyom_contact_url' => [
			'label'       => __( 'Contact URL', 'kyom' ),
			'description' => __( 'If set, contact button will be displayed.', 'kyom' ),
		],
	];
	kyom_register_customizer( $wp_customize, 'kyom_portfolio_section', $args );
} );
