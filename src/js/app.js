/**
 * Description
 */

/*global UIKit: false*/

( function( $ ) {
  'use strict';

  // Check this is night shift.
  const day = new Date();
  const hour  = day.getHours();
  const month = day.getMonth() + 1;
  let isNight = 19 < hour || 7 > hour;
  isNight = false;
  const className = isNight ? 'under-the-moon' : 'daylight';
  $( 'body' ).addClass( className );

  // Indent paragraph.
  $( document ).ready( function() {
    $( '.entry-content p' ).each( function( i, p ) {
      if ( /^[「（—『【　]/.test( $( p ).text() ) ) {
        $( p ).addClass( 'no-indent' );
      }
    });
  });

  // Call out.
  $( document ).ready( () => {
    $( '.kyom-callout-button' ).click( function() {
      const $callout = $( this ).parents( '.kyom-callout' );
      $callout.addClass( 'fade' );
      setTimeout( ()=>{
        $( document ).trigger( 'callout-closed', [ $callout.attr( 'data-slug' ) ]);
        $callout.remove();
      }, 500 );
    });
  });

  // Pager.
  $( document ).ready( () => {
    $( '.pager' ).each( ( index, pager ) => {
      $( pager ).find( '.pager-link' ).fitHeight();
    });
  });

  // Light box.
  $( document ).ready( () => {
    let lightBox  = null;
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
      let hrefChunk = href.split( '/' );
      let caption = hrefChunk[ hrefChunk.length - 1 ];
      let insideImg = $( link ).find( 'img' );
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
        caption: caption
      };
      lightBoxItems.push( item );
      const thisIndex = itemIndex + 0;
      $( link ).click( function( e ) {
        e.preventDefault();
        lightBox.show( thisIndex );
      });
      itemIndex++;
    });
    if ( lightBoxItems ) {
      lightBox = UIkit.lightboxPanel({
        items: lightBoxItems
      });
    }
  });

  // Open mail form.
  $( document ).ready( () => {
    $( '.section-newsletter #mce-EMAIL' ).focus( function() {
      $( '.section-newsletter' ).addClass( 'toggle' );
    });
  });
}( jQuery ) );
