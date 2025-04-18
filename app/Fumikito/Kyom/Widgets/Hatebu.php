<?php

namespace Fumikito\Kyom\Widgets;


use Fumikito\Kyom\Pattern\WidgetBase;

class Hatebu extends WidgetBase {

	protected function name(): string {
		return __( 'Hatena Bookmark', 'kyom' );
	}

	protected function description() {
		return __( 'Display entries in Hatenna Bookmark.', 'kyom' );
	}

	protected function get_params() {
		return array_merge( parent::get_params(), [
			'url'   => [
				'label'       => __( 'URL', 'kyom' ),
				'placeholder' => preg_replace( '#https?://#u', '', untrailingslashit( home_url() ) ),
			],
			'count' => [
				'type'    => 'number',
				'label'   => __( 'Max Width', 'kyom' ),
				'default' => 5,
			],
		] );
	}

	public function widget( $args, $instance ) {
		$instance = $this->get_filled_instance( $instance );
		$url      = $instance['url'];
		if ( ! $url ) {
			return;
		}
		// Use cache.
		$cache     = 'hatena_widget_cache';
		$transient = get_transient( $cache );
		if ( false !== $transient ) {
			echo $transient;
			return;
		}
		ob_start();
		$title = $instance['title'];
		$count = max( 1, (int) $instance['count'] );

		$entries = [];
		foreach ( [
			'count' => _x( 'Popular', 'hatebu', 'kyom' ),
			'hot'   => _x( 'Hot', 'hatebu', 'kyom' ),
			'eid'   => _x( 'New', 'hatebu', 'kyom' ),
		] as $key => $label ) {
			$rss = kyom_get_hatena_rss( $url, $key, $count );
			if ( $rss ) {
				$entries[ $key ] = [
					'label' => $label,
					'items' => $rss,
				];
			}
		}

		if ( $entries ) {
			echo $args['before_widget'];
			if ( $title ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
			}
			?>
			<ul uk-tab class="uk-tab">
				<?php
				$done = false;
				foreach ( $entries as $key => $rss ) :
					?>
					<li class="<?php echo $done ? '' : 'uk-active'; ?>">
						<a href="#"><?php echo esc_html( $rss['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<ul class="uk-switcher uk-margin">
				<?php foreach ( $entries as $key => $rss ) : ?>
					<li id="hatebu-<?php echo $key; ?>" class="kyom-simple-list-wrapper">
						<ul class="kyom-simple-list">
							<?php foreach ( $rss['items'] as $item ) : ?>
								<li class="kyom-simple-list-item">
									<a href="<?php echo esc_url( $item->get_permalink() ); ?>" class="kyom-simple-list-link">
										<div class="kyom-simple-list-count">
											<?php $tag = $item->get_item_tags( 'http://www.hatena.ne.jp/info/xmlns#', 'bookmarkcount' ); ?>
											<?php echo kyom_short_digits( $tag[0]['data'] ); ?>
										</div>
										<h3 class="kyom-simple-list-title">
											<?php echo esc_html( current( array_map( 'trim', explode( '|', $item->get_title() ) ) ) ); ?>
										</h3>
										<div class="kyom-simple-list-meta">
											<span uk-icon="calendar"></span>
											<?php echo $item->get_date( get_option( 'date_format' ) ); ?>
										</div>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php
			echo $args['after_widget'];
		}
		$content = (string) ob_get_contents();
		set_transient( $cache, ob_get_contents(), 30 * 60 );
		ob_end_flush();
	}
}
