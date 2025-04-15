<?php
/**
 * Display related posts.
 *
 */

$author = kyom_get_owner();
if ( $author ) : ?>
<div class="amp-wp-meta amp-read-more kyom-amp-container">
	<h2 class="amp-read-more-title"><?php esc_html_e( 'Follow Us', 'kyom' ); ?></h2>
	<div class="amp-read-more-body">
		<p class="amp-read-more-lead">
			<?php esc_html_e( 'Follow US and Get in Touch', 'kyom' ); ?>
		</p>
		<?php
		foreach ( kyom_get_social_links() as $icon => $url ) :
			if ( ! is_array( $url ) ) {
				$url = [
					'label' => capital_P_dangit( ucfirst( $icon ) ),
					'url'   => $url,
				];
			}
			$icon_class = 'mail' === $icon ? 'envelope-o' : $icon;
			?>
		<a href="<?php echo esc_url( $url['url'] ); ?>" class="amp-read-more-link amp-read-more-link-<?php echo $icon; ?>">
			<i class="fa fa-<?php echo esc_attr( $icon_class ); ?>"></i>
			<?php echo esc_html( $url['label'] ); ?>
		</a>
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>

<?php

$amp = get_option( 'kyom_ad_amp_last' );
if ( $amp ) :
	?>
	<div class="amp-ad-container">
		<?php echo $amp; ?>
	</div>
<?php endif; ?>
