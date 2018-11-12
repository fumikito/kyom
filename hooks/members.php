<?php
/**
 * Member related functions.
 *
 * @package kyom
 */




/**
 * Save last logged in time.
 *
 * @param string $login
 * @param \WP_User $user
 */
add_action('wp_login', function( $login, WP_User $user ) {
	$now = current_time('mysql');
	update_user_meta($user->ID, 'last_login', $now );
	
	// TODO: Add cookie to refresh data.
}, 10, 2);

/**
 * Add cookie if user logged in.
 */
add_action( 'wp_login', function($login_name, $current_user){
	setcookie("kyom-customer", 1, current_time('timestamp') + ( 3600 * 24 * 14 ), "/",  preg_replace('#https?://#', '', rtrim(home_url(), '/')));
}, 10, 2 );

/**
 * Remove cookie if user is logged out.
 */
add_action('wp_logout', function(){
	setcookie("kyom-customer", 1, current_time('timestamp') - 10, "/",  preg_replace('#https?://#', '', rtrim(home_url(), '/')));
});


/**
 * Add mail chimp section.
 */
add_action( 'customize_register', function( WP_Customize_Manager $wp_customize ) {
	
	// Add news letter section.
	$wp_customize->add_section( 'kyom_newsletter_section', [
		'title'    => __( 'Newsletter', 'kyom' ),
		'priority' => 10001,
	] );
	
	kyom_register_customizer( $wp_customize, 'kyom_newsletter_section', [
		'kyom_mailchimp_popup' => [
			'label'       => __( 'Mailchimp Pop Up Script', 'kyom' ),
			'section'     => 'kyom_newsletter_section',
			'description' => __( 'Pasete pop up script of mail chimp here.', 'kyom' ),
			'type'        => 'textarea'
		]
	] );
} );



/**
 * Emebed popup
 */
add_action( 'wp_footer', function() {
	if ( ! $mail_chimp = get_option( 'kyom_mailchimp_popup' ) ) {
		return;
	}
	echo $mail_chimp;
}, 999 );



/**
 * Add admin setting.
 */
add_action( 'admin_init', function() {
	add_settings_section( 'newsletter', __( 'News Letter', 'kyom' ), function() {
	}, 'reading' );
	add_settings_field( 'newsletter_page', __( 'Newsletter Page', 'kyom' ), function() {
		$page_id = get_option( 'newsletter_page', 0 );
		wp_dropdown_pages( [
			'depth' => 0,
			'selected' => $page_id,
			'name' => 'newsletter_page',
			'id' => 'newsletter_page',
			'value_field' => 'ID',
			'show_option_none' => __( 'No newsletter page', 'kyom' )
		] );
	}, 'reading', 'newsletter' );
	register_setting( 'reading', 'newsletter_page' );
} );
