<?php
/**
 * Newsletter subscription form and its endpoint.
 *
 * フォームはかつて Mailchimp の list-manage.com へ直接 POST していたが、
 * 購読者 ID が HTML に露出するためボットに直接叩かれていた。登録はすべて
 * このサイトの REST エンドポイントを経由させ、Mailchimp 側の URL は出さない。
 *
 * ページは CloudFlare でキャッシュされるため、nonce や描画時刻を HTML に
 * 埋めても全訪問者に同じ古い値が配られてしまう。`hooks/comments.php` と同じく
 * 送信を JS 経由にして、検証はサーバー側で行う。
 *
 * @package kyom
 */

/**
 * Get client IP address.
 *
 * CloudFlare を通すと REMOTE_ADDR は CloudFlare のアドレスになるため、
 * オリジナルの接続元が入るヘッダーを優先する。
 *
 * @return string
 */
function kyom_newsletter_client_ip() {
	foreach ( [ 'HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR' ] as $key ) {
		if ( empty( $_SERVER[ $key ] ) ) {
			continue;
		}
		$ip = filter_var( wp_unslash( $_SERVER[ $key ] ), FILTER_VALIDATE_IP );
		if ( $ip ) {
			return $ip;
		}
	}
	return '';
}

/**
 * Occupation choices. Matches MMERGE3 radio options on Mailchimp.
 *
 * この項目は任意のままにしておくこと。2024〜2026 年のスパムは
 * 「ボットは必ず選択し、人間はほぼ空欄で送る」という癖で判別できた。
 * 必須にするとその手がかりを自分で消すことになる。
 *
 * @return string[]
 */
function kyom_newsletter_occupations() {
	return [ '出版関連', '編集者', '作家・ライター', 'Web関連', '学生', 'その他' ];
}

/**
 * Render newsletter form.
 *
 * フッター版はメール欄をフォーカスするまで詳細項目が畳まれている
 * （`section-newsletter-extra` が max-height: 0、`.toggle` で展開）。
 * ショートコード版にその class を付けると開く手段がないまま隠れてしまうので、
 * 文脈によって出し分ける。
 *
 * @param string $context `footer` or `shortcode`.
 * @return string
 */
