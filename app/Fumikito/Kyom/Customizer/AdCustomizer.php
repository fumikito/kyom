<?php

namespace Fumikito\Kyom\Customizer;


use Kunoichi\ThemeCustomizer\CustomizerSetting;

/**
 * Advertisement customizer.
 *
 * @package kyom
 */
class AdCustomizer extends CustomizerSetting {

	protected $section_id = 'kyom_ad_section';

	protected function section_setting() {
		return [
			'title'    => __( 'Advertisement', 'kyom' ),
			'priority' => 10000,
		];
	}

	protected function get_fields(): array {
		$fields = [
			'kyom_ad_after_title' => [
				'label'       => __( 'Ad after title', 'kyom' ),
				'description' => __( 'If set, advertisement will be displayed just after single post page title.', 'kyom' ),
				'stored'      => 'option',
				'type'        => 'textarea',
			],
			'kyom_ad_related'     => [
				'label'       => __( 'Related Ad', 'kyom' ),
				'description' => __( 'If you have related ad of Google Adsense, paste code here.', 'kyom' ),
				'stored'      => 'option',
				'type'        => 'textarea',

			],
			'kyom_ad_automatic'   => [
				'label'       => __( 'Automatic Ad', 'kyom' ),
				'description' => __( 'Enter client ID to enable automatic ad on single page.', 'kyom' ),
				'stored'      => 'option',
				'input_attrs' => [
					'placeholder' => 'e.g. ca-pub-0000000000000000',
				],
			],
			'kyom_ad_content'     => [
				'label'       => __( 'In Article Ad', 'kyom' ),
				'description' => __( 'Insert code for in article ads.', 'kyom' ),
				'type'        => 'textarea',
				'stored'      => 'option',
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

			$args = array_merge( $fields, [
				'kyom_ad_amp_title'   => [
					'label'       => __( 'Ad after Title on AMP', 'kyom' ),
					'description' => __( 'Enter AMP ad tag.', 'kyom' ),
					'type'        => 'textarea',
					'input_attrs' => [
						'placeholder' => 'e.g. &#13;&#10;' . $amp_ad,
					],
					'stored'      => 'option',
				],
				'kyom_ad_amp_content' => [
					'label'       => __( 'Ad after Content on AMP', 'kyom' ),
					'description' => __( 'Enter AMP ad tag.', 'kyom' ),
					'type'        => 'textarea',
					'input_attrs' => [
						'placeholder' => 'e.g. &#13;&#10;' . $amp_ad,
					],
					'stored'      => 'option',
				],
				'kyom_ad_amp_last'    => [
					'label'       => __( 'Ad after related posts on AMP', 'kyom' ),
					'description' => __( 'Enter AMP ad tag.', 'kyom' ),
					'type'        => 'textarea',
					'input_attrs' => [
						'placeholder' => 'e.g. &#13;&#10;' . $amp_ad,
					],
					'stored'      => 'option',
				],
			] );
		}
		return $fields;
	}
}
