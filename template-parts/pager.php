<?php
/**
 * Pager for singular page.
 *
 * @package kyom
 */

$pagers     = [];
$only_right = false;
$label      = get_post_type_object( get_post_type() )->labels->singular_name;
$prev_post  = get_previous_post();
if ( $prev_post ) {
	$pagers[] = [ $prev_post, 'chevron-left', 'previous', sprintf( _x( 'Previous %s', 'pager', 'kyom' ), $label ) ];
}
$next_post = get_next_post();
if ( $next_post ) {
	if ( ! $prev_post ) {
		$only_right = true;
	}
	$pagers[] = [ $next_post, 'chevron-right', 'next', sprintf( _x( 'Next %s', 'pager', 'kyom' ), $label ) ];
}
// If no post, do nothing.
if ( ! $pagers ) {
	return;
}
?>


<div class="pager">
	<div class="pager-wrapper <?php echo $only_right ? 'pager-only-right' : ''; ?>">

	<?php foreach ( $pagers as list( $p, $icon, $type, $label ) ) : ?>
	<div class="pager-item pager-item-<?php echo esc_attr( $type ); ?>">
		<a class="pager-link" href="<?php the_permalink( $p ); ?>">
			<?php if ( has_post_thumbnail( $p ) ) : ?>
			<div class="pager-img" style="<?php echo kyom_thumbnail_bg( $p, 'large' ); ?>">
			</div>
			<?php else : ?>
			<div class="pager-cover"></div>
			<?php endif; ?>
			<span class="pager-icon" uk-icon="<?php echo esc_attr( $icon ); ?>"></span>
			<small class="pager-label"><?php echo esc_html( $label ); ?></small><br />
			<span class="pager-text"><?php echo esc_html( get_the_title( $p ) ); ?></span>
		</a>
	</div>
	<?php endforeach; ?>
	</div>
</div>
