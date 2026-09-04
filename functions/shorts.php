<?php
/**
 * YouTube ショートの取得と表示。
 *
 * ショートは Data API では通常の動画と区別されないため、判定は公開URL
 * https://www.youtube.com/shorts/{id} への HEAD で行う（200 ならショート、
 * 3xx で /watch へ飛ぶなら通常動画）。尺での判定は 2024年10月にショートの
 * 上限が3分へ伸びたため当てにならず、タイトルの #Shorts タグも新しい投稿に
 * は付いていない。
 *
 * プローブはすべて cron 側で解決する。フロントエンドはキャッシュしか読まない。
 *
 * @package kyom
 */

/**
 * ショート一覧のキャッシュキー。
 */
const KYOM_YOUTUBE_SHORTS_CACHE = 'kyom_youtube_shorts';

/**
 * 動画IDごとの「ショートか」の判定結果を溜めるオプション名。
 *
 * ショートかどうかは後から変わらない永続データなので、transient ではなく
 * オプションに置く。揮発性のオブジェクトキャッシュに置くと、キャッシュが
 * 飛んだときに判定結果を全部失い、動画1本ごとの HEAD リクエストをやり直す
 * ことになる。autoload はしない（動画数に応じて増えるし、cron しか読まない）。
 */
const KYOM_YOUTUBE_SHORT_FLAGS_OPTION = 'kyom_youtube_short_flags';

/**
 * 定期実行の cron フック名。
 */
const KYOM_YOUTUBE_SHORTS_CRON = 'kyom_refresh_youtube_shorts';

/**
 * 即時実行（自己修復）の cron フック名。
 *
 * 定期実行と同じフック名にしてはいけない。wp_get_schedule() は「そのフックの
 * 次の1件」の schedule しか見ないため、消化されない単発イベントが先頭に居座ると
 * 定期イベントの有無を判定できなくなり、毎リクエスト定期イベントを積んでしまう。
 * 実際にそれで本番の cron オプションが 473KB まで膨らんだ。
 */
const KYOM_YOUTUBE_SHORTS_CRON_NOW = 'kyom_refresh_youtube_shorts_now';

/**
 * ショートを何日以内のものに限るか。
 *
 * 投稿が途絶えているときに半年前の動画を「最新」として出し続けると、
 * 鮮度を保つどころか放置を宣伝することになるため既定で窓を切る。
 *
 * @return int 日数。
 */
function kyom_youtube_shorts_max_age() {
	$days = (int) get_option( 'kyom_youtube_shorts_max_age', 0 );
	if ( 1 > $days ) {
		$days = 60;
	}
	return (int) apply_filters( 'kyom_youtube_shorts_max_age', $days );
}

/**
 * 一度に走査するアップロード数。
 *
 * @return int
 */
function kyom_youtube_shorts_scan_size() {
	return (int) apply_filters( 'kyom_youtube_shorts_scan_size', 20 );
}

/**
 * 動画IDごとの判定結果マップ。
 *
 * @return array<string, bool>
 */
function kyom_youtube_short_flags() {
	$flags = get_option( KYOM_YOUTUBE_SHORT_FLAGS_OPTION, [] );
	return is_array( $flags ) ? $flags : [];
}

/**
 * 動画がショートかどうか。
 *
 * @param string $video_id 動画ID。
 * @return bool|null 未判定なら null。判定は cron でしか行わない。
 */
function kyom_youtube_is_short( $video_id ) {
	$flags = kyom_youtube_short_flags();
	return array_key_exists( $video_id, $flags ) ? (bool) $flags[ $video_id ] : null;
}

/**
 * 動画がショートかを実際に問い合わせる。
 *
 * @param string $video_id 動画ID。
 * @return bool|WP_Error
 */
