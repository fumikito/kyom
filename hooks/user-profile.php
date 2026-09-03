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
