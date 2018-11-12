<?php
// Display related posts.
if ( function_exists( 'yarpp_get_related' ) && $posts = yarpp_get_related( [], get_the_ID() ) ) :
    ?>
    <div class="amp-wp-meta amp-related-posts kyom-amp-container">
        <h2 class="amp-related-title"><?php esc_html_e( 'Related Posts', 'kyom' ) ?></h2>
        <?php foreach ( $posts as $related ) : ?>
        <div class="amp-related-item">
            <a class="amp-related-item-link" href="<?= esc_url( get_the_permalink( $related ) ) ?>">
                <?php if ( has_post_thumbnail( $related ) ) : ?>
                    <amp-img src="<?= get_the_post_thumbnail_url( $related, 'thumbnail' ) ?>" width="60" height="60"></amp-img>
                <?php endif; ?>
                <span class="amp-related-item-title">
                    <?= esc_html( get_the_title( $related ) ) ?>
                    <small><?= get_the_time( 'Y/m/d', $related ) ?></small>
                </span>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<?php if ( $author = kyom_get_owner() ) : ?>
<div class="amp-wp-meta amp-read-more kyom-amp-container">
    <h2 class="amp-read-more-title"><?php esc_html_e( 'Follow Us', 'kyom' ) ?></h2>
    <div class="amp-read-more-body">
        <p class="amp-read-more-lead">
            <?php esc_html_e( 'Follow US and Get in Touch', 'kyom' ) ?>
        </p>
        <?php foreach ( kyom_get_social_links() as $icon => $url ) :
			if ( ! is_array( $url ) ) {
				$url = [
					'label' => capital_P_dangit( ucfirst( $icon ) ),
					'url'   => $url,
				];
			}
			$icon_class = 'mail' === $icon ? 'envelope-o' : $icon;
			?>
        <a href="<?= esc_url( $url['url'] ) ?>" class="amp-read-more-link amp-read-more-link-<?= $icon ?>">
			<i class="fa fa-<?= esc_attr( $icon_class ) ?>"></i>
            <?= esc_html( $url['label'] ) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if ( $amp = get_option( 'kyom_ad_amp_last' ) ) : ?>
	<div class="amp-ad-container">
		<?= $amp ?>
	</div>
<?php endif; ?>

