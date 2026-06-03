<?php
namespace Fumikito\Kyom\Service;


use Masterminds\HTML5;

/**
 * WordPress.org helper
 *
 * @package kyom
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */
class WordPressOrg {

	/**
	 * WordPressOrg constructor.
	 */
	private function __construct() {}

	/**
	 * Get profile page URL.
	 *
	 * @param string $user_name
	 * @return string
	 */
	public static function get_profile_url( $user_name ) {
		return sprintf( 'https://profiles.wordpress.org/%s', strtolower( rawurlencode( $user_name ) ) );
	}

	/**
	 * Get WordPress.org profile.
	 *
	 * wp.org のプロフィールページ DOM は不定期に変わるため、変わりにくい箇所
	 * （バッジ・リリース・プラグインslug一覧）だけをスクレイピングし、数値は
	 * 公式 API から取得して堅牢化している。
	 *
	 * @param string $user_name
	 * @return array
	 */
	public static function get_profile( $user_name ) {
		$url = self::get_profile_url( $user_name );
		// DOM 構造が変わったのでキャッシュキーを v2 にして旧キャッシュを無効化する。
		$cache_key = 'kyom_wporg_v2_' . md5( $url );
		$data      = get_transient( $cache_key );
		if ( false === $data ) {
			$response = wp_remote_get( $url );
			if ( is_wp_error( $response ) ) {
				return [];
			}
			$body = wp_remote_retrieve_body( $response );
			if ( ! $body ) {
				return [];
			}
			$data = [
				'url'                   => $url,
				'member_since'          => 0,
				'badges'                => [],
				'releases'              => [],
				'active_installs_total' => 0,
				'plugin_count'          => 0,
				'last_updated'          => current_time( 'mysql' ),
			];

			$html5 = new HTML5();
			// disable_html_ns を付けないと要素が XHTML 名前空間に入り、無修飾 XPath が一切マッチしない。
			$document = $html5->loadHTML( $body, [ 'disable_html_ns' => true ] );
			$xpath    = new \DOMXPath( $document );

			// Member since（DOM 健在）.
			$since_el = $document->getElementById( 'user-member-since' );
			if ( $since_el ) {
				$member_since = '';
				foreach ( $since_el->getElementsByTagName( 'strong' ) as $strong ) {
					/** @var \DOMElement $strong */
					$member_since .= $strong->nodeValue;
				}
				if ( $member_since ) {
					$data['member_since'] = strtotime( $member_since );
				}
			}

			// Badges, releases.
			$data['badges']   = self::parse_badges( $xpath );
			$data['releases'] = self::parse_releases( $xpath );

			// Plugins: slug 一覧だけスクレイプし、稼働インストール数は公式 API から正確に取得する。
			$slugs                = self::parse_plugin_slugs( $xpath );
			$data['plugin_count'] = count( $slugs );
			$total                = 0;
			foreach ( $slugs as $slug ) {
				$total += self::get_plugin_active_installs( $slug );
			}
			$data['active_installs_total'] = $total;

			// TODO: 初回キャッシュミス時に slug 数ぶんの API リクエストが直列で走る。
			// アクセス頻度が上がるようなら wp-cron での事前更新に切り出すこと。
			set_transient( $cache_key, $data, 12 * HOUR_IN_SECONDS );
		}
		return $data;
	}

	/**
	 * Parse badges from the new profile DOM.
	 *
	 * 新DOM: `.wp-p2-badges-block` 内のカテゴリ別 `span.medal.badge-XXX`。
	 *
	 * @param \DOMXPath $xpath
	 * @return array
	 */
	private static function parse_badges( \DOMXPath $xpath ) {
		$badges = [];
		$medals = $xpath->query( "//span[contains(concat(' ', normalize-space(@class), ' '), ' medal ')]" );
		if ( ! $medals ) {
			return $badges;
		}
		foreach ( $medals as $medal ) {
			/** @var \DOMElement $medal */
			$label = self::query_text( $xpath, ".//*[contains(concat(' ', normalize-space(@class), ' '), ' mn ')]", $medal );
			if ( ! $label ) {
				continue;
			}
			$icon_nodes = $xpath->query( ".//*[contains(concat(' ', normalize-space(@class), ' '), ' mi ')]", $medal );
			$icon_class = ( $icon_nodes && $icon_nodes->length ) ? $icon_nodes->item( 0 )->getAttribute( 'class' ) : '';
			$badges[]   = [
				'label'       => $label,
				'year'        => self::query_text( $xpath, ".//*[contains(concat(' ', normalize-space(@class), ' '), ' myear ')]", $medal ),
				'icon_class'  => $icon_class,
				'badge_class' => $medal->getAttribute( 'class' ),
				'title'       => $medal->getAttribute( 'title' ),
			];
		}
		return $badges;
	}

