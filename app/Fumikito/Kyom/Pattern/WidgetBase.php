<?php

namespace Fumikito\Kyom\Pattern;

/**
 * Widget base
 * @package kyom
 * @property-read string $id_name
 */
abstract class WidgetBase extends \WP_Widget {
	
	/**
	 * WidgetBase constructor.
	 *
	 * @param string $id_base
	 * @param string $name
	 * @param array $widget_options
	 * @param array $control_options
	 */
	public function __construct( $id_base = '', $name = '', array $widget_options = [], array $control_options = [] ) {
		parent::__construct( $this->id_name, 'Kyom: ' . $this->name(), [
			'description' => $this->description(),
		], $control_options );
	}
	
	/**
	 * Get name string.
	 *
	 * @return string
	 */
	abstract protected function name():string;
	
	/**
	 * Get description.
	 *
	 * @return string
	 */
	protected function description() {
		return '';
	}
	
	/**
	 * Get form object.
	 *
	 * @return array
	 */
	protected function get_params() {
		return [
			'title' => [
				'label' => __( 'Title' ),
			]
		];
	}
	
	/**
	 * Get formatted params
	 *
	 * @return array
	 */
	protected function get_formatted_params() {
		$formatted = [];
		foreach ( $this->get_params() as $key => $setting ) {
			$setting = wp_parse_args( $setting, [
				'default'     => '',
				'type'        => 'text',
				'label'       => '',
				'options'     => [],
				'rows'        => 2,
				'placeholder' => '',
			] );
			$formatted[ $key ] = $setting;
		}
		return $formatted;
	}
	
	/**
	 * Get fully filled instance data.
	 *
	 * @param array $instance
	 * @return array
	 */
	protected function get_filled_instance( $instance ) {
		$default = [];
		foreach ( $this->get_formatted_params() as $key => $setting ) {
			$default[ $key ] = $setting['default'];
		}
		return wp_parse_args( $instance, $default );
	}
	
	/**
	 * Get form object.
	 *
	 * @param array $instance
	 *
	 * @return string
	 */
	public function form( $instance ) {
		$instance = $this->get_filled_instance( $instance );
		if ( ! $instance ) {
			return parent::form( $instance );
		}
		foreach ( $this->get_formatted_params() as $key => $setting ) {
			?>
			<p class="kyom-widget-control">
				<?php switch ( $setting['type'] ) {
					case 'number':
					case 'text':
					case 'url':
					case 'tel':
					case 'password':
					case 'email':
						printf(
							'<label for="%1$s">%3$s</label><input type="%4$s" id="%1$s" name="%2$s" value="%5$s" placeholder="%6$s" />',
							$this->get_field_id( $key ),
							$this->get_field_name( $key ),
							esc_html( $setting['label'] ),
							esc_attr( $setting['type'] ),
							esc_attr( $instance[ $key ] ),
							esc_attr( $setting[ 'placeholder' ] )
						);
						break;
					case 'textarea':
						printf(
							'<label for="%1$s">%3$s</label><textarea id="%1$s" name="%2$s" rows="%5$d" placeholder="%6%s">%4$s</textarea>',
							$this->get_field_id( $key ),
							$this->get_field_name( $key ),
							esc_html( $setting['label'] ),
							esc_textarea( $instance[ $key ] ),
							$setting['rows'],
							esc_attr( $setting[ 'placeholder' ] )
						);
						break;
					case 'bool':
						printf(
							'<label class="inline"><input type="checkbox" value="1" name="%1$s" %2$s/> %3$s</label>',
							$this->get_field_name( $key ),
							checked( 1, $instance[ $key ], false ),
							esc_html( $setting['label'] )
						);
						break;
				} ?>
			</p>
			<?php
		}
	}
	
	
	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'id_name':
				return strtolower( str_replace( '\\', '-', get_called_class() ) );
				break;
			default:
				return null;
				break;
		}
	}
}