function kyom_youtube_probe_short( $video_id ) {
	$response = wp_remote_head(
		'https://www.youtube.com/shorts/' . rawurlencode( $video_id ),
		[
			'timeout'     => 10,
			// 3xx を追ってしまうと 200 と区別できなくなる。
			'redirection' => 0,
		]
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( 200 === $code ) {
		return true;
	}
	if ( 300 <= $code && 400 > $code ) {
		// /watch へのリダイレクト。つまり通常動画。
		return false;
	}
	return new WP_Error(
		'kyom_youtube_probe_failed',
		sprintf(
			/* translators: 1: video id, 2: HTTP status code. */
			__( 'Could not determine whether %1$s is a short (HTTP %2$d).', 'kyom' ),
			$video_id,
			$code
		)
	);
}

/**
 * ショートの縦サムネイルURL。
 *
 * oar2.jpg は元の縦横比のサムネイルで、ショートなら 1080x1920 が返る。
 * 非公式なエンドポイントなので、無ければ API の 16:9 サムネイルに落とす。
 *
 * @param string $video_id 動画ID。
 * @return string 取得できなければ空文字。
 */
function kyom_youtube_portrait_thumbnail( $video_id ) {
	$url      = sprintf( 'https://i.ytimg.com/vi/%s/oar2.jpg', rawurlencode( $video_id ) );
	$response = wp_remote_head( $url, [ 'timeout' => 10 ] );
	if ( is_wp_error( $response ) ) {
		return '';
	}
	return 200 === (int) wp_remote_retrieve_response_code( $response ) ? $url : '';
}

/**
 * API の応答から一番大きいサムネイルを選ぶ。
 *
 * @param array $thumbnails snippet.thumbnails。
 * @return string
 */
function kyom_youtube_largest_thumbnail( $thumbnails ) {
	if ( ! is_array( $thumbnails ) ) {
		return '';
	}
	$best  = '';
	$width = 0;
	foreach ( $thumbnails as $thumbnail ) {
		if ( empty( $thumbnail['url'] ) ) {
			continue;
		}
		$candidate = isset( $thumbnail['width'] ) ? (int) $thumbnail['width'] : 0;
		if ( $candidate >= $width ) {
			$width = $candidate;
			$best  = $thumbnail['url'];
		}
	}
	return $best;
}

/**
 * アップロード済み動画の一覧を取る。
 *
 * 既存の kyom_get_youtube_videos() はステータスコードを見ておらず、quota 超過の
 * 応答でも成功として扱ってしまうため、ここでは MailchimpClient::request() と同じ
 * 作法で書く。
 *
 * @param string $playlist_id アップロードのプレイリストID。
 * @return array|WP_Error playlistItems の items。
 */
function kyom_youtube_fetch_uploads( $playlist_id ) {
	$api_key = get_option( 'kyom_youtube_api_key', '' );
	if ( ! $api_key ) {
		return new WP_Error( 'kyom_youtube_no_api_key', __( 'API Key for YouTube Data API is not set.', 'kyom' ) );
	}
	$endpoint = add_query_arg(
		[
			'part'       => 'snippet,contentDetails',
			'playlistId' => rawurlencode( $playlist_id ),
			'maxResults' => kyom_youtube_shorts_scan_size(),
			'key'        => rawurlencode( $api_key ),
		],
		'https://www.googleapis.com/youtube/v3/playlistItems'
	);
	$response = wp_remote_get( $endpoint, [ 'timeout' => 15 ] );
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	$json = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( 200 !== $code ) {
		$message = isset( $json['error']['message'] ) ? $json['error']['message'] : __( 'Unknown error.', 'kyom' );
		return new WP_Error( 'kyom_youtube_api_error', sprintf( '[%d] %s', $code, $message ), [ 'status' => $code ] );
	}
	if ( ! is_array( $json ) || empty( $json['items'] ) || ! is_array( $json['items'] ) ) {
		return new WP_Error( 'kyom_youtube_empty_response', __( 'Failed to get valid API response.', 'kyom' ) );
	}
	return $json['items'];
}

/**
 * ショート一覧のキャッシュを更新する。cron から呼ばれる。
 *
 * @return void
 */
function kyom_refresh_youtube_shorts() {
	$playlists = kyom_get_youtube_playlist( false );
	if ( empty( $playlists['uploads'] ) ) {
		// チャンネル未設定。しばらく空を返して問い合わせを止める。
		set_transient( KYOM_YOUTUBE_SHORTS_CACHE, [], HOUR_IN_SECONDS );
		return;
	}
	$items = kyom_youtube_fetch_uploads( $playlists['uploads'] );
	if ( is_wp_error( $items ) ) {
		set_transient( KYOM_YOUTUBE_SHORTS_CACHE, [], HOUR_IN_SECONDS );
		return;
	}
	$flags  = kyom_youtube_short_flags();
	$shorts = [];
	foreach ( $items as $item ) {
		$video_id = isset( $item['contentDetails']['videoId'] ) ? $item['contentDetails']['videoId'] : '';
		if ( ! $video_id ) {
			continue;
		}
		if ( ! array_key_exists( $video_id, $flags ) ) {
			$probe = kyom_youtube_probe_short( $video_id );
			if ( is_wp_error( $probe ) ) {
				// 判定できなかったものは記録せず、次回に回す。
				continue;
			}
			$flags[ $video_id ] = $probe;
		}
		if ( ! $flags[ $video_id ] ) {
			continue;
		}
		$thumbnail = kyom_youtube_portrait_thumbnail( $video_id );
		if ( ! $thumbnail ) {
			$thumbnail = kyom_youtube_largest_thumbnail( isset( $item['snippet']['thumbnails'] ) ? $item['snippet']['thumbnails'] : [] );
		}
		$published = '';
		if ( ! empty( $item['contentDetails']['videoPublishedAt'] ) ) {
			$published = $item['contentDetails']['videoPublishedAt'];
		} elseif ( ! empty( $item['snippet']['publishedAt'] ) ) {
			$published = $item['snippet']['publishedAt'];
		}
		$shorts[] = [
			'id'        => $video_id,
			'title'     => isset( $item['snippet']['title'] ) ? $item['snippet']['title'] : '',
			'url'       => 'https://www.youtube.com/shorts/' . $video_id,
			'thumbnail' => $thumbnail,
			'published' => $published,
		];
	}
	// 新しい順。uploads は既にその順で返るが、依存しない。
	usort( $shorts, function ( $a, $b ) {
		return strtotime( $b['published'] ) <=> strtotime( $a['published'] );
	} );
	update_option( KYOM_YOUTUBE_SHORT_FLAGS_OPTION, $flags, false );
	set_transient( KYOM_YOUTUBE_SHORTS_CACHE, $shorts, WEEK_IN_SECONDS );
	delete_transient( 'kyom_youtube_shorts_pending' );
}

/**
 * キャッシュが無いときに cron を1回積む。
 *
 * フロントエンドで HEAD を直列に走らせるとページ表示を待たせるので、
 * 取得は必ず cron に寄せる。
 *
 * @return void
 */
function kyom_schedule_youtube_shorts_refresh() {
	if ( get_transient( 'kyom_youtube_shorts_pending' ) ) {
		return;
	}
	set_transient( 'kyom_youtube_shorts_pending', 1, 10 * MINUTE_IN_SECONDS );
	wp_schedule_single_event( time(), KYOM_YOUTUBE_SHORTS_CRON_NOW );
}

/**
 * 表示すべきショートを返す。
 *
 * @param array $args {
 *     @type int      $count   最大件数。既定 3。
 *     @type int|null $max_age 何日以内のものに限るか。null ならオプションに従う。
 * }
 * @return array 正規化済みのショート。
 */
function kyom_get_youtube_shorts( $args = [] ) {
	$args   = wp_parse_args( $args, [
		'count'   => 3,
		'max_age' => null,
	] );
	$shorts = get_transient( KYOM_YOUTUBE_SHORTS_CACHE );
	if ( ! is_array( $shorts ) ) {
		kyom_schedule_youtube_shorts_refresh();
		return [];
	}
	$max_age = is_null( $args['max_age'] ) ? kyom_youtube_shorts_max_age() : (int) $args['max_age'];
	// 「最新の1本が新しければ古いものも一緒に出す」ではなく、1本ずつ窓で切る。
	// でないと直近1本と3年前のものが並んでしまう。
	$oldest = time() - ( $max_age * DAY_IN_SECONDS );
	$fresh  = array_values( array_filter( $shorts, function ( $short ) use ( $oldest ) {
		return $short['published'] && strtotime( $short['published'] ) >= $oldest;
	} ) );
	return array_slice( $fresh, 0, max( 1, (int) $args['count'] ) );
}

/**
 * 最新ショートを画面右下に出すカードのHTMLを返す。
 *
 * 固定オーバーレイなので、本文のどこに置いても位置は変わらない。そのため
 * ブロックにはせず、wp_footer から1ページに1回だけ出す。
 *
 * @param array $args {
 *     @type string   $label   サムネイル左上のタグ文字。既定は SHORTS。
 *     @type int|null $max_age 何日以内のものに限るか。null ならオプションに従う。
 *     @type string   $class   追加のクラス名。
 * }
 * @return string 出すショートが無ければ空文字。
 */
function kyom_get_shorts_html( $args = [] ) {
	$args   = wp_parse_args( $args, [
		'label'   => '',
		'max_age' => null,
		'class'   => '',
	] );
	$shorts = kyom_get_youtube_shorts( [
		'count'   => 1,
		'max_age' => $args['max_age'],
	] );
	if ( ! $shorts ) {
		// 窓の中に何も無ければ何も出さない。空のカードを残すと放置が目立つ。
		return '';
	}
	$short   = $shorts[0];
	$label   = $args['label'] ? $args['label'] : __( 'SHORTS', 'kyom' );
	$classes = array_filter( [ 'kyom-shorts', $args['class'] ] );

	// hidden で描画しておき、表示は JS に任せる。ページは CloudFlare で
	// キャッシュされるため「閉じたかどうか」をサーバー側では判断できず、
	// 出しておいてから消すと閉じた人に一瞬見えてしまう。
	ob_start();
	?>
	<aside class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-kyom-short="<?php echo esc_attr( $short['id'] ); ?>" hidden>
		<button type="button" class="kyom-shorts-close" aria-label="<?php esc_attr_e( 'Dismiss', 'kyom' ); ?>">
			<span uk-icon="icon:close; ratio: 0.8"></span>
		</button>
		<a class="kyom-shorts-link" href="<?php echo esc_url( $short['url'] ); ?>" target="_blank" rel="noopener noreferrer">
			<span class="kyom-shorts-thumb">
				<?php if ( $short['thumbnail'] ) : ?>
					<img class="kyom-shorts-image" src="<?php echo esc_url( $short['thumbnail'] ); ?>" alt="" loading="lazy" />
				<?php endif; ?>
				<span class="kyom-shorts-tag"><?php echo esc_html( $label ); ?></span>
				<span class="kyom-shorts-play" uk-icon="icon:play; ratio: 1.4"></span>
				<span class="kyom-shorts-caption"><?php echo esc_html( $short['title'] ); ?></span>
			</span>
		</a>
	</aside>
	<?php
	return trim( ob_get_clean() );
}

/**
 * 最新ショートのカードを出力する。
 *
 * @param array $args kyom_get_shorts_html() と同じ。
 * @return void
 */
function kyom_the_shorts( $args = [] ) {
	echo kyom_get_shorts_html( $args ); // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped
}