	/**
	 * Parse "WordPress releases" section.
	 *
	 * @param \DOMXPath $xpath
	 * @return array{count:int, versions:array}|array
	 */
	private static function parse_releases( \DOMXPath $xpath ) {
		$sections = $xpath->query( "//section[contains(concat(' ', normalize-space(@class), ' '), ' wp-p2-specs ')][normalize-space(h3)='WordPress releases']" );
		if ( ! $sections || ! $sections->length ) {
			return [];
		}
		$section = $sections->item( 0 );
		$count   = 0;
		$sub     = self::query_text( $xpath, ".//*[contains(concat(' ', normalize-space(@class), ' '), ' sub ')]", $section );
		if ( $sub && preg_match( '/(\d+)/u', $sub, $m ) ) {
			$count = (int) $m[1];
		}
		$versions = [];
		$chips    = $xpath->query( ".//li[contains(concat(' ', normalize-space(@class), ' '), ' ver-chip ')]", $section );
		if ( $chips ) {
			foreach ( $chips as $chip ) {
				/** @var \DOMElement $chip */
				$number = self::query_text( $xpath, ".//*[contains(concat(' ', normalize-space(@class), ' '), ' ver-num ')]", $chip );
				if ( ! $number ) {
					continue;
				}
				$versions[] = [
					'number' => $number,
					'role'   => $chip->getAttribute( 'title' ),
				];
			}
		}
		return [
			'count'    => $count,
			'versions' => $versions,
		];
	}

	/**
	 * Parse plugin slugs from `#content-plugins`.
	 *
	 * @param \DOMXPath $xpath
	 * @return string[]
	 */
	private static function parse_plugin_slugs( \DOMXPath $xpath ) {
		$slugs = [];
		$links = $xpath->query( "//div[@id='content-plugins']//a[contains(@href, '/plugins/')]" );
		if ( ! $links ) {
			return [];
		}
		foreach ( $links as $link ) {
			/** @var \DOMElement $link */
			if ( preg_match( '#/plugins/([^/]+)/#u', $link->getAttribute( 'href' ), $m ) ) {
				$slugs[ $m[1] ] = $m[1]; // キーで重複排除.
			}
		}
		return array_values( $slugs );
	}

	/**
	 * Get accurate active installs for a plugin via the official API.
	 *
	 * @param string $slug
	 * @return int
	 */
	private static function get_plugin_active_installs( $slug ) {
		$api      = 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=' . rawurlencode( $slug );
		$response = wp_remote_get( $api );
		if ( is_wp_error( $response ) ) {
			return 0;
		}
		$json = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $json ) || empty( $json['active_installs'] ) ) {
			return 0;
		}
		return (int) $json['active_installs'];
	}

	/**
	 * Get trimmed text of the first node matching an XPath expression.
	 *
	 * @param \DOMXPath    $xpath
	 * @param string       $expr
	 * @param \DOMNode|null $context
	 * @return string
	 */
	private static function query_text( \DOMXPath $xpath, $expr, $context = null ) {
		$nodes = $xpath->query( $expr, $context );
		if ( $nodes && $nodes->length ) {
			return trim( $nodes->item( 0 )->nodeValue );
		}
		return '';
	}

	/**
	 * Get delivering item count label (plugins).
	 *
	 * @param array  $data
	 * @param string $before
	 * @param string $after
	 * @return string
	 */
	public static function delivering_item_count( $data, $before, $after ) {
		$count = $data['plugin_count'] ?? 0;
		if ( ! $count ) {
			return '';
		}
		$label = sprintf(
			/* translators: %s: Number of plugins. */
			_n( '%s plugin', '%s plugins', $count, 'kyom' ),
			number_format( $count )
		);
		return $before . $label . $after;
	}

	/**
	 * Translate role.
	 *
	 * @param string $role
	 *
	 * @return string
	 */
	public static function translate_role( $role ) {
		return implode( ' ', array_map( function ( $string ) {
			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
			return _x( $string, 'WordPress', 'kyom' );
		}, explode( ' ', $role ) ) );
	}

	/**
	 * For get text scraping.
	 *
	 * @internal
	 * @see $this->translate_role
	 */
	private function translate_role_segment() {
		_x( 'Developer', 'WordPress', 'kyom' );
		_x( 'Speaker', 'WordPress', 'kyom' );
		_x( 'Plugin', 'WordPress', 'kyom' );
		_x( 'Theme', 'WordPress', 'kyom' );
		_x( 'Organizer', 'WordPress', 'kyom' );
		_x( 'Editor', 'WordPress', 'kyom' );
		_x( 'Contributor', 'WordPress', 'kyom' );
		_x( 'Core', 'WordPress', 'kyom' );
		_x( 'Meta', 'WordPress', 'kyom' );
		_x( 'Support', 'WordPress', 'kyom' );
		_x( 'Translation', 'WordPress', 'kyom' );
		_x( 'Team', 'WordPress', 'kyom' );
		_x( 'Commiter', 'WordPress', 'kyom' );
		_x( 'WordCamp', 'WordPress', 'kyom' );
		_x( 'Meetup', 'WordPress', 'kyom' );
	}
}
