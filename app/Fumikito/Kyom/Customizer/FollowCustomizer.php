<?php

namespace Fumikito\Kyom\Customizer;


use Kunoichi\ThemeCustomizer\CustomizerSetting;

/**
 * フォロー導線のカスタマイザー。
 *
 * URL 自体はサイト代表ユーザーの連絡先情報を出所とするため、
 * ここでは「なぜフォローすべきか」を伝える文言だけを持つ。
 *
 * @package kyom
 */
class FollowCustomizer extends CustomizerSetting {

	protected $section_id = 'kyom_follow_section';

	protected function section_setting() {
		return [
			'title'    => __( 'Follow', 'kyom' ),
			'priority' => 10001,
		];
	}

	protected function get_fields(): array {
		$fields = [];
		// 媒体ごとの役割を書く1行。キーはフォロー導線で大きく見せる媒体に対応する。
		foreach ( kyom_follow_featured_keys() as $key ) {
			$fields[ 'kyom_follow_desc_' . $key ] = [
				// translators: %s is a channel key like twitter.
				'label'             => sprintf( __( 'Description: %s', 'kyom' ), $key ),
				'description'       => __( 'Describe what this channel is for.', 'kyom' ),
				'stored'            => 'option',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			];
		}
		return $fields;
	}
}
