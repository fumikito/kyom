<?php
if ( function_exists( 'wp_pagenavi' ) ) {
	wp_pagenavi();
} else {
	global $wp_query;

	$pagers = [];
	$only_right = false;
	$cur_page = max( 1, $wp_query->get( 'paged' ) );
	$has_prev = 1 < $cur_page;
	if ( $has_prev ) {
		$pagers[] = [ previous_posts( false ), 'chevron-left', 'previous', __( 'Previous Posts', 'kyom' ) ];
	}
	if ( $cur_page * $wp_query->get( 'posts_per_page' ) < $wp_query->found_posts ) {
		if ( ! $has_prev  ) {
			$only_right = true;
		}
		$pagers[] = [ next_posts( $wp_query->max_num_pages, false ), 'chevron-right', 'next', __( 'Next Posts', 'kyom' ) ];
	}
	?>


	<div class="pager archive-pager">
		<div class="pager-wrapper <?= $only_right ? 'pager-only-right' : '' ?>">

			<?php foreach ( $pagers as list( $link, $icon, $type, $label ) ) : ?>
				<div class="pager-item pager-item-<?= esc_attr( $type ) ?>">
					<a class="pager-link" href="<?= esc_url( $link ) ?>">
						<span class="pager-icon" uk-icon="<?= esc_attr( $icon ) ?>"></span>
						<span class="pager-text"><?= esc_html( $label ) ?></span>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<?php
}
