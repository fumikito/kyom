<?php

namespace Fumikito\Kyom\Blocks;

use Fumikito\Kyom\Pattern\BlockBase;

/**
 * Class pullQuotes
 *
 * @package Fumikito\Kyom\Blocks
 */
class Testimonials extends BlockBase {

	protected $icon = 'dashicons-star-filled';

	/**
	 * Check Jetpack is activated.
	 *
	 * @return bool
	 */
	protected function is_available() {
		return post_type_exists( 'jetpack-testimonial' );
	}

	protected function get_name(): string {
		return 'kyom-testimonial';
	}

	protected function get_label(): string {
		return __( 'Testimonials', 'kyom' );
	}

	protected function get_params(): array {
		return [
			'number' => [
				'label'   => __( 'Number of Display', 'kyom' ),
				'type'    => 'number',
				'default' => -1,
			],
			'order' => [
				'label'   => __( 'Order' ),
				'type'    => 'select',
				'default' => 'random',
				'options' => [
					[
						'label' => __( 'Random', 'kyom' ),
						'value' => 'random',
					],
					[
						'label' => __( 'From Newest', 'kyom' ),
						'value' => 'desc',
					],
					[
						'label' => __( 'Post Order', 'kyom' ),
						'value' => 'post_order',
					],
				],
			],
		];
	}

	protected function render( $atts = [], $content = '' ) {
		$args = [
			'post_type' => 'jetpack-testimonial',
			'post_status' => 'publish',
			'posts_per_page' => $atts['number'],
		];
		switch ( $atts['order'] ) {
			case 'random':
				$args['order'] = 'rand';
				break;
			case 'post_order':
				$args['orderby'] = [ 'menu_order' => 'DESC' ];
				break;
			default:
				$args['orderby'] = [ 'date' => 'DESC' ];
				break;
		}
		$posts = get_posts( $args );
		global $post;
		ob_start();
		?>
		<section class="testimonial-slider">
			<div uk-slider="center: true; autoplay:true">
				<div class="uk-position-relative uk-visible-toggle uk-light">
					<ul class="uk-slider-items uk-grid">
						<?php foreach ( $posts as $post ) : setup_postdata( $post ); ?>
							<li class="uk-width-3-4 uk-width-2-3@m">
								<a href="<?php the_permalink( $post ) ?>" class="testimonial-slider-item">
								<div class="uk-card uk-card-default">
									<div class="uk-card-body">
										<h3 class="uk-card-title">
											<?php if ( has_post_thumbnail( $post ) ) : ?>
												<?= get_the_post_thumbnail( $post, 'face-rectangle', [ 'class' => 'testimonial-slider-face' ] ) ?>
											<?php endif; ?>
											<?= esc_html( get_the_title( $post ) ); ?>
										</h3>
										<?php the_excerpt() ?>
									</div>
								</div>
								</a>
							</li>
						<?php endforeach; wp_reset_postdata(); ?>
					</ul>
					<a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#" uk-slidenav-previous
					   uk-slider-item="previous"></a>
					<a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#" uk-slidenav-next
					   uk-slider-item="next"></a>
				</div>
				<ul class="uk-slider-nav uk-dotnav uk-flex-center uk-margin"></ul>
			</div>
		</section>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}


}
