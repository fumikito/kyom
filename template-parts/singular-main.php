<?php
/**
 * Main content area for singular templater.
 *
 * @since 0.3.0
 */

?>

<?php if ( is_singular( 'post' ) && kyom_is_expired_post() ) : ?>
	<div class="kyom-age-note">
		<span uk-icon="<?php echo kyom_is_revised() ? 'refresh' : 'history'; ?>"></span>
		<span class="kyom-age-note-text"><?php echo esc_html( kyom_get_age_note() ); ?></span>
	</div>
<?php endif; ?>

<?php the_content(); ?>

<?php wp_link_pages( [
	'before'      => '<ul class="uk-pagination">',
	'after'       => '</ul>',
	'link_before' => '<li>',
	'link_after'  => '</li>',
] ) ?>
