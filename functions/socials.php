<?php
/**
 * Social related functions.
 *
 * @package kyom
 */


/**
 * Get hatena bookmark total count.
 *
 * @return int
 */
function kyom_hatena_total_bookmark_count() {
	$cache = get_transient( 'hatena_bookmark_total_count' );
	if ( false === $cache ) {
		require_once ABSPATH . WPINC . '/class-IXR.php';
		$client = new IXR_Client( 'http://b.hatena.ne.jp/xmlrpc' );
		$client->query( 'bookmark.getTotalCount', home_url() );
		$cache = $client->getResponse();
		set_transient( 'hatena_bookmark_total_count', $cache, 60 * 60 * 24 );
	}

	return (int) $cache;
}


/**
 * Get hatena bookmark data via XML RPC
 *
 * @param string $fqdn  Domain like 'exmaple.com'.
 * @param string $sort  'count', 'eid', pr 'hot'
 * @param int    $limit Default 5.
 * @return SimplePie_Item[]
 */
function kyom_get_hatena_rss( $fqdn, $sort = 'count', $limit = 5 ) {
	$endpoint = sprintf( 'http://b.hatena.ne.jp/entrylist?mode=rss&url=%s&sort=%s', $fqdn, $sort );
	$feed = fetch_feed( $endpoint );
	if ( is_wp_error( $feed ) ) {
		return [];
	} else {
		if ( 'count' === $sort ) {
			$rank = [];
			for ( $i = 0, $l = $feed->get_item_quantity(); $i < $l; $i++ ) {
				$item = $feed->get_item( $i );
				$rank[$i] = $item->get_item_tags( 'http://www.hatena.ne.jp/info/xmlns#', 'bookmarkcount' )[0]['data'];
			}
			arsort($rank);
			$items = [];
			foreach ( $rank as $index => $value ) {
				$items[] = $feed->get_item($index);
				if ( $limit <= count($items) ) {
					break;
				}
			}
			return $items;
		} else {
			return $feed->get_items( 0, $limit );
		}
	}
}

/**
 * Grab images from RSS feed.
 *
 * @param SimpleXMLElement $item
 *
 * @return array
 */
function kyom_grab_feed_image( $item ) {
	$images = [];
	foreach ( $item->children( 'media', true )->group->content as $thumbnail ) {
		$data                             = $thumbnail->attributes();
		$attributes                       = $thumbnail->attributes();
		$images[ (string) $data['size'] ] = [
			(string) $data['url'],
			(string) $data['width'],
			(string) $data['height'],
		];
	}

	return $images;
}


/**
 * Get RSS feed for widget
 *
 * @param string $url
 * @return array
 */
function kyom_fetch_feed_items( $url ) {
	$posts = get_transient( 'rss_cache_' . $url );
	if ( WP_DEBUG ) {
		$posts = false;
	}
	if ( false === $posts ) {
		try {
			$posts = [];
			$feed  = wp_remote_get( $url, array(
				'timeout' => 5,
			) );
			if ( is_wp_error( $feed ) ) {
				throw new Exception( $feed->get_error_message() );
			}
			libxml_use_internal_errors( true );
			$xml = simplexml_load_string( $feed['body'], 'SimpleXMLElement', LIBXML_NOCDATA );
			if ( false !== $xml ) {
				foreach ( $xml->channel->item as $item ) {
					$thumbnials = $item->children( 'media', true )->thumbnail->attributes();

					$p = array(
						'title'     => (string) $item->title,
						'excerpt'   => (string) $item->description,
						'url'       => (string) $item->children( 'dc', true )->relation,
						'post_date' => date_i18n( 'Y-m-d H:i:s', strtotime( $item->pubDate ) + 60 * 60 * 9 ),
						'category'  => (string) $item->category,
						'image'     => str_replace( 'http://', 'https://', (string) $thumbnials['url'] ),
					);

					$p['images'] = kyom_grab_feed_image( $item );
					$posts[]     = $p;
				}
				// Save transient.
				set_transient( 'hametuha_kdp', $posts, 60 * 60 * 2 );
			}
		} catch ( Exception $e ) {
			return [];
		}
	}

	return $posts;
}


/**
 * Get Google Analytics result.
 *
 * @param string $start_date
 * @param string $end_date
 * @param string $metrics
 * @param array $params
 *
 * @return array
 */
