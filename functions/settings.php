<?php
/**
 * Settings API
 *
 * @package kyom
 */

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
