<?php
/**
 * Main content area for singular templater.
 *
 * @since 0.3.0
 */

?>

<?php if ( is_singular( 'post' ) && kyom_is_expired_post() ) : ?>
	<div class="uk-alert-warning uk-alert-padding" uk-alert>
		<a class="uk-alert-close" uk-close></a>
		<?php echo esc_html( sprintf( __( 'This post is published %s ago. Please consider the content may contain invalid information.', 'kyom' ), kyom_get_outdated_string() ) ); ?>
	</div>
<?php endif; ?>

<?php the_content(); ?>

<?php wp_link_pages( [
	'before'      => '<ul class="uk-pagination">',
	'after'       => '</ul>',
	'link_before' => '<li>',
	'link_after'  => '</li>',
] ) ?>
