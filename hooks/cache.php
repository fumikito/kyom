<?php
/**
 * Cache related functions.
 *
 * @package kyom
 */


/**
 * Add Cache header
 *
 * @filter nocache_headers
 *
 * @param array $headers
 *
 * @return array
 */
add_filter( 'nocache_headers', function ( $headers ) {
	// Only cache singular and front page.
	$should_cache = is_front_page() || is_single();
	$should_cache = apply_filters( 'kyom_should_cache', $should_cache );
	if ( $should_cache ) {
		unset( $headers['Expires'] );
		unset( $headers['Cache-Control'] );
		unset( $headers['Pragma'] );
	} else {
		$headers['X-Accel-Expires'] = 0;
	}

	return $headers;
}, 1 );


/**
 * Add CloudFlare headers.
 *
 * @action template_redirect
 */
add_action( 'template_redirect', function () {
	// Add CF tags.
	$tags = '';
	if ( is_front_page() ) {
		$tags = 'front';
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$cat  = get_queried_object();
		$tags = $cat->taxonomy . '-' . $cat->slug;
	} elseif ( is_single() || is_page() || is_singular() ) {
		$tags = get_post_type() . '-' . get_the_ID();
	}
	if ( $tags ) {
		header( 'Cache-Tag: ' . $tags );
	}
} );

// CloudFlare のパージは hamecache プラグインが担当する。
// テーマにも kyom_purge_cf_cache() があったが、hamecache のほうが対象URLが
// 広く（著者アーカイブ・ページ送り・AMP・投稿タイプアーカイブ・フィードも含む）、
// 完全な上位互換だったため削除した。拡張したいときは hamecache 側の
// `hamecache_urls_to_be_purged` フィルタを使う。
//
// なお wp-config.php の CF_MAIL / CF_TOKEN / CF_ZONE_ID は hamecache が
// そのまま読んでいるので消してはいけない。
