<?php if ( $related_pages = kyom_get_related_pages() ) : ?>
	<h2 class="uk-heading-line uk-text-center">
		<span><?php esc_html_e( 'Related Pages', 'kyom' ) ?></span>
	</h2>
	<div class="uk-container archive-container">
		<div class="uk-child-width-1-2@s uk-child-width-1-3@m" uk-grid="masonry: true">
			<?php
			foreach ( $related_pages as $post ) {
				setup_postdata( $post );
				get_template_part( 'template-parts/loop', get_post_type() );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
<?php endif; ?>
