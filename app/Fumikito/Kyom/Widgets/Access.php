<?php

namespace Fumikito\Kyom\Widgets;


use Fumikito\Kyom\Pattern\WidgetBase;

/**
 * Access ranking widget.
 * @package kyom
 */
class Access extends WidgetBase {

	protected function name(): string {
		return __( 'Access Ranking', 'kyom' );
	}

	protected function description() {
		return __( 'Display Access ranking by Google Analytics. Requires Gianism.', 'kyom' );
	}

	protected function get_params() {
		return array_merge( parent::get_params(), [
			'count' => [
				'type'  => 'number',
				'label' => __( 'Number to display.', 'kyom' ),
			],
			'filters' => [
				'label' => __( 'Filters Expression', 'kyom' ),
				'description' => __( 'Google Analytics Core Reporting API filter. Requires programing knowledge.' ),
				'placeholder' => 'ga:pagePath~=/(foo|var)/\\d+',
			],
		] );
	}

	public function widget( $args, $instance ) {
		$instance = $this->get_filled_instance( $instance );
		$count = max( 5, $instance['count'] );
		$title = $instance['title'];
		$ranking = [];
		foreach ( [
			3     => _x( '3 days', 'ranking', 'kyom' ),
			30    => _x( '30 days', 'ranking', 'kyom' ),
			'all' => _x( 'Total', 'ranking', 'kyom' ),
		] as $date => $label ) {
			$cache_days = 'all' === $date ? 30 : $date / 6;
			$cache_minutes = $cache_days * 24 * 60 * 60;
			$cache_key  = 'kyom_ranking_cache_' . $cache_days;
			$result = get_transient( $cache_key );
			if ( false === $result ) {
				$result = kyom_get_ranking( $date, $count, $instance['filters'] );
				set_transient( $cache_key, $result, $cache_minutes );
			}
			if ( $result ) {
				$ranking[] = [
					'days'  => $date,
					'label' => $label,
					'posts' => $result,
				];
			}
		}
		if ( ! $ranking ) {
			return;
		}
		$before = $args['before_widget'];
		echo $before;
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		?>
		<ul uk-tab class="uk-tab">
			<?php $done = false; foreach ( $ranking as $rank ) : ?>
				<li class="<?= $done ? '' : 'uk-active' ?>">
					<a href="#"><?= esc_html( $rank['label'] ) ?></a></li>
			<?php endforeach; ?>
		</ul>
		<ul class="uk-switcher uk-margin">
			<?php foreach ( $ranking as $rank ) : ?>
			<li id="ga-ranking-<?= $rank['days'] ?>" class="kyom-simple-list-wrapper">
				<ul class="kyom-simple-list">
					<?php foreach ( $rank['posts'] as $item ) : ?>
						<li class="kyom-simple-list-item">
							<a href="<?php the_permalink( $item['post'] ) ?>" class="kyom-simple-list-link">
								<div class="kyom-simple-list-count">
									<?= kyom_short_digits( $item['pv'] ) ?>
								</div>
								<h3 class="kyom-simple-list-title">
									<?= esc_html( get_the_title( $item[ 'post' ] ) ) ?>
								</h3>
								<div class="kyom-simple-list-meta">
									<span uk-icon="calendar"></span>
									<?= get_the_time( get_option( 'date_format' ), $item[ 'post' ] ) ?>
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
}
