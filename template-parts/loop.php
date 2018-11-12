<?php
$post_class = [
	'uk-card', 'uk-width-1-1',
];
if ( is_sticky() || kyom_is_parent() ) {
	$post_class[]  = 'uk-card-secondary';
} elseif ( kyom_is_new() ) {
	$post_class[]  = 'uk-card-primary';
} elseif ( has_post_thumbnail() ) {
	$post_class[]  = 'uk-card-default';
} else {
	$post_class[] = 'uk-card-hover';
}
?>
<div class="loop">
	<div <?php post_class( $post_class ) ?>>
		<?php if ( has_post_thumbnail() ) : ?>
		<div class="uk-card-media-top">
			<?php the_post_thumbnail( 'post-thumbnail', [ 'class' => 'archive-thumbnail' ] ) ?>
		</div>
		<?php endif; ?>
		<div class="uk-card-header">
			<?= kyom_primary_term_link() ?>
			<h3 class="uk-card-title uk-margin-remove-bottom">
				<a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
			</h3>
			<p class="uk-text-meta uk-margin-remove-top">
				<time datetime="<?= mysql2date( DateTime::ISO8601, $post->post_date_gmt ) ?>">
					<?php the_time( get_option( 'date_format' ) ) ?>
				</time>
			</p>
		</div>
		<div class="uk-card-body">
			<?php the_excerpt() ?>
		</div>
		<div class="uk-card-footer">
			<a href="<?php the_permalink() ?>" class="uk-button uk-button-text"><?php esc_html_e( 'Continue Reading', 'kyom' ) ?></a>
		</div>
	</div>
</div>
	