<?php
/**
 * Path related functions
 *
 * @package kyom
 */


/**
 * Get URL and path in 1 liner.
 *
 * @param string $path
 * @return array  [ $url, $version ]
 */
function kyom_asset_url_and_version( $path ) {
	$path = ltrim( $path, '/' );
	$file = trailingslashit( get_template_directory() ) . 'assets/' . $path;
	$url  = trailingslashit( get_template_directory_uri() ) . 'assets/' . $path;
	return [ $url, file_exists( $file ) ? filemtime( $file ) : kyom_version() ];
}

/**
 * Get root directory of namespace.
 *
 * @return string
 */
function kyom_namespace_root_dir() {
	return get_template_directory() . '/app/Fumikito/Kyom';
}

/**
 * Iterator for directory.
 *
 * @param string $path
 *
 * @return Generator|string[]
 */
function kyom_iterate_dir( $path ) {
	$dir = trailingslashit( kyom_namespace_root_dir() ) . $path;
	if ( is_dir( $dir ) ) {
		foreach ( scandir( $dir ) as $file ) {
			if ( preg_match( '/^([^_.].*)\.php$/u', $file, $match ) ) {
				$class_name = sprintf( 'Fumikito\\Kyom\\%s\\%s', str_replace( '/', '\\', $path ), $match[1] );
				if ( class_exists( $class_name ) ) {
					yield $class_name;
				}
			}
		}
	}
}
