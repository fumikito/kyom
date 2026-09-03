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
			// フォロー導線はブロックと同じ共有レンダラーで出す。
			// 記事ごとにブロックを挿入しなくても、著者ボックスから自動的に表示される。
			$owner = kyom_get_owner();
			kyom_the_follow( [
				'user'          => $author,
				'title'         => __( 'Follow Me Via:', 'kyom' ),
				'heading_level' => 3,
				'show_lead'     => false,
				// 媒体ごとの説明はサイト所有者のチャンネルについて書かれた文言なので、
				// 他の著者（Madame Claude 等）の記事では出さない。
				'show_desc'     => $owner && (int) $author->ID === (int) $owner->ID,
				'class'         => 'author-block-follow',
			] );
			?>

			<p>
				<a class="uk-button uk-button-secondary uk-button-small"
					href="<?php echo get_author_posts_url( $author->ID ); ?>">
					<?php esc_html_e( 'See all posts', 'kyom' ); ?>
				</a>
			</p>
		</div>
	</div>
</div><!-- //.author-block -->
