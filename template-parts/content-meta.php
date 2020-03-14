<div class="entry-meta">
	<ul class="entry-meta-list">
		<?php if ( ! is_page() ) : ?>
			<li class="entry-meta-item">
				<span uk-icon="calendar"></span> <?php the_date() ?>
			</li>
			<?php if ( 'post' === get_post_type() ) : ?>
			<li class="entry-meta-item">
				<span uk-icon="clock"></span>
				<?php echo esc_html( kyom_reading_time() ) ?>
				<?php echo esc_html( kyom_paren( kyom_content_length_formatted() ) ) ?>
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
	</ul>
</div>
