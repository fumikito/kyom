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
	 * @param string $user_name
	 * @return array
	 */
	public static function get_profile( $user_name ) {
		$url  = self::get_profile_url( $user_name );
		$data = get_transient( $url );
		if ( false === $data ) {
			$response = wp_remote_get( $url );
			if ( is_wp_error( $response ) ) {
				return [];
			}
			$data['url']       = $url;
			$data['downloads'] = 0;
			$html5             = new HTML5();
			$document          = $html5->loadHTML( $response['body'] );
			// Get since.
			$member_since = '';
			foreach ( $document->getElementById( 'user-member-since' )->getElementsByTagName( 'strong' ) as $strong ) {
				/** @var \DOMElement $strong */
				$member_since .= $strong->nodeValue;
			}
			if ( $member_since ) {
				$data['member_since'] = strtotime( $member_since );
			}
			// Get badges.
			$badges = [];
			foreach ( $document->getElementById( 'user-badges' )->getElementsByTagName( 'li' ) as $li ) {
				$badge = [];
				foreach ( $li->getElementsByTagName( 'div' ) as $div ) {
					$badge['label'] = trim( $div->nextSibling->nodeValue );
					$badge['class'] = explode( ' ', $div->getAttribute( 'class' ) );
					if ( $badge['label'] && $badge['class'] ) {
						$badges[] = $badge;
					}
				}
			}
			$data['badges'] = $badges;
			// Get plugins and themes
			$data['downloads'] = 0;
			foreach ( [
				'theme',
				'plugin',
			] as $extension ) {
				$id              = "content-{$extension}s";
				$plugins         = [];
				$plugin_download = 0;
				$container       = $document->getElementById( $id );
				if ( $container ) {
					foreach ( $container->getElementsByTagName( 'li' ) as $li ) {
						$plugin  = [];
						$counter = 0;
						foreach ( $li->getElementsByTagName( 'a' ) as $link ) {
							if ( ( ! $counter && 'theme' === $extension ) || ( $counter && 'plugin' === $extension ) ) {
								// Text Link.
								$plugin['name']   = trim( $link->nodeValue );
								$plugin['url']    = trim( $link->getAttribute( 'href' ) );
								$plugin['rating'] = 0;
								foreach ( $link->parentNode->getElementsByTagName( 'div' ) as $div ) {
									$rating = trim( $div->getAttribute( 'title' ) );
									if ( $rating ) {
										if ( preg_match_all( '#\d#u', $rating, $match ) ) {
											$plugin['rating'] = $match[0][0] / $match[0][1];
										}
									}
								}
								if ( 'theme' === $extension ) {
									// Get screenshot
									foreach ( $link->getElementsByTagName( 'img' ) as $image ) {
										$plugin['screen_shot'] = $image->getAttribute( 'src' );
									}
								}
							} elseif ( 'plugins' === $extension ) {
								if ( preg_match_all( '#url\(\'([^\']+)(\d{3}x\d{3})([^\']+)\'\)#u', $link->nodeValue, $matches, PREG_SET_ORDER ) ) {
									foreach ( $matches as $match ) {
										$label            = '256x256' === $match[2] ? 'icon_large' : 'icon_small';
										$plugin[ $label ] = $match[1] . $match[2] . $match[3];
									}
								}
							}
							++$counter;
						}
						// Get install counts.
						foreach ( $li->getElementsByTagName( 'p' ) as $p ) {
							$plugin['downloads'] = (int) preg_replace( '#\D#u', '', trim( $p->nodeValue ) );
							$plugin_download    += $plugin['downloads'];
						}
						$plugins[] = $plugin;
					}
				}
				$data[ $extension . 's' ]          = $plugins;
				$data[ $extension . '_downloads' ] = $plugin_download;
				$data['downloads']                += $plugin_download;
			}
			// Get themes.
			$data['last_updated'] = current_time( 'mysql' );
			set_transient( $url, $data, 60 * 60 );
		}
		return $data;
	}

	/**
	 * Get item
	 *
	 * @param array $data
	 * @return string
	 */
	public static function delivering_item_count( $data, $before, $after ) {
		$label = [];
		foreach ( [ 'theme', 'plugin' ] as $ext ) {
			$downloads = count( $data[ $ext . 's' ] );
			if ( ! $downloads ) {
				continue;
			}
			switch ( $ext ) {
				case 'theme':
					$i18n = _n( '%s theme', '%s themes', $downloads, 'kyom' );
					break;
				case 'plugin':
					$i18n = _n( '%s plugin', '%s plugins', $downloads, 'kyom' );
					break;
			}
			$label[] = sprintf( $i18n, number_format( $downloads ) );
		}
		return $label ? $before . implode( _x( ', ', 'csv-glue', 'kyom' ), $label ) . $after : '';
	}

	/**
	 * Translate role.
	 *
	 * @param $role
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
	}
}