function kyom_newsletter_form( $context = 'footer' ) {
	if ( ! \Fumikito\Kyom\Service\MailchimpClient::is_ready() ) {
		return '';
	}
	$is_footer = ( 'footer' === $context );
	// フッターとショートコードが同じページに並ぶことがあるため、
	// id が衝突しないよう描画ごとに連番を振る。
	static $instance = 0;
	$uid             = 'kyom-newsletter-' . ++$instance;
	ob_start();
	?>
	<div class="kyom-newsletter" id="<?php echo esc_attr( $uid ); ?>">
		<?php if ( $is_footer ) : ?>
			<h2 class="section-newsletter-title"><?php esc_html_e( '高橋文樹ニュースレター', 'kyom' ); ?></h2>
			<p class="section-newsletter-lead">
				<?php esc_html_e( '高橋文樹が最近の活動報告、サイトでパブリックにできない情報などをお伝えするメーリングリストです。滅多に送りませんので、ぜひご登録お願いいたします。', 'kyom' ); ?>
			</p>
		<?php endif; ?>
		<form class="kyom-newsletter-form" data-context="<?php echo esc_attr( $context ); ?>" novalidate>
			<p class="section-newsletter-mail-input<?php echo $is_footer ? '' : ' form-group'; ?>">
				<?php // フッター版はメール欄だけを大きく見せるデザインなので、ラベルは読み上げ専用にする。 ?>
				<label class="<?php echo $is_footer ? 'uk-hidden-visually' : ''; ?>" for="<?php echo esc_attr( $uid ); ?>-email">
					<?php esc_html_e( 'Email', 'kyom' ); ?> <span class="asterisk text-danger">*</span>
				</label>
				<input type="email" name="email" id="<?php echo esc_attr( $uid ); ?>-email" class="required email form-control"
						required placeholder="e.g. info@takahashifumiki.com">
			</p>
			<fieldset class="uk-fieldset<?php echo $is_footer ? ' section-newsletter-extra' : ''; ?>">
				<div>
				<div class="uk-grid" uk-grid>
					<div class="form-group">
						<label for="<?php echo esc_attr( $uid ); ?>-name"><?php esc_html_e( 'お名前', 'kyom' ); ?> <span class="asterisk text-danger">*</span></label>
						<input type="text" name="name" id="<?php echo esc_attr( $uid ); ?>-name" class="form-control"
								required placeholder="e.g. 高橋文樹">
					</div>
					<div class="form-group">
						<label for="<?php echo esc_attr( $uid ); ?>-company"><?php esc_html_e( '会社・団体', 'kyom' ); ?></label>
						<input type="text" name="company" id="<?php echo esc_attr( $uid ); ?>-company" class="form-control"
								placeholder="e.g. 株式会社破滅派">
					</div>
				</div>
				<div class="form-group">
					<?php foreach ( kyom_newsletter_occupations() as $index => $label ) : ?>
						<label for="<?php echo esc_attr( $uid . '-job-' . $index ); ?>" class="inline-label">
							<input class="uk-radio" type="radio" name="job" value="<?php echo esc_attr( $label ); ?>"
									id="<?php echo esc_attr( $uid . '-job-' . $index ); ?>">
							<?php echo esc_html( $label ); ?>
						</label>
					<?php endforeach; ?>
				</div>
				<?php // ハニーポット。人間には見えないので、埋まっていればボット。 ?>
				<div class="kyom-newsletter-hp" aria-hidden="true">
					<label for="<?php echo esc_attr( $uid ); ?>-website">Website</label>
					<input type="text" name="website" id="<?php echo esc_attr( $uid ); ?>-website" tabindex="-1" autocomplete="off">
				</div>
				<?php if ( \Fumikito\Kyom\Service\TurnstileClient::is_ready() ) : ?>
					<?php // トークンは hidden input `cf-turnstile-response` としてこのフォーム内に挿入される。 ?>
					<div class="kyom-newsletter-turnstile cf-turnstile"
						data-sitekey="<?php echo esc_attr( \Fumikito\Kyom\Service\TurnstileClient::site_key() ); ?>"
						data-language="ja" data-theme="light"></div>
				<?php endif; ?>
				<p class="uk-text-center">
					<button type="submit" class="btn btn-raised btn-lg btn-primary uk-button-large">
						<?php esc_html_e( '購読する', 'kyom' ); ?>
					</button>
				</p>
				</div>
			</fieldset>
			<p class="kyom-newsletter-message" role="status" aria-live="polite"></p>
		</form>
		<noscript>
			<p class="kyom-newsletter-message">
				<?php esc_html_e( 'ご登録には JavaScript が必要です。お手数ですが有効にしてから再度お試しください。', 'kyom' ); ?>
			</p>
		</noscript>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * フッターのニュースレターセクションへのアンカー。
 *
 * フォーム自体の id は描画順の連番（kyom-newsletter-1, -2 …）なので、
 * ショートコードが同一ページにあると番号がずれてアンカー先に使えない。
 * セクション側に固定の id を振り、その参照をここに集約する。
 *
 * @return string Mailchimp が未設定でフォームが出ないときは空文字。
 */
function kyom_newsletter_anchor() {
	if ( ! \Fumikito\Kyom\Service\MailchimpClient::is_ready() ) {
		return '';
	}
	return '#newsletter';
}

/**
 * Display newsletter section at footer.
 */
add_action( 'kyom_before_site_footer', function () {
	$form = kyom_newsletter_form();
	if ( ! $form ) {
		return;
	}
	?>
	<section class="section-newsletter" id="<?php echo esc_attr( ltrim( kyom_newsletter_anchor(), '#' ) ); ?>">
		<div class="section-newsletter-cover"></div>
		<div class="uk-container">
			<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</section>
	<?php
} );

/**
 * Shortcode for newsletter form.
 */
add_shortcode( 'mailchimp', function () {
	return kyom_newsletter_form( 'shortcode' );
} );

/**
 * Enqueue newsletter script.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! \Fumikito\Kyom\Service\MailchimpClient::is_ready() ) {
		return;
	}
	if ( \Fumikito\Kyom\Service\TurnstileClient::is_ready() ) {
		wp_enqueue_script( 'cf-turnstile' );
	}
	wp_enqueue_script( 'kyom-newsletter' );
	wp_localize_script( 'kyom-newsletter', 'KyomNewsletter', [
		'restUrl' => rest_url( 'kyom/v1/newsletter' ),
		'i18n'    => [
			'sending'    => __( '送信中…', 'kyom' ),
			'submit'     => __( '購読する', 'kyom' ),
			'success'    => __( '確認メールをお送りしました。メール内のボタンを押すと登録が完了します。', 'kyom' ),
			'invalid'    => __( 'メールアドレスとお名前をご入力ください。', 'kyom' ),
			'error'      => __( '登録に失敗しました。時間をおいて再度お試しください。', 'kyom' ),
			'tooMany'    => __( '短時間に何度も送信されています。しばらく待ってからお試しください。', 'kyom' ),
			'unverified' => __( '確認が完了していません。少し待ってから、もう一度お試しください。', 'kyom' ),
		],
	] );
} );

/**
 * Register subscription endpoint.
 */
add_action( 'rest_api_init', function () {
	register_rest_route( 'kyom/v1', '/newsletter', [
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'args'                => [
			'email'     => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_email',
			],
			'name'      => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'company'   => [
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'job'       => [
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'website'   => [
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'elapsed'   => [
				'default'           => 0,
				'sanitize_callback' => 'absint',
			],
			'turnstile' => [
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
		],
		'callback'            => function ( WP_REST_Request $request ) {
			// ハニーポットと即時送信はボット確定なので、成功したように見せて捨てる。
			// エラーを返すと何が引っかかったかを教えることになる。
			$looks_like_bot = $request->get_param( 'website' ) || 3000 > $request->get_param( 'elapsed' );
			if ( $looks_like_bot ) {
				return new WP_REST_Response( [ 'message' => __( '確認メールをお送りしました。メール内のボタンを押すと登録が完了します。', 'kyom' ) ], 200 );
			}
			// 同一IPからの連投を止める。
			$ip = kyom_newsletter_client_ip();
			if ( $ip ) {
				$transient_key = 'kyom_nl_' . md5( $ip );
				$attempts      = (int) get_transient( $transient_key );
				if ( 3 <= $attempts ) {
					return new WP_Error( 'kyom_newsletter_too_many', __( '短時間に何度も送信されています。しばらく待ってからお試しください。', 'kyom' ), [ 'status' => 429 ] );
				}
				set_transient( $transient_key, $attempts + 1, HOUR_IN_SECONDS );
			}
			$email = $request->get_param( 'email' );
			$name  = $request->get_param( 'name' );
			if ( ! is_email( $email ) || '' === $name ) {
				return new WP_Error( 'kyom_newsletter_invalid', __( 'メールアドレスとお名前をご入力ください。', 'kyom' ), [ 'status' => 400 ] );
			}
			// Turnstile。キーが両方保存されているときだけ必須になるので、
			// 未設定の環境（ローカルや設定前の本番）でもフォームは壊れない。
			// ハニーポットや elapsed と違い、これはクライアントが自己申告できない。
			if ( \Fumikito\Kyom\Service\TurnstileClient::is_ready() ) {
				$verified = \Fumikito\Kyom\Service\TurnstileClient::verify( $request->get_param( 'turnstile' ), $ip );
				if ( is_wp_error( $verified ) ) {
					// 何が原因で弾かれたかは利用者に見せない。ボットに手がかりを渡さないため。
					// IP を残す。次に汚染されたとき、ログから発信元をまとめられる。
					error_log( sprintf( 'Newsletter Turnstile rejected from %s: %s', $ip ? $ip : 'unknown', $verified->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					$data   = $verified->get_error_data();
					$status = ( is_array( $data ) && ! empty( $data['status'] ) ) ? (int) $data['status'] : 400;
					return new WP_Error( 'kyom_newsletter_unverified', __( '確認が完了していません。少し待ってから、もう一度お試しください。', 'kyom' ), [ 'status' => $status ] );
				}
			}
			$job    = $request->get_param( 'job' );
			$result = \Fumikito\Kyom\Service\MailchimpClient::subscribe(
				$email,
				[
					'FNAME'   => $name,
					'MMERGE2' => $request->get_param( 'company' ),
					'MMERGE3' => in_array( $job, kyom_newsletter_occupations(), true ) ? $job : '',
				],
				$ip
			);
			if ( is_wp_error( $result ) ) {
				// API 側の詳細は利用者に見せず、ログにだけ残す。
				error_log( 'Newsletter subscription failed: ' . $result->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				return new WP_Error( 'kyom_newsletter_failed', __( '登録に失敗しました。時間をおいて再度お試しください。', 'kyom' ), [ 'status' => 500 ] );
			}
			return new WP_REST_Response( [ 'message' => __( '確認メールをお送りしました。メール内のボタンを押すと登録が完了します。', 'kyom' ) ], 200 );
		},
	] );
} );
