<?php

namespace Fumikito\Kyom\Blocks;


use Fumikito\Kyom\Pattern\BlockBase;

/**
 *
 * @package kyom
 */
class Activities extends BlockBase {
	
	protected $icon = 'dashicons-portfolio';
	
	protected function is_available() {
		return post_type_exists( 'jetpack-portfolio' );
	}
	
	
	protected function get_label(): string {
		return __( 'Recent Projects', 'kyom' );
	}
	
	protected function get_name(): string {
		return 'projects';
	}
	
	protected function get_params(): array {
		return [
			'number' => [
				'label'   => __( 'Number to display', 'kyom' ),
				'type'    => 'number',
				'default' => 5,
			],
		];
	}
	
	/**
	 * Render activities.
	 *
	 * @param array $atts
	 * @param string $content
	 *
	 * @return string
	 */
	protected function render( $atts = [], $content = '' ):string {
		$posts = get_posts( [
			'post_type'      => 'jetpack-portfolio',
			'posts_per_page' => $atts[ 'number' ],
			'post_status'    => 'publish',
			'meta_query'     => [
				[
					'key'     => '_thumbnail_id',
					'value'   => 0,
					'compare' => '>',
				]
			],
		] );
		if ( ! $posts ) {
			return '';
		}
		ob_start();
		?>
		<div class="uk-container-gap">
			<div class="uk-container">
				<div class="uk-position-relative uk-visible-toggle uk-light" uk-slideshow="animation: fade; autoplay; true;">
					<ul class="uk-slideshow-items">
						<?php foreach ( $posts as $post ) : ?>
							<li>
								<img src="<?= get_the_post_thumbnail_url( $post, 'full' ) ?>"
									 alt="<?= esc_attr( get_the_title( $post ) ) ?>" uk-cover>
								<div class="uk-overlay uk-overlay-primary uk-position-bottom uk-text-center uk-transition-slide-bottom">
									<a href="<?php the_permalink( $post ) ?>" style="display: block">
										<h3 class="uk-margin-remove"><?= esc_html( get_the_title( $post ) ); ?></h3>
									<?php
									$terms = get_the_terms( $post, 'jetpack-portfolio-type' );
									if ( $terms && ! is_wp_error( $terms ) ) :
										?>
										<p class="uk-margin-remove">
											<?= implode( ', ', array_map( function ( $term ) {
												return esc_html( $term->name );
											}, $terms ) ) ?>
										</p>
									<?php endif; ?>
									</a>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
					<a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#" uk-slidenav-previous
					   uk-slideshow-item="previous"></a>
					<a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#" uk-slidenav-next
					   uk-slideshow-item="next"></a>
				</div>
			</div>
		</div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		
		return $content;
	}
	
	
}