<?php
/**
 * Hero block rendering
 *
 * @package kyom
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

// 背景スタイルの構築
$style = '';
if ( ! empty( $attributes['background'] ) ) {
	$src = wp_get_attachment_image_url( $attributes['background'], 'full' );
	if ( $src ) {
		$style = sprintf( 'background-image: url(\'%s\')', esc_url( $src ) );
	}
}

// テキストスタイルの構築
$text_color = $attributes['textColor'] ?? '#ffffff';
$text_background = $attributes['textBackground'] ?? '';

$text_style = [sprintf( 'color: %s',$text_color ) ];
if ( $text_background ) {
	$text_style[] = sprintf( 'text-shadow: 0 0 5px %s', $text_background );
}
$text_style = implode( ';', $text_style );

// タイトル取得（デフォルトはサイト名）
$title = $attributes['title'] ?? get_bloginfo( 'name' );

// コンテンツ
$block_content = $attributes['content'] ?? '';
?>
<section class="kyom-hero" style="<?php echo esc_attr( $style ); ?>" uk-parallax="bgy:-300">
	<div class="kyom-hero-box">
		<div class="kyom-hero-container uk-text-center">
			<h1 class="kyom-hero-title" style="<?php echo esc_attr( $text_style ); ?>">
				<?php echo esc_html( $title ); ?>
			</h1>
			<?php if ( $block_content ) : ?>
				<br />
				<div class="kyom-hero-lead" style="<?php echo esc_attr( $text_style ); ?>">
					<?php echo wp_kses_post( wpautop( $block_content ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
