<?php
/**
 * Activities block rendering
 *
 * @package kyom
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

// jetpack-portfolioが存在しない場合は表示しない
if ( ! post_type_exists( 'jetpack-portfolio' ) ) {
	return '';
}

$number = $attributes['number'] ?? 5;

$posts = get_posts( [
	'post_type'      => 'jetpack-portfolio',
	'posts_per_page' => $number,
	'post_status'    => 'publish',
	'meta_query'     => [
		[
			'key'     => '_thumbnail_id',
			'value'   => 0,
			'compare' => '>',
		],
	],
] );

if ( ! $posts ) {
	return '';
}
?>
<div class="uk-container-gap">
	<div class="uk-container">
		<div class="uk-position-relative uk-visible-toggle uk-light" uk-slideshow="animation: fade; autoplay: true;">
			<ul class="uk-slideshow-items">
				<?php foreach ( $posts as $post ) : ?>
					<li>
						<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post, 'full' ) ); ?>"
							alt="<?php echo esc_attr( get_the_title( $post ) ); ?>" uk-cover>
						<div class="uk-overlay uk-overlay-primary uk-position-bottom uk-text-center uk-transition-slide-bottom">
							<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" style="display: block">
								<h3 class="uk-margin-remove"><?php echo esc_html( get_the_title( $post ) ); ?></h3>
							<?php
							$terms = get_the_terms( $post, 'jetpack-portfolio-type' );
							if ( $terms && ! is_wp_error( $terms ) ) :
								?>
								<p class="uk-margin-remove">
									<?php
									echo esc_html( implode( ', ', array_map( function ( $term ) {
										return $term->name;
									}, $terms ) ) );
									?>
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
