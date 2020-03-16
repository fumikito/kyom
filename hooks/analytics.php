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
	?>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?= esc_attr( $tracking_id ) ?>"></script>
	<script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= esc_attr( $tracking_id ) ?>');
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
