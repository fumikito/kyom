<?php
/**
 * Template Name: Full Width
 */
get_header();
the_post();
?>

<main class="full-width">
	<?php the_content(); ?>
</main>

<?php get_footer();
