<?php

namespace Fumikito\Kyom\Service;


use Hametuha\SingletonPattern\Singleton;

/**
 * JSON-LD provide.
 *
 * Provide OGP data.
 *
 * @package kyom
 */
class JsonLdProvider extends  Singleton {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_head', [ $this, 'do_json_ld' ] );
	}

	public function author_ogp() {

	}

	/**
	 * Get JSON-LD.
	 *
	 * @return array
	 */
	public function get_json_ld() {
		return [];
	}

	/**
	 * Render JSON-LD in WP head.
	 *
	 * @return void
	 */
	public function do_json_ld() {
		$jsons = $this->get_json_ld();
		if ( empty( $jsons ) ) {
			return;
		}
		foreach ( $jsons as $json ) {
			if ( ! empty( $value ) ) {
				printf( "<script type=\"application/ld+json\">\n%s</script>\n", wp_json_encode( $json ) );
			}
		}
	}
}
