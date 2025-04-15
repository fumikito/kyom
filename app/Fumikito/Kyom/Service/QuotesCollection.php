<?php

namespace Fumikito\Kyom\Service;

use Hametuha\SingletonPattern\Singleton;

/**
 * Quotes collection Wrapper
 *
 * Support Quotes collection.
 *
 * @see https://wordpress.org/plugins/quotes-collection/
 */
class QuotesCollection extends Singleton {

	public $post_type = 'quotes';

	/**
	 * {@inheritdoc}
	 */
	protected function init() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		if ( $this->active() ) {
			// Post type and taxonomy.
			add_action( 'init', [ $this, 'post_types' ] );
			// Meta data
			add_action( 'save_post_' . $this->post_type, [ $this, 'save_post' ], 10, 2 );
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
			// Admin columns.
			add_filter( 'manage_' . $this->post_type . '_posts_columns', [ $this, 'admin_columns' ] );
			add_action( 'manage_' . $this->post_type . '_posts_custom_column', [ $this, 'do_admin_column' ], 10, 2 );
			// Title tag.
			add_filter( 'document_title_parts', [ $this, 'document_title_filter' ] );
			// Template include.
			add_filter( 'template_include', [ $this, 'change_template' ] );
		}
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		add_settings_section( 'kyom-quotes-section', __( 'Quotes Collection Setting', 'kyom' ), function () {
		}, 'writing' );
		add_settings_field( 'kyom-quotes', __( 'Quotes Collection', 'kyom' ), function () {
			?>
			<select name="kyom-quotes">
				<?php
				foreach ( [
					__( 'Disabled', 'kyom' ) => false,
					__( 'Enabled', 'kyom' )  => true,
				] as $label => $selected ) {
					printf(
						'<option value="%s"%s>%s</option>',
						( $selected ? 1 : '' ),
						selected( $selected, $this->active(), false ),
						esc_html( $label )
					);
				}
				?>
			</select>
			<?php
		}, 'writing', 'kyom-quotes-section' );
		register_setting( 'writing', 'kyom-quotes' );
	}

	/**
	 * Is quotes collection active?
	 *
	 * @return bool
	 */
	public function active() {
		return (bool) get_option( 'kyom-quotes', '' );
	}

	/**
	 * Register post type.
	 *
	 * @return void
	 */
	public function post_types() {
		// Register Quotes.
		register_post_type( $this->post_type, [
			'labels'      => [
				'name'          => __( 'Quotes', 'kyom' ),
				'singular_name' => __( 'Quote', 'kyom' ),
			],
			'menu_icon'   => 'dashicons-editor-quote',
			'has_archive' => true,
			'public'      => true,
			'rewrite'     => [
				'slug'       => 'quote',
				'with_front' => false,
			],
			'supports'    => [
				'editor',
				'slug',
			],
		] );
		// Authors.
		register_taxonomy( 'author', [ $this->post_type ], [
			'label'             => __( 'Author', 'kyom' ),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
		] );
	}

	/**
	 * Register meta boxe
	 *
	 * @param string $post_type
	 * @return void
	 */
	public function add_meta_boxes( $post_type ) {
		if ( $this->post_type === $post_type ) {
			add_meta_box( 'quotes-source', __( 'Source', 'kyom' ), [ $this, 'render_meta_box' ], $post_type );
		}
	}

	/**
	 * Save post meta.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function save_post( $post_id, $post ) {
		if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_kyomquotenonce' ), 'update_quote' ) ) {
			return;
		}
		foreach ( [
			'quote_source',
			'quote_url',
		] as $key ) {
			update_post_meta( $post_id, '_' . $key, filter_input( INPUT_POST, $key ) );
		}
	}

	/**
	 * Render meta box.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render_meta_box( \WP_Post $post ) {
		wp_nonce_field( 'update_quote', '_kyomquotenonce', false );
		?>
		<p>
			<label for="quote-source"><?php esc_html_e( 'Source', 'kyom' ); ?></label><br />
			<input id="quote-source" class="regular-text" type="text" name="quote_source"
				placeholder="<?php esc_attr_e( 'Franz Kafka', 'kyom' ); ?>"
				value="<?php echo esc_attr( get_post_meta( $post->ID, '_quote_source', true ) ); ?>" />
		</p>
		<p>
			<label for="quote-url">URL</label><br />
			<input id="quote-url" class="regular-text" type="url" name="quote_url"
				placeholder="https://example.com/quote/good.html"
				value="<?php echo esc_attr( get_post_meta( $post->ID, '_quote_url', true ) ); ?>" />
		</p>
		<?php
	}

	/**
	 * Admin columns
	 *
	 * @param string[] $columns Column names.
	 * @return string[]
	 */
	public function admin_columns( $columns ) {
		$new_columns = [];
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new_columns['text'] = __( 'Quote', 'kyom' );
			} else {
				$new_columns[ $key ] = $label;
			}
		}
		return $new_columns;
	}

	/**
	 * Render Column.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function do_admin_column( $column, $post_id ) {
		switch ( $column ) {
			case 'text':
				echo get_the_content( null, false, $post_id );
				echo '<br />―― ';
				$source_text = get_post_meta( $post_id, '_quote_source', true ) ?: '---';
				$source_url  = get_post_meta( $post_id, '_quote_url', true );
				if ( ! preg_match( '#^https?://.+#u', $source_url ) ) {
					$source_url = '';
				}
				if ( $source_url ) {
					printf(
						'<a href="%s" rel="noopener noreferrer" target="_blank">%s</a>',
						esc_url( $source_url ),
						esc_html( $source_text )
					);
				} else {
					echo esc_html( $source_text );
				}
				break;
			default:
				// Do nothing.
				break;
		}
	}

	/**
	 * Customize document title parts.
	 *
	 * @param string[] $titles Title parts.
	 * @return string[]
	 */
	public function document_title_filter( $titles ) {
		if ( ! is_singular( $this->post_type ) ) {
			return $titles;
		}
		foreach ( $titles as $key => $value ) {
			$authors = get_the_terms( get_queried_object_id(), 'author' );
			$source  = get_post_meta( get_queried_object_id(), '_quote_source', true );
			if ( $source ) {
				$source = sprintf( _x( '“%s”', 'quote-source', 'kyom' ), $source );
			} else {
				$source = __( 'Source Unknown', 'kyom' );
			}
			if ( $authors && ! is_wp_error( $authors ) ) {
				$source .= ' ' . implode( ', ', array_map( function ( $author ) {
					return $author->name;
				}, $authors ) );
			}
			$titles['title'] = $source;
		}
		return $titles;
	}

	/**
	 * Change template.
	 *
	 * @param string $path Template path.
	 *
	 * @return string
	 */
	public function change_template( $path ) {
		if ( is_singular( 'quotes' ) ) {
			$path = get_template_directory() . '/single-jetpack-testimonial.php';
		}

		return $path;
	}
}