function kyom_get_ga_result( $start_date, $end_date, $metrics, $params = [] ) {
	try{
		if ( ! class_exists( 'Gianism\\Plugins\\Analytics' ) ) {
			throw new \Exception( __( 'Gianism is not installed.', 'kyom' ), 500 );
		}
		$google = Gianism\Plugins\Analytics::get_instance();
		if( ! $google || ! $google->ga_profile['view'] ) {
			throw new \Exception( __( 'Google Analytics is not connected.', 'kyom' ), 500 );
		}
		$result = $google->ga->data_ga->get('ga:'.$google->ga_profile['view'], $start_date, $end_date, $metrics, $params);
		if ( $result && $result->rows && ( 0 < count($result->rows) ) ) {
			return $result->rows;
		} else {
			return [];
		}
	}  catch ( \Exception $e ) {
		if ( WP_DEBUG ) {
			trigger_error( sprintf( '[GA Error:%s] %s', $e->getCode(), $e->getMessage() ) );
		}
		return [];
	}
}

/**
 * Get ranking.
 *
 * @param string|int $from
 * @param int        $count
 * @param string     $filters
 * @param string     $dimensions
 *
 * @return array
 */
function kyom_get_ranking( $from, $count = 5, $filters = '', $dimensions = 'ga:pagePath' ) {
	// Use cache.
	if ( ! $filters ) {
		$structure = untrailingslashit( get_option( 'permalink_structure' ) );
		foreach ( [
			'/%category%/',
			'%post_id%',
			'%year%',
			'%monthnum%',
			'%day%',
			'%hour%',
			'%minutes%',
			'%second%',
			'%postname%',
			'%author%',
			'%minutes%',
		] as $regexp ) {
			switch ( $regexp ) {
				case '/%category%/':
					$replaced = '/.+/';
					break;
				case '%postname%':
				case '%author%':
					$replaced = '[^/]+';
					break;
				default:
					$replaced = '\\d+';
					break;
			}
			$structure = str_replace( $regexp, $replaced, $structure );
		}
		$filters = sprintf( 'ga:pagePath=~^%s', $structure );
	}
	if ( preg_match( '/\d+/u', $from ) ) {
		$from = get_date_from_gmt( date_i18n( 'Y-m-d H:i:s', strtotime( sprintf( '%d days ago', $from ) ) ), 'Y-m-d' );
	} else {
		$from = kyom_oldest_date( 'Y-m-d' );
	}
	$result = kyom_get_ga_result( $from, date_i18n( 'Y-m-d' ), 'ga:pageviews', [
		'max-results' => $count * 3,
		'dimensions'  => $dimensions,
		'filters'     => $filters,
		'sort'        => '-ga:pageviews',
	] );
	$path_and_pvs = [];
	// Arrange ranking with page path and pvs.
	foreach ( $result as list( $page_path, $pv ) ) {
		// Remove amp trailing
		$page_path = preg_replace( '#amp/$#u', '', $page_path );
		if ( isset( $path_and_pvs[ $page_path ] ) ) {
			$path_and_pvs[ $page_path ] += $pv;
		} else {
			$path_and_pvs[ $page_path ] = (int) $pv;
		}
	}
	arsort( $path_and_pvs );
	// Arrange pvs.
	$ranking = [];
	foreach ( $path_and_pvs as $page_path => $pv ) {
		$permalink = home_url( $page_path );
		if ( ! ( $post_id = url_to_postid( $permalink ) ) ) {
			continue;
		}
		$ranking[] = [
			'pv'   => $pv,
			'post' => $post_id,
		];
	}
	// Fix ranking.
	foreach ( $ranking as $index => $rank ) {
		$more = 0;
		foreach ( $ranking as $r ) {
			if ( $rank['pv'] < $r['pv'] ) {
				$more++;
			}
		}
		$ranking[ $index ]['rank'] = $more + 1;
	}
	$filtered = [];
	foreach ( $ranking as $index => $rank ) {
		if ( $index < $count ) {
			$filtered[] = $rank;
		}
	}
	return $filtered;
}

/**
 * Social key to make icons.
 *
 * @return string[]
 */
function kyom_social_keys() {
	return [
		'facebook',
		'twitter',
		'instagram',
		'github',
		'pinterest',
		'youtube',
		'wordpress',
		'linkedin',
		'dribbble',
		'behance',
		'google',
	];
}

/**
 * Get icon name from URL.
 *
 * @param string $url Base URL.
 * @return string Icon name e.g. facebook.
 */
