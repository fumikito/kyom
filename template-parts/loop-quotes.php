<?php
$post_class = [
	'uk-card',
	'uk-card-block-link',
	'uk-width-1-1',
	'uk-card-default',
];
?>
<div class="loop">
	<div <?php post_class( $post_class ); ?>>
		<blockquote class="uk-card-body uk-card-body-quote">
			<span class="quote-left" uk-icon="icon: quote-right; ratio:2"></span>
			<span class="quote-right" uk-icon="icon:quote-right; ratio:2"></span>
			<?php the_excerpt(); ?>
		</blockquote>
		<div class="uk-card-footer">
			<p class="uk-text-meta uk-margin-remove-top">
				<a href="<?php the_permalink(); ?>" class="uk-card-link">
					<?php printf( _x( 'Via %s', 'quotes', 'kyom' ), strip_tags( kyom_testimonial_source() ) ); ?>
				</a>
			</p>
		</div>
	</div>
</div>
