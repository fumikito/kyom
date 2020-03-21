<?php
get_header();
the_post();
?>

	<main class="main">
		<article <?php post_class( 'entry' ) ?>>

			<?php do_action( 'kyom_before_article' ); ?>

			<header class="entry-header <?= has_post_thumbnail() ? 'with-thumbnail' : 'no-thumbnail' ?>">

				<div class="entry-header-box uk-container">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="entry-header-eyecatch entry-header-thumbnail">
							<?php the_post_thumbnail( 'big-block' ); ?>
						</div>
					<?php else :
						wp_enqueue_script( 'kyom-particle' );
						?>
						<div id="particles-js" class="entry-header-particles entry-header-eyecatch">
						</div>
					<?php endif; ?>
					<?php if ( $term = kyom_get_top_category() ) : ?>
						<a class="entry-top-term" href="<?php echo get_term_link( $term ) ?>" rel="tag">
							<?php echo esc_html( $term->name ) ?>
						</a>
					<?php endif; ?>
					<h1 class="entry-header-title">
						<span>
							<?php single_post_title(); ?>
						</span>
					</h1>
					<?php if ( 'post' === get_post_type() ) : ?>
					<p class="entry-header-author">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 36, '', get_the_author(), [ 'class' => 'entry-header-avatar', ] ) ?>
						<span class="entry-header-author-name stroke"><?php the_author() ?></span>
					</p>
					<?php endif; ?>
					<?php get_template_part( 'template-parts/content', 'meta' ) ?>
				</div>

			</header>

			<div class="entry-breadcrumb">
				<?php kyom_breadcrumb() ?>
			</div>

			<?php if ( has_excerpt() ) : ?>
				<div class="entry-excerpt uk-container entry-container">
					<?php the_excerpt(); ?>
				</div>
			<?php endif; ?>

			<div class="entry-content uk-clearfix uk-container entry-container">

				<?php do_action( 'kyom_before_content' ); ?>

				<?php if ( is_singular( 'post' ) && kyom_is_expired_post() ) : ?>
					<div class="uk-alert-warning uk-alert-padding uk-text-center" uk-alert>
						<a class="uk-alert-close" uk-close></a>
						<p>
							<?= esc_html( sprintf( __( 'This post is published %s ago. Please consider the content may contain invalid information.', 'kyom' ), kyom_get_outdated_string() ) ); ?>
						</p>
					</div>
				<?php endif; ?>

				<?php the_content(); ?>

				<?php wp_link_pages( [
					'before'      => '<ul class="uk-pagination">',
					'after'       => '</ul>',
					'link_before' => '<li>',
					'link_after'  => '</li>',
				] ) ?>

				<?php do_action( 'kyom_after_content' ); ?>

			</div>

			<?php get_template_part( 'template-parts/content-footer', get_post_type() ) ?>

			<footer class="entry-footer entry-container uk-container">

				<div class="entry-footer-divider">
					<span uk-icon="icon:chevron-down; ratio: 3 "></span>
				</div>

				<?php
				get_template_part( 'template-parts/footer', get_post_type() );
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
