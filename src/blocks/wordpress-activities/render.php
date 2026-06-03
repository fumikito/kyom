<?php
/**
 * WordPress Activities block rendering
 *
 * @package kyom
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$user_name        = $attributes['userName'] ?? '';
$user_mail        = $attributes['userMail'] ?? '';
$background_color = $attributes['backgroundColor'] ?? '';
$block_content    = $attributes['content'] ?? '';

if ( ! $user_name ) {
	return '';
}

// WordPressOrgクラスを使用してプロフィール情報を取得
if ( ! class_exists( '\Fumikito\Kyom\Service\WordPressOrg' ) ) {
	return '';
}

$data = \Fumikito\Kyom\Service\WordPressOrg::get_profile( $user_name );

if ( ! $data ) {
	return '';
}

// dashiconsをエンキュー（template_redirectの後の場合）
if ( did_action( 'template_redirect' ) ) {
	wp_enqueue_style( 'dashicons' );
}
?>
<section class="wporg" style="<?php echo $background_color ? esc_attr( sprintf( 'background-color: %s', $background_color ) ) : ''; ?>">
	<div class="uk-container">
		<h2 class="wporg-title uk-text-center">
			<span uk-icon="icon: wordpress; ratio: 3"></span>
			<?php esc_html_e( 'WordPress Activity', 'kyom' ); ?>
		</h2>
		<?php if ( $block_content ) : ?>
		<div class="wporg-lead uk-text-center">
			<?php echo wp_kses_post( wpautop( $block_content ) ); ?>
		</div>
		<?php endif; ?>
		<?php if ( ! empty( $data['badges'] ) ) : ?>
			<div class="wporg-badges">
				<?php
				foreach ( $data['badges'] as $badge ) :
					$label = \Fumikito\Kyom\Service\WordPressOrg::translate_role( $badge['label'] );
					$title = trim( $label . ' ' . ( $badge['year'] ?? '' ) );
					?>
				<span class="wporg-role <?php echo esc_attr( $badge['badge_class'] ); ?>" title="<?php echo esc_attr( $title ); ?>">
					<span class="<?php echo esc_attr( $badge['icon_class'] ); ?>"></span>
					<span class="wporg-role-text uk-visible@s" aria-hidden="true"><?php echo esc_html( $label ); ?></span>
				</span>

				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $data['active_installs_total'] ) ) : ?>
		<div class="wporg-downloads">
			<small><?php esc_html_e( 'Total', 'kyom' ); ?></small>
			<strong><?php echo number_format( $data['active_installs_total'] ); ?></strong>
			<small><?php echo esc_html( _n( 'Active Install', 'Active Installs', $data['active_installs_total'], 'kyom' ) ); ?></small>
			<?php echo \Fumikito\Kyom\Service\WordPressOrg::delivering_item_count( $data, '<span class="wporg-downloads-items">(', ')</span>' ); ?>
		</div>
		<?php endif; ?>
		<?php if ( ! empty( $data['releases']['versions'] ) ) : ?>
		<div class="wporg-releases uk-text-center">
			<h3 class="wporg-releases-title">
				<?php
				printf(
					/* translators: %s: Number of WordPress releases. */
					esc_html( _n( 'Contributed to %s WordPress release', 'Contributed to %s WordPress releases', count( $data['releases']['versions'] ), 'kyom' ) ),
					esc_html( number_format( $data['releases']['count'] ?: count( $data['releases']['versions'] ) ) )
				);
				?>
			</h3>
			<ul class="wporg-releases-versions">
				<?php foreach ( $data['releases']['versions'] as $version ) : ?>
					<li class="wporg-release-chip" title="<?php echo esc_attr( $version['role'] ); ?>"><?php echo esc_html( $version['number'] ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
		<p class="wporg-profile">
			<?php echo get_avatar( $user_mail, 360, '', $user_name, [ 'class' => 'wporg-avatar uk-border-circle' ] ); ?>
			<a href="<?php echo esc_url( \Fumikito\Kyom\Service\WordPressOrg::get_profile_url( $user_name ) ); ?>" class="wporg-profile-link">
				<?php echo esc_html( $user_name ); ?>
			</a>
			<br />
			<small>
				<?php
				printf(
					/* translators: %s: Date */
					esc_html__( 'Member Since %s', 'kyom' ),
					date_i18n( get_option( 'date_format' ), $data['member_since'] )
				);
				?>
			</small>
		</p>
	</div>
</section>
