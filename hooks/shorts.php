<?php
/**
 * 最新ショートのカードをフッターから出す。
 *
 * 画面右下に固定するオーバーレイなので、本文のどこに置いても位置は変わらない。
 * ブロックにする意味がないため、サイト全体の仕掛けとして wp_footer に載せる。
 *
 * @package kyom
 */

/**
 * ショートのカードを出す文脈かどうか。
 *
 * @return bool
 */
function kyom_show_floating_shorts() {
	$show = ! is_admin() && ! is_feed() && ! is_embed() && ! is_404();
	return (bool) apply_filters( 'kyom_show_floating_shorts', $show );
}

add_action( 'wp_footer', function () {
	if ( ! kyom_show_floating_shorts() ) {
		return;
	}
	$html = kyom_get_shorts_html();
	if ( ! $html ) {
		return;
	}
	// 表示の可否は JS が判断する（閉じた記録が localStorage にある）。
	wp_enqueue_script( 'kyom-shorts' );
	echo $html; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped
} );
