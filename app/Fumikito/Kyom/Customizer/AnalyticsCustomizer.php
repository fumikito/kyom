<?php

namespace Fumikito\Kyom\Customizer;


use Kunoichi\ThemeCustomizer\CustomizerSetting;

class AnalyticsCustomizer extends CustomizerSetting {

	protected $section_id = 'kyom_analytics_section';

	protected function section_setting() {
		return [
			'title'    => __( 'Analytics', 'kyom' ),
			'priority' => 10000,
		];
	}

	protected function get_fields(): array {
		return [
			'kyom_tracking_id' => [
				'label' => __( 'Tracking ID', 'kyom' ),
				'input_attr' => [
					'placeholder' => 'UA-5329295-4'
				],
				'stored' => 'option',
			],
			'kyom_tracking_id_option' => [
				'label' => __( 'Optional Tracking ID', 'kyom' ),
				'input_attr' => [
					'placeholder' => 'G-K61M5SDFXM',
				],
				'stored' => 'option',
			],
			'kyom_facebook_app_id' => [
				'label' => __( 'Facebook APP ID', 'kyom' ),
				'input_attr' => [
					'placeholder' => '264573556888294',
				],
				'stored' => 'option',
			],
			'kyom_ga4_key' => [
				'label' => __( 'Service Account Key for GA4', 'kyom' ),
				'input_attr' => [
					'placeholder' => __( 'Paste JSON here.', 'kyom' ),
				],
				'stored' => 'option',
				'type'        => 'textarea'
			],
			'kyom_ga4_id' => [
				'label' => __( 'GA4 Property ID', 'kyom' ),
				'input_attr' => [
					'placeholder' => '264573556888294',
				],
				'stored' => 'option',
			],
		];
	}
}
