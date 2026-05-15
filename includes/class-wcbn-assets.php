<?php
/**
 * Assets — registers and enqueues styles and scripts.
 *
 * @package WC_Buy_Now_Button
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCBN_Assets {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_enqueue_scripts',        array( $this, 'register_frontend' ) );
        add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_frontend' ) );
        add_action( 'elementor/preview/enqueue_scripts',      array( $this, 'enqueue_frontend' ) );
        add_action( 'wcbn_render_assets',        array( $this, 'enqueue_frontend' ) );
    }

    /**
     * Register assets on every frontend request.
     * Enqueue immediately on known WooCommerce pages.
     */
    public function register_frontend() {
        wp_register_style(
            'wcbn-style',
            WCBN_PLUGIN_URL . 'public/css/wcbn-style.css',
            array(),
            WCBN_VERSION
        );

        wp_register_script(
            'wcbn-script',
            WCBN_PLUGIN_URL . 'public/js/wcbn-script.js',
            array( 'jquery' ),
            WCBN_VERSION,
            true
        );

        if ( $this->is_wc_page() ) {
            $this->enqueue_frontend();
        }
    }

    /**
     * Enqueue and localise. Safe to call multiple times.
     */
    public function enqueue_frontend() {
        wp_enqueue_style( 'wcbn-style' );
        wp_enqueue_script( 'wcbn-script' );

        wp_localize_script( 'wcbn-script', 'wcbnData', array(
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'wcbn_nonce' ),
            'loadingText' => __( 'Processing...', 'wc-buy-now-button' ),
            'errorText'   => __( 'Something went wrong. Please try again.', 'wc-buy-now-button' ),
        ) );
    }

    /**
     * True on WooCommerce pages — proactive load, not the only load path.
     */
    private function is_wc_page() {
        if ( is_shop() || is_product() || is_product_category() || is_product_tag() || is_cart() || is_checkout() ) {
            return true;
        }
        global $post;
        if ( $post instanceof WP_Post && has_shortcode( $post->post_content, 'buy_now_button' ) ) {
            return true;
        }
        return (bool) apply_filters( 'wcbn_load_assets', false );
    }
}
