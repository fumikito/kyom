<?php if ( $link = kyom_archive_top() ) :
	$post_type = get_post_type_object( get_queried_object()->post_type );
	?>
	<div class="kyom-archive-back entry-container uk-text-center" uk-margin>
		<a class="uk-button uk-button-default uk-button-large" href="<?= esc_url( $link ) ?>">
			<?= esc_html( sprintf( __( 'See All %s', 'kyom' ),  $post_type->labels->name ) ) ?>
		</a>
	</div>
<?php endif; ?>

