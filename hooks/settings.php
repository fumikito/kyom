<?php
/*
 * Brand related functions.
 *
 * @package kyom
 */

// Register customizer.
add_action( 'after_setup_theme', function () {
	$dir = dirname( __DIR__ ) . '/app/Fumikito/Kyom/Customizer';
	if ( ! is_dir( $dir ) ) {
		return;
	}
	foreach ( scandir( $dir ) as $file ) {
		if ( ! preg_match( '#^(.*)\.php$#u', $file, $matches ) ) {
			continue;
		}
		$class_name = "Fumikito\\Kyom\\Customizer\\{$matches[1]}";
		if ( ! class_exists( $class_name ) ) {
			trigger_error( 'Customizer class not found: ' . $class_name );
			continue;
		}
		call_user_func( "{$class_name}::get_instance" );
	}
} );

// Register settings.
add_action( 'admin_init', function () {
	$settings = [
		'kyom_brand'        => [
			'label'       => __( 'Site Brand', 'kyom' ),
			'description' => __( 'Brand setting for widgets and others.', 'kyom' ),
			'page'        => 'general',
			'options'     => [
				'long_desc' => [
					'label'       => __( 'Site long description', 'kyom' ),
					'description' => __( 'A little bit long description to describe your brand. Longer than tag line and less than 140 characters.', 'kyom' ),
					'type'        => 'textarea',
				],
				'address'   => [
					'label' => __( 'Address', 'kyom' ),
					'type'  => 'textarea',
				],
				'tel'       => [
					'label' => __( 'Tel', 'kyom' ),
					'type'  => 'tel',
				],
				'mail'      => [
					'label' => __( 'Email' ),
					'type'  => 'email',
				],
			],
		],
		'kyom_notification' => [
			'label'       => __( 'Notification', 'kyom' ),
			'description' => __( 'Notification displayed sitewide', 'kyom' ),
			'page'        => 'reading',
			'options'     => [
				'callout_text'     => [
					'label'       => __( 'Content' ),
					'description' => __( 'Text link and strong tags are allowed.', 'kyom' ),
					'type'        => 'text',
				],
				'callout_style'    => [
					'type'    => 'select',
					'label'   => __( 'Style', 'kyom' ),
					'options' => [
						'primary' => __( 'Blue(blue)', 'kyom' ),
						'success' => __( 'Success(green)', 'kyom' ),
						'warning' => __( 'Warning(orange)', 'kyom' ),
						'danger'  => __( 'Danger(Red)', 'kyom' ),
					],
				],
				'show_live_stream' => [
					'type'        => 'number',
					'label'       => __( 'Display YouTube Live', 'kyom' ),
					'description' => __( 'If set, scheduled live stream within this preiod(in days) will be called out at footer.', 'kyom' ),
				],
			],
		],
		'kyom_youtube'      => [
			'label'       => 'YouTube',
			'description' => __( 'YouTube channel related to your site.', 'kyom' ),
			'page'        => 'general',
			'options'     => [
				'youtube_api_key'        => [
					'label'       => __( 'API Key', 'kyom' ),
					'description' => __( 'API Key of Google Cloud Platform. YouTube data API v3 is required to be included in libraries.', 'kyom' ),
					'type'        => 'text',
				],
				'youtube_channel_id'     => [
					'label'       => __( 'Channel ID', 'kyom' ),
					'description' => __( 'Your YouTube channel ID. You can get it from URL of YouTube studio. If both values are valid, you can get Channel Detail below.', 'kyom' ),
					'type'        => 'text',
				],
				'youtube_shorts_max_age' => [
					'label'       => __( 'Shorts Freshness (days)', 'kyom' ),
					'description' => __( 'Shorts older than this are not displayed. If nothing is fresh enough, the section disappears instead of advertising a dormant channel. Default is 60.', 'kyom' ),
					'type'        => 'number',
				],
			],
		],
		'kyom_mailchimp'    => [
			'label'       => 'Mailchimp',
			'description' => __( 'Newsletter subscription is sent to Mailchimp through this site, so that the audience ID is never exposed to bots.', 'kyom' ),
			'page'        => 'general',
			'options'     => [
				'mailchimp_api_key' => [
					'label'       => __( 'API Key', 'kyom' ),
					'description' => __( 'Issue a dedicated key from Mailchimp (Account &amp; billing &gt; Extras &gt; API keys). Defining <code>KYOM_MAILCHIMP_API_KEY</code> in wp-config.php takes precedence over this field and is recommended for production.', 'kyom' ),
					'type'        => 'password',
				],
				'mailchimp_list_id' => [
					'label'       => __( 'Audience ID', 'kyom' ),
					'description' => __( 'Audience(list) ID of Mailchimp. If both values are valid, audience detail is displayed below.', 'kyom' ),
					'type'        => 'text',
				],
			],
		],
		'kyom_turnstile'    => [
			'label'       => 'Cloudflare Turnstile',
			'description' => __( 'Bot protection for the newsletter form. Once both keys are saved, a subscription without a valid token is rejected.', 'kyom' ),
			'page'        => 'general',
			'options'     => [
				'turnstile_site_key'   => [
					'label'       => __( 'Site Key', 'kyom' ),
					'description' => __( 'Create a widget at Cloudflare dashboard &gt; Turnstile. <strong>Managed</strong> mode is recommended. This value is embedded in HTML and is not a secret.', 'kyom' ),
					'type'        => 'text',
				],
				'turnstile_secret_key' => [
					'label'       => __( 'Secret Key', 'kyom' ),
					'description' => __( 'Issued together with the site key. Defining <code>KYOM_TURNSTILE_SECRET_KEY</code> in wp-config.php takes precedence over this field and is recommended for production.', 'kyom' ),
					'type'        => 'password',
				],
			],
		],
	];
	foreach ( $settings as $id => $setting ) {
		// Register section.
		$desc = $setting['description'];
		add_settings_section( $id, $setting['label'], function () use ( $desc ) {
			printf( '<p class="description">%s</p>', esc_html( $desc ) );
		}, $setting['page'] );
		// Register values.
		foreach ( $setting['options'] as $key => $option ) {
			$option = wp_parse_args( $option, [
				'label'       => '',
				'type'        => 'text',
				'placeholder' => '',
				'option'      => '',
				'description' => '',
				'rows'        => 3,
			] );
			// Display inputs.
			add_settings_field( $key, $option['label'], function () use ( $key, $option ) {
				echo '<div class="kyom-admin-setting">';
				switch ( $option['type'] ) {
					case 'textarea':
						printf(
							'<p class="kyom-admin-setting-field"><textarea id="%1$s" name="%1$s" rows="%3$d">%2$s</textarea></p>',
							'kyom_' . $key,
							esc_textarea( get_option( 'kyom_' . $key, '' ) ),
							$option['rows']
						);
						break;
					case 'text':
					case 'email':
					case 'url':
					case 'number':
					case 'tel':
					case 'password':
						printf(
							'<p class="kyom-admin-setting-field"><input id="%1$s" name="%1$s" type="%3$s" value="%2$s" /></p>',
							'kyom_' . $key,
							esc_attr( get_option( 'kyom_' . $key, '' ) ),
							esc_attr( $option['type'] )
						);
						break;
					case 'select':
						printf( '<select name="%s">', esc_attr( 'kyom_' . $key ) );
						foreach ( $option['options'] as $value => $label ) {
							printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( get_option( 'kyom_' . $key ), $value, false ), esc_html( $label ) );
						}
						echo '</select>';
						break;
				}
				if ( $option['description'] ) {
					printf( '<p class="description">%s</p>', wp_kses_post( $option['description'] ) );
				}
				if ( 'youtube_channel_id' === $key ) {
					$result = kyom_get_youtube_channel();
					if ( is_wp_error( $result ) ) {
						printf( '<p class="wp-ui-text-notification">%s</p>', esc_html( $result->get_error_message() ) );
					} else {
						printf( '<p class="description">%s: <strong>%s</strong></p>', esc_html__( 'Channel Information', 'kyom' ), esc_html( $result['snippet']['title'] ) );
					}
				}
				if ( 'mailchimp_list_id' === $key && \Fumikito\Kyom\Service\MailchimpClient::is_ready() ) {
					// 認証情報が正しいかを画面で確認できるようにする。
					$result = \Fumikito\Kyom\Service\MailchimpClient::get_list();
					if ( is_wp_error( $result ) ) {
						printf( '<p class="wp-ui-text-notification">%s</p>', esc_html( $result->get_error_message() ) );
					} else {
						printf(
							'<p class="description">%s: <strong>%s</strong>(%s)</p>',
							esc_html__( 'Audience Information', 'kyom' ),
							esc_html( $result['name'] ),
							esc_html( sprintf(
								// translators: %1$d is subscribed count, %2$s is 'enabled' or 'disabled'.
								__( 'subscribers: %1$d, double opt-in: %2$s', 'kyom' ),
								$result['stats']['member_count'],
								empty( $result['double_optin'] ) ? __( 'disabled', 'kyom' ) : __( 'enabled', 'kyom' )
							) )
						);
					}
				}
				if ( 'turnstile_secret_key' === $key ) {
					// キーの正しさはトークンが無いと検証できない。ここで出せるのは設定の充足状態だけ。
					$site   = \Fumikito\Kyom\Service\TurnstileClient::site_key();
					$secret = \Fumikito\Kyom\Service\TurnstileClient::secret_key();
					if ( $site && $secret ) {
						printf( '<p class="description"><strong>%s</strong></p>', esc_html__( 'Enabled. The newsletter form now requires Turnstile.', 'kyom' ) );
					} elseif ( $site || $secret ) {
						printf( '<p class="wp-ui-text-notification">%s</p>', esc_html__( 'Both the site key and the secret key are required. Turnstile is not working with only one of them.', 'kyom' ) );
					} else {
						printf( '<p class="wp-ui-text-notification">%s</p>', esc_html__( 'Not configured. The newsletter form is accepting subscriptions without Turnstile.', 'kyom' ) );
					}
				}
				echo '</div>';
			}, $setting['page'], $id );
			// Register settings.
			register_setting( $setting['page'], 'kyom_' . $key );
		}
	}
} );
