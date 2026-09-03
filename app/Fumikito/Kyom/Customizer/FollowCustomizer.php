<?php

namespace Fumikito\Kyom\Customizer;


use Kunoichi\ThemeCustomizer\CustomizerSetting;

/**
 * フォロー導線のカスタマイザー。
 *
 * URL 自体はユーザーの連絡先情報を出所とするため、ここでは
 * どれを主役にするかと、媒体ごとの説明文だけを持つ。
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
		$keys    = kyom_follow_channel_keys();
		$choices = [];
		foreach ( $keys as $key ) {
			$choices[ $key ] = kyom_follow_channel_label( $key );
		}
		$fields = [
			'kyom_follow_primary' => [
				'label'             => __( 'Primary Action', 'kyom' ),
				'description'       => __( 'This channel gets the big button. The others move to the list below it.', 'kyom' ),
				'type'              => 'select',
				'choices'           => $choices,
				'stored'            => 'option',
				'default'           => (string) reset( $keys ),
				'sanitize_callback' => 'kyom_sanitize_follow_primary',
			],
		];
		// 媒体ごとの役割を書く1行。主役はボタン下のキャプションになる。
		foreach ( $keys as $key ) {
			$fields[ 'kyom_follow_desc_' . $key ] = [
				// translators: %s is a channel name like Amazon.
				'label'             => sprintf( __( 'Description: %s', 'kyom' ), kyom_follow_channel_label( $key ) ),
				'description'       => __( 'Describe what this channel is for.', 'kyom' ),
				'stored'            => 'option',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			];
		}
		return $fields;
	}
}
