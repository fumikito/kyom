<?php

namespace Fumikito\Kyom\Widgets;


use Fumikito\Kyom\Pattern\WidgetBase;

/**
 * YouTube Channel widget
 *
 * @package kyom
 */
class YoutubeChannel extends WidgetBase {

	/**
	 * {@inheritdoc}
	 */
	protected function name(): string {
		return __( 'YouTube Channel', 'kyom' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function description() {
		return __( 'Display YouTube channel widget. Needs API Key and Channel ID.', 'kyom' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_params() {
		return array_merge( parent::get_params(), [
			'layout' => [
				'label'   => __( 'Subscribe Button', 'kyom' ),
				'type'    => 'select',
				'options' => [
					''     => __( 'Default', 'kyom' ),
					'full' => __( 'Wide', 'kyom' ),
				],
			],
		] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function widget( $args, $instance ) {
		$channel = kyom_get_youtube_channel();
		if ( is_wp_error( $channel ) ) {
			// No channel. Skip.
			return;
		}
		$instance = $this->get_filled_instance( $instance );
		$title    = $instance['title'];
		$layout   = $instance['layout'] ?: 'default';
		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		wp_enqueue_script( 'google-api-platform' );
		?>
		<div class="uk-card uk-card-default">
			<?php if ( isset( $channel['snippet']['thumbnails']['high'] ) ) : ?>
			<div class="uk-card-media-top">
				<img loading="lazy" alt="<?php echo esc_attr( $channel['snippet']['title'] ); ?>"
					width="<?php echo esc_attr( $channel['snippet']['thumbnails']['high']['width'] ); ?>"
					height="<?php echo esc_attr( $channel['snippet']['thumbnails']['high']['height'] ); ?>"
					src="<?php echo esc_attr( $channel['snippet']['thumbnails']['high']['url'] ); ?>"
					/>
			</div>
			<?php endif; ?>
			<div class="uk-card-body">
				<h3 class="uk-card-title widget-youtube-title">
					<span class="widget-youtube-icon" uk-icon="youtube"></span>
					<?php echo esc_html( $channel['snippet']['title'] ); ?>
				</h3>
				<?php echo wpautop( esc_html( $channel['snippet']['description'] ) ); ?>
			</div>
			<div class="uk-card-footer widget-youtube">
				<div class="g-ytsubscribe" data-channelid="<?php echo esc_attr( $channel['id'] ); ?>" data-layout="<?php echo esc_attr( $layout ); ?>" data-count="default"></div>
			</div>
		</div>
		<?php
		echo $args['after_widget'];
	}
}
