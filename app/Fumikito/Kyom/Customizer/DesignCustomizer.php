<?php

namespace Fumikito\Kyom\Customizer;


use Kunoichi\ThemeCustomizer\CustomizerSetting;

/**
 * Advertisement customizer.
 *
 * @package kyom
 */
class DesignCustomizer extends CustomizerSetting {

	protected $section_id = 'kyom_design_section';

	protected function section_setting() {
		return [
			'title'    => __( 'Design', 'kyom' ),
			'priority' => 10001,
		];
	}

	protected function get_fields(): array {
		$fields = [
			'kyom_default_eyecatch' => [
				'label'         => __( 'Default Eyecatch', 'kyom' ),
				'description'   => __( 'Default eyecatch for post object.', 'kyom' ),
				'stored'        => 'option',
				'control_class' => 'WP_Customize_Image_Control',
				'default'       => '',
				'width'         => 2048,
				'height'        => 2048,
			],
		];
		return $fields;
	}
}
