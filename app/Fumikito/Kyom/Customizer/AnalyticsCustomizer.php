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
		];
	}
}
