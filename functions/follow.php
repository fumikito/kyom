<?php
/**
 * Author profile / follow block helpers.
 *
 * サイトの「アクション」を1つに絞って前に出し、他の媒体は細い罫線の副導線に
 * 落とす（デザイン D 案）。どれを主役にするかはカスタマイザーで切り替えられ、
 * 既定は書籍（Amazon の著者ページ）。GA4 の外部リンククリック実測で書籍購入が
 * 最多だったことに基づく。主役に選ばれなかった媒体は自動的に副導線へ回る。
 *
 * 記事末尾の著者ボックス（template-parts/singular-footer-post.php）とブロック
 * kyom/follow の双方が、この共有レンダラーを通る。
 *
 * URL はユーザーの連絡先情報（kyom_get_social_links）が唯一の出所で、
 * 媒体ごとの説明文だけをカスタマイザーで持つ。
 *
 * 計測: リンクには kyom-follow-ch-{媒体キー} を付けている。GA4 の
 * Enhanced Measurement が拾う outbound click の linkClasses は <a> の
 * クラスしか見ないため、どこの何が押されたかをここで判別できるようにする。
 * ニュースレターは内部リンクなので outbound click では拾えない点に注意。
 *
 * @package kyom
 */

/**
 * 主役の候補になる媒体のキー。表示したい順に並べる。
 *
 * この並びから主役を1つ抜き、残りが副導線になる。
 *
 * @return string[]
 */
function kyom_follow_channel_keys() {
	return apply_filters( 'kyom_follow_channel_keys', [ 'amazon', 'twitter', 'youtube', 'mail' ] );
}

/**
 * 媒体キーの表示名。
 *
 * mail は連絡先情報のキーではないので個別に持つ。
 *
 * @param string $key 媒体キー。
 * @return string
 */
function kyom_follow_channel_label( $key ) {
	if ( 'mail' === $key ) {
		return __( 'Newsletter', 'kyom' );
	}
	return kyom_social_label( $key );
}

/**
 * 主アクションに据える媒体のキー。カスタマイザーで切り替える。
 *
 * @return string
 */
function kyom_follow_primary_key() {
	$keys  = kyom_follow_channel_keys();
	$saved = (string) get_option( 'kyom_follow_primary', '' );
	$key   = in_array( $saved, $keys, true ) ? $saved : (string) reset( $keys );
	return apply_filters( 'kyom_follow_primary_key', $key );
}

/**
 * 副導線に並べる媒体のキー。候補から主役を差し引いたもの。
 *
 * @return string[]
 */
function kyom_follow_secondary_keys() {
	$primary = kyom_follow_primary_key();
	$keys    = array_values( array_filter(
		kyom_follow_channel_keys(),
		function ( $key ) use ( $primary ) {
			return $key !== $primary;
		}
	) );
	return apply_filters( 'kyom_follow_secondary_keys', $keys );
}

/**
 * 主アクション＋副導線のキー。アイコン列から除外する判定に使う。
 *
 * @return string[]
 */
function kyom_follow_featured_keys() {
	return kyom_follow_channel_keys();
}

/**
 * 主役の設定値を検証する。
 *
 * @param string $value 入力値。
 * @return string 候補外なら既定値。
 */
function kyom_sanitize_follow_primary( $value ) {
	$keys = kyom_follow_channel_keys();
	return in_array( $value, $keys, true ) ? $value : (string) reset( $keys );
}

/**
 * 媒体キーに対応する uk-icon 名を返す。
 *
 * @param string $key 媒体キー。
 * @param string $url 対象URL。
 * @return string
 */
function kyom_follow_icon( $key, $url = '' ) {
	if ( 'mail' === $key ) {
		return 'mail';
	}
	$icon = $url ? kyom_icon_from_url( $url ) : 'link';
	if ( 'link' === $icon && in_array( $key, kyom_social_keys(), true ) ) {
		// URL から判定できなくてもキー自体が媒体名ならそれを使う。
		$icon = kyom_icon_name( $key );
	}
	return $icon;
}

/**
 * 副導線の行末に出す動詞。
 *
 * @param string $key 媒体キー。
 * @return string
 */
function kyom_follow_action_label( $key ) {
	switch ( $key ) {
		case 'amazon':
			$label = __( 'Browse', 'kyom' );
			break;
		case 'youtube':
			$label = __( 'Watch', 'kyom' );
			break;
		case 'mail':
			$label = __( 'Subscribe', 'kyom' );
			break;
		default:
			$label = __( 'Follow', 'kyom' );
			break;
	}
	return apply_filters( 'kyom_follow_action_label', $label, $key );
}

/**
 * 主アクションのボタン文言。
 *
 * 媒体によって動詞が変わる。書籍は「フォロー」ではなく「著作を見る」。
 *
 * @param string $key   媒体キー。
 * @param string $label 媒体の表示名。
 * @return string
 */
