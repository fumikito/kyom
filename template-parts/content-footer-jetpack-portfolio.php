<?php
$contact_url = get_option( 'kyom_contact_url' );
if ( ! $contact_url ) {
	return;
}
?>
<footer class="uk-container uk-text-center kyom-portfolio-contact">
	<a class="uk-button uk-button-default" href="<?php echo get_post_type_archive_link( 'jetpack-portfolio' ); ?>">
		<?php echo esc_html( sprintf( __( 'See All %s', 'kyom' ), get_post_type_object( 'jetpack-portfolio' )->labels->name ) ); ?>
	</a>
	<span class="kyom-portfolio-contact-divider"><?php echo esc_html( _x( 'OR', 'button-divider', 'kyom' ) ); ?></span>
	<a class="uk-button uk-button-primary" href="<?php echo esc_url( $contact_url ); ?>">
		<span uk-icon="mail"></span> <?php esc_html_e( 'Contact', 'kyom' ); ?>
	</a>
</footer>
