<?php
/**
 * Error fix for Gutenberg incompatibility
 */

function jeherve_rm_jetpack_publicize_woocommerce() {
}

add_action( 'init', function() {
	remove_post_type_support( 'jetpack-portfolio', 'publicize' );
}, 1000 );


