<?php
/**
 * Header template for singular page.
 *
 * @since 0.3.0
 */

if ( has_post_thumbnail() ) {
	$attachment_id = get_post_thumbnail_id();
} else {
	$attachment_id     = 0;
	$default_thumbnail = get_option( 'kyom_default_eyecatch' );
	if ( $default_thumbnail ) {
		$attachment_id = attachment_url_to_postid( $default_thumbnail );
	}
}
?>
<header class="entry-header <?php echo $attachment_id ? 'with-thumbnail' : 'no-thumbnail'; ?>">

	<div class="entry-header-box uk-container">
		<?php if ( $attachment_id ) : ?>
			<div class="entry-header-eyecatch entry-header-thumbnail">
				<?php echo wp_get_attachment_image( $attachment_id, 'big-block' ); ?>
			</div>
		<?php else : ?>
			<div class="entry-header-particles entry-header-eyecatch"></div>
		<?php endif; ?>
		<?php
		$term = kyom_get_top_category();
		if ( $term ) :
			?>
			<a class="entry-top-term" href="<?php echo get_term_link( $term ); ?>" rel="tag">
				<?php echo esc_html( $term->name ); ?>
			</a>
		<?php endif; ?>
		<h1 class="entry-header-title">
			<span>
				<?php single_post_title(); ?>
			</span>
		</h1>
		<?php if ( 'post' === get_post_type() ) : ?>
			<p class="entry-header-author">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 36, '', get_the_author(), [ 'class' => 'entry-header-avatar' ] ); ?>
				<span class="entry-header-author-name stroke"><?php the_author(); ?></span>
			</p>
		<?php endif; ?>
		<?php get_template_part( 'template-parts/content-meta', get_post_type() ); ?>
	</div>

</header>
