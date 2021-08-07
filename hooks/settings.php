<?php
/*
 * Brand related functions.
 *
 * @package kyom
 */

// Register customizer.
add_action( 'after_setup_theme', function() {
	$dir = dirname( __DIR__ ) . '/app/Fumikito/Kyom/Customizer';
	if ( ! is_dir( $dir ) ) {
		return;
	}
	foreach ( scandir( $dir ) as $file ) {
		if ( ! preg_match( '#^(.*)\.php$#u', $file, $matches ) ) {
			continue;
		}
		$class_name = "Fumikito\\Kyom\\Customizer\\{$matches[1]}";
		if ( ! class_exists( $class_name ) || ! method_exists( $class_name, 'register' ) ) {
			trigger_error( 'Customizer class not found: ' . $class_name );
			continue;
		}
		call_user_func( "{$class_name}::register" );
	}
} );

// Register settings.
add_action( 'admin_init', function () {
	$settings = [
		'kyom_brand' => [
			'label'       => __( 'Site Brand', 'kyom' ),
			'description' => __( 'Brand setting for widgets and others.', 'kyom' ),
			'page'        => 'general',
			'options'     => [
				'long_desc' => [
					'label'       => __( 'Site long description', 'kyom' ),
					'description' => __( 'A little bit long description to describe your brand. Longer than tag line and less than 140 characters.', 'kyom' ),
					'type'        => 'textarea',
				],
				'address' => [
					'label' => __( 'Address', 'kyom' ),
					'type'  => 'textarea',
				],
				'tel' => [
					'label' => __( 'Tel', 'kyom' ),
					'type'  => 'tel',
				],
				'mail' => [
					'label' => __( 'Email' ),
					'type'  => 'email',
				],
			],
		],
		'kyom_notification' => [
			'label' => __( 'Notification', 'kyom' ),
			'description' => __( 'Notification displayed sitewide', 'kyom' ),
			'page' => 'reading',
			'options' => [
				'callout_text' => [
					'label' => __( 'Content' ),
					'description' => __( 'Text link and strong tags are allowed.', 'kyom' ),
					'type' => 'text'
				],
				'callout_style' => [
					'type' => 'select',
					'label' => __( 'Style', 'kyom' ),
					'options' => [
						'primary' => __( 'Blue(blue)', 'kyom' ),
						'success' => __( 'Success(green)', 'kyom' ),
						'warning' => __( 'Warning(orange)', 'kyom' ),
						'danger' => __( 'Danger(Red)', 'kyom' ),
					],
				],
				'show_live_stream' => [
					'type'        => 'number',
					'label'       => __( 'Display YouTube Live', 'kyom' ),
					'description' => __( 'If set, scheduled live stream within this preiod(in days) will be called out at footer.', 'kyom' ),
				],
			],
		],
		'kyom_youtube' => [
			'label'       => 'YouTube',
			'description' => __( 'YouTube channel related to your site.', 'kyom' ),
			'page'        => 'general',
			'options'     => [
				'youtube_api_key' => [
					'label'       => __( 'API Key', 'kyom' ),
					'description' => __( 'API Key of Google Cloud Platform. YouTube data API v3 is required to be included in libraries.', 'kyom' ),
					'type'        => 'text',
				],
				'youtube_channel_id' => [
					'label'       => __( 'Channel ID', 'kyom' ),
					'description' => __( 'Your YouTube channel ID. You can get it from URL of YouTube studio. If both values are valid, you can get Channel Detail below.', 'kyom' ),
					'type'        => 'text',
				],
			],
		],
	];
	foreach ( $settings as $id => $setting ) {
		// Register section.
		$desc = $setting[ 'description' ];
		add_settings_section( $id, $setting[ 'label' ], function () use ( $desc ) {
			printf( '<p class="description">%s</p>', esc_html( $desc ) );
		}, $setting[ 'page' ] );
		// Register values.
		foreach ( $setting[ 'options' ] as $key => $option ) {
			$option = wp_parse_args( $option, [
				'label'       => '',
				'type'        => 'text',
				'placeholder' => '',
				'option'      => '',
				'description' => '',
				'rows'        => 3,
			] );
			// Display inputs.
			add_settings_field( $key, $option[ 'label' ], function () use ( $key, $option ) {
				echo '<div class="kyom-admin-setting">';
				switch ( $option[ 'type' ] ) {
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
				if ( $option[ 'description' ] ) {
					printf( '<p class="description">%s</p>', wp_kses_post( $option[ 'description' ] ) );
				}
				if ( 'youtube_channel_id' === $key ) {
					$result = kyom_get_youtube_channel();
					if ( is_wp_error( $result ) ) {
						printf( '<p class="wp-ui-text-notification">%s</p>', esc_html( $result->get_error_message() ) );
					} else {
						printf( '<p class="description">%s: <strong>%s</strong></p>', esc_html__( 'Channel Information', 'kyom' ), esc_html( $result['snippet']['title']) );
					}
				}
				echo '</div>';
			}, $setting[ 'page' ], $id );
			// Register settings.
			register_setting( $setting['page'], 'kyom_' . $key );
		}
	}


} );
