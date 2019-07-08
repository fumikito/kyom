<?php

namespace Fumikito\Kyom\Customizer;


use Kunoichi\ThemeCustomizer\CustomizerSetting;

/**
 * Portfolio setting.
 */
class PortfolioCustomizer extends CustomizerSetting {
	
	protected $section_id = 'kyom_portfolio_section';
	
	protected function section_setting() {
		return [
			'title'      => __( 'Portfolio', 'kyom' ),
			'priority'   => 10000,
		];
	}
	
	protected function get_fields(): array {
		return [
			'kyom_contact_url' => [
				'label'       => __( 'Contact URL', 'kyom' ),
				'description' => __( 'If set, contact button will be displayed.', 'kyom' ),
				'stored'      => 'option',
			],
		];
	}
}