function kyom_follow_primary_label( $key, $label ) {
	switch ( $key ) {
		case 'amazon':
			// translators: %s is a channel name like Amazon.
			$text = sprintf( __( 'See books on %s', 'kyom' ), $label );
			break;
		default:
			// translators: %s is a channel name like X.
			$text = sprintf( __( 'Follow on %s', 'kyom' ), $label );
			break;
	}
	return apply_filters( 'kyom_follow_primary_label', $text, $key, $label );
}

/**
 * URL からアカウント名（@つき）を推測する。
 *
 * X のボタンにハンドルを添えるために使う。判定できなければ空文字。
 *
 * @param string $url 対象URL。
 * @return string
 */
function kyom_follow_handle_from_url( $url ) {
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	$path = trim( $path, '/' );
	if ( ! $path || false !== strpos( $path, '/' ) ) {
		return '';
	}
	return '@' . ltrim( $path, '@' );
}

/**
 * 一媒体ぶんの情報を組み立てる。
 *
 * @param string $key       媒体キー。
 * @param array  $link      kyom_get_social_links() の1要素。
 * @param bool   $with_desc 説明文を含めるか。
 * @return array
 */
function kyom_follow_build_channel( $key, $link, $with_desc = true ) {
	return [
		'key'         => $key,
		'label'       => $link['label'],
		'url'         => $link['url'],
		'description' => $with_desc ? (string) get_option( 'kyom_follow_desc_' . $key, '' ) : '',
		'icon'        => kyom_follow_icon( $key, $link['url'] ),
		'action'      => kyom_follow_action_label( $key ),
		'handle'      => kyom_follow_handle_from_url( $link['url'] ),
	];
}

/**
 * 表示する媒体をまとめて返す。
 *
 * @param WP_User|null $user      対象ユーザー。null ならサイト代表ユーザー。
 * @param bool         $with_desc 説明文を含めるか。
 * @return array primary / secondary / extras を持つ配列。
 */
function kyom_get_follow_data( $user = null, $with_desc = true ) {
	$links       = kyom_get_social_links( $user, true );
	$primary_key = kyom_follow_primary_key();
	$featured    = kyom_follow_featured_keys();
	$data        = [
		'primary'   => null,
		'secondary' => [],
		'extras'    => [],
	];
	if ( ! empty( $links[ $primary_key ]['url'] ) ) {
		$data['primary'] = kyom_follow_build_channel( $primary_key, $links[ $primary_key ], $with_desc );
	}
	foreach ( kyom_follow_secondary_keys() as $key ) {
		if ( empty( $links[ $key ]['url'] ) ) {
			continue;
		}
		$data['secondary'][] = kyom_follow_build_channel( $key, $links[ $key ], $with_desc );
	}
	foreach ( $links as $key => $link ) {
		if ( in_array( $key, $featured, true ) || empty( $link['url'] ) ) {
			continue;
		}
		$data['extras'][] = [
			'key'   => $key,
			'label' => $link['label'],
			'url'   => $link['url'],
			'icon'  => kyom_follow_icon( $key, $link['url'] ),
		];
	}
	return $data;
}

/**
 * ユーザーの肩書き。
 *
 * @param WP_User|null $user 対象ユーザー。
 * @return string 未設定なら空文字。
 */
function kyom_get_user_role_title( $user ) {
	if ( ! $user ) {
		return '';
	}
	return (string) get_user_meta( $user->ID, KYOM_USER_ROLE_TITLE_KEY, true );
}

/**
 * ユーザーの活動開始年。登録日から導出する。
 *
 * @param WP_User|null $user 対象ユーザー。
 * @return string 4桁の年。取れなければ空文字。
 */
function kyom_get_user_since( $user ) {
	if ( ! $user || empty( $user->user_registered ) ) {
		return '';
	}
	return mysql2date( 'Y', $user->user_registered );
}

/**
 * 著者プロフィール／フォロー導線のHTMLを返す。
 *
 * @param array $args {
 *     @type WP_User|null $user          対象ユーザー。null ならサイト代表ユーザー。
 *     @type string       $wrapper_attrs ラッパーに直接出す属性文字列。ブロックからは
 *                                       get_block_wrapper_attributes() の戻り値を渡す。
 *     @type string       $class         追加のクラス名。$wrapper_attrs 指定時は無視。
 *     @type bool         $show_bio      紹介文を出すか。
 *     @type bool         $show_archive  「記事一覧をすべて見る」を出すか。
 *     @type int          $heading_level 名前の見出しレベル。既定は 2。
 * }
 * @return string 表示すべきものがなければ空文字。
 */
