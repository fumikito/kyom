<?php
/**
 * User profile fields.
 *
 * 著者ボックス（D案）が使う肩書きを、ユーザープロフィールで編集できるようにする。
 * 活動開始年は user_registered から導出するので保存項目は持たない。
 *
 * @package kyom
 */

/**
 * 肩書きを保存するユーザーメタのキー。
 */
const KYOM_USER_ROLE_TITLE_KEY = 'kyom_role_title';

/**
 * 肩書き入力欄をプロフィール画面に出す。
 *
 * @param WP_User $user 対象ユーザー。
 * @return void
 */
function kyom_render_user_role_title_field( $user ) {
	?>
	<h2><?php esc_html_e( 'Author Box', 'kyom' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th>
				<label for="<?php echo esc_attr( KYOM_USER_ROLE_TITLE_KEY ); ?>">
					<?php esc_html_e( 'Role Title', 'kyom' ); ?>
				</label>
			</th>
			<td>
				<input type="text" class="regular-text"
					id="<?php echo esc_attr( KYOM_USER_ROLE_TITLE_KEY ); ?>"
					name="<?php echo esc_attr( KYOM_USER_ROLE_TITLE_KEY ); ?>"
					value="<?php echo esc_attr( get_user_meta( $user->ID, KYOM_USER_ROLE_TITLE_KEY, true ) ); ?>"
					placeholder="<?php esc_attr_e( 'e.g. Novelist', 'kyom' ); ?>" />
				<p class="description">
					<?php esc_html_e( 'Displayed under your name in the author box. Leave empty to hide.', 'kyom' ); ?>
				</p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'kyom_render_user_role_title_field' );
add_action( 'edit_user_profile', 'kyom_render_user_role_title_field' );

/**
 * 肩書きを保存する。
 *
 * @param int $user_id 対象ユーザーID。
 * @return void
 */
function kyom_save_user_role_title_field( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Core verifies the nonce before this hook fires.
	if ( ! isset( $_POST[ KYOM_USER_ROLE_TITLE_KEY ] ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Same as above.
	$value = sanitize_text_field( wp_unslash( $_POST[ KYOM_USER_ROLE_TITLE_KEY ] ) );
	if ( '' === $value ) {
		delete_user_meta( $user_id, KYOM_USER_ROLE_TITLE_KEY );
	} else {
		update_user_meta( $user_id, KYOM_USER_ROLE_TITLE_KEY, $value );
	}
}
add_action( 'personal_options_update', 'kyom_save_user_role_title_field' );
add_action( 'edit_user_profile_update', 'kyom_save_user_role_title_field' );

/**
 * 構造化データ（Person）用のユーザーメタのキー。
 *
 * jobTitle は上の肩書きを流用するので、ここには持たない。
 */
const KYOM_USER_ALTERNATE_NAME_KEY = 'kyom_alternate_name';
const KYOM_USER_WORKS_FOR_NAME_KEY = 'kyom_works_for_name';
const KYOM_USER_WORKS_FOR_URL_KEY  = 'kyom_works_for_url';
const KYOM_USER_SAME_AS_KEY        = 'kyom_same_as';

/**
 * 構造化データの入力欄をプロフィール画面に出す。
 *
 * @param WP_User $user 対象ユーザー。
 * @return void
 */
function kyom_render_user_structured_data_fields( $user ) {
	$fields = [
		KYOM_USER_ALTERNATE_NAME_KEY => [
			'label'       => __( 'Alternate Name', 'kyom' ),
			'type'        => 'text',
			'placeholder' => 'e.g. Fumiki Takahashi',
		],
		KYOM_USER_WORKS_FOR_NAME_KEY => [
			'label' => __( 'Organization Name', 'kyom' ),
			'type'  => 'text',
		],
		KYOM_USER_WORKS_FOR_URL_KEY  => [
			'label' => __( 'Organization URL', 'kyom' ),
			'type'  => 'url',
		],
	];
	?>
	<h2><?php esc_html_e( 'Structured Data', 'kyom' ); ?></h2>
	<p><?php esc_html_e( 'Output as Person structured data of the site owner. Role Title is used as the job title.', 'kyom' ); ?></p>
	<table class="form-table" role="presentation">
		<?php foreach ( $fields as $key => $field ) : ?>
			<tr>
				<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
				<td>
					<input type="<?php echo esc_attr( $field['type'] ); ?>" class="regular-text"
						id="<?php echo esc_attr( $key ); ?>"
						name="<?php echo esc_attr( $key ); ?>"
						value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>"
						placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>" />
				</td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<th><label for="<?php echo esc_attr( KYOM_USER_SAME_AS_KEY ); ?>"><?php esc_html_e( 'Other Profile URLs', 'kyom' ); ?></label></th>
			<td>
				<textarea class="large-text" rows="4"
					id="<?php echo esc_attr( KYOM_USER_SAME_AS_KEY ); ?>"
					name="<?php echo esc_attr( KYOM_USER_SAME_AS_KEY ); ?>"><?php echo esc_textarea( get_user_meta( $user->ID, KYOM_USER_SAME_AS_KEY, true ) ); ?></textarea>
				<p class="description">
					<?php esc_html_e( 'One URL per line, e.g. Wikipedia or Wikidata. Social links in Contact Info are included automatically.', 'kyom' ); ?>
				</p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'kyom_render_user_structured_data_fields' );
add_action( 'edit_user_profile', 'kyom_render_user_structured_data_fields' );

/**
 * 構造化データの入力を保存する。
 *
 * @param int $user_id 対象ユーザーID。
 * @return void
 */
function kyom_save_user_structured_data_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	$sanitizers = [
		KYOM_USER_ALTERNATE_NAME_KEY => 'sanitize_text_field',
		KYOM_USER_WORKS_FOR_NAME_KEY => 'sanitize_text_field',
		KYOM_USER_WORKS_FOR_URL_KEY  => 'esc_url_raw',
		KYOM_USER_SAME_AS_KEY        => function ( $value ) {
			$urls = array_filter( array_map( 'trim', preg_split( '/\R/u', $value ) ), 'kyom_is_profile_url' );
			return implode( "\n", array_map( 'esc_url_raw', $urls ) );
		},
	];
	foreach ( $sanitizers as $key => $sanitizer ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Core verifies the nonce before this hook fires.
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized right below.
		$value = call_user_func( $sanitizer, wp_unslash( $_POST[ $key ] ) );
		if ( '' === $value ) {
			delete_user_meta( $user_id, $key );
		} else {
			update_user_meta( $user_id, $key, $value );
		}
	}
}
add_action( 'personal_options_update', 'kyom_save_user_structured_data_fields' );
add_action( 'edit_user_profile_update', 'kyom_save_user_structured_data_fields' );
