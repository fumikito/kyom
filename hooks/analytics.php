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
