<?php
/**
 * Display ad in AMP.
 *
 */

$amp = get_option( 'kyom_ad_amp_content' );
if ( $amp ) :
	?>
	<div class="amp-ad-container">
		<?php echo $amp; ?>
	</div>
<?php endif; ?>

<div class="kyom-amp-return">

	<a href="<?php the_permalink(); ?>" class="koym-amp-return-btn"><?php esc_html_e( 'Read Original Version', 'kyom' ); ?></a>

</div>
