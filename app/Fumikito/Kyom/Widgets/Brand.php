<?php
namespace Fumikito\Kyom\Widgets;


use Fumikito\Kyom\Pattern\WidgetBase;

/**
 * Brand widget.
 */
class Brand extends WidgetBase {
	
	protected function name(): string {
		return __( 'Brand', 'kyom' );
	}
	
	protected function description() {
		return __( 'Displays your brand with custom icon, custom logo and description.', 'kyom' );
	}
	
	public function widget( $args, $instance ) {
		$title = $instance['title'] ?? '';
		$instance = $this->get_actual_value( $instance );
		if ( ! array_filter( $instance ) ) {
			return;
		}
		$out = '';
		foreach ( $instance as $key => $value ) {
			if ( ! $value ) {
				continue;
			}
			switch ( $key ) {
				case 'site_icon':
					$out .= sprintf( '<img src="%s" alt="%s" class="kyom-brand-icon" />', esc_url( $value ), get_bloginfo( 'name' ) );
					break;
				case 'custom_logo':
					$out .= $value;
					break;
				case 'tagline':
					$out .= sprintf( '<div class="kyom-brand-tag-line">%s</div>', wp_kses_post( wpautop( $value ) ) );
					break;
				default:
					$value = get_option( 'kyom_' . $key, '' );
					if ( ! $value ) {
						break;
					}
					switch ( $key ) {
						case 'long_desc';
							$out .= sprintf( '<div class="kyom-brand-long-desc">%s</div>', wp_kses_post( wpautop( $value ) ) );
							break;
						case 'address';
							$out .= sprintf( '<address class="kyom-brand-address">%s</address>', nl2br( esc_html( $value ) ) );
							break;
						case 'tel';
							$out  .= sprintf(
								'<p class="kyom-brand-contact"><span uk-icon="phone"></span> <a href="tel:%s">%s</a></p>',
								preg_replace( '/\D/u', '', $value ),
								esc_html( $value )
							);
							break;
						case 'mail';
							$out  .= sprintf(
								'<p class="kyom-brand-contact"><span uk-icon="mail"></span> <a href="mailto:%s">%s</a></p>',
								esc_attr( $value ),
								esc_html( $value )
							);
							break;
					}
					break;
			}
		}
		if ( ! $out ) {
			return;
		}
		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		echo '<div class="kyom-brand">' . $out . '</div>';
		echo $args['after_widget'];
	}
	
	/**
	 * Get actual value.
	 *
	 * @param array $instance
	 *
	 * @return array
	 */
	protected function get_actual_value( $instance ) {
		$instance = $this->get_filled_instance( $instance );
		$values = [];
		foreach ( $instance as $key => $display ) {
			if ( ! $display ) {
				$values[ $key ] = false;
				continue;
			}
			switch ( $key ) {
				case 'site_icon':
					$values[ $key ] = has_site_icon() ? get_site_icon_url() : '';
					break;
				case 'custom_logo':
					$values[ $key ] = has_custom_logo() ? get_custom_logo() : '';
					break;
				case 'tagline':
					$values[ $key ] = get_bloginfo( 'description' );
					break;
				default:
					$values[ $key ] = get_option( 'kyom_' . $key, '' );
					break;
			}
		}
		return $values;
	}
	
	protected function get_params() {
		return array_merge( parent::get_params(), [
			'site_icon'  => [
				'label' => __( 'Display site icon', 'kyom' ),
				'type'  => 'bool',
			],
			'custom_logo' => [
				'label' => __( 'Display custom logo', 'kyom' ),
				'type'  => 'bool',
			],
			'tagline' => [
				'label' => __( 'Display tag line', 'kyom' ),
				'type'  => 'bool',
			],
			'long_desc' => [
				'label' => __( 'Display long description', 'kyom' ),
				'type'  => 'bool',
			],
			'address' => [
				'label' => __( 'Display address', 'kyom' ),
				'type'  => 'bool',
			],
			'tel' => [
				'label' => __( 'Display tel', 'kyom' ),
				'type'  => 'bool',
			],
			'mail' => [
				'label' => __( 'Display mail', 'kyom' ),
				'type'  => 'bool',
			],
		] );
	}
	
	
}
