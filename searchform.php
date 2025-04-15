<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div class="uk-inline uk-width-1-1">
		<button class="uk-form-icon uk-form-icon-flip" href="" uk-icon="icon: search" type="submit"></button>
		<input class="uk-input" type="search" name="s" placeholder="<?php esc_attr_e( 'Input keywords and enter to search.', 'kyom' ); ?>"
				value="<?php echo get_search_query(); ?>"/>
	</div>
</form>
