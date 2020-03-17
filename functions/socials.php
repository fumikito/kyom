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
		if ( $result && ( 0 < count($result->rows) ) ) {
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
