/**
 * Netabare feature
 *
 * @package kyom
 */

/* global Netabare:false */

const $ = jQuery;

$( '.netabare' ).each( ( index, section ) => {
	$( section ).wrap( '<div class="netabare-wrap"></div>' ).after( `<a href="#" class="netabare-opener">${ Netabare.label }</a>` );
});

$( '.netabare-opener' ).click( function( e ) {
	e.preventDefault();
	$( this ).prev().addClass( 'netabare-opened' );
	$( this ).remove();
});
