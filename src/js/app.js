/*!
 * @handle kyom
 * @deps kyom-netabare,kyom-fit-height,uikit-icon
 * @strategy defer
 */

const $ = jQuery;

// Check this is night shift.
const day = new Date();
const hour = day.getHours();
let isNight = 19 < hour || 7 > hour;
isNight = false;
const className = isNight ? 'under-the-moon' : 'daylight';
$( 'body' ).addClass( className );

// Indent paragraph.
$( '.entry-content p:not([class])' ).each( function( i, p ) {
	// Avoid EM-space catches eslint.
	// eslint-disable-next-line
	if ( /^[「（—『【　]/.test( $( p ).text() ) ) {
		$( p ).addClass( 'no-indent' );
	}
} );

// Call out.
const $callouts = $( '.kyom-callout-button' );
let callOutLengths = $callouts.length;
$callouts.click( function() {
	const $callout = $( this ).parents( '.kyom-callout' );
	$callout.addClass( 'fade' );
	callOutLengths--;
	if ( 1 > callOutLengths ) {
		$( 'body' ).removeClass( 'has-callouts' );
	}
	setTimeout( () => {
		$( document ).trigger( 'callout-closed', [ $callout.attr( 'data-slug' ) ] );
		$callout.remove();
	}, 500 );
} );

// Pager.
$( '.pager' ).each( ( index, pager ) => {
	$( pager ).find( '.pager-link' ).fitHeight();
} );

// Light box.
let lightBox = null;
let itemIndex = 0;
const lightBoxItems = [];
$( '.entry-content a[href]' ).each( function( index, link ) {
	// Test this is image link.
	const href = $( link ).attr( 'href' );
	if ( ! /\.(jpe?g|gif|png)$/i.test( href ) ) {
		return true;
	}

	// If in jetpack gallery, skip.
	if ( $( link ).parents( '.tiled-gallery' ).length ) {
		return true;
	}

	// Generate caption.
	const hrefChunk = href.split( '/' );
	let caption = hrefChunk[ hrefChunk.length - 1 ];
	const insideImg = $( link ).find( 'img' );
	if ( insideImg.length && insideImg.attr( 'alt' ) ) {
		caption = insideImg.attr( 'alt' );
	}
	if ( insideImg.length && insideImg.attr( 'data-image-title' ) ) {
		caption = insideImg.attr( 'data-image-title' );
	}
	if ( $( link ).next( '.wp-caption' ).length ) {
		caption = $( link ).next( '.wp-caption' ).text();
	}

	// Add link to light box and register event listener.
	const item = {
		source: href,
		caption,
	};
	lightBoxItems.push( item );
	const thisIndex = itemIndex + 0;
	$( link ).click( function( e ) {
		e.preventDefault();
		lightBox.show( thisIndex );
	} );
	itemIndex++;
} );

if ( lightBoxItems.length ) {
	lightBox = UIkit.lightboxPanel( {
		items: lightBoxItems,
	} );
}

// Dropdown
$( '.kyom-navigations-list > li.menu-item-has-children > a ' ).click( function( e ) {
	e.preventDefault();
	$( this ).parent( 'li' ).toggleClass( 'active-menu' );
} );

// Open mail form.
$( '.section-newsletter #mce-EMAIL' ).focus( function() {
	$( '.section-newsletter' ).addClass( 'toggle' );
} );
