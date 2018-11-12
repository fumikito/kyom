<?php
/**
 * Should be removed because it's too specific for takahashifumiki.com
 */

/**
 * Add ebook post type.
 */
add_action( 'init', function() {
	if ( function_exists( 'lwp_files' ) ) {
		return;
	}
	register_post_type( 'ebook', [
		'label'  => '電子書籍',
		'public' => true,
	    'supports' => [ 'title', 'editor', 'author', 'custom-fields' ],
	] );
} );


add_shortcode( 'mailchimp', function ( $atts = [], $contnet = '' ) {
	ob_start();
	?>
	<!-- Begin MailChimp Signup Form -->
	<div id="mc_embed_signup">
		<form
			action="<?= esc_attr( add_query_arg( [
				'u'  => '9b5777bb4451fb83373411d34',
				'id' => 'bf9c92d04a',
				'REGISTERED' => urlencode( date_i18n( 'Y-m-d' ) ),
				'SOURCE' => urlencode( 'takahashifumiki.com' ),
			], 'https://takahashifumiki.us14.list-manage.com/subscribe/post' ) )?>"
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
					<?php foreach ( [
						'出版関連', '編集者', '作家・ライター', 'Web関連', '学生', 'その他'
					] as $index=> $label ) : ?>
						<label for="mce-MMERGE3-<?= $index ?>">
							<input class="uk-radio" type="radio" value="<?= esc_attr( $label ) ?>" name="MMERGE3" id="mce-MMERGE3-<?= $index ?>">
							<?= esc_html( $label ) ?>
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
