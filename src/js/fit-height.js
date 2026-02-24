/*!
 * @handle kyom-fit-height
 * @deps jquery
 * @strategy defer
 */

const $ = jQuery;

const fitHeight = function( $elem ) {
	let maximum = 0;
	$elem.each( function( index, el ) {
		maximum = Math.max( $( el ).height(), maximum );
	} );
	$elem.css( 'height', maximum + 'px' );
};

$.fn.fitHeight = function() {
	const self = this;
	fitHeight( self );
	let timer = null;
	$( window ).resize( function() {
		if ( timer ) {
			clearTimeout( timer );
		}
		self.css( 'height', 'auto' );
		timer = setTimeout( function() {
			fitHeight( self );
		}, 10 );
	} );
};
