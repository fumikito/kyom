<?php
/**
 * Replace oembed content of internal URL.
 *
 * This file is replace wp-includes/theme-compat/embed-content.php
 * Version tag is one of WordPress, so you can track the difference
 * between core update.
 *
 * @since 5.3.2
 */
$thumbnail_id = 0;
if ( has_post_thumbnail() ) {
	$thumbnail_id = get_post_thumbnail_id();
}
$thumbnail_id = apply_filters( 'embed_thumbnail_id', $thumbnail_id );
$image_size   = apply_filters( 'embed_thumbnail_image_size', 'full', $thumbnail_id );
$is_own       = false !== strpos( get_permalink(), home_url() );
$rel          = $is_own ? 'target="_top"' : 'target="_blank" rel="noopener noreferrer"';
?>
<div <?php post_class( [ 'wp-embed', ( $is_own ? 'wp-embed-own' : 'wp-embed-external' ) ] ); ?>>

	<a class="wp-embed-link" href="<?php the_permalink(); ?>" <?php echo $rel; ?>>
		
		<?php if ( $thumbnail_id ) : ?>
			<div class="wp-embed-featured-image">
				<?php echo wp_get_attachment_image( $thumbnail_id, $image_size ); ?>
			</div>
		<?php endif; ?>

		<div class="wp-embed-body">
			
			<p class="wp-embed-also-read">
				<span><?php esc_html_e( 'See Also', 'kyom' ); ?></span>
			</p>
			
			<h1 class="wp-embed-heading"><?php the_title(); ?></h1>
			
			<p class="wp-embed-date">
				<time datetime="<?php echo esc_attr( get_the_date( '' ) ); ?>"><?php the_date(); ?></time>
			</p>
			
		</div>
		
	</a>
	
	<?php do_action( 'embed_content' ); ?>
	
	<footer class="wp-embed-footer">
		<?php the_embed_site_title(); ?>

		<div class="wp-embed-meta">
			<?php
			/**
			 * Prints additional meta content in the embed template.
			 *
			 * @since 4.4.0
			 */
			do_action( 'embed_content_meta' );
			?>
		</div>
	</footer>
</div>
