<?php
/**
 * YouTube ショートのキャッシュ更新を cron に載せる。
 *
 * ショート判定は動画1本ごとに HEAD リクエストが必要なので、フロントエンドで
 * 走らせるとページ表示を待たせる。取得はすべてここに寄せ、表示側は
 * kyom_get_youtube_shorts() でキャッシュだけを読む。
 *
 * @package kyom
 */

/**
 * cron フック名。
 */
const KYOM_YOUTUBE_SHORTS_CRON = 'kyom_refresh_youtube_shorts';

// 定期実行を登録する。単発イベント（キャッシュミス時の自己修復）は
// wp_get_schedule() では拾われないので、定期の有無だけを見て判定する。
add_action( 'init', function () {
	if ( ! wp_get_schedule( KYOM_YOUTUBE_SHORTS_CRON ) ) {
		// 単発イベントと同じ時刻に重ならないよう1時間ずらして開始する。
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'twicedaily', KYOM_YOUTUBE_SHORTS_CRON );
	}
} );

add_action( KYOM_YOUTUBE_SHORTS_CRON, 'kyom_refresh_youtube_shorts' );

// テーマを外したらスケジュールを残さない。
add_action( 'switch_theme', function () {
	wp_clear_scheduled_hook( KYOM_YOUTUBE_SHORTS_CRON );
} );

// チャンネルの設定が変わったらキャッシュを捨てて作り直す。
foreach ( [ 'kyom_youtube_api_key', 'kyom_youtube_channel_id' ] as $kyom_youtube_option ) {
	add_action( 'update_option_' . $kyom_youtube_option, function () {
		delete_transient( KYOM_YOUTUBE_SHORTS_CACHE );
		delete_transient( 'kyom_youtube_channel' );
		kyom_schedule_youtube_shorts_refresh();
	} );
}
unset( $kyom_youtube_option );
