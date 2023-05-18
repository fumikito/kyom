<?php if ( $link = kyom_archive_top() ) :
	$post_type = get_post_type_object( get_queried_object()->post_type );
	if ( $post_type ) {
		$label = sprintf( __( 'See All %s', 'kyom' ), $post_type->labels->name );
	} else {
		$label = __( 'See All', 'kyom' );
	}
	?>
	<div class="kyom-archive-back entry-container uk-text-center" uk-margin>
		<a class="uk-button uk-button-default uk-button-large" href="<?php echo esc_url( $link ) ?>">
			<?php echo esc_html( $label ) ?>
		</a>
	</div>
<?php endif; ?>

