<?php
namespace Fumikito\Kyom\Service;

/**
 * Mailchimp API client.
 *
 * 以前はフォームから list-manage.com へ直接 POST していたが、購読者 ID が
 * HTML に露出するためボットに発見され、2024〜2026 年に 284 件のスパムが流入した。
 * 登録はすべてサーバー経由でこのクラスから行い、エンドポイントを公開しない。
 *
 * @package kyom
 */
class MailchimpClient {

	/**
	 * MailchimpClient constructor.
	 */
	private function __construct() {}

	/**
	 * Get API key.
	 *
	 * 本番では wp-config.php の定数を推奨。DB に置かない分、
	 * 管理画面を奪われてもキーは漏れない。
	 *
	 * @return string
	 */
	public static function api_key() {
		if ( defined( 'KYOM_MAILCHIMP_API_KEY' ) && KYOM_MAILCHIMP_API_KEY ) {
			return (string) KYOM_MAILCHIMP_API_KEY;
		}
		return (string) get_option( 'kyom_mailchimp_api_key', '' );
	}

	/**
	 * Get audience(list) ID.
	 *
	 * @return string
	 */
	public static function list_id() {
		if ( defined( 'KYOM_MAILCHIMP_LIST_ID' ) && KYOM_MAILCHIMP_LIST_ID ) {
			return (string) KYOM_MAILCHIMP_LIST_ID;
		}
		return (string) get_option( 'kyom_mailchimp_list_id', '' );
	}

	/**
	 * Data center is embedded in the API key suffix like `xxxx-us14`.
	 *
	 * 設定項目を増やさないためにキーから導出する。
	 *
	 * @return string
	 */
	public static function data_center() {
		$key = self::api_key();
		if ( ! $key || false === strpos( $key, '-' ) ) {
			return '';
		}
		return substr( $key, strrpos( $key, '-' ) + 1 );
	}

	/**
	 * Is this client configured?
	 *
	 * @return bool
	 */
	public static function is_ready() {
		return (bool) ( self::api_key() && self::list_id() && self::data_center() );
	}

	/**
	 * Send a request to Mailchimp API.
	 *
	 * @param string $method   HTTP method.
	 * @param string $endpoint Endpoint starting with slash.
	 * @param array  $body     Request body.
	 * @return array|\WP_Error
	 */
	protected static function request( $method, $endpoint, $body = [] ) {
		if ( ! self::is_ready() ) {
			return new \WP_Error( 'kyom_mailchimp_not_configured', __( 'Mailchimp is not configured.', 'kyom' ) );
		}
		$args = [
			'method'  => $method,
			'timeout' => 15,
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode( 'key:' . self::api_key() ),
				'Content-Type'  => 'application/json',
			],
		];
		if ( $body ) {
			$args['body'] = wp_json_encode( $body );
		}
		$url      = sprintf( 'https://%s.api.mailchimp.com/3.0%s', self::data_center(), $endpoint );
		$response = wp_remote_request( $url, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code   = wp_remote_retrieve_response_code( $response );
		$parsed = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( 200 > $code || 300 <= $code ) {
			// Mailchimp はエラー詳細を title / detail で返す。
			$detail = is_array( $parsed ) && ! empty( $parsed['detail'] ) ? $parsed['detail'] : '';
			$title  = is_array( $parsed ) && ! empty( $parsed['title'] ) ? $parsed['title'] : 'Unknown';
			return new \WP_Error(
				'kyom_mailchimp_api_error',
				sprintf( '[%s] %s %s', $code, $title, $detail ),
				[ 'status' => $code ]
			);
		}
		return is_array( $parsed ) ? $parsed : [];
	}

	/**
	 * Get audience information. Used to verify the credentials on setting screen.
	 *
	 * @param bool $force Skip cache if true.
	 * @return array|\WP_Error
	 */
	public static function get_list( $force = false ) {
		$cache_key = 'kyom_mailchimp_list_' . md5( self::list_id() . self::api_key() );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached && ! $force ) {
			return $cached;
		}
		$result = self::request( 'GET', '/lists/' . rawurlencode( self::list_id() ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		set_transient( $cache_key, $result, 5 * MINUTE_IN_SECONDS );
		return $result;
	}

	/**
	 * Subscribe an email address with double opt-in.
	 *
	 * PUT なので既存購読者に対しては上書き更新になる。`status` ではなく
	 * `status_if_new` を渡すのが重要で、これにより「すでに購読解除した人を
	 * こちらの操作で再購読させてしまう」事故が構造的に起きない。
	 *
	 * `ip_signup` を必ず渡すこと。サーバー経由で登録するようにした結果、
	 * 渡さないと Mailchimp は API 呼び出し元＝**このサーバーの IP** を記録してしまう。
	 * 2026-09 に汚染を調べたとき、211 件すべてが同じ IP になっていて、
	 * 2019 年の調査で最も役に立った判別材料を自分で潰していたことが分かった。
	 *
	 * なお `ip_signup` を渡すと、**Mailchimp は `ip_opt`（確認クリック時の IP）にも
	 * 同じ値を勝手に入れる**（2026-09-17 に実測。こちらからは送っていない）。
	 * そのため `ip_opt` が空かどうかは確認済み判定に使えない。
	 * 未確認の判定には `status` と `timestamp_opt` を見ること
	 * ——この 2 つは pending のまま正しく残る。
	 *
	 * @param string $email        Email address.
	 * @param array  $merge_fields Merge fields like FNAME.
	 * @param string $ip           Client IP address who submitted the form.
	 * @return array|\WP_Error
	 */
	public static function subscribe( $email, $merge_fields = [], $ip = '' ) {
		$email = strtolower( trim( $email ) );
		if ( ! is_email( $email ) ) {
			return new \WP_Error( 'kyom_mailchimp_invalid_email', __( 'Email address is invalid.', 'kyom' ) );
		}
		$merge_fields = array_filter( array_merge( [
			'REGISTERED' => date_i18n( 'Y-m-d' ),
			'SOURCE'     => wp_parse_url( home_url(), PHP_URL_HOST ),
		], $merge_fields ) );
		$body         = [
			'email_address' => $email,
			'status_if_new' => 'pending',
			'merge_fields'  => $merge_fields,
		];
		// プライベートアドレスは調査の役に立たないうえ Mailchimp 側で弾かれ得るので送らない。
		if ( $ip && filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
			$body['ip_signup'] = $ip;
		}
		return self::request(
			'PUT',
			sprintf( '/lists/%s/members/%s', rawurlencode( self::list_id() ), md5( $email ) ),
			$body
		);
	}
}
