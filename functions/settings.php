<?php
/**
 * Settings API
 *
 * @package kyom
 */

/**
 * Register
 *
 * @param WP_Customize_Manager $customizer
 * @param string               $section
 * @param array                $args
 */
function kyom_register_customizer( $customizer, $section, $args = [] ) {
	foreach ( $args as $id => $arg ) {
		$arg = wp_parse_args( $arg, [
			'label'       => '',
			'stored'      => 'option',
			'type'        => 'text',
			'description' => '',
			'input_attrs' => [],
			'default'     => '',
			'transport' => 'refresh',
		] );
		$customizer->add_setting( $id , array(
			'default'   => $arg['default'],
			'type'      => $arg['stored'],
			'transport' => $arg['transport'],
		) );
		$customizer->add_control( new WP_Customize_Control( $customizer, $id, [
			'label'       => $arg['label'],
			'section'     => $section,
			'description' => $arg['description'],
			'type'        => $arg['type'],
			'input_attrs' => $arg['input_attrs'],
		] ) );
	}
}

/**
 * Get AMP color.
 *
 * @return string
 */
function kyom_amp_link_color() {
	$option = wp_parse_args( (array) get_option( 'amp_customizer', [] ), [
		'header_background_color' => AMP_Customizer_Design_Settings::DEFAULT_HEADER_BACKGROUND_COLOR,
	] );
	return $option['header_background_color'];
}
