<?php
/**
 * Display related functions
 *
 * @package kyom
 */


/**
 * Get parenthesis
 *
 * @param string $string
 *
 * @return string
 */
function kyom_paren( $string ) {
	return sprintf( _x( '(%s)', 'parenthesis', 'kyom' ), $string );
}

/**
 * Get hex
 *
 * @see https://mekshq.com/how-to-convert-hexadecimal-color-code-to-rgb-or-rgba-using-php/
 * @author Harshil Barot
 * @param $color
 * @param bool|float $opacity
 *
 * @return string
 */
function kyom_hex2rgba($color, $opacity = false) {
	
	$default = 'rgb(0,0,0)';
	
	//Return default if no color provided
	if(empty($color))
		return $default;
	
	//Sanitize $color if "#" is provided
	if ($color[0] == '#' ) {
		$color = substr( $color, 1 );
	}
	
	//Check if color has 6 or 3 characters and get values
	if (strlen($color) == 6) {
		$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
	} elseif ( strlen( $color ) == 3 ) {
		$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
	} else {
		return $default;
	}
	
	//Convert hexadec to rgb
	$rgb =  array_map('hexdec', $hex);
	
	//Check if opacity is set(rgba or rgb)
	if($opacity){
		if(abs($opacity) > 1)
			$opacity = 1.0;
		$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
	} else {
		$output = 'rgb('.implode(",",$rgb).')';
	}
	
	//Return rgb(a) color string
	return $output;
}

/**
 * Get archive slug.
 *
 * @return string
 */
function kyom_archive_slug() {
	$slug = '';
	
	/**
	 * kyom_archive_slug
	 *
	 * Return archive slug.
	 *
	 * @param string $slug
	 */
	return apply_filters( 'kyom_archive_slug', $slug );
}

/**
 * Display archive title.
 *
 *
 */
function kyom_archive_title() {
	if ( is_search() ) {
		$title = __( 'Search Results', 'kyom' );
	} elseif ( 'all' == get_query_var( 'quote' ) ) {
		$title = __( 'Quotes Collection', 'kyom' );
	} elseif ( is_post_type_archive() ) {
		$post_type        = get_query_var( 'post_type' );
		$post_type_object = get_post_type_object( $post_type );
		$title            = $post_type_object ? $post_type_object->label : get_the_archive_title();
	} else {
		$title = get_the_archive_title();
	}
	return apply_filters( 'kyom_archive_title', $title );
}

/**
 * Get archive description
 *
 * @return string
 */
function kyom_archive_description() {
	$description = '';
	$object = get_queried_object();
	if ( is_a( $object, 'WP_Term' ) ) {
		$description = $object->description;
	} elseif ( is_post_type_archive() ) {
		$post_type_object = get_post_type_object( get_query_var( 'post_type' ) );
		$description = $post_type_object ? $post_type_object->description : '';
	} elseif ( is_search() ) {
		$description = sprintf( esc_html( __( 'Search results of "%s".', 'kyom' ) ), get_search_query() );
	}
	return $description;
}

/**
 * Get searchable taxonomies.
 *
 * @return array
 */
function kyom_searchable_taxonomies() {
	$default = [
		'category' => 'cat',
		'post_tag' => 'tag',
	];
	
	return apply_filters( 'kyom_searchable_taxonomies', $default );
}

/**
 * Shorten number.
 *
 * @param int $number
 *
 * @return string
 */
function kyom_short_digits( $number ) {
	$number = (int) $number;
	$base = [ 'K', 'M', 'B' ];
	$hit  = 1;
	$suffix = '';
	foreach ( $base as $index => $letter ) {
		$divider = pow( 1000, $index + 1 );
		if ( 1 <= $number / $divider ) {
			$hit    = $divider;
			$suffix = '<small>' . $letter . '</small>';
		}
	}
	return floor( $number / $hit ) . $suffix;
}
