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
 * @param string $sort 'count' 'eid', または 'hot'のいずれか
 * @return array
 */
function kyom_get_hatena_rss( $sort = 'count' ) {
	$hatena_transient_name = 'hatena_hotentry_' . $sort;
	
	$endpoint = sprintf(
		'http://b.hatena.ne.jp/entrylist?mode=rss&url=%s&sort=%s',
		preg_replace( '#^https?://#u', '', home_url() ),
		$sort
	);
	$feed = fetch_feed( $endpoint );
	if ( is_wp_error( $feed ) ) {
		return [];
	} else {
		if ( 'count' === $sort ) {
			$rank = [];
			for ( $i = 0, $l = $feed->get_item_quantity(); $i < $l; $i++ ) {
				$item = $feed->get_item($i);
				$rank[$i] = $item->get_item_tags( 'http://www.hatena.ne.jp/info/xmlns#', 'bookmarkcount' )[0]['data'];
			}
			arsort($rank);
			$items = [];
			foreach ( $rank as $index => $value ) {
				$items[] = $feed->get_item($index);
				if ( 5 <= count($items) ) {
					break;
				}
			}
			return $items;
		} else {
			return $feed->get_items( 0, 5 );
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