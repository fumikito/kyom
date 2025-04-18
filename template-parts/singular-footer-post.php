<?php
/**
 * kyom_author_of_post
 *
 * If author exists and description is filled,
 * Author block will be display.
 *
 * @param WP_User $author
 * @return null|WP_User
 */
$author = apply_filters( 'kyom_author_of_post', get_userdata( get_the_author_meta( 'ID' ) ) );
if ( ! $author || ! $author->description ) {
	return;
}
?>
<div class="author-block">

	<div class="author-block-body">
		<div class="author-block-image">
			<?php echo get_avatar( $author->ID, 300, '', '', [ 'class' => 'author-block-avatar' ] ); ?>
		</div>

		<div class="author-block-content">

			<h2 class="author-block-title">
				<small><?php echo esc_html( _x( 'Article Written By:', 'author-box', 'kyom' ) ); ?></small>
				<?php echo esc_html( $author->display_name ); ?>
			</h2>

			<div class="author-block-description">
				<?php echo wp_kses_post( wpautop( $author->description ) ); ?>
			</div>

			<?php
			$contacts = kyom_get_social_links( $author, true );
			if ( $contacts ) :
				?>
				<div class="author-block-contact">
					<h3 class="author-block-contact-title"><?php esc_html_e( 'Follow Me Via:', 'kyom' ); ?></h3>
					<p class="author-block-contact-links">
						<?php foreach ( $contacts as $icon => $var ) : ?>
							<a href="<?php echo esc_url( $var['url'] ); ?>" target="_blank"
								class="uk-button uk-button-default uk-button-small author-button-contact-link">
								<span uk-icon="<?php echo esc_attr( $icon ); ?>"></span>
								<span class="author-block-contact-link-method"><?php echo esc_html( $var['label'] ); ?></span>
							</a>
						<?php endforeach; ?>
					</p>
				</div>
			<?php endif; ?>

			<p>
				<a class="uk-button uk-button-secondary uk-button-small"
					href="<?php echo get_author_posts_url( $author->ID ); ?>">
					<?php esc_html_e( 'See all posts', 'kyom' ); ?>
				</a>
			</p>
		</div>
	</div>
</div><!-- //.author-block -->