function kyom_icon_from_url( $url ) {
	$icon = 'link';
	foreach ( kyom_social_keys() as $key ) {
		if ( preg_match( '#https?://(.*\.)?' . $key . '\.(co\.jp|jp|com|org)#u', $url ) ) {
			return  $key;
		}
	}
	return $icon;
}

/**
 * Get YouTube channel information.
 *
 * @return array|WP_Error
 */
function kyom_get_youtube_channel() {
	$caches = get_transient( 'kyom_youtube_channel' );
	if ( false !== $caches ) {
		return $caches;
	}
	$key = get_option( 'kyom_youtube_api_key', '' );
	if ( ! $key ) {
		return new WP_Error( 'no_api_key', __( 'API Key for YouTube Data API is not set.', 'kyom' ) );
	}
	$channel_id = get_option( 'kyom_youtube_channel_id', '' );
	if ( ! $channel_id ) {
		return new WP_Error( 'no_channel_id', __( 'YouTube Channel ID is not set.', 'kyom' ) );
	}
	$url = add_query_arg([
		'part' => 'contentDetails,snippet',
		'id'   => rawurlencode( $channel_id ),
		'key'  => rawurlencode( $key )
	], 'https://www.googleapis.com/youtube/v3/channels' );
	$result = wp_remote_get( $url );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	$json = json_decode( $result['body'], true );
	if ( ! $json ) {
		return new WP_Error( 'api_error', __( 'Failed to get valid API response.', 'kyom' ) );
	}
	$return = $json['items'][0];
	set_transient( 'kyom_youtube_channel', $return, 60 * 60 * 24 );
	return $return;
}

/**
 * Get YouTube playlist.
 *
 * @param bool $wp_error If false, return empty array.
 * @return array|WP_Error
 */
function kyom_get_youtube_playlist( $wp_error = true ) {
	$channel = kyom_get_youtube_channel();
	if ( is_wp_error( $channel ) ) {
		return $wp_error ? $channel : [];
	}
	return $channel['contentDetails']['relatedPlaylists'];
}

/**
 * Get YouTube Play list.
 *
 * @param string $playlist   Playlist ID.
 * @param int    $cache_time Cache life time.
 * @return array|WP_Error
 */
function kyom_get_youtube_videos( $playlist, $cache_time = 3600 ) {
	$cache_key = 'kyom_youtube_videos_in_' . $playlist;
	$cache = get_transient( $cache_key );
	if ( false !== $cache ) {
		return $cache;
	}
	$channel = kyom_get_youtube_channel();
	if ( is_wp_error( $channel ) ) {
		return $channel;
	}
	$endpoint = add_query_arg( [
		'part'       => 'contentDetails,snippet,status',
		'playlistId' => rawurlencode( $playlist ),
		'key'        => rawurlencode( get_option( 'kyom_youtube_api_key' ) )
	], 'https://www.googleapis.com/youtube/v3/playlistItems' );
	$response = wp_remote_get( $endpoint );
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$json = json_decode( $response['body'], true );
	if ( ! $json ) {
		return new WP_Error( 'parse_error', __( 'Failed to get valid response.', 'kyom') );
	}
	$result = $json['items'];
	set_transient( $cache_key, $result, $cache_time );
	return $result;
}

/**
 * Get scheduled live stream.
 *
 * @param int $days Get youtube stream in these days.
 * @return array
 */
function kyom_get_scheduled_youtube_live_stream( $days ) {
	$channel = kyom_get_youtube_channel();
	if ( is_wp_error( $channel ) ) {
		return [];
	}
	$cache = get_transient( 'youtube_live_scheduled' );
	if ( false !== $cache ) {
		return $cache;
	}
	$url = sprintf( 'https://www.youtube.com/channel/%s/live', $channel['id'] );
	$response = wp_remote_get( $url );
	if ( is_wp_error( $response ) ) {
		return [];
	}
	$lives = [];
	$html  = $response['body'];
	if ( false !== strpos( $html, '<link rel="alternate" type="text/xml+oembed"' ) ) {
		// Oembed exists, maybe scheduled live exists.
		preg_match( '#<meta name="title" content="([^"]+)">#u', $html, $matches );
		list( $all, $title ) = $matches;
		preg_match( '#"dateText":{"simpleText":"([^"]+)"}#u', $html, $matches );
		list( $all, $date ) = $matches;
		if ( $title && $date ) {
			$lives[] = [
				'title'    => $title,
				'url'      => $url,
				'schedule' => $date,
			];
		}
	}
	set_transient( 'youtube_live_scheduled', $lives, 3600 * 3 );
	return $lives;
}
