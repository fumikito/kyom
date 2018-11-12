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
			'title' => [
				'label' => __( 'Title', 'kyom' ),
			],
			'background' => [
				'label' => __( 'Background Image', 'kyom' ),
				'type'  => 'attachment',
			],
			'text_color' => [
				'label'   => __( 'Text Color', 'kyom' ),
				'type'    => 'color',
				'default' => '#ffffff'
			],
			'align' => [
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
		if ( $atts['background'] && $src = wp_get_attachment_image_url( $atts['background'], 'full' ) ) {
			$style = sprintf( 'background-image: url(\'%s\')', esc_url( $src ) );
		} else {
			$style = '';
		}
		?>
		<section class="kyom-hero" style="<?= $style ?>" uk-parallax="bgy:-300">
			<div class="kyom-hero-box">
				<div class="kyom-hero-container uk-text-<?= esc_attr( $atts[ 'align' ] ) ?>">
					<h1 class="kyom-hero-title" style="color: <?= esc_attr( $atts['text_color'] ) ?>"><?= esc_html( $atts[ 'title' ] ) ?></h1>
					<?php if ( $content ) : ?>
						<div class="kyom-hero-lead" style="color: <?= esc_attr( $atts['text_color'] ) ?>">
							<?= wp_kses_post( wpautop( $content ) ) ?>
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
