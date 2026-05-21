/**
 * WC Buy Now Button — front-end script
 * Works in Elementor editor, Elementor preview, and normal frontend.
 *
 * @package WC_Buy_Now_Button
 */
/* global wcbnData, jQuery */
( function ( $, data ) {
    'use strict';

    if ( ! data || ! data.ajaxUrl ) { return; }

    // ------------------------------------------------------------------
    // Hover colour for shortcode/hook buttons (data-hover-color attr).
    // Elementor buttons use pure CSS :hover selectors — skip those.
    // ------------------------------------------------------------------
    $( document ).on( 'mouseenter', '.wcbn-buy-now-btn:not(.wcbn-elementor)', function () {
        var $btn = $( this );
        var hoverColor = $btn.data( 'hover-color' );
        if ( hoverColor ) {
            $btn.data( 'orig-bg', $btn.css( 'background-color' ) );
            $btn.css( 'background-color', hoverColor );
        }
    } ).on( 'mouseleave', '.wcbn-buy-now-btn:not(.wcbn-elementor)', function () {
        var origBg = $( this ).data( 'orig-bg' );
        if ( origBg ) { $( this ).css( 'background-color', origBg ); }
    } );

    // ------------------------------------------------------------------
    // Click handler
    // ------------------------------------------------------------------
    $( document ).on( 'click', '.wcbn-buy-now-btn', function ( e ) {
        e.preventDefault();
        e.stopPropagation();

        var $btn = $( this );
        if ( $btn.hasClass( 'wcbn-loading' ) ) { return; }

        var productId   = parseInt( $btn.data( 'product-id' ), 10 );
        var variationId = parseInt( $btn.data( 'variation-id' ), 10 ) || 0;
        var quantity    = parseInt( $btn.data( 'quantity' ), 10 ) || 1;
        var redirect    = $btn.data( 'redirect' ) || 'checkout';
        var nonce       = $btn.data( 'nonce' ) || data.nonce;

        if ( ! productId ) { return; }

        // Read selected variation from WooCommerce single-product form.
        if ( ! variationId ) {
            var $form = $btn.closest( 'form.cart' );
            var $varInput = $form.find( 'input[name="variation_id"]' );
            if ( $varInput.length ) {
                var pv = parseInt( $varInput.val(), 10 );
                if ( pv > 0 ) { variationId = pv; }
            }
        }

        // Sync quantity with WooCommerce qty input if present.
        var $qtyField = $btn.closest( 'form.cart' ).find( '.qty' );
        if ( $qtyField.length ) {
            var pq = parseInt( $qtyField.val(), 10 );
            if ( pq > 0 ) { quantity = pq; }
        }

        setLoading( $btn, true );
        clearError( $btn );

        $.ajax( {
            url:    data.ajaxUrl,
            method: 'POST',
            data:   {
                action:       'wcbn_buy_now',
                product_id:   productId,
                variation_id: variationId,
                quantity:     quantity,
                redirect:     redirect,
                nonce:        nonce
            },
            success: function ( response ) {
                if ( response && response.success && response.data && response.data.redirect ) {
                    $( document.body ).trigger( 'wc_fragment_refresh' );
                    window.location.href = response.data.redirect;
                } else {
                    var msg = ( response && response.data && response.data.message )
                        ? response.data.message : data.errorText;
                    setLoading( $btn, false );
                    showError( $btn, msg );
                }
            },
            error: function ( xhr ) {
                var msg = data.errorText;
                try {
                    var parsed = JSON.parse( xhr.responseText );
                    if ( parsed && parsed.data && parsed.data.message ) { msg = parsed.data.message; }
                } catch (ex) {}
                setLoading( $btn, false );
                showError( $btn, msg );
            }
        } );
    } );

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------
    function setLoading( $btn, on ) {
        var $text    = $btn.find( '.wcbn-btn-text' );
        var $spinner = $btn.find( '.wcbn-spinner' );
        if ( on ) {
            $btn.addClass( 'wcbn-loading' ).prop( 'disabled', true ).attr( 'aria-busy', 'true' );
            $text.text( data.loadingText );
            $spinner.show();
        } else {
            $btn.removeClass( 'wcbn-loading' ).prop( 'disabled', false ).removeAttr( 'aria-busy' );
            var orig = $btn.data( 'original-text' );
            if ( orig ) { $text.text( orig ); }
            $spinner.hide();
        }
    }

    function clearError( $btn ) {
        $btn.closest( '.wcbn-wrap' ).find( '.wcbn-error-notice' ).remove();
    }

    function showError( $btn, message ) {
        clearError( $btn );
        $( '<p class="wcbn-error-notice" role="alert"></p>' )
            .text( message )
            .appendTo( $btn.closest( '.wcbn-wrap' ) );
    }

} )( jQuery, typeof wcbnData !== 'undefined' ? wcbnData : null );
