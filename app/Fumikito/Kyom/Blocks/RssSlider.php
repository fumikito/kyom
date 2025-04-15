<?php

namespace Fumikito\Kyom\Blocks;


use Fumikito\Kyom\Pattern\BlockBase;

class RssSlider extends BlockBase {

	protected $icon = 'dashicons-rss';

	protected function get_label(): string {
		return __( 'RSS Sliders', 'kyom' );
	}

	protected function get_name(): string {
		return 'rss-sliders';
	}

	protected function get_params(): array {
		return [
			'url' => [
				'label' => __( 'Feed URL', 'kyom' ),
				'type'  => 'url',
			],
		];
	}

	protected function render( $atts = [], $content = '' ) {
		ob_start();
		if ( ! $atts['url'] ) {
			return '';
		}
		$items = kyom_fetch_feed_items( $atts['url'] );
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
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
