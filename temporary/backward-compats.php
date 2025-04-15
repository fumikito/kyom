<?php
/**
 * Should be removed because it's too specific for takahashifumiki.com
 */

/**
 * Add ebook post type.
 */
add_action( 'init', function () {
	if ( function_exists( 'lwp_files' ) ) {
		return;
	}
	register_post_type( 'ebook', [
		'label'    => '電子書籍',
		'public'   => true,
		'supports' => [ 'title', 'editor', 'author', 'custom-fields' ],
	] );
} );

/**
 * Get mail chimp URL.
 *
 * @return string
 */
function fumiki_mailchimp_url() {
	return add_query_arg( [
		'u'          => '9b5777bb4451fb83373411d34',
		'id'         => 'bf9c92d04a',
		'REGISTERED' => urlencode( date_i18n( 'Y-m-d' ) ),
		'SOURCE'     => urlencode( 'takahashifumiki.com' ),
	], 'https://takahashifumiki.us14.list-manage.com/subscribe/post' );
}

add_action( 'kyom_before_site_footer', function () {
	?>
	<section class="section-newsletter">
		<div class="section-newsletter-cover"></div>
		<div class="uk-container">

			<h2 class="section-newsletter-title">高橋文樹ニュースレター</h2>
			
			<p class="section-newsletter-lead">
				高橋文樹が最近の活動報告、サイトでパブリックにできない情報などをお伝えするメーリングリストです。
				滅多に送りませんので、ぜひご登録お願いいたします。
				お得なダウンロードコンテンツなども計画中です。
			</p>
			
			<form
					action="<?php echo esc_attr( fumiki_mailchimp_url() ); ?>"
					method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form"
					class="validate wpcf7-form"
					target="_blank" novalidate>
					<p class="section-newsletter-mail-input">
						<input type="email" value="" name="EMAIL" class="required email form-control" id="mce-EMAIL"
								placeholder="e.g. info@takahashifumiki.com">
					</p>

				<fieldset class="uk-fieldset section-newsletter-extra">
					<div>
					
					<div class="uk-grid" uk-grid>
					
					<div class="form-group">
						<label for="mce-FNAME">お名前 </label>
						<input type="text" value="" name="FNAME" class="form-control" id="mce-FNAME"
								placeholder="e.g. 高橋文樹">
					</div>
					<div class="form-group">
						<label for="mce-MMERGE2">会社・団体 </label>
						<input type="text" value="" name="MMERGE2" class="form-control" id="mce-MMERGE2"
								placeholder="e.g. 株式会社破滅派">
					</div>
					</div>
					<div class="form-group">
						<?php
						foreach (
							[
								'出版関連',
								'編集者',
								'作家・ライター',
								'Web関連',
								'学生',
								'その他',
							] as $index => $label
						) :
							?>
							<label for="mce-MMERGE3-<?php echo $index; ?>" class="inline-label">
								<input class="uk-radio" type="radio" value="<?php echo esc_attr( $label ); ?>" name="MMERGE3"
										id="mce-MMERGE3-<?php echo $index; ?>">
								<?php echo esc_html( $label ); ?>
							</label>
						<?php endforeach; ?>
					</div>
					<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
					<div style="position: absolute; left: -5000px;" aria-hidden="true">
						<input type="text" name="b_9b5777bb4451fb83373411d34_bf9c92d04a" tabindex="-1" value="">
					</div>
					<p class="uk-text-center">
						<input type="submit" value="購読する" name="subscribe" id="mc-embedded-subscribe"
								class="btn btn-raised btn-lg btn-primary uk-button-large">
					</p>
					</div>
				</fieldset>
			</form>
		</div>
	</section>
	<?php
} );

add_shortcode( 'mailchimp', function ( $atts = [], $contnet = '' ) {
	ob_start();
	?>
	<!-- Begin MailChimp Signup Form -->
	<div id="mc_embed_signup">
		<form
			action="<?php echo esc_attr( fumiki_mailchimp_url() ); ?>"
			method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate wpcf7-form"
			target="_blank" novalidate>
			
			<fieldset class="uk-fieldset">
				
				<legend class="uk-legend">ニュースレターのご購読</legend>
				<p class="description"><span class="asterisk text-danger">*</span> は必須項目です。</p>
				<p class="form-group">
					<label for="mce-EMAIL">メール <span class="asterisk text-danger">*</span>
					</label>
					<input type="email" value="" name="EMAIL" class="required email form-control" id="mce-EMAIL" placeholder="e.g. info@takahashifumiki.com">
				</p>
				<p class="form-group">
					<label for="mce-FNAME">お名前 </label>
					<input type="text" value="" name="FNAME" class="form-control" id="mce-FNAME" placeholder="e.g. 高橋文樹">
				</p>
				<p class="form-group">
					<label for="mce-MMERGE2">会社・団体 </label>
					<input type="text" value="" name="MMERGE2" class="form-control" id="mce-MMERGE2" placeholder="e.g. 株式会社破滅派">
				</p>
				<div class="form-group">
					<?php
					foreach ( [
						'出版関連',
						'編集者',
						'作家・ライター',
						'Web関連',
						'学生',
						'その他',
					] as $index => $label ) :
						?>
						<label for="mce-MMERGE3-<?php echo $index; ?>">
							<input class="uk-radio" type="radio" value="<?php echo esc_attr( $label ); ?>" name="MMERGE3" id="mce-MMERGE3-<?php echo $index; ?>">
							<?php echo esc_html( $label ); ?>
						</label>
					<?php endforeach; ?>
				</div>
				<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
				<div style="position: absolute; left: -5000px;" aria-hidden="true">
					<input type="text" name="b_9b5777bb4451fb83373411d34_bf9c92d04a" tabindex="-1" value="">
				</div>
				<p class="text-center">
					<input type="submit" value="購読する" name="subscribe" id="mc-embedded-subscribe"
							class="btn btn-raised btn-lg btn-primary">
				</p>
			</fieldset>
			<aside>
				※フォームを送信すると、Mailchimpというサービスに移動します。
			</aside>
		</form>
	</div>
	<!--End mc_embed_signup-->
	<?php
	$form = ob_get_contents();
	ob_end_clean();

	return implode( "\n", array_filter( array_map( function ( $line ) {
		return trim( $line );
	}, explode( "\n", $form ) ) ) );
} );