function kyom_get_follow_html( $args = [] ) {
	$args = wp_parse_args( $args, [
		'user'          => null,
		'wrapper_attrs' => '',
		'class'         => '',
		'show_bio'      => true,
		'show_archive'  => true,
		'heading_level' => 2,
	] );

	$user = $args['user'] ? $args['user'] : kyom_get_owner();
	if ( ! $user ) {
		return '';
	}
	$owner = kyom_get_owner();
	// 媒体ごとの説明はサイト所有者のチャンネルについて書かれた文言なので、
	// 他の著者（Madame Claude 等）には出さない。
	$with_desc = $owner && (int) $user->ID === (int) $owner->ID;
	$data      = kyom_get_follow_data( $user, $with_desc );
	if ( ! $data['primary'] && ! $data['secondary'] && ! $data['extras'] ) {
		return '';
	}

	$heading = 'h' . max( 2, min( 6, (int) $args['heading_level'] ) );
	$role    = kyom_get_user_role_title( $user );
	$since   = kyom_get_user_since( $user );
	$meta    = array_filter( [
		$role,
		// translators: %s is a 4 digit year.
		$since ? sprintf( __( 'SINCE %s', 'kyom' ), $since ) : '',
	] );
	if ( $args['wrapper_attrs'] ) {
		$wrapper = $args['wrapper_attrs'];
	} else {
		$classes = array_filter( [ 'kyom-follow', $args['class'] ] );
		$wrapper = sprintf( 'class="%s"', esc_attr( implode( ' ', $classes ) ) );
	}

	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped ?>>

		<div class="kyom-follow-bar">
			<span class="kyom-follow-bar-label"><?php esc_html_e( 'PROFILE', 'kyom' ); ?></span>
			<?php if ( $meta ) : ?>
				<span class="kyom-follow-bar-meta"><?php echo esc_html( implode( ' / ', $meta ) ); ?></span>
			<?php endif; ?>
		</div>

		<div class="kyom-follow-body">

			<div class="kyom-follow-portrait">
				<?php echo get_avatar( $user->ID, 560, '', $user->display_name, [ 'class' => 'kyom-follow-avatar' ] ); ?>
			</div>

			<div class="kyom-follow-content">

				<p class="kyom-follow-eyebrow"><?php esc_html_e( 'WRITTEN BY', 'kyom' ); ?></p>

				<<?php echo esc_html( $heading ); ?> class="kyom-follow-name"><?php echo esc_html( $user->display_name ); ?></<?php echo esc_html( $heading ); ?>>

				<?php if ( $role ) : ?>
					<p class="kyom-follow-role"><?php echo esc_html( $role ); ?></p>
				<?php endif; ?>

				<?php if ( $args['show_bio'] && $user->description ) : ?>
					<div class="kyom-follow-bio"><?php echo wp_kses_post( wpautop( $user->description ) ); ?></div>
				<?php endif; ?>

				<?php if ( $data['primary'] ) : ?>
					<div class="kyom-follow-primary">
						<a class="kyom-follow-button kyom-follow-ch-<?php echo esc_attr( $data['primary']['key'] ); ?>"
							href="<?php echo esc_url( $data['primary']['url'] ); ?>">
							<span class="kyom-follow-button-label">
								<?php echo esc_html( kyom_follow_primary_label( $data['primary']['key'], $data['primary']['label'] ) ); ?>
							</span>
							<?php if ( $data['primary']['handle'] ) : ?>
								<span class="kyom-follow-button-handle"><?php echo esc_html( $data['primary']['handle'] ); ?></span>
							<?php endif; ?>
						</a>
						<?php if ( $data['primary']['description'] ) : ?>
							<p class="kyom-follow-caption"><?php echo esc_html( $data['primary']['description'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $data['secondary'] ) : ?>
					<ul class="kyom-follow-list">
						<?php foreach ( $data['secondary'] as $channel ) : ?>
							<li class="kyom-follow-item kyom-follow-item-<?php echo esc_attr( $channel['key'] ); ?>">
								<a class="kyom-follow-link kyom-follow-ch-<?php echo esc_attr( $channel['key'] ); ?>"
									href="<?php echo esc_url( $channel['url'] ); ?>">
									<span class="kyom-follow-text">
										<span class="kyom-follow-label"><?php echo esc_html( $channel['label'] ); ?></span>
										<?php if ( $channel['description'] ) : ?>
											<span class="kyom-follow-desc"><?php echo esc_html( $channel['description'] ); ?></span>
										<?php endif; ?>
									</span>
									<span class="kyom-follow-action"><?php echo esc_html( $channel['action'] ); ?> &rarr;</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $data['extras'] ) : ?>
					<ul class="kyom-follow-extras uk-iconnav">
						<?php foreach ( $data['extras'] as $extra ) : ?>
							<li>
								<a class="kyom-follow-extra kyom-follow-ch-<?php echo esc_attr( $extra['key'] ); ?>"
									href="<?php echo esc_url( $extra['url'] ); ?>" title="<?php echo esc_attr( $extra['label'] ); ?>"
									uk-icon="icon: <?php echo esc_attr( $extra['icon'] ); ?>"></a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $args['show_archive'] ) : ?>
					<p class="kyom-follow-archive">
						<a href="<?php echo esc_url( get_author_posts_url( $user->ID ) ); ?>">
							<?php esc_html_e( 'See all posts', 'kyom' ); ?> &rarr;
						</a>
					</p>
				<?php endif; ?>

			</div>
		</div>
	</section>
	<?php
	return trim( ob_get_clean() );
}

/**
 * 著者プロフィール／フォロー導線を出力する。
 *
 * @param array $args kyom_get_follow_html() と同じ。
 * @return void
 */
function kyom_the_follow( $args = [] ) {
	echo kyom_get_follow_html( $args ); // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped
}
