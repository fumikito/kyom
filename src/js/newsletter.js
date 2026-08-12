/*!
 * @handle kyom-newsletter
 * @strategy defer
 */

/**
 * Newsletter subscription form for CloudFlare-cached pages.
 *
 * ページがキャッシュされるため、HTML に nonce や描画時刻を埋めても
 * 全訪問者に同じ値が配られてしまう。送信はここから REST に投げ、
 * 「描画から送信までの経過時間」もクライアント側で測って送る。
 *
 * @package
 */

( function() {
	const config = window.KyomNewsletter || {};
	const forms = document.querySelectorAll( '.kyom-newsletter-form' );
	if ( ! forms.length || ! config.restUrl ) {
		return;
	}

	/**
	 * Attach submit handler to a single form.
	 *
	 * @param {HTMLFormElement} form Target form.
	 */
	function initForm( form ) {
		// 人間なら入力に時間がかかる。ボットは即座に POST する。
		const renderedAt = Date.now();
		const message = form.querySelector( '.kyom-newsletter-message' );
		const button = form.querySelector( 'button[type="submit"]' );

		/**
		 * Display a message to the user.
		 *
		 * @param {string}  text    Message body.
		 * @param {boolean} isError Render as error if true.
		 */
		function setMessage( text, isError ) {
			if ( ! message ) {
				return;
			}
			message.textContent = text;
			message.classList.toggle( 'is-error', !! isError );
		}

		form.addEventListener( 'submit', async function( event ) {
			event.preventDefault();
			const data = new FormData( form );
			const email = ( data.get( 'email' ) || '' ).trim();
			const name = ( data.get( 'name' ) || '' ).trim();
			if ( ! email || ! name ) {
				setMessage( config.i18n.invalid, true );
				return;
			}
			button.disabled = true;
			button.textContent = config.i18n.sending;
			setMessage( '', false );
			try {
				const response = await fetch( config.restUrl, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify( {
						email,
						name,
						company: data.get( 'company' ) || '',
						job: data.get( 'job' ) || '',
						website: data.get( 'website' ) || '',
						elapsed: Date.now() - renderedAt,
					} ),
				} );
				const body = await response.json();
				if ( ! response.ok ) {
					const fallback = 429 === response.status ? config.i18n.tooMany : config.i18n.error;
					setMessage( body && body.message ? body.message : fallback, true );
					return;
				}
				form.reset();
				setMessage( body.message || config.i18n.success, false );
			} catch ( error ) {
				setMessage( config.i18n.error, true );
			} finally {
				button.disabled = false;
				button.textContent = config.i18n.submit;
			}
		} );
	}

	forms.forEach( initForm );
}() );
