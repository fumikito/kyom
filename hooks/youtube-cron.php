<?php
/**
 * YouTube ショートのキャッシュ更新を cron に載せる。
 *
 * ショート判定は動画1本ごとに HEAD リクエストが必要なので、フロントエンドで
 * 走らせるとページ表示を待たせる。取得はすべてここに寄せ、表示側は
 * kyom_get_youtube_shorts() でキャッシュだけを読む。
 *
 * フック名の定数は functions/shorts.php にある（functions/ は hooks/ より先に
 * 読み込まれる）。定期用と即時用でフックを分けている理由もそちらに書いた。
 *
 * @package kyom
 */

// 定期実行を登録する。
//
// 判定は wp_next_scheduled() で行う。wp_get_schedule() を使ってはいけない。
// あれは「そのフックの次の1件」の schedule を返すので、期限切れの単発イベントが
// 先頭にあると定期イベントがあっても false になり、毎リクエスト積んでしまう。
add_action( 'init', function () {
	if ( wp_next_scheduled( KYOM_YOUTUBE_SHORTS_CRON ) ) {
		return;
	}
	wp_schedule_event( time() + HOUR_IN_SECONDS, 'twicedaily', KYOM_YOUTUBE_SHORTS_CRON );
} );

add_action( KYOM_YOUTUBE_SHORTS_CRON, 'kyom_refresh_youtube_shorts' );
add_action( KYOM_YOUTUBE_SHORTS_CRON_NOW, 'kyom_refresh_youtube_shorts' );

// テーマを外したらスケジュールを残さない。
add_action( 'switch_theme', function () {
	wp_clear_scheduled_hook( KYOM_YOUTUBE_SHORTS_CRON );
	wp_clear_scheduled_hook( KYOM_YOUTUBE_SHORTS_CRON_NOW );
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
