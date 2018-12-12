<?php
get_header();
the_post();
?>

	<main class="main">
		<article <?php post_class( 'entry' ) ?>>
			
			<?php do_action( 'kyom_before_article' ); ?>
			
			<header class="entry-header <?= has_post_thumbnail() ? '' : 'no-thumbnail' ?>">
				<?php if ( has_post_thumbnail() ) : ?>
				<div class="entry-header-cover" style="<?= kyom_thumbnail_bg() ?>" uk-parallax="bgy:-300"></div>
				<?php endif; ?>
				<div class="uk-container entry-breadcrumb">
					<?php kyom_breadcrumb() ?>
				</div>
				<div class="entry-header-box uk-container">
					<h1 class="entry-title"><?php single_post_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
					<div class="entry-header-excerpt">
						<?= wp_kses_post( wpautop( get_the_excerpt() ) ) ?>
					</div>
					<?php endif; ?>
					<?php get_template_part( 'template-parts/content', 'meta' ) ?>
				</div>
			</header>

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
			
			<?php get_sidebar() ?>
			
			<footer class="entry-footer entry-container uk-container">
				
				<div class="entry-footer-divider">
					<span uk-icon="icon:chevron-down; ratio: 3 "></span>
				</div>
				
				<?php if ( 'post' === get_post_type() ) : ?>
					<?php get_template_part( 'template-parts/block-author' ); ?>
				<?php endif; ?>
				<?php do_action( 'kyom_content_footer' ); ?>
				
				<?php comments_template() ?>
				
				<?php get_template_part( 'template-parts/pager', get_post_type() ); ?>
				
			</footer>
			
			<?php do_action( 'kyom_after_article' ); ?>
			
			<?php get_template_part( 'template-parts/content-back-top', get_post_type() ); ?>
			
		</article>

	</main>
<?php
get_sidebar();
get_footer();
