<a href="<?php the_permalink() ?>" class="recent-wide-card">
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="recent-wide-cover" style="background-image: url('<?= wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ) ?>');">
		</div>
		<?php ?>
	<?php endif; ?>
	<?php
	$cats = get_the_category();
	if ( $cats && ! is_wp_error( $cats ) ) :
		foreach ( $cats as $cat ) :
	?>
		<span class="recent-wide-card-label"><?= esc_html( $cat->name ) ?></span>
	<?php break; endforeach; endif; ?>
	<h3 class="recent-wide-card-title">
		<?php the_title(); ?>
	</h3>
</a>