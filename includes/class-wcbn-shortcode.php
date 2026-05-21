<?php
/**
 * Shortcode — [buy_now_button]
 *
 * Just drop this shortcode anywhere. The plugin automatically detects
 * the product from the page context — no product ID required.
 *
 * Works on:
 *  - Single product pages
 *  - Product loops / archive pages
 *  - Any page where a WooCommerce product is in the global $product
 *  - Custom templates with a product in the query
 *
 * Optional overrides (all have sensible defaults from plugin settings):
 *   [buy_now_button text="Order Now" redirect="cart" quantity="2"]
 *
 * @package WC_Buy_Now_Button
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCBN_Shortcode {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'buy_now_button', array( $this, 'render' ) );
    }

    /**
     * Render the button. Product is detected automatically from page context.
     */
    public function render( $atts, $content = '' ) {

        // Always ensure CSS + JS load regardless of where shortcode appears.
        do_action( 'wcbn_render_assets' );

        $settings = WCBN_Settings::instance();

        if ( ! is_array( $atts ) ) {
            $atts = array();
        }

        $atts = shortcode_atts(
            array(
                'text'         => $settings->get( 'button_text', __( 'Buy Now', 'wc-buy-now-button' ) ),
                'quantity'     => $settings->get( 'quantity', 1 ),
                'redirect'     => $settings->get( 'redirect_url', 'checkout' ),
                'class'        => '',
                'bg_color'     => '',
                'text_color'   => '',
                'hover_color'  => '',
                'border_radius'=> '',
                'font_size'    => '',
            ),
            $atts,
            'buy_now_button'
        );

        // Auto-detect product from page context — no user input needed.
        $product = $this->detect_product();

        if ( ! $product ) {
            return $this->error(
                __( 'Buy Now Button: Could not detect a product on this page. Place this shortcode on a single product page or inside a product loop.', 'wc-buy-now-button' )
            );
        }

        if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
            return '';
        }

        return WCBN_Renderer::button( $product, $atts );
    }

    /**
     * Detect the current product from WordPress/WooCommerce context.
     * Tries every possible source in order of reliability.
     *
     * @return WC_Product|false
     */
    private function detect_product() {

        // 1. WooCommerce global $product (set on single product pages & loops).
        global $product;
        if ( $product instanceof WC_Product ) {
            return $product;
        }

        // 2. Current post is a product post type.
        $post_id = get_the_ID();
        if ( $post_id ) {
            $post = get_post( $post_id );
            if ( $post && 'product' === $post->post_type ) {
                $p = wc_get_product( $post_id );
                if ( $p ) {
                    return $p;
                }
            }
        }

        // 3. Single product page conditional (belt-and-suspenders).
        if ( is_product() ) {
            $p = wc_get_product( get_queried_object_id() );
            if ( $p ) {
                return $p;
            }
        }

        // 4. WooCommerce loop: try the global post inside a product loop.
        global $post;
        if ( isset( $post ) && $post instanceof WP_Post && 'product' === $post->post_type ) {
            $p = wc_get_product( $post->ID );
            if ( $p ) {
                return $p;
            }
        }

        return false;
    }

    /**
     * Return an error — visible yellow notice in WP_DEBUG, silent comment in production.
     *
     * @param  string $message
     * @return string
     */
    private function error( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            return '<p style="background:#fff3cd;border:1px solid #ffc107;padding:10px 14px;'
                . 'border-radius:4px;color:#856404;font-size:14px;margin:8px 0;">'
                . '<strong>&#9888; WC Buy Now Button:</strong> '
                . esc_html( $message )
                . '</p>';
        }
        return '<!-- WC Buy Now Button: ' . esc_html( $message ) . ' -->';
    }
}
