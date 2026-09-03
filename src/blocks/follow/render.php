<?php
/**
 * Follow block rendering.
 *
 * 実際のマークアップ生成は kyom_get_follow_html() に集約している。
 * 記事末尾の著者ボックスも同じ関数を通る。
 *
 * @package kyom
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! function_exists( 'kyom_get_follow_html' ) ) {
	return '';
}

echo kyom_get_follow_html( [ // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped
	'show_bio'      => $attributes['showBio'] ?? true,
	'show_archive'  => $attributes['showArchive'] ?? true,
	// align 等の supports をラッパーに反映させる。
	'wrapper_attrs' => get_block_wrapper_attributes( [ 'class' => 'kyom-follow' ] ),
] );
