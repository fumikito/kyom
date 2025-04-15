<?php

namespace Fumikito\Kyom\Customizer;


use Kunoichi\ThemeCustomizer\CustomizerSetting;

class NewsletterCustomizer extends CustomizerSetting {

	protected $section_id = 'kyom_newsletter_section';

	protected function section_setting() {
		return [
			'title'    => __( 'Newsletter', 'kyom' ),
			'priority' => 10001,
		];
	}

	protected function get_fields(): array {
		return [
			'kyom_mailchimp_popup' => [
				'label'       => __( 'Mailchimp Pop Up Script', 'kyom' ),
				'section'     => 'kyom_newsletter_section',
				'description' => __( 'Pasete pop up script of mail chimp here.', 'kyom' ),
				'type'        => 'textarea',
				'stored'      => 'option',
			],
		];
	}
}
