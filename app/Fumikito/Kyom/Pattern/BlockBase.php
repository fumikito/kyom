<?php

namespace Fumikito\Kyom\Pattern;


/**
 * Base class for block
 *
 * @package kyom
 * @property string $block_name
 * @property array  $params
 */
abstract class BlockBase {

	protected $namespace = 'kyom';

	protected static $instances = [];

	protected $icon = '';

	/**
	 * @var bool If true, content will be used.
	 */
	protected $allow_content = false;

	/**
	 * Detect if this is available.
	 *
	 * @return bool
	 */
	protected function is_available() {
		return true;
	}

	/**
	 * Constructor
	 */
	final private function __construct() {
		if ( ! $this->is_available() ) {
			// Do nothing.
			return;
		}
		$this->init();
		// Register short code.
		add_shortcode( $this->get_name(), [ $this, 'do_short_code' ] );
		// Register short cake.
		add_action( 'register_shortcode_ui', [ $this, 'register_shortcake' ], 10, 2 );
	}

	/**
	 * Do something in constructor
	 */
	protected function init() {
		// Do something.
	}

	/**
	 * Limits post type
	 *
	 * @return array|string[]
	 */
	protected function limits() {
		return [];
	}

	/**
	 * Get label
	 *
	 * @return string
	 */
	abstract protected function get_label(): string;

	/**
	 * Should return shortcode naem.
	 *
	 * @return string
	 */
	abstract protected function get_name(): string;

	/**
	 * Process short code.
	 *
	 * @param array $atts
	 * @param string $content
	 * @return string
	 */
	public function do_short_code( $atts = [], $content = '' ) {
		$default = [];
		foreach ( $this->params as $key => $param ) {
			$default[ $key ] = $atts[ $key ] ?? $param['default'];
		}
		$output = $this->render( $default, $content );
		return implode( "\n", array_filter( array_map( function ( $row ) {
			return trim( $row );
		}, explode( "\n", $output ) ) ) );
	}

	/**
	 * Register short code UI.
	 */
	public function register_shortcake() {
		$args = [
			'label'         => $this->get_label(),
			'listItemImage' => $this->icon,
		];
		if ( $this->allow_content ) {
			$args['inner_content'] = [
				'label' => __( 'Content', 'kyom' ),
			];
		}
		$limit = $this->limits();
		if ( $limit ) {
			$args['post_type'] = $limit;
		}
		$args['attrs'] = [];
		foreach ( $this->params as $key => $param ) {
			$param           = wp_parse_args( $param, [
				'attr'        => $key,
				'label'       => '',
				'description' => '',
				'type'        => 'text',
				'options'     => [],
				'meta'        => [],
				'default'     => '',
			] );
			$args['attrs'][] = $param;
		}
		shortcode_ui_register_for_shortcode( $this->get_name(), $args );
	}

	/**
	 * Params of this block.
	 *
	 * @see https://github.com/wp-shortcake/Shortcake/blob/master/dev.php
	 * @return array
	 */
	abstract protected function get_params(): array;

	/**
	 * Rendering result.
	 *
	 *
	 * @param array  $atts
	 * @param string $content
	 * @return string
	 */
	abstract protected function render( $atts = [], $content = '' );

	/**
	 * Get instance.
	 *
	 * @return static
	 */
	public static function get_instance() {
		$class_name = get_called_class();
		if ( ! isset( self::$instances[ $class_name ] ) ) {
			self::$instances[ $class_name ] = new $class_name();
		}
		return self::$instances[ $class_name ];
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'block_name':
				return $this->namespace . '/' . $this->get_name();
			case 'params':
				// Todo fill required params.
				return $this->get_params();
			default:
				return null;
		}
	}
}
