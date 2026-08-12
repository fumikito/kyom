<?php
/**
 * Should be removed because it's too specific for takahashifumiki.com
 */

/**
 * Add ebook post type.
 */
add_action( 'init', function () {
	if ( function_exists( 'lwp_files' ) ) {
		return;
	}
	register_post_type( 'ebook', [
		'label'    => '電子書籍',
		'public'   => true,
		'supports' => [ 'title', 'editor', 'author', 'custom-fields' ],
	] );
} );
