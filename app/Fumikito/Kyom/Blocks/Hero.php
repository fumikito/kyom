<?php

namespace Fumikito\Kyom\Blocks;


use Fumikito\Kyom\Pattern\BlockBase;

/**
 * Hero unit
 * @package blocks
 */
class Hero extends BlockBase {

	protected $icon = 'dashicons-nametag';

	protected $allow_content = true;

	protected function get_label(): string {
		return __( 'Hero Unit', 'kyom' );
	}

	protected function get_name(): string {
		return 'kyom-hero';
	}

	protected function get_params(): array {
		return [
			'title'           => [
				'label' => __( 'Title', 'kyom' ),
			],
			'background'      => [
				'label' => __( 'Background Image', 'kyom' ),
				'type'  => 'attachment',
			],
			'text_color'      => [
				'label'   => __( 'Text Color', 'kyom' ),
				'type'    => 'color',
				'default' => '#ffffff',
			],
			'text_background' => [
				'label'   => __( 'Text Background Color', 'kyom' ),
				'type'    => 'color',
				'default' => '',
			],
			'align'           => [
				'label'   => __( 'Text Align', 'kyom' ),
				'type'    => 'select',
				'default' => 'center',
				'options' => [
					[
						'label' => __( 'Center', 'kyom' ),
						'value' => 'center',
					],
					[
						'label' => __( 'Left', 'kyom' ),
						'value' => 'left',
					],
					[
						'label' => __( 'Right', 'kyom' ),
						'value' => 'right',
					],
				],
			],
		];
	}

	protected function render( $atts = [], $content = '' ) {
		ob_start();
		$style = '';
		if ( $atts['background'] ) {
			$src = wp_get_attachment_image_url( $atts['background'], 'full' )
			if ( $src ) {
				$style = sprintf( 'background-image: url(\'%s\')', esc_url( $src ) );
			}
		}
		$background = $atts['text_background'] ? kyom_hex2rgba( $atts['text_background'], .6 ) : 'transparent';
		$text_style = sprintf( 'color: %s; background-color: %s', esc_attr( $atts['text_color'] ), esc_attr( kyom_hex2rgba( $atts['text_background'], 0.6 ) ) );
		?>
		<section class="kyom-hero" style="<?php echo $style; ?>" uk-parallax="bgy:-300">
			<div class="kyom-hero-box">
				<div class="kyom-hero-container uk-text-center">
					<h1 class="kyom-hero-title" style="<?php echo $text_style; ?>">
						<?php echo esc_html( $atts['title'] ); ?>
					</h1>
					<?php if ( $content ) : ?>
						<br />
						<div class="kyom-hero-lead" style="<?php echo $text_style; ?>">
							<?php echo wp_kses_post( wpautop( $content ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
}
