/**
 * WC Buy Now Button — Admin JS
 * Handles the click-to-copy Product ID feature on the Products list page.
 *
 * @package WC_Buy_Now_Button
 */
/* global jQuery */
( function ( $ ) {
    'use strict';

    $( document ).on( 'click', '.wcbn-copy-id', function () {
        var $el = $( this );
        var id  = $el.data( 'id' );

        if ( ! id ) { return; }

        if ( navigator.clipboard && navigator.clipboard.writeText ) {
            navigator.clipboard.writeText( String( id ) ).then( function () {
                $el.css( 'background', '#d4edda' );
                setTimeout( function () { $el.css( 'background', '#f0f0f0' ); }, 1500 );
            } );
        } else {
            // Fallback for older browsers.
            var $temp = $( '<input>' );
            $( 'body' ).append( $temp );
            $temp.val( String( id ) ).select();
            document.execCommand( 'copy' );
            $temp.remove();
            $el.css( 'background', '#d4edda' );
            setTimeout( function () { $el.css( 'background', '#f0f0f0' ); }, 1500 );
        }
    } );

} )( jQuery );
