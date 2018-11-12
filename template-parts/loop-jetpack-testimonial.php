<?php
$post_class = [
	'uk-card', 'uk-width-1-1', 'uk-card-default',
];
?>
<div class="loop">
	<div <?php post_class( $post_class ) ?>>
		<div class="uk-card-header">
			<div class="uk-grid-small uk-flex-middle" uk-grid>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="uk-width-auto">
						<?php the_post_thumbnail( 'face-rectangle', [ 'class' => 'uk-border-circle pull-quote-archive-image' ] ) ?>
					</div>
				<?php endif; ?>
				<div class="uk-width-expand">
					<h3 class="uk-card-title uk-margin-remove-bottom"><?php the_title() ?></h3>
					<?php if ( $source = kyom_testimonial_source() ) : ?>
						<p class="uk-text-meta uk-margin-remove-top">
							<?= sprintf( _x( 'Via %s', 'quotes', 'kyom' ),  strip_tags( $source ) ) ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<blockquote class="uk-card-body uk-card-body-quote">
			<span class="quote-left" uk-icon="icon: quote-right; ratio:2"></span>
			<span class="quote-right" uk-icon="icon:quote-right; ratio:2"></span>
			<?php the_excerpt() ?>
		</blockquote>
		<div class="uk-card-footer">
			<a href="<?php the_permalink() ?>" class="uk-button uk-button-text"><?php esc_html_e( 'See Full Quote', 'kyom' ) ?></a>
		</div>
	</div>
</div>
	