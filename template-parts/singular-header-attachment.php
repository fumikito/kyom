<?php
/**
 * Header template for singular page.
 *
 * @since 0.3.0
 */

?>
<header class="entry-header no-thumbnail">

	<div class="entry-header-box uk-container">
		<h1 class="entry-header-title">
			<span>
				<?php
				esc_html_e( 'Attached File: ', 'kyom' );
				single_post_title();
				?>
			</span>
		</h1>
		<?php get_template_part( 'template-parts/content-meta', get_post_type() ); ?>
	</div>

</header>
