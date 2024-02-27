<?php
$post_class = [
		'uk-card', 'uk-width-1-1',
];
if ( is_sticky() || kyom_is_parent() ) {
	$post_class[] = 'uk-card-secondary';
} elseif ( kyom_is_new() ) {
	$post_class[] = 'uk-card-primary';
} else {
	$post_class[] = 'uk-card-default';
}
?>
<div class="loop">
	<div <?php post_class( $post_class ) ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="uk-card-media-top">
				<?php the_post_thumbnail( 'post-thumbnail', ['class' => 'archive-thumbnail'] ) ?>
			</div>
		<?php endif; ?>
		<a class="uk-card-link" href="<?php the_permalink() ?>">
			<div class="uk-card-header">
				<?php
				$link = kyom_primary_term_link();
				if ( $link ) :
				?>
				<span class="primary-term-link"><?php echo strip_tags( $link ); ?></span>
				<?php endif; ?>
				<h3 class="uk-card-title uk-margin-remove-bottom">
					<?php the_title(); ?>
				</h3>
				<p class="uk-text-meta uk-margin-remove-top">
					<time datetime="<?= mysql2date( DateTime::ISO8601, $post->post_date_gmt ) ?>">
						<?php the_time( get_option( 'date_format' ) ) ?>
					</time>
				</p>
			</div>
		</a>
		<div class="uk-card-body">
			<?php the_excerpt() ?>
		</div>
	</div>
</div>
