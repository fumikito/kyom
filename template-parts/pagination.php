<?php
if ( function_exists( 'wp_pagenavi' ) ) {
	wp_pagenavi();
} else {
	$pagers = [];
	$only_right = false;
	if ( $prev = get_previous_posts_page_link() ) {
		$pagers[] = [ $prev, 'chevron-left', 'previous', __( 'Previous Posts', 'kyom' ) ];
	}
	if ( $next = get_next_posts_page_link() ) {
		if ( ! $prev ) {
			$only_right = true;
		}
		$pagers[] = [ $next, 'chevron-right', 'next', __( 'Next Posts', 'kyom' ) ];
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