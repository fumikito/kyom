<?php
/**
 * Google Analytics tag
 *
 * @package kyom
 */


/**
 * Register analytics tag.
 */
add_action( 'wp_head', function () {
	if ( ! ( $tracking_id = get_option( 'kyom_tracking_id' ) ) ) {
		return;
	}
	$optiona_tag = get_option( 'kyom_tracking_id_option' );
	// Define page type.
	if ( is_front_page() ) {
		$page_type = 'front';
	} else if ( is_home() ) {
		$page_type = 'home';
	} else if ( is_post_type_archive() ) {
		$page_type = get_post_type() . '-archive';
	} else if ( is_singular() ) {
		$page_type = get_queried_object()->post_type;
	} else if ( is_category() ) {
		$page_type = 'category';
	} else if ( is_tag() ) {
		$page_type = 'tag';
	} else if ( is_tax() ) {
		$taxonomies = get_taxonomies();
		$page_type  = 'taxonomy';
		foreach ( $taxonomies as $tax ) {
			if ( is_tax( $tax ) ) {
				$page_type = $tax;
				break;
			}
		}
	} else if ( is_search() ) {
		$page_type = 'search';
	} else {
		$page_type = 'undefined';
	}
	?>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?= esc_attr( $tracking_id ) ?>"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		var config = {
			'custom_map': {
				'dimension1': 'role',
				'dimension2': 'web_font',
				'dimension3': 'post_type',
			}
		};
		<?php if ( is_singular() ) : ?>
		config.role = document.cookie.match(/ctwp_uuid/) ? 'subscriber' : 'anonymous';
		config.post_type = '<?php echo esc_js( $page_type ) ?>';
		<?php endif; ?>
		gtag('config', '<?= esc_attr( $tracking_id ) ?>', config );
		<?php if ( $optiona_tag ) : ?>
		gtag('config', '<?= esc_attr( $optiona_tag ) ?>', config );
		<?php endif; ?>
	</script>
	<?php
}, 9999 );

/**
 * Render facebook & twitter widgets
 *
 * @todo Move app ID to option.
 */
add_action( 'wp_body_open', function() {
	?>
	<div id="fb-root"></div>
	<script> ( function ( d, s, id ) {
			var js, fjs = d.getElementsByTagName( s )[ 0 ];
			if ( d.getElementById( id ) ) return;
			js = d.createElement( s );
			js.id = id;
			js.src = "//connect.facebook.net/ja_JP/all.js#xfbml=1&appId=264573556888294";
			fjs.parentNode.insertBefore( js, fjs );
		}( document, 'script', 'facebook-jssdk' ) );</script>
	<script> ! function ( d, s, id ) {
		var js, fjs = d.getElementsByTagName( s )[ 0 ], p = /^http:/.test( d.location ) ? 'http' : 'https';
		if ( ! d.getElementById( id ) ) {
			js = d.createElement( s );
			js.id = id;
			js.src = p + '://platform.twitter.com/widgets.js';
			fjs.parentNode.insertBefore( js, fjs );
		}
	} ( document, 'script', 'twitter-wjs' );</script>
	<?php
} );
