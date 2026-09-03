/*!
 * @handle kyom-shorts
 * @strategy defer
 */

/**
 * 画面右下に出す最新ショートのカード。
 *
 * ページは CloudFlare でキャッシュされるので、閉じたかどうかをサーバー側で
 * 判断できない。カードは hidden で描画しておき、閉じられていない場合にだけ
 * ここで表示する。閉じた記録は動画IDで持つため、新しいショートが投稿されれば
 * また出る。
 */

const STORAGE_KEY = 'kyomShortsDismissed';

/**
 * この動画のカードが閉じられているか。
 *
 * @param {string} id 動画ID
 * @return {boolean} 閉じられていれば true
 */
function isDismissed( id ) {
	try {
		return window.localStorage.getItem( STORAGE_KEY ) === id;
	} catch {
		// プライベートブラウズなどで読めないことがある。その場合は出す。
		return false;
	}
}

/**
 * 閉じたことを覚える。
 *
 * @param {string} id 動画ID
 */
function remember( id ) {
	try {
		window.localStorage.setItem( STORAGE_KEY, id );
	} catch {
		// 書けなくても閉じる動作自体は成立させる。
	}
}

document.addEventListener( 'DOMContentLoaded', () => {
	const card = document.querySelector( '.kyom-shorts[data-kyom-short]' );
	if ( ! card ) {
		return;
	}
	const id = card.dataset.kyomShort;
	if ( isDismissed( id ) ) {
		card.remove();
		return;
	}
	card.hidden = false;
	// 読み込み直後に飛び込ませない。少し遅らせてから滑り込ませる。
	window.setTimeout( () => card.classList.add( 'is-visible' ), 1200 );

	const close = card.querySelector( '.kyom-shorts-close' );
	if ( close ) {
		close.addEventListener( 'click', () => {
			remember( id );
			card.classList.remove( 'is-visible' );
			window.setTimeout( () => card.remove(), 400 );
		} );
	}
} );
