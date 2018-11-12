<?php
/**
 * Advertisement related function
 *
 * @package kyom
 */


/**
 * Register ad section
 *
 */
add_action( 'customize_register', function( WP_Customize_Manager $wp_customize ) {
	
	// Add ad section.
	$wp_customize->add_section( 'kyom_ad_section' , [
		'title'      => __( 'Advertisement', 'kyom' ),
		'priority'   => 10000,
	] );
	
	$args = [
		'kyom_ad_after_title' => [
			'label'       => __( 'Ad after title', 'kyom' ),
			'description' => __( 'If set, advertisement will be displayed just after single post page title.', 'kyom' ),
			'type'        => 'textarea'
		],
		'kyom_ad_related'     => [
			'label'       => __( 'Related Ad', 'kyom' ),
			'description' => __( 'If you have related ad of Google Adsense, paste code here.', 'kyom' ),
			'type'        => 'textarea'
		
		],
		'kyom_ad_automatic'   => [
			'label'       => __( 'Automatic Ad', 'kyom' ),
			'description' => __( 'Enter client ID to enable automatic ad on single page.', 'kyom' ),
			'input_attrs' => [
				'placeholder' => 'e.g. ca-pub-0000000000000000',
			],
		],
	];
	if ( defined( 'AMP__VERSION' ) ) {
		$amp_ad = <<<HTML
<amp-ad
	type="adsense"
	data-ad-client="ca-pub-00000000000"
	data-ad-slot="00000000"
	width="300"
	height="250">
</amp-ad>
HTML;
		$amp_ad = str_replace( "\n", '&#13;&#10;', $amp_ad );

		$args = array_merge( $args, [
			'kyom_ad_amp_title' => [
				'label'       => __( 'Ad after Title on AMP', 'kyom' ),
				'description' => __( 'Enter AMP ad tag.', 'kyom' ),
				'type'        => 'textarea',
				'input_attrs' => [
					'placeholder' => 'e.g. &#13;&#10;' . $amp_ad,
				],
			],
			'kyom_ad_amp_content' => [
				'label'       => __( 'Ad after Content on AMP', 'kyom' ),
				'description' => __( 'Enter AMP ad tag.', 'kyom' ),
				'type'        => 'textarea',
				'input_attrs' => [
					'placeholder' => 'e.g. &#13;&#10;' . $amp_ad,
				],
			],
			'kyom_ad_amp_last' => [
				'label'       => __( 'Ad after related posts on AMP', 'kyom' ),
				'description' => __( 'Enter AMP ad tag.', 'kyom' ),
				'type'        => 'textarea',
				'input_attrs' => [
					'placeholder' => 'e.g. &#13;&#10;' . $amp_ad,
				],
			],
		] );
	}
	
	kyom_register_customizer( $wp_customize, 'kyom_ad_section', $args );
} );

/**
 * Display ad after title.
 */
add_action( 'kyom_before_content', function () {
	$ad = get_option( 'kyom_ad_after_title' );
	if ( ! $ad ) {
		return;
	}
	echo <<<HTML
<div class="uk-margin-medium-bottom">
{$ad}
</div>
HTML;

} );

/**
 * Display related ads.
 */
add_action( 'kyom_content_footer', function () {
	$ad = get_option( 'kyom_ad_related' );
	if ( ! $ad ) {
		return;
	}
	echo <<<HTML
<div class="uk-margin-medium-bottom">
{$ad}
</div>
HTML;
} );

/**
 * Add Atutomatic ad of Google Adsense
 */
add_action( 'wp_head', function() {
	if ( ! ( $client_id = get_option( 'kyom_ad_automatic' ) ) ) {
		return;
	}
	if ( ! is_singular() ) {
		return;
	}
	?>
	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<script>
      (adsbygoogle = window.adsbygoogle || []).push({
        google_ad_client: "<?= esc_js( $client_id ) ?>",
        enable_page_level_ads: true
      });
	</script>
	<?php
} );
