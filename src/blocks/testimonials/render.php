<?php
/**
 * Testimonials block rendering
 *
 * @package kyom
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

// jetpack-testimonialが存在しない場合は表示しない
if ( ! post_type_exists( 'jetpack-testimonial' ) ) {
	return '';
}

$number = $attributes['number'] ?? -1;
$order  = $attributes['order'] ?? 'random';

$args = [
	'post_type'      => 'jetpack-testimonial',
	'post_status'    => 'publish',
	'posts_per_page' => $number,
];

switch ( $order ) {
	case 'random':
		$args['orderby'] = 'rand';
		break;
	case 'post_order':
		$args['orderby'] = [ 'menu_order' => 'DESC' ];
		break;
	default:
		$args['orderby'] = [ 'date' => 'DESC' ];
		break;
}

$posts = get_posts( $args );

if ( ! $posts ) {
	return '';
}

global $post;
?>
<section class="testimonial-slider">
	<div uk-slider="center: true; autoplay:true">
		<div class="uk-position-relative uk-visible-toggle uk-light">
			<ul class="uk-slider-items uk-grid">
				<?php
				foreach ( $posts as $post ) :
					setup_postdata( $post );
					?>
					<li class="uk-width-3-4 uk-width-2-3@m">
						<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="testimonial-slider-item">
						<div class="uk-card uk-card-default">
							<div class="uk-card-body">
								<h3 class="uk-card-title">
									<?php if ( has_post_thumbnail( $post ) ) : ?>
										<?php echo get_the_post_thumbnail( $post, 'face-rectangle', [ 'class' => 'testimonial-slider-face' ] ); ?>
									<?php endif; ?>
									<?php echo esc_html( get_the_title( $post ) ); ?>
								</h3>
								<?php the_excerpt(); ?>
							</div>
						</div>
						</a>
					</li>
					<?php
				endforeach;
				wp_reset_postdata();
				?>
			</ul>
			<a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#" uk-slidenav-previous
				uk-slider-item="previous"></a>
			<a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#" uk-slidenav-next
				uk-slider-item="next"></a>
		</div>
		<ul class="uk-slider-nav uk-dotnav uk-flex-center uk-margin"></ul>
	</div>
</section>
