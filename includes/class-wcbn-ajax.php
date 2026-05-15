<?php
/**
 * AJAX handler — processes the "Buy Now" action.
 *
 * Handles both logged-in and guest users. Adds the product to the cart
 * (clearing other items first so the shopper goes straight to checkout),
 * then returns the redirect URL.
 *
 * @package WC_Buy_Now_Button
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WCBN_Ajax
 */
class WCBN_Ajax {

    /** @var WCBN_Ajax|null */
    private static $instance = null;

    /**
     * @return WCBN_Ajax
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'wp_ajax_wcbn_buy_now',        array( $this, 'handle' ) );
        add_action( 'wp_ajax_nopriv_wcbn_buy_now', array( $this, 'handle' ) );
    }

    /**
     * Process the AJAX request.
     */
    public function handle() {
        // 1. Verify nonce.
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'wcbn_nonce' ) ) {
            wp_send_json_error(
                array( 'message' => __( 'Security check failed. Please refresh and try again.', 'wc-buy-now-button' ) ),
                403
            );
        }

        // 2. Validate product ID.
        $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        if ( ! $product_id ) {
            wp_send_json_error(
                array( 'message' => __( 'Invalid product.', 'wc-buy-now-button' ) ),
                400
            );
        }

        $product = wc_get_product( $product_id );
        if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
            wp_send_json_error(
                array( 'message' => __( 'This product is not available for purchase.', 'wc-buy-now-button' ) ),
                400
            );
        }

        // 3. Validate quantity.
        $quantity   = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
        $quantity   = max( 1, $quantity );

        // 4. Validate redirect target.
        $redirect_to = isset( $_POST['redirect'] ) ? sanitize_text_field( wp_unslash( $_POST['redirect'] ) ) : 'checkout';
        $redirect_to = in_array( $redirect_to, array( 'checkout', 'cart' ), true ) ? $redirect_to : 'checkout';

        // 5. Variation support.
        $variation_id   = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
        $variation_data = array();

        if ( $variation_id ) {
            $variation = wc_get_product( $variation_id );
            if ( $variation && $variation->is_type( 'variation' ) ) {
                $variation_data = $variation->get_variation_attributes();
            }
        }

        // Allow other plugins to hook in before we touch the cart.
        do_action( 'wcbn_before_add_to_cart', $product_id, $quantity, $variation_id );

        // 6. Add to cart (WooCommerce handles session / cookie automatically).
        $cart_item_key = WC()->cart->add_to_cart(
            $product_id,
            $quantity,
            $variation_id,
            $variation_data,
            array( 'wcbn_buy_now' => true )
        );

        if ( false === $cart_item_key ) {
            // WooCommerce already added a notice; let's capture it.
            $notices = wc_get_notices( 'error' );
            $message = ! empty( $notices )
                ? wp_strip_all_tags( $notices[0]['notice'] )
                : __( 'Could not add the product to cart. Please try again.', 'wc-buy-now-button' );

            wp_send_json_error( array( 'message' => $message ), 400 );
        }

        do_action( 'wcbn_after_add_to_cart', $product_id, $quantity, $variation_id, $cart_item_key );

        // 7. Build redirect URL.
        $url = ( 'cart' === $redirect_to )
            ? wc_get_cart_url()
            : wc_get_checkout_url();

        /**
         * Filter the redirect URL.
         *
         * @param string $url         Redirect URL.
         * @param int    $product_id  Product ID.
         * @param string $redirect_to 'checkout' or 'cart'.
         */
        $url = apply_filters( 'wcbn_redirect_url', $url, $product_id, $redirect_to );

        wp_send_json_success( array( 'redirect' => esc_url_raw( $url ) ) );
    }
}
