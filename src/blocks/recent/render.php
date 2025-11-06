<?php
/**
 * Recent Posts block rendering
 *
 * @package kyom
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$title    = $attributes['title'] ?? __( 'Recent Posts', 'kyom' );
$category = $attributes['category'] ?? '';

$args = [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'cat'            => $category,
	'posts_per_page' => 5,
];

$query = new WP_Query( $args );

if ( ! $query->have_posts() ) {
	return sprintf( '<div class="uk-alert uk-alert-muted">%s</div>', esc_html__( 'No post found.', 'kyom' ) );
}
?>
<div class="recent-wide">
	<h2 class="recent-wide-title uk-text-center">
		<?php echo esc_html( $title ); ?>
	</h2>
	<div class="recent-wide-list uk-container">
		<div class="recent-wide-list-column">
			<?php $query->the_post(); ?>
			<div class="recent-wide-item-large">
				<?php get_template_part( 'template-parts/loop', 'recent-card' ); ?>
			</div>
		</div>
		<div class="recent-wide-list-column">
			<div class="recent-wide-grid">
			<?php
			$counter = 0;
			if ( $query->have_posts() ) :
				?>
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					++$counter;
					?>
				<div class="recent-wide-item">
					<?php get_template_part( 'template-parts/loop', 'recent-card' ); ?>
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
	<?php
	$page = get_post( get_option( 'page_for_posts' ) );
	if ( $page ) :
		?>
		<div class="recent-wide-link uk-text-center uk-margin">
			<a href="<?php echo esc_url( get_permalink( $page ) ); ?>" class="uk-button uk-button-primary uk-button-large"><?php echo esc_html( get_the_title( $page ) ); ?></a>
		</div>
	<?php endif; ?>
</div>
<?php
wp_reset_postdata();
