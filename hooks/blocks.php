<?php
/**
 * Block related functions
 *
 * @package kyom
 */

/**
 * Register all blocks
 */
add_action( 'init', function () {
	foreach ( kyom_iterate_dir( 'Blocks' ) as $class_name ) {
		/** @var \Fumikito\Kyom\Pattern\BlockBase $class_name */
		$class_name::get_instance();
	}
}, 11 );
