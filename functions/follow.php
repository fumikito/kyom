<?php
/**
 * Follow action block helpers.
 *
 * サイトの「アクション」＝フォローしてもらうことなので、
 * その導線をブロックとテンプレートの双方から同じマークアップで出せるようにする。
 *
 * URL はサイト代表ユーザーの連絡先情報（kyom_get_social_links）を唯一の出所とし、
 * 「なぜフォローすべきか」の文言だけをカスタマイザーで持つ。
 *
 * @package kyom
 */

/**
 * 大きく見せる媒体のキー。
 *
 * 読者の心理的ハードルが低い順に並べる。
 * twitter / youtube は連絡先情報のキー、mail は kyom_get_social_links が
 * ニュースレターページから合成する擬似キー。
 *
 * @return string[]
 */
function kyom_follow_featured_keys() {
	return apply_filters( 'kyom_follow_featured_keys', [ 'twitter', 'youtube', 'mail' ] );
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
		$icon = $key;
	}
	return $icon;
}

/**
 * フォロー導線で大きく見せる媒体の一覧。
 *
 * URL が未設定の媒体は含めない。
 *
 * @param string[] $keys 絞り込むキー。空なら kyom_follow_featured_keys() を使う。
 * @return array[] key/label/url/description/icon を持つ配列。
 */
function kyom_get_follow_channels( $keys = [] ) {
	$featured = kyom_follow_featured_keys();
	if ( $keys ) {
		// 指定順ではなく既定の並び（ハードルの低い順）を保つ。
		$featured = array_values( array_intersect( $featured, $keys ) );
	}
	$links    = kyom_get_social_links( null, true );
	$channels = [];
	foreach ( $featured as $key ) {
		if ( empty( $links[ $key ]['url'] ) ) {
			continue;
		}
		$channels[] = [
			'key'         => $key,
			'label'       => $links[ $key ]['label'],
			'url'         => $links[ $key ]['url'],
			'description' => (string) get_option( 'kyom_follow_desc_' . $key, '' ),
			'icon'        => kyom_follow_icon( $key, $links[ $key ]['url'] ),
		];
	}
	return $channels;
}

/**
 * 大きく見せる媒体以外の連絡先。アイコン列にまとめる用。
 *
 * @return array[] key/label/url/icon を持つ配列。
 */
function kyom_get_follow_extras() {
	$featured = kyom_follow_featured_keys();
	$extras   = [];
	foreach ( kyom_get_social_links( null, true ) as $key => $link ) {
		if ( in_array( $key, $featured, true ) || empty( $link['url'] ) ) {
			continue;
		}
		$extras[] = [
			'key'   => $key,
			'label' => $link['label'],
			'url'   => $link['url'],
			'icon'  => kyom_follow_icon( $key, $link['url'] ),
		];
	}
	return $extras;
}

/**
 * フォロー導線のHTMLを返す。
 *
 * ブロックの render.php とテンプレートの双方から呼ぶ共有レンダラー。
 *
 * @param array $args {
 *     @type string   $title         見出し。未指定ならカスタマイザーの値。
 *     @type string   $lead          リード文。未指定ならカスタマイザーの値。
 *     @type string[] $keys          表示する媒体の絞り込み。空なら全部。
 *     @type string   $class         追加のクラス名。
 *     @type string   $wrapper_attrs ラッパーに直接出す属性文字列。ブロックからは
 *                                   get_block_wrapper_attributes() の戻り値を渡す。
 *                                   指定時は $class より優先される。
 * }
 * @return string 表示すべき媒体がなければ空文字。
 */
function kyom_get_follow_html( $args = [] ) {
	$args = wp_parse_args( $args, [
		'title'         => '',
		'lead'          => '',
		'keys'          => [],
		'class'         => '',
		'wrapper_attrs' => '',
	] );

	$channels = kyom_get_follow_channels( $args['keys'] );
	if ( ! $channels ) {
		return '';
	}

	$title  = $args['title'] ? $args['title'] : (string) get_option( 'kyom_follow_title', '' );
	$lead   = $args['lead'] ? $args['lead'] : (string) get_option( 'kyom_follow_lead', '' );
	$extras = $args['keys'] ? [] : kyom_get_follow_extras();
	if ( $args['wrapper_attrs'] ) {
		$wrapper = $args['wrapper_attrs'];
	} else {
		$classes = array_filter( [ 'kyom-follow', $args['class'] ] );
		$wrapper = sprintf( 'class="%s"', esc_attr( implode( ' ', $classes ) ) );
	}

	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped ?>>
		<?php if ( $title ) : ?>
			<h2 class="kyom-follow-title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		<?php if ( $lead ) : ?>
			<p class="kyom-follow-lead"><?php echo wp_kses_post( $lead ); ?></p>
		<?php endif; ?>
		<ul class="kyom-follow-list">
			<?php foreach ( $channels as $channel ) : ?>
				<li class="kyom-follow-item kyom-follow-item-<?php echo esc_attr( $channel['key'] ); ?>">
					<a class="kyom-follow-link" href="<?php echo esc_url( $channel['url'] ); ?>">
						<span class="kyom-follow-icon" uk-icon="icon: <?php echo esc_attr( $channel['icon'] ); ?>; ratio: 1.4"></span>
						<span class="kyom-follow-body">
							<span class="kyom-follow-label"><?php echo esc_html( $channel['label'] ); ?></span>
							<?php if ( $channel['description'] ) : ?>
								<span class="kyom-follow-desc"><?php echo esc_html( $channel['description'] ); ?></span>
							<?php endif; ?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php if ( $extras ) : ?>
			<ul class="kyom-follow-extras uk-iconnav">
				<?php foreach ( $extras as $extra ) : ?>
					<li>
						<a href="<?php echo esc_url( $extra['url'] ); ?>" title="<?php echo esc_attr( $extra['label'] ); ?>"
							uk-icon="icon: <?php echo esc_attr( $extra['icon'] ); ?>"></a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>
	<?php
	return trim( ob_get_clean() );
}

/**
 * フォロー導線を出力する。
 *
 * @param array $args kyom_get_follow_html() と同じ。
 * @return void
 */
function kyom_the_follow( $args = [] ) {
	echo kyom_get_follow_html( $args ); // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped
}
