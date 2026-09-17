<?php
namespace Fumikito\Kyom\Service;

/**
 * Cloudflare Turnstile client.
 *
 * ニュースレターフォームには以前からハニーポット・経過時間・IP レート制限を
 * 入れてあるが、どれも「クライアントの自己申告」なので POST を手で組めば通る。
 * 実際 2026-08〜09 にかけて pending が 213 件まで再増殖した。
 *
 * Turnstile はトークンを CloudFlare が発行し、その正しさをサーバーから
 * siteverify に問い合わせて確かめる。ペイロードを偽装しても作れない点が違う。
 * sitekey は公開前提の値なので、ページが CloudFlare にキャッシュされていても
 * 埋め込んでよい（nonce が使えない理由と同じ制約に引っかからない）。
 *
 * @package kyom
 */
class TurnstileClient {

	/**
	 * Endpoint to verify a token.
	 */
	const VERIFY_ENDPOINT = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

	/**
	 * TurnstileClient constructor.
	 */
	private function __construct() {}

	/**
	 * Get site key.
	 *
	 * HTML に出る公開値なので秘匿する必要はない。
	 *
	 * @return string
	 */
	public static function site_key() {
		if ( defined( 'KYOM_TURNSTILE_SITE_KEY' ) && KYOM_TURNSTILE_SITE_KEY ) {
			return (string) KYOM_TURNSTILE_SITE_KEY;
		}
		return (string) get_option( 'kyom_turnstile_site_key', '' );
	}

	/**
	 * Get secret key.
	 *
	 * 本番では wp-config.php の定数を推奨。MailchimpClient と同じ方針で、
	 * DB に置かない分だけ管理画面を奪われたときの被害が小さい。
	 *
	 * @return string
	 */
	public static function secret_key() {
		if ( defined( 'KYOM_TURNSTILE_SECRET_KEY' ) && KYOM_TURNSTILE_SECRET_KEY ) {
			return (string) KYOM_TURNSTILE_SECRET_KEY;
		}
		return (string) get_option( 'kyom_turnstile_secret_key', '' );
	}

	/**
	 * Is this client configured?
	 *
	 * 片方だけでは検証が成立しないので、両方揃って初めて有効とみなす。
	 *
	 * @return bool
	 */
	public static function is_ready() {
		return (bool) ( self::site_key() && self::secret_key() );
	}

	/**
	 * Verify a token issued by the widget.
	 *
	 * **到達できないときは通さない。** 検証できなかったリクエストを
	 * 許してしまうと、siteverify を妨害するだけで素通りできることになり、
	 * Turnstile を入れた意味が消える。
	 *
	 * @param string $token Value of `cf-turnstile-response`.
	 * @param string $ip    Client IP address. Optional.
	 * @return true|\WP_Error
	 */
	public static function verify( $token, $ip = '' ) {
		if ( ! self::is_ready() ) {
			return new \WP_Error( 'kyom_turnstile_not_configured', __( 'Turnstile is not configured.', 'kyom' ), [ 'status' => 503 ] );
		}
		$token = trim( (string) $token );
		if ( '' === $token ) {
			return new \WP_Error( 'kyom_turnstile_missing', __( 'Turnstile token is missing.', 'kyom' ), [ 'status' => 400 ] );
		}
		$body = [
			'secret'   => self::secret_key(),
			'response' => $token,
		];
		if ( $ip ) {
			$body['remoteip'] = $ip;
		}
		$response = wp_remote_post( self::VERIFY_ENDPOINT, [
			'timeout' => 10,
			'body'    => $body,
		] );
		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'kyom_turnstile_unreachable', $response->get_error_message(), [ 'status' => 503 ] );
		}
		$parsed = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $parsed ) ) {
			return new \WP_Error(
				'kyom_turnstile_invalid_response',
				sprintf( 'Unexpected response from Turnstile. [%s]', wp_remote_retrieve_response_code( $response ) ),
				[ 'status' => 503 ]
			);
		}
		if ( empty( $parsed['success'] ) ) {
			// error-codes に invalid-input-secret 等が入る。原因の切り分けに要るのでそのまま残す。
			$codes = ! empty( $parsed['error-codes'] ) && is_array( $parsed['error-codes'] )
				? implode( ', ', array_map( 'strval', $parsed['error-codes'] ) )
				: 'unknown';
			return new \WP_Error( 'kyom_turnstile_failed', sprintf( 'Turnstile rejected the token: %s', $codes ), [ 'status' => 400 ] );
		}
		return true;
	}
}
