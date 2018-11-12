<?php
/**
 * Pager for singular page.
 *
 * @package kyom
 */

$pagers = [];
$only_right = false;
if ( $prev_post = get_previous_post() ) {
	$pagers[] = [ $prev_post, 'chevron-left', 'previous', __( 'Previous Post', 'kyom' ) ];
}
if ( $next_post = get_next_post() ) {
	if ( ! $prev_post ) {
		$only_right = true;
	}
	$pagers[] = [ $next_post, 'chevron-right', 'next', __( 'Next Post', 'kyom' ) ];
}
// If no post, do nothing.
if ( ! $pagers ) {
	return;
}
?>


<div class="pager">
	<div class="pager-wrapper <?= $only_right ? 'pager-only-right' : '' ?>">
	
	<?php foreach ( $pagers as list( $p, $icon, $type, $label ) ) : ?>
	<div class="pager-item pager-item-<?= esc_attr( $type ) ?>">
		<a class="pager-link" href="<?php the_permalink( $p ) ?>">
			<?php if ( has_post_thumbnail( $p ) ) : ?>
			<div class="pager-img" style="<?= kyom_thumbnail_bg( $p, 'large' ) ?>">
			</div>
			<?php else: ?>
			<div class="pager-cover"></div>
			<?php endif; ?>
			<span class="pager-icon" uk-icon="<?= esc_attr( $icon ) ?>"></span>
			<small class="pager-label"><?= esc_html( $label ) ?></small><br />
			<span class="pager-text"><?= esc_html( get_the_title( $p ) ) ?></span>
		</a>
	</div>
	<?php endforeach; ?>
	</div>
</div>
