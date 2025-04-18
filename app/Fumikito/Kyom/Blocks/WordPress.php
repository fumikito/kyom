<?php

namespace Fumikito\Kyom\Blocks;


use Fumikito\Kyom\Pattern\BlockBase;
use Fumikito\Kyom\Service\WordPressOrg;


class WordPress extends BlockBase {

	protected $icon = 'dashicons-wordpress';

	protected $allow_content = true;

	protected function init() {
		parent::init();
	}


	protected function get_label(): string {
		return __( 'WordPress Activities', 'kyom' );
	}

	protected function get_name(): string {
		return 'wordpress-activities';
	}

	protected function get_params(): array {
		return [
			'user_name'        => [
				'label' => __( 'WordPress User Name', 'kyom' ),
			],
			'user_mail'        => [
				'label' => __( 'WordPress User Email', 'kyom' ),
				'type'  => 'email',
			],
			'background-color' => [
				'label'   => __( 'Background Color' ),
				'type'    => 'color',
				'default' => '',
			],
		];
	}

	protected function render( $atts = [], $content = '' ) {
		if ( ! $atts['user_name'] ) {
			return '';
		}

		$data = WordPressOrg::get_profile( $atts['user_name'] );
		if ( ! $data ) {
			return '';
		}
		if ( did_action( 'template_redirect' ) ) {
			wp_enqueue_style( 'dashicons' );
		}
		ob_start();
		?>
		<section class="wporg" style="<?php echo $atts['background-color'] ? esc_attr( sprintf( 'background-color: %s', $atts['background-color'] ) ) : ''; ?>">
			<div class="uk-container">
				<h2 class="wporg-title uk-text-center">
					<span uk-icon="icon:WordPress; ratio: 3"></span>
					<?php esc_html_e( 'WordPress Activity', ' ' ); ?>
				</h2>
				<?php if ( $content ) : ?>
				<div class="wporg-lead uk-text-center">
					<?php echo wp_kses_post( wpautop( $content ) ); ?>
				</div>
				<?php endif; ?>
				<?php if ( $data['badges'] ) : ?>
					<div class="wporg-badges">
						<?php
						foreach ( $data['badges'] as $badge ) :
							$label = WordPressOrg::translate_role( $badge['label'] );
							?>
						<span class="wporg-role">
							<span class="<?php echo esc_attr( implode( ' ', $badge['class'] ) ); ?>" title="<?php echo esc_attr( $label ); ?>"></span>
							<span class="wporg-role-text uk-visible@s" aria-hidden="true"><?php echo esc_html( $label ); ?></span>
						</span>

						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<div class="wporg-downloads">
					<small><?php esc_html_e( 'Total', 'kyom' ); ?></small>
					<strong><?php echo number_format( $data['downloads'] ); ?></strong>
					<small><?php echo _n( 'Download', 'Downloads', $data['downloads'], 'kyom' ); ?></small>
					<?php echo WordPressOrg::delivering_item_count( $data, '<span class="wporg-downloads-items">(', ')</span>' ); ?>
				</div>
				<p class="wporg-profile">
					<?php echo get_avatar( $atts['user_mail'], 360, '', $atts['user_name'], [ 'class' => 'wporg-avatar uk-border-circle' ] ); ?>
					<a href="<?php echo esc_url( WordPressOrg::get_profile_url( $atts['user_name'] ) ); ?>" class="wporg-profile-link">
						<?php echo esc_html( $atts['user_name'] ); ?>
					</a>
					<small>
						<?php printf( esc_html__( 'Member Since %s', 'kyom' ), date_i18n( get_option( 'date_format' ), $data['member_since'] ) ); ?>
					</small>
				</p>
			</div>
		</section>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
