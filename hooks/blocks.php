<?php
/**
 * Block related functions
 *
 * @package kyom
 */

/**
 * Register all blocks (block.json-based)
 */
add_action( 'init', function () {
	$blocks_dir = get_template_directory() . '/assets/blocks';

	// Check if blocks directory exists
	if ( ! is_dir( $blocks_dir ) ) {
		return;
	}

	// Scan blocks directory
	foreach ( scandir( $blocks_dir ) as $block_name ) {
		// Skip hidden files and parent directory references
		if ( '.' === $block_name[0] ) {
			continue;
		}

		$block_json = $blocks_dir . '/' . $block_name . '/block.json';

		// Check if block.json exists
		if ( ! file_exists( $block_json ) ) {
			continue;
		}

		// Register block
		register_block_type( $block_json );
	}
} );

/**
 * Register block category.
 *
 * @param array{title:string, slug:string, icon:string}[]
 */
add_filter( 'block_categories_all', function ( $categories ) {
	$new_categories = [];
	foreach ( $categories as $category ) {
		$new_categories[] = $category;
		if ( 'widgets' === $category['slug'] ) {
			$new_categories[] = [
				'slug'  => 'kyom',
				'title' => 'Kyom',
				'icon'  => 'welcome-view-site',
			];
		}
	}
	return $new_categories;
} );

/**
 * Enqueue block variations script for editor.
 */
add_action( 'enqueue_block_editor_assets', function () {
	wp_enqueue_script(
		'kyom-block-variations',
		get_template_directory_uri() . '/assets/js/block-variations.js',
		[ 'wp-blocks', 'wp-dom-ready', 'wp-edit-post' ],
		kyom_version(),
		true
	);

	// Enqueue editor styles
	wp_enqueue_style(
		'kyom-editor-styles',
		get_template_directory_uri() . '/assets/css/editor.css',
		[],
		kyom_version()
	);
} );
