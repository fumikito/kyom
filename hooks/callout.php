<?php
/**
 * Get site wide callout.
 *
 * @package kyom
 */

/**
 * Get callouts.
 *
 * @return array[]
 */
function kyom_get_callouts() {
	$call_outs = apply_filters( 'kyom_callouts', [] );
	$call_outs = array_filter( array_map( function( $call_out ) {
		$call_out = wp_parse_args( $call_out, [
			'style'   => 'info',
			'text'    => '',
			'display' => '',
			'icon'    => 'info',
			'slug'  => 'callout'
		] );
		return $call_out;
	}, $call_outs ), function( $call_out ) {
		return isset( $call_out['text'] ) && $call_out['text'] ;
	} );
	return $call_outs;
}

/**
 * Add body class if callouts exist.
 */
add_filter( 'body_class', function( $classes = [] ) {
	if ( kyom_get_callouts() ) {
		$classes[] = 'has-callouts';
	}
	return $classes;
} );

/**
 * Add call outs.
 */
add_action( 'wp_footer', function( ) {
	$call_outs = kyom_get_callouts();
	if ( ! $call_outs ) {
		return;
	}
	?>
	<aside class="kyom-callouts">
		<?php foreach ( $call_outs as $call_out ) : ?>
			<div class="kyom-callout kyom-callout-<?= esc_attr( $call_out['style'] ) ?>" data-display="<?= esc_attr( $call_out['display'] ) ?>" data-slug="<?= esc_attr( $call_out['slug'] ) ?>">
				<div class="kyom-callout-text">
					<button class="kyom-callout-button">
						<span uk-icon="close"></span>
					</button>
					<span uk-icon="<?= esc_attr( $call_out['icon'] ) ?>" class="kyom-callout-icon"></span>
					<div class="kyom-callout-scroll">
						<div class="kyom-callout-scroll-body">
							<?= wp_kses_post( $call_out['text'] ) ?>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</aside>
	<?php
}, 1000 );

/**
 * If option is set, render callouts.
 */
add_filter( 'kyom_callouts', function( $call_outs ) {
	$call_out = get_option( 'kyom_callout_text', '' );
	if ( ! $call_out ) {
		return $call_outs;
	}
	$call_outs[] = [
		'text' => $call_out,
		'style' => get_option( 'kyom_callout_style', 'primary' ),
	];
	return $call_outs;
} );
