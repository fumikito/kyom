<?php
get_header();
the_post();
?>

	<main class="main">
		<article <?php post_class( 'entry' ) ?>>

			<?php do_action( 'kyom_before_article' ); ?>

			<?php get_template_part( 'template-parts/singular-header', get_post_type() ); ?>

			<div class="entry-breadcrumb">
				<?php kyom_breadcrumb() ?>
			</div>

			<?php if ( has_excerpt() && ! is_attachment() ) : ?>
				<div class="entry-excerpt uk-container entry-container">
					<?php echo wp_kses_post( wpautop( get_the_excerpt() ) ); ?>
				</div>
			<?php endif; ?>

			<div class="entry-content uk-clearfix uk-container entry-container">

				<?php
					do_action( 'kyom_before_content' );
					get_template_part( 'template-parts/singular-main', get_post_type() );
					do_action( 'kyom_after_content' );
				?>

			</div>

			<?php get_template_part( 'template-parts/content-footer', get_post_type() ) ?>

			<footer class="entry-footer entry-container uk-container">

				<div class="entry-footer-divider">
					<span uk-icon="icon:chevron-down; ratio: 3 "></span>
				</div>

				<?php
				get_template_part( 'template-parts/singular-footer', get_post_type() );
				do_action( 'kyom_content_footer' );
				if ( ! is_page() ) {
					get_template_part( 'template-parts/pager', get_post_type() );
					comments_template();
				}
				?>

			</footer>

			<?php get_sidebar() ?>

			<?php do_action( 'kyom_after_article' ); ?>

			<?php get_template_part( 'template-parts/content-back-top', get_post_type() ); ?>

		</article>

	</main>
<?php
get_sidebar();
get_footer();
