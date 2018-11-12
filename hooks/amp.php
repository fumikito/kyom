<?php
/**
 * Amp related functions.
 *
 * @package kyom
 */

/**
 * Amd amp footer.
 */
add_filter( 'amp_post_article_footer_meta', function( $meta ) {
	$meta = array_merge( [ 'kyom-after-content' ], $meta );
	$meta[] = 'kyom-related-posts';
	return $meta;
} );

add_action( 'amp_post_template_head', function () {

} );


/**
 * Add custom css
 *
 * @param string $amp_template
 */
add_action( 'amp_post_template_css', function ( $amp_template ) {
	$css = apply_filters( 'kyom_css_source', get_template_directory() . '/assets/css/amp.css' );
	if ( file_exists( $css ) ) {
		echo trim( preg_replace( '#/\*(.*?)\*/#', '', file_get_contents( $css ) ) );
	}
	$color = kyom_amp_link_color();
	echo <<<CSS
.kyom-amp-return a:link,
.kyom-amp-return a:visited{
  color: {$color};
  border-color: {$color};
}
.kyom-amp-return a:hover,
.kyom-amp-return a:active{
  color: #fff;
  background-color: {$color};
  border-color: {$color};
}
CSS;

} );

/**
 * Remove merriweather
 */
add_action( 'amp_post_template_data', function ( $data ) {
	if ( 'ja' == kyom_is_cjk() ) {
		$data['font_urls'] = [
			'FontAwesome' => 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
		];
	}
	return $data;
} );

/**
 * Add ad for content.
 */
add_action( 'pre_amp_render_post', function () {
	add_filter( 'the_content', function ( $content ) {
		// Ad title.
		if ( $amp_ad = get_option( 'kyom_ad_amp_title' ) ) {
			$ad      = <<<HTML
<div class="amp-ad-container">{$amp_ad}</div>
HTML;
			$content = $ad . $content;
		}
		return $content;
	} );
} );

/**
 * ロゴ追加
 */
add_filter( 'amp_post_template_metadata', function ( $data ){
	$data['publisher']['logo'] = [
		'@type' => 'ImageObject',
		'url' => get_stylesheet_directory_uri().'/styles/img/favicon/amp-logo.png',
		'height' => 60,
		'width' => 600,
	];
	return $data;
} );

/**
 * Register Google Analytics.
 */
add_filter( 'amp_post_template_analytics', function ( $analytics ) {
	if ( ! is_array( $analytics ) ) {
		$analytics = [];
	}
	
	if ( ! ( $tracking_id = get_option( 'kyom_tracking_id' ) ) ) {
		return $analytics;
	}
	
	// https://developers.google.com/analytics/devguides/collection/amp-analytics/
	$analytics['googleanalytics'] = [
		'type'        => 'googleanalytics',
		'attributes'  => [
			// 'data-credentials' => 'include',
		],
		'config_data' => [
			'vars'     => [
				'account' => $tracking_id,
			],
			'triggers' => [
				'trackPageview' => [
					'on'      => 'visible',
					'request' => 'pageview',
				],
			],
		],
	];
	
	return $analytics;
} );
