<?php

namespace Fumikito\Kyom\Widgets;


use Fumikito\Kyom\Pattern\WidgetBase;

class Ad extends WidgetBase {
	
	protected function name(): string {
		return __( 'Advertisement', 'kyom' );
	}
	
	protected function description() {
		return __( 'Insert advertisement scripts like Google Adsense. Any javascripts are allowed.', 'kyom' );
	}
	
	protected function get_params() {
		return array_merge( parent::get_params(), [
			'content' => [
				'type'  => 'textarea',
				'label' => __( 'Ad Script', 'kyom' ),
				'rows'  => 10,
			],
			'width' => [
				'type'  => 'number',
				'label' => __( 'Max Width', 'kyom' ),
			],
		] );
	}
	
	public function widget( $args, $instance ) {
		$instance = $this->get_filled_instance( $instance );
		$content = $instance['content'];
		if ( ! $content ) {
			return;
		}
		$title = $instance['title'];
		$width = (int) $instance['width'];
		$before = $args['before_widget'];
		if ( $width ) {
			$before = str_replace( ' class="normal-widget-inner', sprintf( ' style="max-width: %dpx" class="normal-widget-inner', $width ), $before );
		}
		echo $before;
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		echo $content;
		
		echo $args['after_widget'];
	}
	
	
}
