<?php
/**
 * Portfolio related functions.
 *
 * @package kyom
 */

/**
 * Get source of pull quote.
 *
 * @param null|int|WP_Post $post
 * @return string
 */
function kyom_testimonial_source( $post = null ) {
	$post  = get_post( $post );
	$label = get_post_meta( $post->ID, '_source_label', true );
	$url   = get_post_meta( $post->ID, '_source_url', true );
	if ( 'quote' === $post->post_type ) {
		return get_the_title( $post );
	}
	if ( ! $label && ! $url ) {
		return '';
	} elseif ( ! $url ) {
		return esc_html( $label );
	} elseif ( ! $label ) {
		return sprintf(
			'<a class="pull-quote-link" href="%s" target="_blank">%s</a>',
			esc_url( $url ),
			preg_replace( '#^https?://([^/]+).*?$#u', '$1', $url )
		);
	} else {
		return sprintf(
			'<a class="pull-quote-link" href="%s" target="_blank">%s</a>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
}

/**
 * Register meta box
 */
add_action( 'add_meta_boxes', function ( $post_type ) {
	if ( 'jetpack-testimonial' !== $post_type ) {
		return;
	}
	add_meta_box( 'testimonial-source', __( 'Testimonial Source', 'kyom' ), function( $post ) {
		wp_nonce_field( 'kyom_testimonial', '_testimonialnonce', false );
		?>
		<table class="form-table">
			<tr>
				<th><label for="kyom-testimonial-position"><?php esc_html_e( 'Position', 'kyom' ) ?></label></th>
				<td>
					<input name="kyom-testimonial-position" id="kyom-testimonial-position" type="text" class="regular-text"
						   value="<?= esc_attr( get_post_meta( $post->ID, '_source_position', true ) ) ?>" placeholder="<?php esc_attr_e( 'e.g. Critic', 'kyom' ) ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="kyom-testimonial-source"><?php esc_html_e( 'Source', 'kyom' ) ?></label></th>
				<td>
					<input name="kyom-testimonial-source" id="kyom-testimonial-source" type="text" class="regular-text"
						   value="<?= esc_attr( get_post_meta( $post->ID, '_source_label', true ) ) ?>" placeholder="<?php esc_attr_e( 'e.g. twitter', 'kyom' ) ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="kyom-testimonial-url"><?php esc_html_e( 'URL', 'kyom' ) ?></label></th>
				<td>
					<input name="kyom-testimonial-url" id="kyom-testimonial-url" type="url" class="regular-text"
						   value="<?= esc_attr( get_post_meta( $post->ID, '_source_url', true ) ) ?>" placeholder="<?php esc_attr_e( 'e.g. https://example.com', 'kyom' ) ?>" />
				</td>
			</tr>
		</table>
		<?php
	}, $post_type );
} );

/**
 * Save post meta
 *
 * @param int     $post_id
 * @param WP_Post $post
 */
add_action( 'save_post', function( $post_id, $post ) {
	if ( ( 'jetpack-testimonial' != $post->post_type ) || ! wp_verify_nonce( filter_input( INPUT_POST, '_testimonialnonce' ), 'kyom_testimonial' ) ) {
		return;
	}
	foreach ( [
		'kyom-testimonial-source' => '_source_label',
		'kyom-testimonial-url' => '_source_url',
		'kyom-testimonial-position' => '_source_position',
	] as $key => $meta_key ) {
		update_post_meta( $post_id, $meta_key, filter_input( INPUT_POST, $key ) );
	}
}, 10, 2 );
