<header class="archive-header">

	<?php if ( ( is_category() || is_tag() || is_tax() ) && ( $url = kyom_term_image( get_queried_object(), 'full' ) ) ) : ?>
	<div class="archive-header-cover" style="background-image: url( '<?= esc_url( $url ) ?>' )"  uk-parallax="bgy:-300">
	</div>
	<?php endif; ?>

	<div class="uk-container">
		<h1 class="uk-heading-line uk-text-center archive-header-title"><span><?= wp_kses_post( kyom_archive_title() ) ?></span></h1>

		<?php if ( $description = category_description() ) : ?>
		<div class="archive-header-desc">
			<?= wp_kses_post( wpautop( $description ) ) ?>
		</div>
		<?php endif; ?>
		
		<div class="archive-header-search">
			<?php
			global $wp_query;
			$found = (int) $wp_query->found_posts;
			echo wp_kses_post( sprintf(
				__( '%1$s found and now %2$d page of %3$s. Try detailed search.', 'kyom' ),
				sprintf( _n( '1 post', '%s posts', $found, 'kyom' ), number_format( $found ) ),
				max( 1, $wp_query->paged ),
				sprintf( _n( '1 page', '%s pages', $wp_query->max_num_pages, 'kyom' ), number_format( $wp_query->max_num_pages ) )
			) );
			?>
			<button class="uk-button uk-button-text uk-button-small" uk-toggle="target:#header-search"><span uk-icon="search"></span></button>
		</div>
		
		<form id="header-search" class="archive-header-form uk-form-horizontal" hidden aria-hidden="true" method="get" action="<?= home_url() ?>">
			<p class="uk-text-right">
				<button type="button" uk-close uk-toggle="target:#header-search"></button>
			</p>
			<fieldset>


				<?php do_action( 'kyom_before_search_form' ); ?>
				
				<legend><?php esc_html_e( 'Detailed Search', 'kyom' ) ?></legend>

				<div class="uk-margin">
					<label class="uk-form-label" for="search-keyword"><?php esc_html_e( 'Keywords', 'kyom' ) ?></label>
					<div class="uk-form-controls">
						<input name="s" class="uk-input" id="search-keyword" type="search" placeholder="" value="<?php the_search_query() ?>" />
					</div>
				</div>

				<?php
				foreach ( kyom_searchable_taxonomies() as $taxonomy => $name ) :
					$taxonomy_object = get_taxonomy( $taxonomy );
					$terms = get_terms( [ 'taxonomy' => $taxonomy ] );
					if ( ! $terms || is_wp_error( $terms ) ) {
						continue;
					}
					?>
				<div class="uk-margin">
					<div class="uk-form-label"><?= esc_html( $taxonomy_object->label ) ?></div>
					<div class="uk-form-controls uk-form-controls-text">
						<?php foreach ( $terms as $term ) :
							$value = $term->term_id;
							switch ( $term->taxonomy ) {
								case 'category':
									$selected = is_category( $term->term_id );
									break;
								case 'post_tag':
									$selected = is_tag( $term->term_id );
									$value = $term->slug;
									break;
								default:
									$selected = is_tax( $term->taxonomy, $term->term_id );
									break;
							}
							?>
						<label class="uk-form-label-inline">
							<input class="uk-radio" type="radio" name="<?= esc_attr( $name ) ?>" value="<?= esc_attr( $value ) ?>" <?php checked( $selected ) ?>>
							<?= esc_html( $term->name ) ?>
						</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endforeach; ?>

				<div class="uk-margin">
					<label class="uk-form-label" for="search-order"><?php esc_html_e( 'Order', 'kyom' ) ?></label>
					<div class="uk-form-controls">
						<select class="uk-select" id="search-order" name="order">
							<?php $order = get_query_var( 'order' ); ?>
							<option value="desc" <?php selected( ! $order || 'decs' == $order ) ?>><?php esc_html_e( 'Newest to oldest', 'kyom' ) ?></option>
							<option value="asc" <?php selected( $order, 'asc' ) ?>><?php esc_html_e( 'Oldest to newest', 'kyom' ) ?></option>
						</select>
					</div>
				</div>
				
				<?php do_action( 'kyom_after_search_form' ); ?>
				
				<p class="uk-margin uk-text-center">
					<button class="uk-button uk-button-default uk-button-large" type="reset"><?php esc_html_e( 'Reset', 'kyom' ) ?></button>
					<button class="uk-button uk-button-primary uk-button-large" type="submit"><?php esc_html_e( 'Search' ) ?></button>
				</p>
				
			</fieldset>
		</form>
		
		
	</div>

</header>
