<?php
/**
 * Google Analytics tag
 *
 * @package kyom
 */

/**
 * Register ad section
 *
 */
add_action( 'customize_register', function( WP_Customize_Manager $wp_customize ) {
	
	// Add ad section.
	$wp_customize->add_section( 'kyom_analytics_section', [
		'title'    => __( 'Analytics', 'kyom' ),
		'priority' => 10000,
	] );
	
	kyom_register_customizer( $wp_customize, 'kyom_analytics_section', [
		'kyom_tracking_id' => [
			'label' => __( 'Tracking ID', 'kyom' ),
			'input_attr' => [
				'placeholder' => 'UA-5329295-4'
			],
		],
	] );
} );

/**
 * Register analytics tag.
 */
add_action( 'wp_head', function () {
	if ( ! ( $tracking_id = get_option( 'kyom_tracking_id' ) ) ) {
		return;
	}
	?>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?= esc_attr( $tracking_id ) ?>"></script>
	<script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= esc_attr( $tracking_id ) ?>');
	</script>
	<?php
}, 9999 );
