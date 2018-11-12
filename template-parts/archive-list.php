<div class="uk-container archive-container">
	<div class="uk-child-width-1-2@s uk-child-width-1-3@m" uk-grid="masonry: true">
		<?php
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/loop', get_post_type() );
		}
		?>
	</div>
</div>
