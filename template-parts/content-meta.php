<div class="entry-meta">
	<ul class="entry-meta-list">
		<?php if ( ! is_page() ) : ?>
			<li class="entry-meta-item">
				<span uk-icon="calendar"></span>
				<span class="stroke"><?php the_date() ?></span>
			</li>
			<?php if ( 'post' === get_post_type() ) : ?>
				<?php if ( kyom_is_updated() ) : ?>
					<li class="entry-meta-item">
						<span uk-icon="refresh"></span>
						<span class="stroke">
							<?php the_modified_date( '', esc_html__( 'Last Updated: ', 'kyom' ) ) ?>
						</span>
					</li>
				<?php endif; ?>
				<li class="entry-meta-item">
					<span uk-icon="clock"></span>
					<span class="stroke">
						<?php echo esc_html( kyom_reading_time() ) ?>
						<?php echo esc_html( kyom_paren( kyom_content_length_formatted() ) ) ?>
					</span>
				</li>
			<?php endif; ?>
		<?php endif; ?>
		<?php
		$taxonomy_to_display = apply_filters( 'kyom_taxonomy_to_display', [
			'post_tag'                => 'tag',
			'jetpack-portfolio-type' => 'hashtag',
			'jetpack-portfolio-tag'  => 'tag',
		] );
		foreach ( $taxonomy_to_display as $taxonomy => $icon ) {
			$terms = get_the_terms( get_post(), $taxonomy );
			if ( ! $icon ) {
				$icon = 'tag';
			}
			if ( ! $terms || is_wp_error( $terms ) ) {
				continue;
			}
			printf(
				'<li class="entry-meta-item"><span uk-icon="%s"></span> %s</li>',
				esc_attr( $icon ),
				implode( '', array_map( function ( $term ) {
					return sprintf( '<a class="entry-meta-link" href="%s">%s</a>', esc_url( get_term_link( $term ) ), esc_html( $term->name ) );
				}, $terms ) )
			);
		} ?>
		<?php if ( is_attachment() ) : ?>
		<?php
		$file = get_attached_file( get_the_ID() );
		if ( $file ) : ?>
		<li class="entry-meta-item">
			<span uk-icon="file-text"></span>
			<span class="stroke">
				<?php
				echo esc_html( basename( $file ) ); ?>
				(<?php echo esc_html( get_post_mime_type() ); ?>)
			</span>
		</li>
		<?php endif; ?>
		<li class="entry-meta-item">
			<span uk-icon="download"></span>
			<span class="stroke"><?php echo esc_html( kyom_attachment_size() ); ?></span>
		</li>
		<?php endif; ?>
	</ul>
</div>
