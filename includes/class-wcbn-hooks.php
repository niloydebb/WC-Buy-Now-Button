<?php
/**
 * Hooks — auto-injects Buy Now button on WooCommerce template hooks.
 * Zero configuration. Product is detected automatically from page context.
 *
 * @package WC_Buy_Now_Button
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCBN_Hooks {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $settings = WCBN_Settings::instance();

        if ( 'yes' === $settings->get( 'show_on_single', 'yes' ) ) {
            // Fires inside the single product add-to-cart form — $product global is always set here.
            add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'render_single' ), 15 );
        }

        if ( 'yes' === $settings->get( 'show_on_archive', 'yes' ) ) {
            // Fires inside the product loop — $product global is always set here.
            add_action( 'woocommerce_after_shop_loop_item', array( $this, 'render_archive' ), 15 );
        }
    }

    public function render_single() {
        global $product;
        if ( ! $product instanceof WC_Product ) {
            return;
        }
        if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
            return;
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo WCBN_Renderer::button( $product, array( 'class' => 'wcbn-single-product' ) );
    }

    public function render_archive() {
        global $product;
        if ( ! $product instanceof WC_Product ) {
            return;
        }
        // Skip non-simple products on archives — no variation UI available.
        if ( ! $product->is_type( 'simple' ) ) {
            return;
        }
        if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
            return;
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<div class="wcbn-loop-button">' . WCBN_Renderer::button( $product, array( 'class' => 'wcbn-archive-product' ) ) . '</div>';
    }
}
