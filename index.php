<?php
/**
 * Archive template
 */
get_header();

?>
	<div class="entry-breadcrumb">
		<?php kyom_breadcrumb() ?>
	</div>
<?php


if ( have_posts() ) {
	get_template_part( 'template-parts/archive-header', kyom_archive_slug() );
	get_template_part( 'template-parts/archive-list' );
	get_template_part( 'template-parts/pagination' );
} else {
	get_template_part( 'template-parts/archive-no' );
}



get_footer();
