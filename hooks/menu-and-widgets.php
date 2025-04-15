<?php
/**
 * Menu and widgets related hooks.
 *
 * @package kyom
 */

Kunoichi\SetMenu::enable();

// Register sidebar
register_sidebar( [
	'name'          => __( 'After Article', 'kyom' ),
	'id'            => 'normal-sidebar',
	'description'   => __( 'Displayed just after main content. 4 widgets are the best looking.', 'kyom' ),
	'before_widget' => '<div id="%1$s" class="widget normal-widget"><div class="normal-widget-inner %2$s">',
	'after_widget'  => '</div></div>',
	'before_title'  => '<h2  class="uk-heading-line uk-text-center normal-widget-title"><span>',
	'after_title'   => '</span></h2>',
] );
register_sidebar( [
	'name'          => __( 'Footer Sidebar', 'kyom' ),
	'id'            => 'footer-sidebar',
	'description'   => __( 'Displayed in footer. Settling 4 widgets is recommended.', 'kyom' ),
	'before_widget' => '<div id="%1$s" class="widget site-footer-widget %2$s">',
	'after_widget'  => '</div>',
	'before_title'  => '<h2 class="site-footer-widget-title">',
	'after_title'   => '</h2>',
] );

/**
 * Register menus.
 */
add_action( 'init', function () {
	register_nav_menus( [
		'top-pages'    => __( 'Mobile Menu', 'kyom' ),
		'top-pages-pc' => __( 'Desktop Menu', 'kyom' ),
		'bottom-pages' => __( 'Footer Menu', 'kyom' ),
		'social-links' => __( 'Social Links', 'kyom' ),
	] );
} );

/**
 * Register widgets.
 */
add_action( 'widgets_init', function () {
	foreach ( kyom_iterate_dir( 'Widgets' ) as $class_name ) {
		register_widget( $class_name );
	}
} );

/**
 * Filter HTML output of menus.
 *
 * @param string   $item_output HTML.
 * @param WP_Post  $item        Item object.
 * @param int      $depth       Depth.
 * @param stdClass $args        Menu arguments.
 * @return string
 */
add_filter( 'walker_nav_menu_start_el', function ( $item_output, $item, $depth, $args ) {
	if ( 'social-links' !== $args->theme_location ) {
		return $item_output;
	}
	$brand = kyom_icon_from_url( $item->url );
	return sprintf( '<a href="%s" uk-icon="icon:%s; ratio: 2"></a>', esc_url( $item->url ), esc_attr( $brand ) );
}, 10, 4 );
