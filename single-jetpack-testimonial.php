<?php
get_header();
the_post();
?>

	<main class="main">
		<article <?php post_class( 'entry' ) ?>>
			
			<div class="uk-container entry-breadcrumb">
				<?php kyom_breadcrumb() ?>
			</div>
			
			<?php do_action( 'kyom_before_article' ); ?>

			<div class="entry-content uk-clearfix uk-container entry-container">
				<?php if ( 'quote' !== get_post_type() ) : ?>
				<h1 class="pull-quote-title">
					<small><?php esc_html_e( 'Testimonial By', 'kyom' ) ?></small>
					<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'face-rectangle' ) ?>
					<?php endif; ?>
					<span class="pull-quote-title-text"><?php the_title() ?></span>
					<?php if ( $position = get_post_meta( get_the_ID(), '_source_position', true ) ) : ?>
						<small><?= esc_html( $position ) ?></small>
					<?php endif; ?>
				</h1>
				<?php endif; ?>
				<blockquote class="pull-quote">
					<span class="pull-quote-icon-left" uk-icon="icon: quote-right; ratio: 3"></span>
					<span class="pull-quote-icon-right" uk-icon="icon: quote-right; ratio: 3"></span>
					
					<?= wp_kses_post( wpautop( $post->post_content ) ); ?>
					<?php if ( $source = kyom_testimonial_source() ) : ?>
						<cite class="pull-quote-source">
						<?= $source ?>
						</cite>
					<?php endif; ?>
				</blockquote>
			</div>
			<footer class="entry-footer entry-container uk-container">

				<div class="entry-footer-divider">
					<span uk-icon="icon:chevron-down; ratio: 3 "></span>
				</div>
				
				<?php do_action( 'kyom_content_footer' ); ?>
				
			</footer>
			
			<?php do_action( 'kyom_after_article' ); ?>
			
			<?php get_template_part( 'template-parts/content-back-top', get_post_type() ); ?>
		</article>
	</main>
<?php
get_sidebar();
get_footer();