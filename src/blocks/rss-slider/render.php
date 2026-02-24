<?php
/**
 * RSS Slider block rendering
 *
 * @package kyom
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$url = $attributes['url'] ?? '';

if ( ! $url ) {
	return '';
}

// kyom_fetch_feed_items関数を使用してフィードを取得
$items = function_exists( 'kyom_fetch_feed_items' ) ? kyom_fetch_feed_items( $url ) : [];

if ( ! $items ) {
	return '';
}
?>
<div uk-slider class="kyom-rss-slider">
	<div class="uk-position-relative uk-visible-toggle uk-light">
		<ul class="uk-slider-items uk-child-width-1-3 uk-child-width-1-4@s uk-child-width-1-6@m">
			<?php
			foreach ( $items as $index => $item ) :
				if ( ! $item['image'] ) {
					continue;
				}
				?>
				<li class="kyom-rss-slider-item">
					<a class="kyom-rss-slider-link" href="<?php echo esc_url( $item['url'] ); ?>">
						<img class="kyom-rss-slider-image" src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>">
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#" uk-slidenav-previous
			uk-slider-item="previous"></a>
		<a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#" uk-slidenav-next
			uk-slider-item="next"></a>
	</div>
	<ul class="uk-slider-nav uk-dotnav uk-flex-center uk-margin">
	</ul>
</div>
