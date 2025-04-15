<?php
/**
 * Main content area for attachment template.
 *
 * @since 0.3.0
 */
$siblings = get_children( [
	'post_type'      => 'attachment',
	'post_parent'    => get_post_parent()->ID,
	'posts_per_page' => -1,
	'orderby'        => [ 'ID' => 'ASC' ],
	'post__not_in'   => [ get_post_thumbnail_id( get_post_parent()->ID ) ],
] );

/**
 * Filter attachment meta data.
 */
add_filter( 'prepend_attachment', function ( $p ) {
	$file = get_attached_file( get_the_ID() );
	if ( ! $file ) {
		return $p;
	}
	if ( false !== strpos( get_post_mime_type(), 'image/' ) ) {
		// Image.
		ob_start();
		?>
		<figure class="wp-block-image alignwide size-full">
			<a href="<?php echo wp_get_attachment_image_url( get_the_ID(), 'full' ); ?>">
				<?php echo wp_get_attachment_image( get_the_ID(), 'full' ); ?>
			</a>
			<?php if ( has_excerpt() ) : ?>
			<figcaption class="wp-caption-text">
				<?php echo wpautop( wp_kses_post( get_post()->post_excerpt ) ); ?>
			</figcaption>
			<?php endif; ?>
		</figure>
		<?php
		$p = ob_get_contents();
		ob_end_clean();
	}
	return $p;
} );

?>


<?php the_content(); ?>

<?php if ( $siblings ) : ?>

	<nav class="entry-attachment">
		<h2 class="entry-attachment-header text-center"><?php esc_html_e( 'Other Files', 'kyom' ); ?></h2>
		<ol class="entry-attachment-list alignwide">
			<?php foreach ( $siblings as $sibling ) : ?>
				<li class="entry-attachment-item">
					<a class="entry-attachment-link <?php echo ( get_the_ID() === $sibling->ID ? ' current' : '' ); ?>"
						href="<?php echo esc_url( get_permalink( $sibling ) ); ?>">
						<p class="entry-attachment-thumbnail">
							<?php if ( false !== strpos( get_post_mime_type( $sibling ), 'image/' ) ) : ?>
								<?php echo wp_get_attachment_image( $sibling->ID, 'thumbnail', false, [ 'class' => 'entry-attachment-thumbnail' ] ); ?>
							<?php else : ?>
								<span uk-icon="icon: file-text; ratio: 3"></span>
							<?php endif; ?>
						</p>

						<span class="entry-attachment-title">
							<?php echo esc_html( get_the_title( $sibling ) ); ?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>

<?php endif; ?>

<div class="kyom-archive-back entry-container uk-text-center" uk-margin>
	<a href="<?php echo get_permalink( get_post_parent() ); ?>">
		<?php
		printf(
			esc_html__( '&raquo; See parent article "%s" &laquo;', 'kyom' ),
			esc_html( get_the_title( get_post_parent() ) )
		);
		?>
	</a>
</div>
