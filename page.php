<?php
get_header();
the_post();
?>
	
	<main class="main">
		<article <?php post_class( 'entry' ) ?>>
			
			<?php do_action( 'kyom_before_article' ); ?>
			
			<header class="entry-header no-thumbnail">
				<div class="uk-container entry-breadcrumb">
					<?php kyom_breadcrumb() ?>
				</div>
				<div class="entry-header-box uk-container">
					<h1 class="entry-title"><?php single_post_title(); ?></h1>
				</div>
			</header>
			
			<div class="entry-content uk-clearfix uk-container entry-container">
				
				<?php do_action( 'kyom_before_content' ); ?>
				
				<?php the_content(); ?>
				
				<?php wp_link_pages( [
					'before'      => '<ul class="uk-pagination">',
					'after'       => '</ul>',
					'link_before' => '<li>',
					'link_after'  => '</li>',
				] ) ?>
				
				<?php do_action( 'kyom_after_content' ); ?>
			
			</div>
			
			<footer class="entry-footer entry-container uk-container">
				
				<div class="entry-footer-divider">
					<span uk-icon="icon:chevron-down; ratio: 3 "></span>
				</div>
				
				<?php if ( $related_pages = kyom_get_related_pages() ) : ?>
					<h2 class="uk-heading-line uk-text-center">
						<span><?php esc_html_e( 'Related Pages', 'kyom' ) ?></span>
					</h2>
					<div class="uk-container archive-container">
						<div class="uk-child-width-1-2@s uk-child-width-1-3@m" uk-grid="masonry: true">
							<?php
							foreach ( $related_pages as $post ) {
								setup_postdata( $post );
								get_template_part( 'template-parts/loop', get_post_type() );
							}
							wp_reset_postdata();
							?>
						</div>
					</div>
				<?php endif; ?>
				
				<?php do_action( 'kyom_content_footer' ); ?>
				
			</footer>
			
			<?php do_action( 'kyom_after_article' ); ?>
			
			<?php if ( $link = kyom_archive_top() ) : ?>
				<div class="kyom-archive-back entry-container uk-text-center" uk-margin>
					<a class="uk-button uk-button-default uk-button-large" href="<?= esc_url( $link ) ?>">
						<?php esc_html_e( 'See All Posts' ) ?>
					</a>
				</div>
			<?php endif; ?>
		
		</article>
	
	</main>
<?php
get_sidebar();
get_footer();
