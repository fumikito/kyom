/**
 * Dynamic comment form for CloudFlare-cached pages.
 *
 * @package
 */

/* global KyomComments */ // eslint-disable-line no-unused-vars

( function( $ ) {
	const app = $( '#kyom-comment-app' );
	if ( ! app.length ) {
		return;
	}

	const config = window.KyomComments || {};
	const postId = app.data( 'post-id' );
	let authData = null;
	let parentId = 0;

	/**
	 * Check if user has the login cookie (quick check before API call).
	 *
	 * @return {boolean} True if the cookie exists.
	 */
	function hasCookie() {
		return document.cookie.split( ';' ).some( ( c ) => c.trim().startsWith( 'kyom-customer=' ) );
	}

	/**
	 * Fetch auth data from REST API (not cached by CloudFlare).
	 *
	 * @return {Promise<Object>} Auth data with nonce and user info.
	 */
	function fetchAuth() {
		return $.ajax( {
			url: config.restUrl + 'kyom/v1/comment-auth',
			method: 'GET',
			xhrFields: { withCredentials: true },
		} );
	}

	/**
	 * Render the login prompt.
	 */
	function renderLoginPrompt() {
		const html = `
			<div class="kyom-comment-login uk-alert uk-alert-padding" uk-alert>
				<p>
					<a href="${ config.loginUrl }" class="uk-button uk-button-primary uk-button-small">
						${ config.i18n.login }
					</a>
				</p>
				${ config.contactUrl ? `<p class="uk-text-small uk-text-muted"><a href="${ config.contactUrl }">${ config.i18n.contact }</a></p>` : '' }
			</div>`;
		app.html( html );
	}

	/**
	 * Render the comment form.
	 *
	 * @param {Object} auth Auth data from REST API.
	 */
	function renderForm( auth ) {
		const html = `
			<div class="kyom-comment-form">
				<div class="kyom-comment-form-header uk-flex uk-flex-middle uk-margin-small-bottom">
					<img src="${ auth.user_avatar }" alt="" class="uk-comment-avatar uk-margin-small-right" width="40" height="40" />
					<span class="uk-text-bold">${ auth.user_name }</span>
				</div>
				<div class="kyom-comment-reply-info" style="display:none;"></div>
				<textarea class="uk-textarea kyom-comment-textarea" rows="4"
					placeholder="${ config.i18n.placeholder }"></textarea>
				<div class="kyom-comment-form-footer uk-flex uk-flex-between uk-flex-middle uk-margin-small-top">
					<div>
						${ config.contactUrl ? `<a href="${ config.contactUrl }" class="uk-text-small uk-text-muted">${ config.i18n.contact }</a>` : '' }
					</div>
					<button type="button" class="uk-button uk-button-primary uk-button-small kyom-comment-submit">
						${ config.i18n.submit }
					</button>
				</div>
				<div class="kyom-comment-status" style="display:none;"></div>
			</div>`;
		app.html( html );
	}

	/**
	 * Set reply target.
	 *
	 * @param {number} commentId Parent comment ID.
	 * @param {string} author    Parent comment author name.
	 */
	function setReplyTarget( commentId, author ) {
		parentId = commentId;
		const info = app.find( '.kyom-comment-reply-info' );
		if ( commentId ) {
			info.html(
				`<span class="comment-reply-to">
					<a href="#div-comment-${ commentId }">
						<span uk-icon="icon: reply; ratio: 0.8"></span>
						${ author }
					</a>
					<button type="button" class="uk-button uk-button-text uk-margin-small-left kyom-cancel-reply">
						${ config.i18n.cancel }
					</button>
				</span>`
			).show();
		} else {
			info.empty().hide();
		}
		app.find( '.kyom-comment-textarea' ).focus();
	}

	/**
	 * Submit a comment via REST API.
	 */
	function submitComment() {
		const textarea = app.find( '.kyom-comment-textarea' );
		const content = textarea.val().trim();
		if ( ! content ) {
			return;
		}

		const btn = app.find( '.kyom-comment-submit' );
		const status = app.find( '.kyom-comment-status' );
		btn.prop( 'disabled', true ).text( config.i18n.sending );
		status.hide();

		const data = {
			post: postId,
			content,
		};
		if ( parentId ) {
			data.parent = parentId;
		}

		$.ajax( {
			url: config.restUrl + 'wp/v2/comments',
			method: 'POST',
			data,
			beforeSend( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', authData.nonce );
			},
		} )
			.done( function( comment ) {
				textarea.val( '' );
				setReplyTarget( 0, '' );
				const isPending = comment.status === 'hold';
				if ( isPending ) {
					status.html( `<div class="uk-alert-warning uk-alert-padding uk-margin-small-top" uk-alert>${ config.i18n.moderation }</div>` ).show();
				} else {
					status.html( `<div class="uk-alert-success uk-alert-padding uk-margin-small-top" uk-alert>${ config.i18n.success }</div>` ).show();
				}
				appendComment( comment );
				setTimeout( () => status.fadeOut(), 3000 );
			} )
			.fail( function( xhr ) {
				let msg = config.i18n.error;
				if ( xhr.responseJSON && xhr.responseJSON.message ) {
					msg = xhr.responseJSON.message;
				}
				status.html( `<div class="uk-alert-danger uk-alert-padding uk-margin-small-top" uk-alert>${ msg }</div>` ).show();
			} )
			.always( function() {
				btn.prop( 'disabled', false ).text( config.i18n.submit );
			} );
	}

	/**
	 * Append a new comment to the DOM.
	 *
	 * @param {Object} comment REST API comment response.
	 */
	function appendComment( comment ) {
		const isPrimary = comment.is_primary ? ' uk-comment-primary' : '';
		const isPending = comment.status === 'hold';
		const avatarUrl = comment.author_avatar_urls?.[ '96' ] || comment.author_avatar_urls?.[ '48' ] || '';
		const parentHtml = comment.parent ? buildReplyToHtml( comment.parent ) : '';
		const pendingHtml = isPending
			? `<p class="comment-awaiting-moderation uk-alert-warning" uk-alert>${ config.i18n.moderation }</p>`
			: '';

		const html = `
			<li>
				<article id="div-comment-${ comment.id }" class="uk-comment${ isPrimary } comment">
					<header class="uk-comment-header uk-position-relative">
						<div class="uk-grid-medium uk-flex-middle" uk-grid>
							<div class="uk-width-auto">
								<img src="${ avatarUrl }" alt="" class="uk-comment-avatar" width="80" height="80" />
							</div>
							<div class="uk-width-expand">
								<h4 class="uk-comment-title uk-margin-remove vcard">${ comment.author_name }</h4>
								<p class="uk-comment-meta uk-margin-remove-top">
									<time>${ new Date().toLocaleDateString() }</time>
								</p>
								${ pendingHtml }
							</div>
						</div>
					</header>
					${ parentHtml }
					<div class="uk-comment-body">${ comment.content.rendered }</div>
				</article>
			</li>`;

		let list = $( '.uk-comment-list' );
		if ( ! list.length ) {
			list = $( '<ul class="uk-comment-list"></ul>' );
			app.before( list );
		}
		const $el = $( html ).appendTo( list );
		$el[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center' } );
		$el.css( 'opacity', 0 ).animate( { opacity: 1 }, 500 );
	}

	/**
	 * Build reply-to HTML for a parent comment.
	 *
	 * @param {number} commentParentId Parent comment ID.
	 * @return {string} HTML string for reply-to link.
	 */
	function buildReplyToHtml( commentParentId ) {
		const parentEl = document.getElementById( 'div-comment-' + commentParentId );
		if ( ! parentEl ) {
			return '';
		}
		const authorEl = parentEl.querySelector( '.uk-comment-title' );
		const authorName = authorEl ? authorEl.textContent.trim() : '';
		return `<div class="comment-reply-to">
			<a href="#div-comment-${ commentParentId }">
				<span uk-icon="icon: reply; ratio: 0.8"></span>
				${ authorName }
			</a>
		</div>`;
	}

	// --- Initialize ---

	// Show loading state.
	app.html( '<div class="uk-text-center uk-padding-small"><span uk-spinner></span></div>' );

	if ( ! hasCookie() ) {
		// No cookie = definitely not logged in.
		renderLoginPrompt();
		return;
	}

	// Has cookie, verify with API.
	fetchAuth()
		.done( function( auth ) {
			if ( ! auth.logged_in ) {
				renderLoginPrompt();
				return;
			}
			authData = auth;
			renderForm( auth );
		} )
		.fail( function() {
			renderLoginPrompt();
		} );

	// --- Event handlers ---

	// Submit button.
	app.on( 'click', '.kyom-comment-submit', submitComment );

	// Ctrl+Enter to submit.
	app.on( 'keydown', '.kyom-comment-textarea', function( e ) {
		if ( ( e.ctrlKey || e.metaKey ) && e.key === 'Enter' ) {
			e.preventDefault();
			submitComment();
		}
	} );

	// Cancel reply.
	app.on( 'click', '.kyom-cancel-reply', function() {
		setReplyTarget( 0, '' );
	} );

	// Reply buttons (delegated to document since they're outside #kyom-comment-app).
	$( document ).on( 'click', '.kyom-reply-btn', function() {
		const btn = $( this );
		setReplyTarget( btn.data( 'comment-id' ), btn.data( 'comment-author' ) );
	} );
}( jQuery ) );
