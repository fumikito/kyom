<?php

namespace Fumikito\Kyom\Blocks;


use Fumikito\Kyom\Pattern\BlockBase;

/**
 * Get recent posts.
 * @package kyom
 */
class Recent extends BlockBase {
	
	protected $icon = 'dashicons-admin-post';
	
	protected function get_label(): string {
		return __( 'Recent Posts' );
	}
	
	protected function get_name(): string {
		return 'recent-posts';
	}
	
	protected function get_params(): array {
		return [
			'title' => [
				'label'   => __( 'Title' ),
				'default' => __( 'Recent Posts' ),
			],
			'category' => [
				'label'    => __( 'Category' ),
				'type'     => 'term_select',
				'taxonomy' => 'category',
				'multiple' => true,
			],
		];
	}
	
	protected function render( $atts = [], $content = '' ) {
		$args = [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'cat'            => $atts['category'],
			'posts_per_page' => 5,
		];
		$query = new \WP_Query( $args );
		if ( ! $query->have_posts()) {
			return sprintf( '<div class="uk-alert uk-alert-muted">%s</div>', esc_attr__( 'No post found.', 'kyom' ) );
		}
		ob_start();
		?>
		<div class="recent-wide">
			<h2 class="recent-wide-title">
				<?= esc_html( $atts['title'] ) ?>
			</h2>
			<div class="recent-wide-list">
				<div class="recent-wide-list-column">
					<?php $query->the_post(); ?>
					<div class="recent-wide-item-large">
						<?php get_template_part( 'template-parts/loop', 'recent-card' ) ?>
					</div>
				</div>
				<div class="recent-wide-list-column">
					<div class="recent-wide-grid">
					<?php $counter = 0; if ( $query->have_posts() ) : ?>
						<?php while ( $query->have_posts() ): $query->the_post(); $counter++; ?>
						<div class="recent-wide-item">
							<?php get_template_part( 'template-parts/loop', 'recent-card' ) ?>
						</div>
						<?php
							if ( 4 === $counter ) {
								break;
							}
							endwhile;
						?>
					<?php endif; ?>
					</div>
				</div>
			</div>
			<?php if ( $page = get_post( get_option( 'page_for_posts' ) ) ) :?>
			<div class="recent-wide-link uk-text-center uk-margin">
				<?php ?>
				<a href="<?php the_permalink( $page ) ?>" class="uk-button uk-button-primary uk-button-large"><?= esc_html( get_the_title( $page ) ) ?></a>
			</div>
			<?php endif; ?>
		</div>
		<?php
		wp_reset_postdata();
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
