<?php
/**
 * Feed related functions.
 *
 * @package kyom
 */


/**
 * Add media to RSS2.0
 */
add_action( 'rss2_ns', function () {
	echo 'xmlns:media="http://search.yahoo.com/mrss/"';
} );

/**
 * Add thumbnails to media tag.
 */
add_action('rss2_item', function () {
	$src = apply_filters( 'kyom_rss_thumbnail_src', get_the_post_thumbnail_url( null, 'full' ) );
	if ( ! $src ) {
		return;
	}
	$title = get_the_title_rss();
	echo <<<EOS
		<media:thumbnail url="{$src}" />
		<media:content url="{$src}" medium="image">
			<media:title type="html">{$title}</media:title>
		</media:content>
EOS;
} );

/**
 * Avoid self ping.
 *
 * @param array $links
 */
add_action( 'pre_ping', function ( &$links ) {
	foreach ( $links as $index => $link ) {
		if ( false !== strpos( home_url(), $link ) ) {
			unset( $links[ $index ] );
		}
	}
});
