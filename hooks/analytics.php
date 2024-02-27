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
	$optional_tag = get_option( 'kyom_tracking_id_option' );
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
		config.role = document.cookie.match(/ctwp_uuid/) ? 'subscriber' : 'anonymous';
		config.post_type = '<?php echo esc_js( $page_type ) ?>';
		gtag('config', '<?= esc_attr( $tracking_id ) ?>', config );
		<?php if ( $optional_tag ) : ?>
		gtag('config', '<?= esc_attr( $optional_tag ) ?>', config );
		<?php endif; ?>
	</script>
	<?php
}, 9999 );

/**
 * Render facebook & twitter widgets
 *
 */
add_action( 'wp_body_open', function() {
	$fb_app_id = get_option( 'kyom_facebook_app_id' );
	if ( $fb_app_id ) :
	?>
	<div id="fb-root"></div>
	<script>
		window.fbAsyncInit = function() {
			FB.init({
				appId            : '<?php echo esc_js( $fb_app_id ); ?>',
				autoLogAppEvents : true,
				xfbml            : true,
				version          : 'v23.0'
			});
		};
	</script>
	<script async defer crossorigin="anonymous" src="https://connect.facebook.net/ja_JP/sdk.js"></script>
	<?php endif; ?>
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

/**
 * Override ga communicator values.
 */
add_filter( 'ga_communicator_predefined_option', function( $option, $key ) {
	switch ( $key ) {
		case 'ga4-property':
			$map = [
				'ga4-property' => 'kyom_ga4_id',
			];
			return get_option( $map[ $key ] );
		default:
			return $option;
	}
}, 10, 2 );

/**
 * Override ga service account.
 */
add_filter( 'ga_communicator_predefined_key', function() {
	return get_option( 'kyom_ga4_key' );
} );
