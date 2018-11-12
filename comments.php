<?php
/**
 * Comment template
 *
 * @package kyom
 *
 */

defined( 'ABSPATH' ) || die();

// If password required, skip.
if ( post_password_required() ) {
	printf(
		'<div class="uk-alert-danger uk-alert-padding" uk-alert><p class="uk-text-center">%s</p></div>',
		esc_html__('This post is password protected. Enter the password to view comments.')
	);
	return;
}
?>



<?php if ( have_comments() ) : ?>
	<h3 id="comments" class="comments-title uk-heading-bullet">
		<?php
			if ( 1 == get_comments_number() ) {
				/* translators: %s: post title */
				printf( __( 'One response to %s' ),  '&#8220;' . get_the_title() . '&#8221;' );
			} else {
				/* translators: 1: number of comments, 2: post title */
				printf( _n( '%1$s response to %2$s', '%1$s responses to %2$s', get_comments_number() ),
					number_format_i18n( get_comments_number() ),  '&#8220;' . get_the_title() . '&#8221;' );
			}
		?>
	</h3>

	<div class="comments-nav">
		<div class="comments-nav-left"><?php previous_comments_link() ?></div>
		<div class="comments-nav-right"><?php next_comments_link() ?></div>
	</div>

	<ul class="uk-comment-list">
	<?php wp_list_comments( [
		'callback' => function ( $comment, $args, $depth ) {
			echo '<li>';
			/** @var WP_Comment $comment */
			if ( in_array( $comment->comment_type, [ 'pingback', 'trackback' ] ) ) {
				?>
				<article class="uk-comment pingback">
					<span class="pingback-label">
						<span uk-icon="social"></span>
						<?php esc_html_e( 'Ping Back', 'kyom' ) ?>
					</span>
					<a class="pingback-link" href="<?= esc_url( $comment->comment_author_url ) ?>" rel="nofollow" target="_blank">
						<span class="pingback-title"><?php comment_author( $comment ) ?></span>
					</a>
					<time class="pingback-time" datetime="<?= esc_attr( mysql2date( DateTime::ISO8601, $comment->comment_date_gmt ) ) ?>">
						<?= kyom_date_diff( $comment->comment_date ); ?>
					</time>
				</article>
				<?php
				return;
			}
			$classes = [ 'uk-comment', 'uk-visible-toggle' ];
			if ( kyom_is_primary_comment( $comment ) ) {
				$classes[] = 'uk-comment-primary';
			}
			?>
			<article id="div-comment-<?= esc_html( $comment->comment_ID ); ?>" <?php comment_class( $classes ) ?>>
				<header class="uk-comment-header uk-position-relative">
					<div class="uk-grid-medium uk-flex-middle" uk-grid>
						<div class="uk-width-auto">
							<?= get_avatar( $comment->user_id ?: $comment->comment_author_email, 80, '', $comment->comment_author, [
								'class' => 'uk-comment-avatar',
							] ) ?>
						</div>
						<div class="uk-width-expand">
							<h4 class="uk-comment-title uk-margin-remove vcard">
								<?php if ( preg_match( '#^https?://.+#u', $comment->comment_author_url ) ) : ?>
								<a href="<?= esc_url( $comment->comment_author_url ) ?>" rel="nofollow" target="_blank">
									<?php comment_author( $comment ) ?>
								</a>
								<?php else : ?>
									<?php comment_author( $comment ) ?>
								<?php endif; ?>
							</h4>
							<p class="uk-comment-meta uk-margin-remove-top">
								<time datetime="<?= esc_attr( mysql2date( DateTime::ISO8601, $comment->comment_date_gmt ) ) ?>">
									<?= kyom_date_diff( $comment->comment_date ); ?>
								</time>
							</p>
							<?php if ( '0' == $comment->comment_approved ) : ?>
								<p class="comment-awaiting-moderation uk-alert-warning" uk-alert>
									<?php _e( 'Your comment is awaiting moderation.' ); ?>
								</p>
							<?php endif; ?>
						</div>
					</div>
					<div class="uk-position-top-right uk-position-small uk-hidden-hover">
						<?php
						comment_reply_link( array_merge( $args, array(
						'add_below' => 'div-comment',
						'depth'     => $depth,
						'max_depth' => $args['max_depth'],
						'before'    => '<div class="reply">',
							'after'     => '</div>'
						) ) );
						?>
					</div>
				</header>
				<div class="uk-comment-body">
					<?= wp_kses_post( wpautop( get_comment_text( $comment ) ) ) ?>
				</div>
			</article>
			<?php
		}
	] );?>
	</ul>

	<div class="comments-nav">
		<div class="comments-nav-left"><?php previous_comments_link() ?></div>
		<div class="comments-nav-right"><?php next_comments_link() ?></div>
	</div>

 <?php else : // this is displayed if there are no comments so far ?>

	<?php
	if ( comments_open() ) {
		// If comments are open, but there are no comments
		printf(
			'<div class="uk-alert uk-alert-padding" uk-alert><p class="uk-text-center">%s</p></div>',
			esc_html__( 'This post has no comment yet. Please share your thoughts.', 'kyom' )
		);
	} else {
		// Comments are closed.
		printf(
			'<div class="uk-alert-muted uk-alert-padding" uk-alert><p class="uk-text-center">%s</p></div>',
			esc_html__( 'Comments are closed.' )
		);
	} ?>
<?php endif; ?>

<?php comment_form(); ?>
