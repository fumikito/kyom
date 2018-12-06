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
		'kyom_ad_content' => [
			'label'       => __( 'In Article Ad', 'kyom' ),
			'description' => __( 'Insert code for in article ads.', 'kyom' ),
			'type'        => 'textarea',
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

/**
 * Display automatic ad.
 *
 * @internal
 * @param string $content
 * @return string
 */
function kyom_in_article_ads( $content ) {
	$code = get_option( 'kyom_ad_content' );
	$should_display_ad = apply_filters( 'kyom_should_display_in_article_ad', $code && ( 'post' === get_post_type() ), get_post() );
	if ( ! $should_display_ad ) {
		return $content;
	}
	
	$html   = sprintf( '<!DOCTYPE html><html><body>%s</body></html>', $content );
	$parser = new \Masterminds\HTML5();
	$dom    = $parser->loadHTML( $html );
	/** @var DOMElement $body */
	$body   = $dom->getElementsByTagName( 'body' )[0];
	$length = $body->childNodes->length;
	$ad_starts = ceil( $length / 3 );
	/**
	 * kyom_minimum_line_count_for_in_article_ad
	 *
	 * @param int     $minium Default 10
	 * @param WP_Post $post
	 */
	$minimum_ad_line = apply_filters( 'kyom_minimum_line_count_for_in_article_ad', 10, get_post() );
	if ( $ad_starts < 10 ) {
		return $content;
	}
	// Add replacer.
	$elem  = $dom->createElement( 'kyomAd' );
	/** @var DOMElement $target */
	$target = $body->childNodes[ $ad_starts - 1 ];
	$body->insertBefore( $elem, $target );
	$html = $parser->saveHTML( $body );
	$html = preg_replace( '#</?body>#u', '', $html );
	$html = str_replace( '<kyomAd></kyomAd>', "\n" . trim ( $code ) . "\n", $html );
	return $html;
}

/**
 * Register in article ad filter.
 */
add_action( 'kyom_before_content', function() {
	add_filter( 'the_content', 'kyom_in_article_ads', 99999 );
} );
add_action( 'kyom_after_content', function() {
	remove_filter( 'the_content', 'kyom_in_article_ads', 99999 );
} );
