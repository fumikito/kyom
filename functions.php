<?php
/**
 * Theme bootstrap file.
 *
 * @package hoo
 */

$kyom_theme_info = get_file_data( __DIR__ . '/style.css', [
	'version' => 'Version',
	'domain'  => 'Text Domain',
] );

// Register domain.
load_theme_textdomain( $kyom_theme_info[ 'domain' ], __DIR__ . '/languages' );

// Load autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}


// Load all subroutines.
$templates = [];
$directory_to_scan = [ __DIR__ ];
// If child theme exists, add them.
if ( __DIR__ !== get_stylesheet_directory() ) {
	$directory_to_scan[] = get_stylesheet_directory();
}
// Register file to load.
foreach ( $directory_to_scan as $theme_dir ) {
	foreach ( [ 'functions', 'hooks', 'temporary' ] as $dir ) {
		$dir_path = $theme_dir . '/' . $dir;
		if ( is_dir( $dir_path ) ) {
			foreach ( scandir( $dir_path ) as $file ) {
				if ( ! preg_match( '/^([^._].*)\.php$/u', $file, $matches ) ) {
					continue;
				}
				$slug = $dir . '/' . $matches[1];
				if ( ! in_array( $slug, $templates ) ) {
					$templates[] = $slug;
				}
			}
		}
	}
}

// Load them all.
array_map( function( $template ) {
	get_template_part( $template );
}, $templates );

/**
 * Get theme version.
 *
 * @return string
 */
function kyom_version( $display = false ) {
	global $kyom_theme_info;
	$version = $kyom_theme_info[ 'version' ] ?? '0.0.0';
	$version = trim( $version );
	if ( ! $display ) {
		return $version;
	}
	if ( preg_match( '/^\d+\.\d+\.\d+$/u', $version, $matches ) ) {
		// This is sem ver.
		return 'v' . $matches[0];
	} elseif ( 'nightly' === $version ) {
		return 'Nightly Version';
	} else {
		// This is commit hash.
		return '#' . substr( $version, 0, 7 );
	}
}
