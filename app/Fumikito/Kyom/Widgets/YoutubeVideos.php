<?php

namespace Fumikito\Kyom\Widgets;


use Fumikito\Kyom\Pattern\WidgetBase;

/**
 * YouTube Video List widget.
 *
 * @package kyom
 */
class YoutubeVideos extends WidgetBase {

	protected function name(): string {
		return __( 'YouTube Video List', 'kyom' );
	}

	protected function description() {
		return __( 'Display YouTube video list from your channel.', 'kyom' );
	}

	protected function get_params() {
	    $options = [];
	    foreach ( kyom_get_youtube_playlist( false ) as $label => $value ) {
	        $options[ $value ] = $label;
        }
		return array_merge( parent::get_params(), [
            'playlist' => [
                'label'   => __( 'Play List', 'kyom' ),
                'type'    => 'select',
                'options' => $options,
                'description' => __( 'If no playlist displayed, you need set YouTube channel ID on Setting > General.', 'kyom' ),
            ],
            'subscription' => [
                'label' => __( 'Display Subscription Button', 'kyom' ),
                'type'  => 'bool',
            ],
        ] );
	}


	public function widget( $args, $instance ) {
		$channel  = kyom_get_youtube_channel();
		if ( is_wp_error( $channel ) ) {
			// No channel. Skip.
			return;
		}
		$instance     = $this->get_filled_instance( $instance );
		$title        = $instance['title'];
		$playlist     = $instance['playlist'];
		$subscription = $instance['subscription'];
		$videos       = kyom_get_youtube_videos( $playlist );
		if ( is_wp_error( $videos ) || empty( $videos ) ) {
		    // No video, return.
		    return;
        }

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		if ( $subscription ) {
		    wp_enqueue_script( 'google-api-platform' );
        }
		?>
        <ul class="widget-youtube-list">
            <?php foreach ( $videos as $video ) : ?>
            <li class="widget-youtube-list-item">
                <a class="widget-youtube-list-link" href="https://www.youtube.com/watch?v=<?php echo esc_attr( $video['contentDetails']['videoId'] ); ?>" target="_blank" rel="noopener noreferrer">
                    <img class="widget-youtube-list-image" loading="lazy" alt=""
                         src="<?php echo esc_url( $video['snippet']['thumbnails']['default']['url'] ) ?>"
                         width="<?php echo esc_attr( $video['snippet']['thumbnails']['default']['width'] ) ?>"
                         height="<?php echo esc_attr( $video['snippet']['thumbnails']['default']['height'] ) ?>"
                    />
                    <p class="widget-youtube-list-body">
                        <span class="widget-youtube-list-title"><?php echo esc_html( $video['snippet']['title'] ) ?></span>
                        <span class="widget-youtube-list-meta">
                            <span uk-icon="calendar"></span>
                            <?php echo $video['contentDetails']['videoPublishedAt'] ?>
                        </span>
                    </p>
                </a>
            </li>

            <?php endforeach; ?>
        </ul>
		<?php if ( $subscription ) : ?>
            <div class="widget-youtube-list-footer" style="min-height: 48px">
                <div class="g-ytsubscribe" data-channelid="<?php echo esc_attr( $channel['id'] ) ?>" data-layout="full" data-count="default"></div>
            </div>
		<?php endif; ?>
		<?php
		echo $args['after_widget'];
	}

}
