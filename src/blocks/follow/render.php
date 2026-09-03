<?php
/**
 * Follow block rendering.
 *
 * 実際のマークアップ生成は kyom_get_follow_html() に集約している。
 * テンプレートからは kyom_the_follow() で同じものを呼べる。
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
	'title'         => $attributes['title'] ?? '',
	'lead'          => $attributes['lead'] ?? '',
	'keys'          => $attributes['keys'] ?? [],
	// align 等の supports をラッパーに反映させる。
	'wrapper_attrs' => get_block_wrapper_attributes( [ 'class' => 'kyom-follow' ] ),
] );
