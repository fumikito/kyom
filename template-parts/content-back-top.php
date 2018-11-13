<?php if ( $link = kyom_archive_top() ) : ?>
	<div class="kyom-archive-back entry-container uk-text-center" uk-margin>
		<a class="uk-button uk-button-default uk-button-large" href="<?= esc_url( $link ) ?>">
			<?php esc_html_e( 'See All Posts', 'kyom' ) ?>
		</a>
	</div>
<?php endif; ?>

